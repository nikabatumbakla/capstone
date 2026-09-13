<?php

namespace App\Models\Staff;

use CodeIgniter\Model;

class StaffDashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getKpis(int $staffUserId): array
{
    $today = date('Y-m-d');

    $posToday = $this->db->table('pos_transactions')
        ->select('COUNT(*) as txn_count, COALESCE(SUM(total),0) as txn_total')
        ->where('DATE(created_at)', $today)
        ->where('cashier_id', $staffUserId)
        ->where('status', 'completed')
        ->get()->getRow();

    return [
        'pos_txns'  => (int) $posToday->txn_count,
        'pos_total' => (float) $posToday->txn_total,

        'pending_grr' => $this->db->table('purchase_orders')
            ->whereIn('status', ['sent', 'acknowledged', 'in_transit'])
            ->countAllResults(),

        'orders_to_process' => $this->db->table('sales_orders')
            ->where('status', 'pending')
            ->countAllResults(),

        'assigned_alerts' => $this->db->table('alerts')
            ->where('assigned_to', $staffUserId)
            ->where('is_resolved', 0)
            ->countAllResults(),
    ];
}

    public function getTodaysTasks(int $staffUserId, int $limit = 5): array
    {
        return $this->db->table('alerts')
            ->where('is_resolved', 0)
            ->groupStart()
                ->where('assigned_to', $staffUserId)
                ->orWhere('assigned_to IS NULL', null, false)
            ->groupEnd()
            ->orderBy('priority', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getLowStock(int $limit = 5): array
    {
        return $this->db->table('products as p')
            ->select('p.product_id, p.name, COALESCE(SUM(ib.quantity_avail),0) as total_stock, COALESCE(MAX(ib.reorder_level),5) as reorder_level')
            ->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left')
            ->where('p.is_active', 1)
            ->groupBy('p.product_id')
            ->having('total_stock <= reorder_level', null, false)
            ->orderBy('total_stock', 'ASC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getRecentActivity(int $staffUserId, int $limit = 6): array
{
    return $this->db->table('stock_movements as sm')
        ->select('sm.movement_type, sm.quantity, sm.moved_at, p.name as product_name')
        ->join('products as p', 'p.product_id = sm.product_id')
        ->where('sm.scanned_by', $staffUserId)
        ->orderBy('sm.moved_at', 'DESC')
        ->limit($limit)
        ->get()->getResultArray();
}
}