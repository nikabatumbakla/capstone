<?php

namespace App\Models\Client;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getClientProfile(int $clientId)
    {
        return $this->db->table('institutional_clients')->where('client_id', $clientId)->get()->getRow();
    }

    public function getKpis(int $clientId): array
    {
        $thisYear = date('Y');
        return [
            'active_orders'    => $this->db->table('sales_orders')->where('client_id', $clientId)->where('status !=', 'delivered')->where('status !=', 'cancelled')->countAllResults(),
            'total_orders_ytd' => $this->db->table('sales_orders')->where('client_id', $clientId)->where('YEAR(created_at)', $thisYear)->countAllResults(),
            'pending_payment'  => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'unpaid')->countAllResults(),
            'total_spend_ytd'  => $this->db->table('sales_orders')->selectSum('total')->where('client_id', $clientId)->where('YEAR(created_at)', $thisYear)->where('status !=', 'cancelled')->get()->getRow()->total ?? 0,
        ];
    }

    public function getRecentOrders(int $clientId, int $limit = 5): array
    {
        return $this->db->table('sales_orders as so')
            ->select('so.*, (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count')
            ->where('so.client_id', $clientId)
            ->orderBy('so.created_at', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }

    // Orders where payment came in via check but hasn't been confirmed cleared yet —
    // the real exposure that matters once "pay before delivery" is the policy.
    public function getPendingClearance(int $clientId, int $limit = 5): array
    {
        return $this->db->table('sales_orders')
            ->where('client_id', $clientId)
            ->where('payment_method', 'check')
            ->where('payment_status', 'unpaid')
            ->orderBy('created_at', 'ASC')
            ->limit($limit)->get()->getResultArray();
    }

    public function getActiveAnnouncements(int $limit = 3): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->table('bulletin_posts')
            ->where('is_published', 1)
            ->groupStart()->where('target_audience', 'all')->orWhere('target_audience', 'clients')->groupEnd()
            ->groupStart()->where('starts_at IS NULL', null, false)->orWhere('starts_at <=', $now)->groupEnd()
            ->groupStart()->where('ends_at IS NULL', null, false)->orWhere('ends_at >=', $now)->groupEnd()
            ->orderBy('is_pinned', 'DESC')->orderBy('created_at', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }
}