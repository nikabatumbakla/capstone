<?php

namespace App\Models\Admin\Main;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getKpis(): array
    {
        $today = date('Y-m-d');

        $posTotal = $this->db->table('pos_transactions')->selectSum('total')->where('DATE(created_at)', $today)->where('status', 'completed')->get()->getRow()->total ?? 0;
        $soTotal = $this->db->table('sales_orders')->selectSum('total')->where('DATE(created_at)', $today)->where('status !=', 'cancelled')->get()->getRow()->total ?? 0;

        return [
            'total_sales_today' => $posTotal + $soTotal,
            'active_products'   => $this->db->table('products')->where('is_active', 1)->countAllResults(),
            'low_stock_count'   => $this->db->table('inventory_batches')->where('quantity_avail <= reorder_level', null, false)->countAllResults(),
            'pending_orders'    => $this->db->table('sales_orders')->where('status', 'pending')->countAllResults(),
        ];
    }

    public function getWeeklyTrend(): array
    {
        $days = []; $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $days[] = date('D', strtotime($date));
            $pos = $this->db->table('pos_transactions')->selectSum('total')->where('DATE(created_at)', $date)->where('status', 'completed')->get()->getRow()->total ?? 0;
            $so = $this->db->table('sales_orders')->selectSum('total')->where('DATE(created_at)', $date)->where('status !=', 'cancelled')->get()->getRow()->total ?? 0;
            $values[] = round($pos + $so, 2);
        }
        return ['labels' => $days, 'data' => $values];
    }

    public function getTopClients(int $limit = 5): array
    {
        return $this->db->table('institutional_clients as ic')
            ->select('ic.organization, ic.client_type, COUNT(so.order_id) as total_orders, SUM(so.total) as total_spent')
            ->join('sales_orders as so', 'so.client_id = ic.client_id')
            ->where('so.status !=', 'cancelled')
            ->groupBy('ic.client_id')
            ->orderBy('total_spent', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }

    public function getActiveAlerts(int $limit = 3): array
    {
        return $this->db->table('alerts')
            ->where('is_resolved', 0)
            ->orderBy('priority', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }

    // Category sales combining BOTH POS (walk-in) and Sales Orders (institutional) —
    // the previous version only counted POS, silently ignoring B2B revenue entirely.
    public function getCategorySales(int $limit = 6): array
    {
        $posSales = $this->db->table('pos_transaction_items as pti')
            ->select('c.category_id, c.name as category, SUM(pti.subtotal) as total')
            ->join('products as p', 'p.product_id = pti.product_id')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->groupBy('c.category_id')->get()->getResultArray();

        $soSales = $this->db->table('sales_order_items as soi')
            ->select('c.category_id, c.name as category, SUM(soi.subtotal) as total')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->join('sales_orders as so', 'so.order_id = soi.order_id')
            ->where('so.status !=', 'cancelled')
            ->groupBy('c.category_id')->get()->getResultArray();

        $combined = [];
        foreach (array_merge($posSales, $soSales) as $row) {
            $combined[$row['category']] = ($combined[$row['category']] ?? 0) + (float) $row['total'];
        }
        arsort($combined);
        $combined = array_slice($combined, 0, $limit, true);

        $maxValue = !empty($combined) ? max($combined) : 1;
        $result = [];
        foreach ($combined as $cat => $total) {
            $result[] = ['category' => $cat, 'total' => $total, 'percent' => $maxValue > 0 ? round(($total / $maxValue) * 100) : 0];
        }
        return $result;
    }

    // Sales orders being fulfilled TO clients — corrected label, no more "supplier" mix-up
    public function getPendingDeliveries(int $limit = 3): array
{
    return $this->db->table('sales_orders as so')
        ->select('so.order_number, ic.organization as client_name, so.status, so.created_at')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->whereIn('so.status', ['processing', 'shipped'])
        ->orderBy('so.created_at', 'ASC')
        ->limit($limit)->get()->getResultArray();
}
}