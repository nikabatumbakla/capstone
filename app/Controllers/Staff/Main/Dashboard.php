<?php

namespace App\Controllers\Staff\Main;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        $userId = session()->get('user_id');

        // 1. KPI Tile Calculations
        $data['pos_txns'] = $db->table('pos_transactions')->where('DATE(created_at)', $today)->countAllResults();
        $data['pending_grr'] = $db->table('purchase_orders')->where('status', 'sent')->countAllResults();
        $data['orders_process'] = $db->table('sales_orders')->where('status', 'pending')->countAllResults();
        $data['assigned_alerts'] = $db->table('alerts')->where(['is_resolved' => 0])->countAllResults();

        // 2. Today's Tasks (From Alerts Table)
        $data['tasks'] = $db->table('alerts')
            ->where('is_resolved', 0)
            ->orderBy('created_at', 'DESC')
            ->limit(4)->get()->getResultArray();

        // 3. Low Stock to Restock Table (Intelligence)
        $data['low_stock'] = $db->table('inventory_batches as ib')
            ->select('p.name, ib.quantity_avail, ib.reorder_level, ib.batch_id')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('ib.quantity_avail <= ib.reorder_level')
            ->limit(5)->get()->getResultArray();

        $data = [
            'title'     => 'Staff Terminal',
            'fullname'  => session()->get('full_name'),
            'page_name' => 'dashboard' // Triggers 'active' in sidebar
        ];
        return view('pages/staff/main/dashboard', $data);
    }
}