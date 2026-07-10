<?php

namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class Sales extends BaseController
{
    public function clients()
    {
        $db = \Config\Database::connect();
        
        // 1. KPI Counts (Figma Match)
        $data['count_schools']   = $db->table('institutional_clients')->where('client_type', 'school')->countAllResults();
        $data['count_hospitals'] = $db->table('institutional_clients')->where('client_type', 'hospital')->countAllResults();
        $data['count_lgu']       = $db->table('institutional_clients')->whereIn('client_type', ['lgu', 'sk'])->countAllResults();
        $data['count_brgy']      = $db->table('institutional_clients')->where('client_type', 'barangay')->countAllResults();

        // 2. Fetch Registry with Credit/Balance
        $builder = $db->table('institutional_clients as ic');
        $data['clients'] = $builder->where('is_active', 1)->get()->getResultArray();

        $data['title'] = "Client Directory";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "sales";
        return view('pages/admin/sales/institutional_clients', $data);
    }

    public function get_client_history($id)
    {
        $db = \Config\Database::connect();
        $client = $db->table('institutional_clients')->where('client_id', $id)->get()->getRow();
        $orders = $db->table('sales_orders')->where('client_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray();
        
        return $this->response->setJSON(['client' => $client, 'orders' => $orders]);
    }

    public function save_order()
    {
        $db = \Config\Database::connect();
        // 1 Form 1 Process Logic
        $db->transStart();
        
        $total = $this->request->getPost('total_amount');
        $orderData = [
            'client_id' => $this->request->getPost('client_id'),
            'order_number' => 'SO-' . time(),
            'status' => 'pending',
            'subtotal' => $total / 1.12,
            'vat_amount' => $total - ($total / 1.12),
            'total' => $total,
            'payment_status' => 'unpaid',
            'created_by' => session()->get('user_id') ?? 1
        ];
        
        $db->table('sales_orders')->insert($orderData);
        $db->transComplete();

        return redirect()->to('admin/sales/institutional-clients')->with('success', 'Sales Order Recorded.');
    }

    public function save_client()
    {
        $db = \Config\Database::connect();
        $data = [
            'organization'   => $this->request->getPost('org'),
            'client_type'    => $this->request->getPost('type'),
            'contact_person' => $this->request->getPost('contact'),
            'phone'          => $this->request->getPost('phone'),
            'address'        => $this->request->getPost('address'),
            'credit_limit'   => $this->request->getPost('limit') ?? 0,
            'is_active'      => 1
        ];
        $db->table('institutional_clients')->insert($data);
        return redirect()->back()->with('success', 'Organization Registered.');
    }

    public function get_client_details($id)
    {
        $db = \Config\Database::connect();
        $client = $db->table('institutional_clients')->where('client_id', $id)->get()->getRow();
        $orders = $db->table('sales_orders')->where('client_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray();
        return $this->response->setJSON(['client' => $client, 'orders' => $orders]);
    }

public function orders()
{
    $db = \Config\Database::connect();
    $status = $this->request->getGet('status');

    $builder = $db->table('sales_orders as so');
    $builder->select('so.*, ic.organization as client_name, ic.client_type');
    $builder->join('institutional_clients as ic', 'ic.client_id = so.client_id');
    
    if ($status) {
        $builder->where('so.status', $status);
    }

    $data['orders'] = $builder->orderBy('so.created_at', 'DESC')->get()->getResultArray();
    
    // Summary Counts for Tabs
    $data['count_pending'] = $db->table('sales_orders')->where('status', 'pending')->countAllResults();
    $data['count_processing'] = $db->table('sales_orders')->where('status', 'processing')->countAllResults();

    $data['title'] = "Sales Order Management";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "sales";
    return view('pages/admin/sales/sales_orders', $data);
}

public function get_order_details($id)
{
    $db = \Config\Database::connect();
    $order = $db->table('sales_orders as so')
        ->select('so.*, ic.organization, ic.address, ic.phone')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->where('so.order_id', $id)
        ->get()->getRow();

    $items = $db->table('sales_order_items as soi')
        ->select('soi.*, p.name, p.sku')
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('soi.order_id', $id)
        ->get()->getResultArray();

    return $this->response->setJSON(['order' => $order, 'items' => $items]);
}

public function update_order_status()
{
    $db = \Config\Database::connect();
    $id = $this->request->getPost('order_id');
    $status = $this->request->getPost('status');
    
    $db->table('sales_orders')->where('order_id', $id)->update(['status' => $status]);
    return redirect()->back()->with('success', 'Order status updated.');
}

public function pos()
{
    $db = \Config\Database::connect();
    // Fetch products with available stock only
    $data['products'] = $db->table('inventory_batches as ib')
        ->select('ib.batch_id, ib.quantity_avail, ib.sell_price, p.name, p.sku, p.barcode_value')
        ->join('products as p', 'p.product_id = ib.product_id')
        ->where('ib.quantity_avail >', 0)->get()->getResultArray();

    $data['title'] = "Point of Sale";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "sales";
    return view('pages/admin/sales/pos', $data);
}

public function process_pos()
{
    $db = \Config\Database::connect();
    $session = session();
    $items = $this->request->getPost('items'); // Array of batch_id, qty, price
    $total = $this->request->getPost('grand_total');

    $db->transStart();

    // 1. Generate POS Transaction
    $txnData = [
        'txn_number' => 'TXN-' . time(),
        'cashier_id' => $session->get('user_id') ?? 1,
        'subtotal'   => $total / 1.12,
        'vat_amount' => $total - ($total / 1.12),
        'total'      => $total,
        'payment_method' => $this->request->getPost('payment_method'),
        'amount_tendered' => $this->request->getPost('tendered'),
        'change_due' => $this->request->getPost('change'),
        'or_number' => 'OR-' . time() // BIR Compliant Sequence
    ];
    $db->table('pos_transactions')->insert($txnData);
    $txn_id = $db->insertID();

    // 2. Process Items & Deduct Stock
    foreach ($items as $item) {
        $db->table('pos_transaction_items')->insert([
            'txn_id' => $txn_id,
            'product_id' => $item['product_id'],
            'batch_id' => $item['batch_id'],
            'quantity' => $item['qty'],
            'unit_price' => $item['price'],
            'subtotal' => $item['qty'] * $item['price']
        ]);

        // Real-time deduction
        $db->table('inventory_batches')
           ->where('batch_id', $item['batch_id'])
           ->set('quantity_avail', "quantity_avail - {$item['qty']}", false)
           ->update();
    }

    $db->transComplete();
    return $this->response->setJSON(['status' => 'success', 'txn_id' => $txn_id]);
}

public function returns()
{
    $db = \Config\Database::connect();
    $data['returns'] = $db->table('sales_returns as sr')
        ->select('sr.*, u.full_name as staff, so.order_number')
        ->join('users as u', 'u.user_id = sr.processed_by')
        ->join('sales_orders as so', 'so.order_id = sr.order_id')
        ->get()->getResultArray();

    $data['title'] = "Sales Returns";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "sales";
    return view('pages/admin/sales/sales_returns', $data);
}

}