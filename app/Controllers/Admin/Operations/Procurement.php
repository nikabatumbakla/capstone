<?php

namespace App\Controllers\Admin\Operations;
use App\Controllers\BaseController;

class Procurement extends BaseController
{
    public function suppliers()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();
    $search = trim((string) $request->getGet('search'));
    $categoryFilter = $request->getGet('category');

    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 8;
    $offset = ($page - 1) * $perPage;

    $applyFilters = function($builder) use ($search, $categoryFilter) {
        if ($search !== '') {
            $builder->like('s.name', $search);
        }
        if ($categoryFilter) {
            $catId = (int) $categoryFilter;
            // Supplier qualifies if they supply at least one product in this category
            $builder->where("s.supplier_id IN (SELECT supplier_id FROM products WHERE category_id = {$catId} AND supplier_id IS NOT NULL)", null, false);
        }
        return $builder;
    };

    $countBuilder = $db->table('suppliers as s')->where('s.is_active', 1);
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('suppliers as s');
    $builder->select('s.*, sc.on_time_rate, sc.accuracy_rate, sc.total_orders, sc.avg_lead_time_actual');
    $builder->join('supplier_scorecards as sc', 'sc.supplier_id = s.supplier_id', 'left');
    $builder->where('s.is_active', 1);
    $applyFilters($builder);
    $builder->orderBy('s.supplier_id', 'DESC');
    $builder->limit($perPage, $offset);
    $data['suppliers'] = $builder->get()->getResultArray();

    $data['categories'] = $db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();

    $data['current_page'] = $page;
    $data['per_page']     = $perPage;
    $data['total_rows']   = $totalRows;
    $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));
    $data['search'] = $search;
    $data['category_filter'] = $categoryFilter;

    $data['title'] = "Supplier Management";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "procurement";

    return view('pages/admin/operations/procurement/suppliers', $data);
}

// Products belonging to ONE supplier only — used to scope the Create PO form
public function get_supplier_products($supplierId)
{
    $db = \Config\Database::connect();

    $supplier = $db->table('suppliers')->where('supplier_id', $supplierId)->get()->getRow();
    if (!$supplier) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Supplier not found']);
    }

    $products = $db->table('products as p')
        ->select('p.product_id, p.name, p.sku, p.category_id, c.name as cat_name, spc.unit_cost, spc.minimum_order_qty')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->join('supplier_product_catalog as spc', 'spc.product_id = p.product_id AND spc.supplier_id = p.supplier_id', 'left')
        ->where('p.supplier_id', $supplierId)
        ->where('p.is_active', 1)
        ->orderBy('c.name', 'ASC')
        ->orderBy('p.name', 'ASC')
        ->get()->getResultArray();

    return $this->response->setJSON([
        'supplier' => $supplier,
        'products' => $products
    ]);
}

    public function get_supplier_details($id)
{
    $db = \Config\Database::connect();
    $supplier = $db->table('suppliers as s')
        ->select('s.*, sc.on_time_rate, sc.accuracy_rate, sc.on_time_deliveries, sc.accurate_orders')
        ->join('supplier_scorecards as sc', 'sc.supplier_id = s.supplier_id', 'left')
        ->where('s.supplier_id', $id)
        ->get()->getRow();

    if (!$supplier) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
    }

    // Real, live PO history — not the stale scorecard cache
    $poStats = $db->table('purchase_orders')
        ->select('COUNT(*) as po_count, COALESCE(SUM(total_amount),0) as total_spent')
        ->where('supplier_id', $id)
        ->get()->getRow();

    $recentPOs = $db->table('purchase_orders')
        ->select('po_id, po_number, status, total_amount, expected_date, created_at')
        ->where('supplier_id', $id)
        ->orderBy('created_at', 'DESC')
        ->limit(5)
        ->get()->getResultArray();

    $supplier->po_count    = (int) $poStats->po_count;
    $supplier->total_spent = (float) $poStats->total_spent;
    $supplier->recent_pos  = $recentPOs;

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

    $status = $request->getGet('status');
    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $countBuilder = $db->table('purchase_orders as po');
    if ($status) $countBuilder->where('po.status', $status);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('purchase_orders as po');
    $builder->select('po.*, s.name as supplier_name, (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.po_id) as item_count');
    $builder->join('suppliers as s', 's.supplier_id = po.supplier_id');
    if ($status) $builder->where('po.status', $status);
    $builder->orderBy('po.created_at', 'DESC');
    $builder->limit($perPage, $offset);
    $data['pos'] = $builder->get()->getResultArray();

    $data['count_pending'] = $db->table('purchase_orders')->where('status', 'pending_approval')->countAllResults();

    $monthStart = date('Y-m-01 00:00:00');
$monthEnd   = date('Y-m-t 23:59:59');

$data['po_this_month'] = $db->table('purchase_orders')
    ->where('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())', null, false)
    ->countAllResults();

$data['auto_reorders_this_month'] = $db->table('purchase_orders')
    ->where('is_auto_generated', 1)
    ->where('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())', null, false)
    ->countAllResults();

$spendRow = $db->table('purchase_orders')
    ->selectSum('total_amount')
    ->where('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())', null, false)
    ->where('status !=', 'cancelled')
    ->get()->getRow();
$data['spend_this_month'] = $spendRow->total_amount ?? 0;

    $data['total_rows']   = $totalRows;
    $data['current_page'] = $page;
    $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));
    $data['per_page']     = $perPage;
    $data['status_filter'] = $status;

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
    $products    = $this->request->getPost('products');
    $qtys        = $this->request->getPost('qtys');
    $costs       = $this->request->getPost('costs');
    $notes       = trim((string) $this->request->getPost('notes'));

    $db->transStart();

    $db->table('purchase_orders')->insert([
        'supplier_id'       => $supplier_id,
        'po_number'         => 'PO-' . date('Y') . '-' . time(),
        'status'            => 'sent',
        'is_auto_generated' => 0,
        'expected_date'     => $this->request->getPost('expected_date'),
        'total_amount'      => 0,
        'created_by'        => session()->get('user_id') ?? 1,
        'notes'             => $notes !== '' ? $notes : null,
    ]);
    $po_id = $db->insertID();

    $total = 0;
    foreach ($products as $index => $pid) {
        $qty  = (float) ($qtys[$index] ?? 0);
        $cost = (float) ($costs[$index] ?? 0);
        $total += $qty * $cost;

        $db->table('purchase_order_items')->insert([
            'po_id'       => $po_id,
            'product_id'  => $pid,
            'qty_ordered' => $qty,
            'unit_cost'   => $cost
        ]);

        // Keep the per-supplier price catalog current so next PO auto-fills correctly
        $existingCatalog = $db->table('supplier_product_catalog')
            ->where('supplier_id', $supplier_id)
            ->where('product_id', $pid)
            ->get()->getRow();

        if ($existingCatalog) {
            $db->table('supplier_product_catalog')
                ->where('catalog_id', $existingCatalog->catalog_id)
                ->update(['unit_cost' => $cost]);
        } else {
            $db->table('supplier_product_catalog')->insert([
                'supplier_id' => $supplier_id,
                'product_id'  => $pid,
                'unit_cost'   => $cost
            ]);
        }
    }

    $db->table('purchase_orders')->where('po_id', $po_id)->update(['total_amount' => $total]);
    $db->transComplete();

    if ($db->transStatus() === false) {
        return redirect()->to('admin/procurement/suppliers')->with('error', 'Failed to create Purchase Order.');
    }

    return redirect()->to('admin/procurement/purchase-orders')->with('success', 'Purchase Order Created.');
}

public function reject_po($id)
{
    $db = \Config\Database::connect();
    $po = $db->table('purchase_orders')->where('po_id', $id)->get()->getRow();

    if (!$po || $po->status !== 'pending_approval') {
        return redirect()->to('admin/procurement/purchase-orders')->with('error', 'Only pending purchase orders can be rejected.');
    }

    $db->table('purchase_orders')->where('po_id', $id)->update(['status' => 'cancelled']);
    return redirect()->to('admin/procurement/purchase-orders')->with('success', 'Purchase Order rejected and cancelled.');
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

    $po = $db->table('purchase_orders as po')
        ->select('po.*, s.name as sname, u.full_name as creator')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->join('users as u', 'u.user_id = po.created_by', 'left')
        ->where('po.po_id', $id)
        ->get()->getRow();

    if (!$po) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'PO Not Found']);
    }

    $items = $db->table('purchase_order_items as poi')
        ->select("poi.*, p.name, p.sku,
            (SELECT ib.quantity_avail FROM inventory_batches ib WHERE ib.product_id = poi.product_id ORDER BY ib.received_at DESC LIMIT 1) as current_stock,
            (SELECT ib.reorder_level FROM inventory_batches ib WHERE ib.product_id = poi.product_id ORDER BY ib.received_at DESC LIMIT 1) as reorder_level,
            (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = poi.product_id ORDER BY ib.received_at DESC LIMIT 1) as last_sell_price")
        ->join('products as p', 'p.product_id = poi.product_id')
        ->where('poi.po_id', $id)
        ->get()->getResultArray();

    $settingsRows = $db->table('store_settings')->get()->getResultArray();
    $storeInfo = [];
    foreach ($settingsRows as $row) {
        $storeInfo[$row['setting_key']] = $row['setting_value'];
    }

    return $this->response->setJSON([
        'po'         => $po,
        'items'      => $items,
        'store_info' => $storeInfo,
        'server_time'=> date('Y-m-d H:i:s')
    ]);
}

public function goods_receipt()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();

    $categoryFilter = $request->getGet('category');

    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $applyFilter = function($builder) use ($categoryFilter) {
        $builder->whereIn('po.status', ['sent', 'acknowledged', 'in_transit']);
        if ($categoryFilter) {
            $catId = (int) $categoryFilter;
            $builder->where(
                "po.po_id IN (SELECT poi.po_id FROM purchase_order_items poi JOIN products p ON p.product_id = poi.product_id WHERE p.category_id = {$catId})",
                null, false
            );
        }
        return $builder;
    };

    $countBuilder = $db->table('purchase_orders as po');
    $applyFilter($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('purchase_orders as po');
    $builder->select('po.*, s.name as supplier_name');
    $builder->join('suppliers as s', 's.supplier_id = po.supplier_id');
    $applyFilter($builder);
    $builder->orderBy('po.expected_date', 'ASC');
    $builder->limit($perPage, $offset);
    $data['pending_receipts'] = $builder->get()->getResultArray();

    $data['categories'] = $db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
    $data['category_filter'] = $categoryFilter;

    $data['current_page'] = $page;
    $data['per_page']     = $perPage;
    $data['total_rows']   = $totalRows;
    $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));

    $data['title'] = "Goods Receipt Recording";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "procurement";
    return view('pages/admin/operations/procurement/goods_receipt', $data);
}

public function save_grr()
{
    $db = \Config\Database::connect();
    $po_id       = $this->request->getPost('po_id');
    $product_ids = $this->request->getPost('product_ids');
    $qty_received = $this->request->getPost('qty_received');
    $qty_expected = $this->request->getPost('qty_expected');
    $unit_costs   = $this->request->getPost('unit_costs');
    $lot_numbers  = $this->request->getPost('lot_numbers');
    $expires_ats  = $this->request->getPost('expires_ats');
    $sell_prices  = $this->request->getPost('sell_prices');
    $delivery_ref = trim((string) $this->request->getPost('delivery_ref'));
    $notes        = trim((string) $this->request->getPost('notes'));
    $userId       = session()->get('user_id') ?? 1;

    $po = $db->table('purchase_orders')->where('po_id', $po_id)->get()->getRow();
    if (!$po) {
        return redirect()->to('admin/procurement/goods-receipt')->with('error', 'Purchase Order not found.');
    }

    // Determine overall discrepancy status across all items in THIS delivery
    $hasDiscrepancy = false;
    foreach ($product_ids as $index => $pid) {
        if ((int) $qty_received[$index] !== (int) $qty_expected[$index]) {
            $hasDiscrepancy = true;
            break;
        }
    }
    $grrStatus = $hasDiscrepancy ? 'discrepancy' : 'complete';
    $poStatus  = $hasDiscrepancy ? 'partial' : 'received';

    $db->transStart();

    // 1. GRR header
    $db->table('goods_receipts')->insert([
        'po_id'        => $po_id,
        'received_by'  => $userId,
        'delivery_ref' => $delivery_ref !== '' ? $delivery_ref : null,
        'delivery_date'=> date('Y-m-d'),
        'status'       => $grrStatus,
        'notes'        => $notes !== '' ? $notes : null,
    ]);
    $grr_id = $db->insertID();

    // 2. Per-item processing
    foreach ($product_ids as $index => $pid) {
        $receivedQty = (int) $qty_received[$index];
        $expectedQty = (int) $qty_expected[$index];

        // a) Log the receipt line itself (schema: discrepancy is a GENERATED column, auto-computed — don't insert it)
        $itemNote = ($receivedQty !== $expectedQty)
            ? ($receivedQty < $expectedQty ? 'Short-delivered' : 'Over-delivered')
            : null;

        $db->table('goods_receipt_items')->insert([
            'grr_id'       => $grr_id,
            'product_id'   => $pid,
            'batch_id'     => null, // linked below once the batch exists
            'qty_expected' => $expectedQty,
            'qty_received' => $receivedQty,
            'notes'        => $itemNote,
        ]);
        $gri_id = $db->insertID();

        // b) Reflect what actually arrived back onto the PO's own line item
        $db->table('purchase_order_items')
            ->where('po_id', $po_id)
            ->where('product_id', $pid)
            ->update(['qty_received' => $receivedQty]);

        // c) Only create a stock batch if something actually arrived
        if ($receivedQty > 0) {
            $lastBatch = $db->table('inventory_batches')
                ->where('product_id', $pid)
                ->orderBy('received_at', 'DESC')
                ->get()->getRow();

            $batch_id = null;
            $db->table('inventory_batches')->insert([
                'product_id'      => $pid,
                'supplier_id'     => $po->supplier_id,
                'po_id'           => $po_id,
                'batch_number'    => 'BAT-' . date('Ymd') . '-' . str_pad($pid, 4, '0', STR_PAD_LEFT) . '-' . $index,
                'lot_number'      => $lot_numbers[$index] !== '' ? $lot_numbers[$index] : null,
                'expires_at'      => $expires_ats[$index] !== '' ? $expires_ats[$index] : null,
                'cost_price'      => $unit_costs[$index] ?? 0,
                'sell_price'      => $sell_prices[$index],
                'quantity_in'     => $receivedQty,
                'quantity_avail'  => $receivedQty,
                'reorder_level'   => $lastBatch ? $lastBatch->reorder_level : 5,
                'received_at'     => date('Y-m-d H:i:s'),
            ]);
            $batch_id = $db->insertID();

            // Link the GRR item to the batch it created
            $db->table('goods_receipt_items')->where('gri_id', $gri_id)->update(['batch_id' => $batch_id]);

            // d) Log it in the inbound movement ledger
            $db->table('stock_movements')->insert([
                'product_id'     => $pid,
                'batch_id'       => $batch_id,
                'movement_type'  => 'inbound',
                'quantity'       => $receivedQty,
                'reference_id'   => $po_id,
                'reference_type' => 'po',
                'scanned_by'     => $userId,
                'scan_mode'      => 'inbound_stock_in',
                'reason'         => $itemNote,
                'notes'          => $notes !== '' ? $notes : null,
            ]);
        }
    }

    // 3. Close (or partially close) the PO
    $db->table('purchase_orders')->where('po_id', $po_id)->update([
        'status'        => $poStatus,
        'received_date' => date('Y-m-d')
    ]);

    $db->transComplete();

    if ($db->transStatus() === false) {
        return redirect()->to('admin/procurement/goods-receipt')->with('error', 'Failed to record delivery — please try again.');
    }

    $message = $hasDiscrepancy
        ? 'Delivery recorded with discrepancies — PO marked Partial. Inventory updated with actual quantities received.'
        : 'Delivery fully verified — inventory updated and PO closed.';

    return redirect()->to('admin/procurement/goods-receipt')->with('success', $message);
}

}