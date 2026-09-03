<?php

namespace App\Models\Admin\Management;

use CodeIgniter\Model;

class AlertsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // System-generated types this model owns the lifecycle of — auto-created AND auto-resolved.
    // 'assigned_task' is deliberately excluded: that one is always manual, so it's never auto-touched.
    private const AUTO_TYPES = ['low_stock', 'near_expiry', 'expired', 'po_approval'];

    // Call this on page load: reconciles the alerts table against real current conditions.
    // Opens new alerts for conditions that now exist, and auto-resolves ones that no longer apply —
    // so the feed always reflects the current state of the business, not a stale manual log.
    
    public function syncSystemAlerts(): void
{
    $this->syncLowStock();
    $this->syncNearExpiry();
    $this->syncExpired();
    $this->syncPoApproval();
    $this->autoResolveStale();
    $this->escalateOverdueTasks();
}

private function escalateOverdueTasks(): void
{
    $this->db->table('alerts')
        ->where('alert_type', 'assigned_task')
        ->where('is_resolved', 0)
        ->where('priority', 'normal')
        ->where('due_date IS NOT NULL', null, false)
        ->where('due_date <', date('Y-m-d'))
        ->update(['priority' => 'high']);
}

    private function alreadyOpen(string $type, ?int $productId = null, ?int $batchId = null, ?int $poId = null): bool
    {
        $builder = $this->db->table('alerts')->where('alert_type', $type)->where('is_resolved', 0);
        if ($productId) $builder->where('product_id', $productId);
        if ($batchId) $builder->where('batch_id', $batchId);
        if ($poId) $builder->where('po_id', $poId);
        return $builder->countAllResults() > 0;
    }

    private function syncLowStock(): void
    {
        $rows = $this->db->query("
            SELECT ib.batch_id, ib.product_id, ib.quantity_avail, ib.reorder_level, p.name
            FROM inventory_batches ib JOIN products p ON p.product_id = ib.product_id
            WHERE p.is_active = 1 AND ib.quantity_avail <= ib.reorder_level
        ")->getResultArray();

        foreach ($rows as $r) {
            if ($this->alreadyOpen('low_stock', $r['product_id'], $r['batch_id'])) continue;
            $this->db->table('alerts')->insert([
                'alert_type'  => 'low_stock',
                'product_id'  => $r['product_id'],
                'batch_id'    => $r['batch_id'],
                'message'     => "{$r['name']} — {$r['quantity_avail']} units left (reorder level: {$r['reorder_level']})",
                'priority'    => 'high',
            ]);
        }
    }

    private function syncNearExpiry(): void
    {
        $rows = $this->db->query("
            SELECT ib.batch_id, ib.product_id, ib.batch_number, ib.expires_at, p.name
            FROM inventory_batches ib JOIN products p ON p.product_id = ib.product_id
            WHERE p.is_active = 1 AND ib.quantity_avail > 0
              AND ib.expires_at IS NOT NULL
              AND ib.expires_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
        ")->getResultArray();

        foreach ($rows as $r) {
            if ($this->alreadyOpen('near_expiry', $r['product_id'], $r['batch_id'])) continue;
            $daysLeft = (strtotime($r['expires_at']) - time()) / 86400;
            $this->db->table('alerts')->insert([
                'alert_type'  => 'near_expiry',
                'product_id'  => $r['product_id'],
                'batch_id'    => $r['batch_id'],
                'message'     => "{$r['name']} — {$daysLeft} days left (Batch {$r['batch_number']})",
                'priority'    => 'normal',
            ]);
        }
    }

    private function syncExpired(): void
    {
        $rows = $this->db->query("
            SELECT ib.batch_id, ib.product_id, ib.batch_number, ib.quantity_avail, p.name
            FROM inventory_batches ib JOIN products p ON p.product_id = ib.product_id
            WHERE p.is_active = 1 AND ib.quantity_avail > 0
              AND ib.expires_at IS NOT NULL AND ib.expires_at < CURDATE()
        ")->getResultArray();

        foreach ($rows as $r) {
            if ($this->alreadyOpen('expired', $r['product_id'], $r['batch_id'])) continue;
            $this->db->table('alerts')->insert([
                'alert_type'  => 'expired',
                'product_id'  => $r['product_id'],
                'batch_id'    => $r['batch_id'],
                'message'     => "{$r['name']} Batch {$r['batch_number']} expired — {$r['quantity_avail']} units require write-off",
                'priority'    => 'high',
            ]);
        }
    }

    private function syncPoApproval(): void
    {
        $rows = $this->db->table('purchase_orders as po')
            ->select('po.po_id, po.po_number, s.name as supplier_name')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id')
            ->where('po.status', 'pending_approval')
            ->get()->getResultArray();

        foreach ($rows as $r) {
            if ($this->alreadyOpen('po_approval', null, null, $r['po_id'])) continue;
            $this->db->table('alerts')->insert([
                'alert_type'  => 'po_approval',
                'po_id'       => $r['po_id'],
                'message'     => "{$r['po_number']} from {$r['supplier_name']} awaiting admin approval",
                'priority'    => 'normal',
            ]);
        }
    }

    // If a condition that generated an alert no longer holds (restocked, PO resolved, etc.), close it out.
    private function autoResolveStale(): void
    {
        $open = $this->db->table('alerts')->whereIn('alert_type', self::AUTO_TYPES)->where('is_resolved', 0)->get()->getResultArray();

        foreach ($open as $a) {
            $stillValid = match ($a['alert_type']) {
                'low_stock' => $this->db->table('inventory_batches')->where('batch_id', $a['batch_id'])
                    ->where('quantity_avail <= reorder_level', null, false)->countAllResults() > 0,
                'near_expiry' => $this->db->table('inventory_batches')->where('batch_id', $a['batch_id'])
                    ->where('quantity_avail >', 0)
                    ->where('expires_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)', null, false)->countAllResults() > 0,
                'expired' => $this->db->table('inventory_batches')->where('batch_id', $a['batch_id'])
                    ->where('quantity_avail >', 0)->where('expires_at <', date('Y-m-d'))->countAllResults() > 0,
                'po_approval' => $this->db->table('purchase_orders')->where('po_id', $a['po_id'])->where('status', 'pending_approval')->countAllResults() > 0,
                default => true,
            };

            if (!$stillValid) {
                $this->db->table('alerts')->where('alert_id', $a['alert_id'])
                    ->update(['is_resolved' => 1, 'resolved_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    public function getCounts(): array
    {
        $count = fn($type) => $this->db->table('alerts')->where('alert_type', $type)->where('is_resolved', 0)->countAllResults();
        return [
            'low_stock'    => $count('low_stock'),
            'near_expiry'  => $count('near_expiry'),
            'expired'      => $count('expired'),
            'po_approval'  => $count('po_approval'),
        ];
    }

    public function getFeed(string $status = 'open', string $type = '', string $priority = '', ?int $assignedTo = null, int $page = 1, int $perPage = 5): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function ($builder) use ($status, $type, $priority, $assignedTo) {
        if ($status === 'open') $builder->where('is_resolved', 0);
        if ($status === 'resolved') $builder->where('is_resolved', 1);
        if ($type !== '') $builder->where('alert_type', $type);
        if ($priority !== '') $builder->where('priority', $priority);
        if ($assignedTo) $builder->where('assigned_to', $assignedTo);
        return $builder;
    };

    $countBuilder = $this->db->table('alerts');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('alerts as a')
        ->select('a.*, u.full_name as assigned_name')
        ->join('users as u', 'u.user_id = a.assigned_to', 'left');
    $apply($builder);
    $builder->orderBy('a.priority', 'DESC')->orderBy('a.due_date', 'ASC')->orderBy('a.created_at', 'DESC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

    public function getById(int $id)
    {
        return $this->db->table('alerts')->where('alert_id', $id)->get()->getRow();
    }

    public function saveManual(array $payload, ?int $id = null): void
{
    if ($id) {
        $this->db->table('alerts')->where('alert_id', $id)->update($payload);
    } else {
        $payload['alert_type'] = 'assigned_task';
        $this->db->table('alerts')->insert($payload);
    }
}

    public function resolve(int $id, ?string $note = null): void
{
    $this->db->table('alerts')->where('alert_id', $id)->update([
        'is_resolved' => 1,
        'resolved_at' => date('Y-m-d H:i:s'),
        'resolution_note' => $note,
    ]);
}

    public function removeAlert(int $id): void
{
    $this->db->table('alerts')->where('alert_id', $id)->delete();
}


    public function getAssignableStaff(): array
    {
        return $this->db->table('users')->select('user_id, full_name')->whereIn('role', ['admin', 'staff'])->where('is_active', 1)->get()->getResultArray();
    }

    public function getHeaderNotifications(int $limit = 6): array
{
    $count = $this->db->table('alerts')->where('is_resolved', 0)->countAllResults();

    $recent = $this->db->table('alerts')
        ->where('is_resolved', 0)
        ->orderBy('priority', 'DESC')
        ->orderBy('created_at', 'DESC')
        ->limit($limit)
        ->get()->getResultArray();

    return ['count' => $count, 'recent' => $recent];
}

}
