<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        // 1. STATS (Today's Sales, Products, Low Stock, Pending)
        $posTotal = $db->table('pos_transactions')->selectSum('total')->where('DATE(created_at)', $today)->get()->getRow()->total ?? 0;
        $soTotal = $db->table('sales_orders')->selectSum('total')->where('DATE(created_at)', $today)->get()->getRow()->total ?? 0;
        
        $data['total_sales_today'] = $posTotal + $soTotal;
        $data['active_products'] = $db->table('products')->where('is_active', 1)->countAllResults();
        
        // Low Stock from inventory_batches (RSC Recommendation)
        $data['low_stock_count'] = $db->table('inventory_batches')->where('quantity_avail <= reorder_level')->countAllResults();
        $data['pending_orders'] = $db->table('sales_orders')->where('status', 'pending')->countAllResults();

        // 2. WEEKLY TREND (For Chart)
        $data['weekly_trend'] = $this->getWeeklyTrend($db);

        // 3. TOP CLIENTS
        $data['top_clients'] = $db->table('institutional_clients as ic')
            ->select('ic.organization, ic.client_type, COUNT(so.order_id) as total_orders, SUM(so.total) as total_spent')
            ->join('sales_orders as so', 'so.client_id = ic.client_id')
            ->groupBy('ic.client_id')
            ->orderBy('total_spent', 'DESC')->limit(5)->get()->getResultArray();

        // 4. ACTIVE ALERTS (Figma Style)
        $data['active_alerts'] = $db->table('alerts')
            ->where('is_resolved', 0)->orderBy('created_at', 'DESC')->limit(3)->get()->getResultArray();

        // 5. SALES BY CATEGORY (Progress Bars)
        // Note: Using a fixed array if no sales exist yet to show style
        $data['category_sales'] = $db->table('pos_transaction_items as pti')
            ->select('c.name as category, SUM(pti.subtotal) as total')
            ->join('products as p', 'p.product_id = pti.product_id')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->groupBy('c.category_id')->orderBy('total', 'DESC')->get()->getResultArray();

        // 6. PENDING DELIVERIES
        $data['pending_deliveries'] = $db->table('sales_orders as so')
            ->select('so.order_number as po_no, ic.organization as supplier, so.status')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->whereIn('so.status', ['processing', 'shipped'])->limit(3)->get()->getResultArray();

        $data['fullname'] = session()->get('full_name');
        $data['title'] = "Admin Dashboard";
        $data['page_name'] = "dashboard";

        return view('pages/admin/dashboard', $data);
    }

    private function getWeeklyTrend($db) {
        $days = []; $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $days[] = date('D', strtotime($date));
            $pos = $db->table('pos_transactions')->selectSum('total')->where('DATE(created_at)', $date)->get()->getRow()->total ?? 0;
            $so = $db->table('sales_orders')->selectSum('total')->where('DATE(created_at)', $date)->get()->getRow()->total ?? 0;
            $values[] = $pos + $so;
        }
        return ['labels' => $days, 'data' => $values];
    }
}