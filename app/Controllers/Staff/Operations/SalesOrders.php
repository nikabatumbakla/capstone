<?php

namespace App\Controllers\Staff\Operations;
use App\Controllers\BaseController;

class SalesOrders extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        $data['orders'] = $db->table('sales_orders as so')
            ->select('so.*, ic.organization')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->orderBy('so.created_at', 'DESC')->get()->getResultArray();

        $data['title'] = "Distribution Queue";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "orders";

        // FIXED VIEW PATH
        return view('pages/staff/operations/sales_orders', $data);
    }
}