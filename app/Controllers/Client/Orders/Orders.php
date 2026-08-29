<?php

namespace App\Controllers\Client\Orders;
use App\Controllers\BaseController;

class Orders extends BaseController
{
    protected $db;
    public function __construct() { $this->db = \Config\Database::connect(); }

    // 1. MY ORDERS LIST
    public function index()
    {
        $clientId = session()->get('client_id');
        $data['orders'] = $this->db->table('sales_orders')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'DESC')->get()->getResultArray();

        $data['title'] = "My Order History";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "orders";
        return view('pages/client/orders/my_orders', $data);
    }

    // 2. PLACE ORDER FORM
    public function place_order_view()
    {
        // Fetch products that have stock
        $data['products'] = $this->db->table('inventory_batches as ib')
            ->select('ib.batch_id, ib.sell_price, ib.quantity_avail, p.name, p.sku, p.product_id')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('ib.quantity_avail >', 0)->get()->getResultArray();

        $data['title'] = "Place New Sales Order";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "place";
        return view('pages/client/orders/place_order', $data);
    }

    // 3. SAVE ORDER (1 Form 1 Process)
    public function save_order()
    {
        $clientId = session()->get('client_id');
        $items = $this->request->getPost('items');
        $qtys = $this->request->getPost('qtys');
        
        $this->db->transStart();
        
        // Header
        $orderData = [
            'client_id' => $clientId,
            'order_number' => 'SO-' . time(),
            'status' => 'pending',
            'total' => $this->request->getPost('grand_total_hidden'),
            'payment_status' => 'unpaid',
            'created_by' => session()->get('user_id')
        ];
        $this->db->table('sales_orders')->insert($orderData);
        $order_id = $this->db->insertID();

        // Items
        foreach($items as $index => $batch_id) {
            $batch = $this->db->table('inventory_batches')->where('batch_id', $batch_id)->get()->getRow();
            $this->db->table('sales_order_items')->insert([
                'order_id' => $order_id,
                'product_id' => $batch->product_id,
                'batch_id' => $batch_id,
                'quantity' => $qtys[$index],
                'unit_price' => $batch->sell_price,
                'subtotal' => $batch->sell_price * $qtys[$index]
            ]);
        }

        $this->db->transComplete();
        return redirect()->to('client/orders/my-orders')->with('success', 'Order Placed Successfully.');
    }
}