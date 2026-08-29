<?php

namespace App\Controllers\Supplier\Main;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $supplierId = session()->get('supplier_id');

        // 1. Fetch Scorecard Data (On-time, Accuracy, Total)
        $data['scorecard'] = $db->table('supplier_scorecards')->where('supplier_id', $supplierId)->get()->getRow();

        // 2. Fetch Open Purchase Orders
        $data['open_pos'] = $db->table('purchase_orders')
            ->select('po_id, po_number, total_amount, expected_date, status, (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = purchase_orders.po_id) as items')
            ->where('supplier_id', $supplierId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)->get()->getResultArray();

        $data['title'] = "Supplier Dashboard";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "dashboard";
        
        return view('pages/supplier/main/dashboard', $data);
    }
}