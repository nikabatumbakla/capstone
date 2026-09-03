<?php

namespace App\Models\Staff\Operations;

use CodeIgniter\Model;

class ReturnsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getReturns(string $status = '', string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($status, $search) {
            if ($status !== '') $b->where('sr.status', $status);
            if ($search !== '') $b->groupStart()->like('so.order_number', $search)->orLike('ic.organization', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('sales_returns as sr')
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('sales_returns as sr')
            ->select('sr.*, so.order_number, ic.organization, p.name as product_name')
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->join('products as p', 'p.product_id = sr.product_id', 'left');
        $apply($builder);
        $builder->orderBy('sr.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getCounts(): array
    {
        return [
            'pending'  => $this->db->table('sales_returns')->where('status', 'pending')->countAllResults(),
            'approved' => $this->db->table('sales_returns')->where('status', 'approved')->countAllResults(),
            'rejected' => $this->db->table('sales_returns')->where('status', 'rejected')->countAllResults(),
        ];
    }

    // Delivered orders with no active (pending/approved) return already filed — same rule as admin
    public function getEligibleOrders(): array
    {
        return $this->db->table('sales_orders as so')
            ->select('so.order_id, so.order_number, ic.organization')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->where('so.status', 'delivered')
            ->where("so.order_id NOT IN (SELECT order_id FROM sales_returns WHERE status != 'rejected')", null, false)
            ->get()->getResultArray();
    }

    public function getOrderItems(int $orderId): array
    {
        return $this->db->table('sales_order_items as soi')
            ->select('soi.product_id, soi.batch_id, soi.quantity, soi.unit_price, p.name, ic.organization')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->join('sales_orders as so', 'so.order_id = soi.order_id')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->where('soi.order_id', $orderId)
            ->get()->getResultArray();
    }

    // Staff files the request — always lands as pending. Stock is never touched here;
    // only an approval (admin-side) restores inventory, matching the segregation of duties.
    public function submitReturn(array $payload): void
    {
        $payload['status'] = 'pending';
        $this->db->table('sales_returns')->insert($payload);
    }
}