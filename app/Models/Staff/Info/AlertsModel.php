<?php

namespace App\Models\Staff\Info;

use CodeIgniter\Model;

class AlertsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getFeed(int $userId, string $type = '', string $status = 'active', string $priority = '', string $unread = '', int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function ($b) use ($userId, $type, $status, $priority, $unread) {
        $b->groupStart()->where('a.assigned_to', $userId)->orWhere('a.assigned_to IS NULL', null, false)->groupEnd();
        if ($status === 'active') $b->where('a.is_resolved', 0);
        if ($status === 'completed') $b->where('a.is_resolved', 1);
        if ($type !== '') $b->where('a.alert_type', $type);
        if ($priority !== '') $b->where('a.priority', $priority);
        if ($unread === '1') $b->where('a.is_read', 0);
        return $b;
    };

    $countBuilder = $this->db->table('alerts as a')->join('products as p', 'p.product_id = a.product_id', 'left');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('alerts as a')
        ->select('a.*, p.name as product_name')
        ->join('products as p', 'p.product_id = a.product_id', 'left');
    $apply($builder);
    $builder->orderBy('a.priority', 'DESC')->orderBy('a.created_at', 'DESC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

    public function getCounts(int $userId): array
    {
        $baseUnresolved = fn() => $this->db->table('alerts')->where('is_resolved', 0)
            ->groupStart()->where('assigned_to', $userId)->orWhere('assigned_to IS NULL', null, false)->groupEnd();

        return [
            'total_active' => $baseUnresolved()->countAllResults(),
            'high_priority' => $baseUnresolved()->where('priority', 'high')->countAllResults(),
            'unread' => $baseUnresolved()->where('is_read', 0)->countAllResults(),
        ];
    }

    public function markAsRead(int $alertId): void
    {
        $this->db->table('alerts')->where('alert_id', $alertId)->update(['is_read' => 1]);
    }

    // Staff can only complete a task that is BOTH type=assigned_task AND assigned specifically to them —
    // system-generated alerts (low_stock, near_expiry, etc.) can never be resolved from here; they clear
    // only when the real condition is fixed, via the admin-side sync job.
    public function completeTask(int $alertId, int $userId): bool
    {
        $alert = $this->db->table('alerts')->where('alert_id', $alertId)->get()->getRow();
        if (!$alert || $alert->alert_type !== 'assigned_task' || (int) $alert->assigned_to !== $userId) {
            return false;
        }

        $this->db->table('alerts')->where('alert_id', $alertId)->update([
            'is_resolved' => 1,
            'is_read'     => 1,
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function getHeaderNotifications(int $userId, int $limit = 8): array
{
    $notifications = [];

    // 1. Open alerts (assigned to me or unassigned system alerts)
    $alerts = $this->db->table('alerts')
        ->where('is_resolved', 0)
        ->groupStart()->where('assigned_to', $userId)->orWhere('assigned_to IS NULL', null, false)->groupEnd()
        ->orderBy('priority', 'DESC')->orderBy('created_at', 'DESC')
        ->limit($limit)->get()->getResultArray();

    foreach ($alerts as $a) {
        $notifications[] = [
            'type'    => $a['alert_type'],
            'icon'    => 'fa-bell',
            'message' => $a['message'],
            'time'    => $a['created_at'],
            'link'    => base_url('staff/info/alerts'),
        ];
    }

    // 2. Escalations awaiting a staff reply
    $escalations = $this->db->table('chatbot_escalations')
        ->where('status', 'open')
        ->orderBy('created_at', 'DESC')->limit(5)->get()->getResultArray();

    foreach ($escalations as $e) {
        $notifications[] = [
            'type'    => 'escalation',
            'icon'    => 'fa-headset',
            'message' => 'A customer is waiting for a response.',
            'time'    => $e['created_at'],
            'link'    => base_url('staff/info/support-queue'),
        ];
    }

    // 3. Deliveries due for inspection today or overdue
    $deliveries = $this->db->table('purchase_orders as po')
        ->select('po.po_number, po.expected_date, s.name as supplier')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->whereIn('po.status', ['sent', 'acknowledged', 'in_transit'])
        ->where('po.expected_date <=', date('Y-m-d'))
        ->orderBy('po.expected_date', 'ASC')->limit(5)->get()->getResultArray();

    foreach ($deliveries as $d) {
        $notifications[] = [
            'type'    => 'delivery',
            'icon'    => 'fa-truck-loading',
            'message' => "{$d['po_number']} from {$d['supplier']} is due for inspection.",
            'time'    => $d['expected_date'],
            'link'    => base_url('staff/operations/goods-receipt'),
        ];
    }

    // Sort merged feed by most recent, trim to limit
    usort($notifications, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));
    $notifications = array_slice($notifications, 0, $limit);

    return ['count' => count($alerts) + count($escalations) + count($deliveries), 'recent' => $notifications];
}

}