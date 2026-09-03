<?php

namespace App\Models\Supplier;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getScorecard(int $supplierId)
    {
        $row = $this->db->table('supplier_scorecards')->where('supplier_id', $supplierId)->get()->getRow();

        // No scorecard yet (brand-new supplier, zero delivery history) — return honest zeros, not a crash
        if (!$row) {
            return (object) [
                'on_time_rate'  => null,
                'accuracy_rate' => null,
                'total_orders'  => 0,
            ];
        }
        return $row;
    }

    public function getKpis(int $supplierId): array
    {
        return [
            'open_pos'      => $this->db->table('purchase_orders')->where('supplier_id', $supplierId)->whereIn('status', ['sent', 'acknowledged', 'in_transit'])->countAllResults(),
            'pending_ack'   => $this->db->table('purchase_orders')->where('supplier_id', $supplierId)->where('status', 'sent')->countAllResults(),
            'completed_ytd' => $this->db->table('purchase_orders')->where('supplier_id', $supplierId)->where('status', 'received')->where('YEAR(created_at)', date('Y'))->countAllResults(),
        ];
    }

    public function getRecentPos(int $supplierId, int $limit = 5): array
{
    return $this->db->table('purchase_orders as po')
        ->select("po.po_id, po.po_number, po.total_amount, po.expected_date, po.status,
            (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.po_id) as items")
        ->where('po.supplier_id', $supplierId)
        ->orderBy('po.created_at', 'DESC')
        ->limit($limit)->get()->getResultArray();
}

    public function getActiveAnnouncements(int $limit = 3): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->table('bulletin_posts')
            ->where('is_published', 1)
            ->groupStart()->where('target_audience', 'all')->orWhere('target_audience', 'suppliers')->groupEnd()
            ->groupStart()->where('starts_at IS NULL', null, false)->orWhere('starts_at <=', $now)->groupEnd()
            ->groupStart()->where('ends_at IS NULL', null, false)->orWhere('ends_at >=', $now)->groupEnd()
            ->orderBy('is_pinned', 'DESC')->orderBy('created_at', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }
}