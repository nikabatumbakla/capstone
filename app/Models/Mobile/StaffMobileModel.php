<?php

namespace App\Models\Mobile;

use CodeIgniter\Model;

class StaffMobileModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getTaskSummary(int $userId): array
    {
        return [
            'grr_pending' => $this->db->table('purchase_orders')->whereIn('status', ['sent', 'acknowledged', 'in_transit'])->countAllResults(),
            'low_stock'   => $this->db->table('inventory_batches')->where('quantity_avail <= reorder_level', null, false)->countAllResults(),
        ];
    }

    public function getHomeSummary(int $userId): array
    {
        return [
            'grr_pending'    => $this->db->table('purchase_orders')->whereIn('status', ['sent', 'acknowledged', 'in_transit'])->countAllResults(),
            'low_stock'      => $this->db->table('inventory_batches')->where('quantity_avail <= reorder_level', null, false)->countAllResults(),
            'my_scans_today' => $this->db->table('stock_movements')->where('scanned_by', $userId)->where('DATE(moved_at)', date('Y-m-d'))->countAllResults(),
            'my_alerts'      => $this->db->table('alerts')->where('assigned_to', $userId)->where('is_resolved', 0)->countAllResults(),
        ];
    }

    public function getRecentScans(int $userId, int $limit = 5): array
    {
        return $this->db->table('stock_movements as sm')
            ->select('sm.movement_type, sm.quantity, sm.moved_at, p.name as product_name')
            ->join('products as p', 'p.product_id = sm.product_id')
            ->where('sm.scanned_by', $userId)
            ->orderBy('sm.moved_at', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }

    public function getMyTasks(int $userId, int $limit = 5): array
{
    return $this->db->table('alerts')
        ->where('is_resolved', 0)
        ->groupStart()->where('assigned_to', $userId)->orWhere('assigned_to IS NULL', null, false)->groupEnd()
        ->orderBy('priority', 'DESC')
        ->orderBy('created_at', 'DESC')
        ->limit($limit)
        ->get()->getResultArray();
}

public function getFullAlertsList(int $userId): array
{
    return $this->db->table('alerts')
        ->where('is_resolved', 0)
        ->groupStart()->where('assigned_to', $userId)->orWhere('assigned_to IS NULL', null, false)->groupEnd()
        ->orderBy('priority', 'DESC')
        ->orderBy('created_at', 'DESC')
        ->get()->getResultArray();
}

    public function getTaskPageCounts(int $userId): array
{
    $base = fn() => $this->db->table('alerts')->where('is_resolved', 0)
        ->groupStart()->where('assigned_to', $userId)->orWhere('assigned_to IS NULL', null, false)->groupEnd();
    return [
        'pending' => $base()->countAllResults(),
        'urgent'  => $base()->where('priority', 'high')->countAllResults(),
    ];
}

public function completeTask(int $alertId, int $userId): bool
{
    $alert = $this->db->table('alerts')->where('alert_id', $alertId)->get()->getRow();
    if (!$alert || $alert->alert_type !== 'assigned_task' || (int) $alert->assigned_to !== $userId) {
        return false;
    }
    $this->db->table('alerts')->where('alert_id', $alertId)->update([
        'is_resolved' => 1,
        'resolved_at' => date('Y-m-d H:i:s'),
    ]);
    return true;
}
}