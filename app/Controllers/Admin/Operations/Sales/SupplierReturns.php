<?php

namespace App\Controllers\Admin\Operations\Sales;

use App\Controllers\BaseController;

class SupplierReturns extends BaseController
{
    public function supplier_returns()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();

        $search = trim((string) ($request->getGet('search') ?? ''));
        $status = $request->getGet('status') ?? 'pending';

        $page = (int) ($request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $applyFilters = function($builder) use ($search, $status) {
            if ($status !== 'all') $builder->where('pr.status', $status);
            if ($search !== '') {
                $builder->groupStart()
                    ->like('s.name', $search)
                    ->orLike('po.po_number', $search)
                    ->groupEnd();
            }
            return $builder;
        };

        $countBuilder = $db->table('procurement_returns as pr')
            ->join('purchase_orders as po', 'po.po_id = pr.po_id')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id');
        $applyFilters($countBuilder);
        $totalRows = $countBuilder->countAllResults();

        $builder = $db->table('procurement_returns as pr');
        $builder->select('pr.*, po.po_number, s.name as supplier_name, p.name as product_name, u.full_name as staff');
        $builder->join('purchase_orders as po', 'po.po_id = pr.po_id');
        $builder->join('suppliers as s', 's.supplier_id = po.supplier_id');
        $builder->join('products as p', 'p.product_id = pr.product_id');
        $builder->join('users as u', 'u.user_id = pr.processed_by');
        $applyFilters($builder);
        $builder->orderBy('pr.created_at', 'DESC');
        $builder->limit($perPage, $offset);
        $data['returns'] = $builder->get()->getResultArray();

        $data['received_pos'] = $db->table('purchase_orders as po')
            ->select('po.po_id, po.po_number, s.name as sname')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id')
            ->whereIn('po.status', ['received', 'partial'])
            ->get()->getResultArray();

        $data['total_rows']    = $totalRows;
        $data['current_page']  = $page;
        $data['per_page']      = $perPage;
        $data['total_pages']   = max(1, (int) ceil($totalRows / $perPage));
        $data['active_status'] = $status;
        $data['search']        = $search;

        $data['title'] = "Supplier Returns";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "supplier-returns";
        return view('pages/admin/operations/procurement/supplier_returns', $data);
    }

    public function get_po_items_for_return($poId)
    {
        $db = \Config\Database::connect();
        $items = $db->table('purchase_order_items as poi')
            ->select("poi.product_id, poi.unit_cost, p.name, poi.qty_received,
                (SELECT ib.batch_id FROM inventory_batches ib WHERE ib.po_id = poi.po_id AND ib.product_id = poi.product_id ORDER BY ib.received_at DESC LIMIT 1) as batch_id")
            ->join('products as p', 'p.product_id = poi.product_id')
            ->where('poi.po_id', $poId)
            ->where('poi.qty_received >', 0)
            ->get()->getResultArray();
        return $this->response->setJSON($items);
    }

    public function save_supplier_return()
    {
        $db = \Config\Database::connect();
        $session = session();

        $po_id      = $this->request->getPost('po_id');
        $product_id = $this->request->getPost('product_id');
        $batch_id   = $this->request->getPost('batch_id');
        $qty        = (int) $this->request->getPost('qty');
        $notes      = trim((string) $this->request->getPost('notes'));
        $creditNote = trim((string) $this->request->getPost('credit_note_number'));
        $refund     = $this->request->getPost('refund_amount');

        if (empty($po_id) || empty($product_id) || $qty <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please complete all required fields.');
        }

        $db->table('procurement_returns')->insert([
            'po_id'              => $po_id,
            'product_id'         => $product_id,
            'batch_id'           => $batch_id ?: null,
            'quantity'           => $qty,
            'reason'             => $notes,
            'credit_note_number' => $creditNote !== '' ? $creditNote : null,
            'refund_amount'      => $refund !== '' ? $refund : null,
            'status'             => 'pending',
            'processed_by'       => $session->get('user_id') ?? 1,
        ]);

        return redirect()->to('admin/procurement/supplier-returns')->with('success', 'Supplier return request submitted for approval.');
    }

    public function approve_supplier_return($id)
    {
        $db = \Config\Database::connect();
        $ret = $db->table('procurement_returns')->where('return_id', $id)->get()->getRow();
        if (!$ret || $ret->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending returns can be approved.');
        }

        $db->transStart();
        $db->table('procurement_returns')->where('return_id', $id)->update([
            'status'      => 'approved',
            'resolved_by' => session()->get('user_id') ?? 1,
            'resolved_at' => date('Y-m-d H:i:s')
        ]);

        if ($ret->batch_id) {
            $db->table('inventory_batches')->where('batch_id', $ret->batch_id)
                ->set('quantity_avail', "GREATEST(quantity_avail - {$ret->quantity}, 0)", false)->update();

            $db->table('stock_movements')->insert([
                'product_id'     => $ret->product_id,
                'batch_id'       => $ret->batch_id,
                'movement_type'  => 'outbound',
                'quantity'       => $ret->quantity,
                'reference_id'   => $ret->po_id,
                'reference_type' => 'supplier_return',
                'scanned_by'     => session()->get('user_id') ?? 1,
                'reason'         => 'Returned to supplier' . ($ret->credit_note_number ? ' — Credit Note: ' . $ret->credit_note_number : '')
            ]);
        }
        $db->transComplete();
        return redirect()->back()->with('success', 'Return approved — stock removed and marked for return to supplier.');
    }

    public function reject_supplier_return($id)
    {
        $db = \Config\Database::connect();
        $ret = $db->table('procurement_returns')->where('return_id', $id)->get()->getRow();
        if (!$ret || $ret->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending returns can be rejected.');
        }
        $db->table('procurement_returns')->where('return_id', $id)->update([
            'status' => 'rejected',
            'resolved_at' => date('Y-m-d H:i:s')
        ]);
        return redirect()->back()->with('info', 'Supplier return rejected.');
    }

    public function get_supplier_return_details($id)
    {
        $db = \Config\Database::connect();
        $data = $db->table('procurement_returns as pr')
            ->select('pr.*, po.po_number, s.name as supplier_name, p.name, p.sku, ib.batch_number, ru.full_name as resolved_by_name')
            ->join('purchase_orders as po', 'po.po_id = pr.po_id')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id')
            ->join('products as p', 'p.product_id = pr.product_id')
            ->join('inventory_batches as ib', 'ib.batch_id = pr.batch_id', 'left')
            ->join('users as ru', 'ru.user_id = pr.resolved_by', 'left')
            ->where('pr.return_id', $id)
            ->get()->getRow();

        if (!$data) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($data);
    }
}