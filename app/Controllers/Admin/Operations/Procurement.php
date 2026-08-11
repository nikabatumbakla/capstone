<?php

namespace App\Controllers\Admin\Operations;
use App\Controllers\BaseController;

class Procurement extends BaseController
{
    public function suppliers()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();
        $search = $request->getGet('search'); // Capture the filter input

        $builder = $db->table('suppliers as s');
        $builder->select('s.*, sc.on_time_rate, sc.accuracy_rate, sc.total_orders, sc.avg_lead_time_actual');
        $builder->join('supplier_scorecards as sc', 'sc.supplier_id = s.supplier_id', 'left');
        $builder->where('s.is_active', 1);

        if ($search) {
            $builder->like('s.name', $search);
        }
        
        $data['suppliers'] = $builder->get()->getResultArray();
        $data['title'] = "Supplier Management";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "procurement";

        return view('pages/admin/operations/procurement/suppliers', $data);
    }

    // FIX: AJAX fetch for individual supplier data
    public function get_supplier_details($id)
    {
        $db = \Config\Database::connect();
        $supplier = $db->table('suppliers')->where('supplier_id', $id)->get()->getRow();
        
        if (!$supplier) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }
        return $this->response->setJSON($supplier);
    }

    // FIX: Process the "Add Supplier" form
    public function save_supplier()
    {
        $db = \Config\Database::connect();
        $data = [
            'name'           => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact'),
            'phone'          => $this->request->getPost('phone'),
            'email'          => $this->request->getPost('email'),
            'address'        => $this->request->getPost('address'),
            'lead_time_days' => 7,
            'is_active'      => 1
        ];

        $db->table('suppliers')->insert($data);
        return redirect()->to('admin/procurement/suppliers')->with('success', 'Supplier registered.');
    }
public function purchase_orders()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();

        // 1. Pagination Logic
        $page = (int)($request->getGet('page') ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $status = $request->getGet('status');

        $builder = $db->table('purchase_orders as po');
        $builder->select('po.*, s.name as supplier_name, (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.po_id) as item_count');
        $builder->join('suppliers as s', 's.supplier_id = po.supplier_id');
        
        if ($status) $builder->where('po.status', $status);
        
        $totalRows = $builder->countAllResults(false);
        $data['pos'] = $builder->orderBy('po.created_at', 'DESC')->limit($perPage, $offset)->get()->getResultArray();

        // 2. Data for Tabs and Drawers
        $data['count_pending'] = $db->table('purchase_orders')->where('status', 'pending_approval')->countAllResults();
        $data['suppliers'] = $db->table('suppliers')->where('is_active', 1)->get()->getResultArray();
        
        // Categorized Products for Searchable feeling
        $data['categories'] = $db->table('categories')->get()->getResultArray();
        $data['products'] = $db->table('products as p')
            ->select('p.*, c.name as cat_name')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->where('p.is_active', 1)->get()->getResultArray();

        // Pagination variables
        $data['total_rows'] = $totalRows;
        $data['current_page'] = $page;
        $data['total_pages'] = ceil($totalRows / $perPage);
        $data['per_page'] = $perPage;

        $data['title'] = "Purchase Orders";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "procurement"; 
        return view('pages/admin/operations/procurement/purchase_orders', $data);
    }

    // NEW: Save PO Method
    public function save_po()
    {
        $db = \Config\Database::connect();
        $supplier_id = $this->request->getPost('supplier_id');
        $products = $this->request->getPost('products');
        $qtys = $this->request->getPost('qtys');
        $costs = $this->request->getPost('costs');

        $db->transStart();

        $po_id = $db->table('purchase_orders')->insertBatch([[
            'supplier_id' => $supplier_id,
            'po_number' => 'PO-' . time(),
            'status' => 'sent', // Admin created POs go straight to sent
            'expected_date' => $this->request->getPost('expected_date'),
            'total_amount' => 0, // Will update below
            'created_by' => session()->get('user_id') ?? 1
        ]]);
        $po_id = $db->insertID();

        $total = 0;
        foreach($products as $index => $pid) {
            $subtotal = $qtys[$index] * $costs[$index];
            $total += $subtotal;
            $db->table('purchase_order_items')->insert([
                'po_id' => $po_id,
                'product_id' => $pid,
                'qty_ordered' => $qtys[$index],
                'unit_cost' => $costs[$index]
            ]);
        }

        $db->table('purchase_orders')->where('po_id', $po_id)->update(['total_amount' => $total]);
        $db->transComplete();

        return redirect()->to('admin/procurement/purchase-orders')->with('success', 'Purchase Order Created.');
    }

    public function approve_po($id)
    {
        $db = \Config\Database::connect();
        $db->table('purchase_orders')->where('po_id', $id)->update(['status' => 'sent']);
        return redirect()->to('admin/procurement/purchase-orders')->with('success', 'PO Approved and Sent.');
    }

    public function get_po_details($id)
{
    $db = \Config\Database::connect();
    
    // Fetch PO with Supplier Name and Creator Name
    $po = $db->table('purchase_orders as po')
        ->select('po.*, s.name as sname, u.full_name as creator')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->join('users as u', 'u.user_id = po.created_by', 'left')
        ->where('po.po_id', $id)
        ->get()->getRow();

    if (!$po) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'PO Not Found']);
    }

    // Fetch itemized list
    $items = $db->table('purchase_order_items as poi')
        ->select('poi.*, p.name, p.sku')
        ->join('products as p', 'p.product_id = poi.product_id')
        ->where('poi.po_id', $id)
        ->get()->getResultArray();

    return $this->response->setJSON([
        'po' => $po,
        'items' => $items,
        'server_time' => date('Y-m-d H:i:s')
    ]);
}

public function goods_receipt()
{
    $db = \Config\Database::connect();
    // Fetch POs that are Sent (Ready to be received)
    $data['pending_receipts'] = $db->table('purchase_orders as po')
        ->select('po.*, s.name as supplier_name')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->where('po.status', 'sent')
        ->get()->getResultArray();

    $data['title'] = "Goods Receipt Recording";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "procurement";
    return view('pages/admin/operations/procurement/goods_receipt', $data);
}

public function save_grr()
{
    $db = \Config\Database::connect();
    $po_id = $this->request->getPost('po_id');
    $product_ids = $this->request->getPost('product_ids');
    $qty_received = $this->request->getPost('qty_received');

    $db->transStart();

    // 1. Create GRR Header
    $db->table('goods_receipts')->insert([
        'po_id' => $po_id,
        'received_by' => session()->get('user_id') ?? 1,
        'delivery_date' => date('Y-m-d'),
        'status' => 'complete'
    ]);
    $grr_id = $db->insertID();

    // 2. Process Items & Update Inventory
    foreach ($product_ids as $index => $pid) {
        // Log GRR Item
        $db->table('goods_receipt_items')->insert([
            'grr_id' => $grr_id,
            'product_id' => $pid,
            'qty_expected' => $this->request->getPost('qty_expected')[$index],
            'qty_received' => $qty_received[$index]
        ]);

        // Update Stock in inventory_batches (Assumes 1 batch per PO item for simplicity)
        $db->table('inventory_batches')->insert([
            'product_id' => $pid,
            'po_id' => $po_id,
            'batch_number' => 'BAT-' . time() . $index,
            'quantity_in' => $qty_received[$index],
            'quantity_avail' => $qty_received[$index],
            'sell_price' => 0, // Admin must update this in Stock Management
            'received_at' => date('Y-m-d H:i:s')
        ]);
    }

    // 3. Close PO
    $db->table('purchase_orders')->where('po_id', $po_id)->update(['status' => 'received', 'received_date' => date('Y-m-d')]);

    $db->transComplete();
    return redirect()->to('admin/procurement/goods-receipt')->with('success', 'Inventory Updated via GRR.');
}

}