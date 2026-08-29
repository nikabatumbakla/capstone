<?php

namespace App\Controllers\Client\Main;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $clientId = session()->get('client_id');

        // 1. Fetch Client Profile (For Balance/Credit)
        $data['client'] = $db->table('institutional_clients')->where('client_id', $clientId)->get()->getRow();

        // 2. Fetch Stats
        $data['active_orders'] = $db->table('sales_orders')->where(['client_id' => $clientId, 'status !=' => 'delivered'])->countAllResults();
        $data['total_orders_ytd'] = $db->table('sales_orders')->where('client_id', $clientId)->countAllResults();

        // 3. Fetch Recent Orders
        $data['recent_orders'] = $db->table('sales_orders')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)->get()->getResultArray();

        $data['title'] = "Client Dashboard";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "dashboard";
        return view('pages/client/main/dashboard', $data); 
    }
}