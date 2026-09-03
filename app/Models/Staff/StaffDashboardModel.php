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

        return [
            'pos_txns' => $this->db->table('pos_transactions')
                ->where('DATE(created_at)', $today)
                ->where('cashier_id', $staffUserId)
                ->where('status', 'completed')
                ->countAllResults(),

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
        return $this->db->table('inventory_batches as ib')
            ->select('p.product_id, p.name, ib.batch_id, ib.quantity_avail, ib.reorder_level')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('p.is_active', 1)
            ->where('ib.quantity_avail <= ib.reorder_level', null, false)
            ->orderBy('ib.quantity_avail', 'ASC')
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