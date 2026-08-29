<?php

namespace App\Controllers\Supplier\Orders;
use App\Controllers\BaseController;

class PurchaseOrders extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $supplierId = session()->get('supplier_id');
        $status = $this->request->getGet('tab') ?? 'open';

        $builder = $db->table('purchase_orders as po');
        $builder->select('po.*, (SELECT SUM(qty_ordered) FROM purchase_order_items WHERE po_id = po.po_id) as total_qty');
        $builder->where('supplier_id', $supplierId);

        if ($status == 'history') {
            $builder->whereIn('po.status', ['received', 'cancelled']);
        } else {
            $builder->whereIn('po.status', ['sent', 'approved', 'partial']);
        }

        $data['pos'] = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
        
        $data['title'] = "Purchase Order Inbox";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "po_inbox";
        $data['active_tab'] = $status;

        return view('pages/supplier/orders/po_inbox', $data);
    }

    public function acknowledge($id)
    {
        $db = \Config\Database::connect();
        $db->table('purchase_orders')->where('po_id', $id)->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'PO Acknowledged. Robin Rose Trading has been notified.');
    }

    // --- ACKNOWLEDGE PO VIEW ---
    public function view_acknowledge($id) {
        $data['po'] = $this->db->table('purchase_orders')->where('po_id', $id)->get()->getRow();
        $data['items'] = $this->db->table('purchase_order_items as poi')
            ->select('poi.*, p.name, p.sku')
            ->join('products as p', 'p.product_id = poi.product_id')
            ->where('poi.po_id', $id)->get()->getResultArray();

        $data['title'] = "Acknowledge PO";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "po_inbox";
        return view('pages/supplier/orders/acknowledge', $data);
    }

    public function process_acknowledge()
    {
        $db = \Config\Database::connect();
        $po_id = $this->request->getPost('po_id');
        
        $data = [
            'status'        => 'approved', // Moves from 'sent' to 'approved'
            'expected_date' => $this->request->getPost('confirmed_date'), // Updates based on supplier capacity
            'notes'         => $this->request->getPost('notes'),
            'updated_at'    => date('Y-m-d H:i:s')
        ];

        $db->table('purchase_orders')->where('po_id', $po_id)->update($data);

        return redirect()->to('supplier/orders/inbox')->with('success', 'Order acknowledged and confirmed.');
    }


    // --- DELIVERY UPDATES VIEW ---
    public function delivery_updates() {
        $supplierId = session()->get('supplier_id');
        $data['orders'] = $this->db->table('purchase_orders')
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['approved', 'sent', 'partial'])
            ->get()->getResultArray();

        $data['title'] = "Delivery Logistics";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "delivery";
        return view('pages/supplier/orders/delivery', $data);
    }

    public function update_delivery() {
        $id = $this->request->getPost('po_id');
        $this->db->table('purchase_orders')->where('po_id', $id)->update([
            'status' => 'sent', // Marks as "In-Transit"
            'notes' => $this->request->getPost('tracking_ref')
        ]);
        return redirect()->to('supplier/orders/delivery')->with('success', 'Delivery status updated.');
    }
}