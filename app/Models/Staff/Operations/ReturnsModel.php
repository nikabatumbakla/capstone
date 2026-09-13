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

    // Staff-filed returns exist ONLY for walk-in/no-account customers — institutional
    // clients self-report via Confirm Receipt in their own portal, and pickup orders
    // (any customer type) are inspected in person, so neither needs this path.
    public function getReturns(string $status = '', string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($status, $search) {
            $b->where('so.guest_client_id IS NOT NULL', null, false);
            if ($status !== '') $b->where('sr.status', $status);
            if ($search !== '') $b->groupStart()->like('so.order_number', $search)->orLike('gc.name', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('sales_returns as sr')
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('sales_returns as sr')
            ->select("sr.*, so.order_number, gc.name as organization, p.name as product_name")
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id')
            ->join('products as p', 'p.product_id = sr.product_id', 'left');
        $apply($builder);
        $builder->orderBy('sr.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getCounts(): array
    {
        $base = fn() => $this->db->table('sales_returns as sr')
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->where('so.guest_client_id IS NOT NULL', null, false);

        return [
            'pending'  => $base()->where('sr.status', 'pending')->countAllResults(),
            'approved' => $base()->where('sr.status', 'approved')->countAllResults(),
            'rejected' => $base()->where('sr.status', 'rejected')->countAllResults(),
        ];
    }

    // Only walk-in/no-account DELIVERY orders that reached 'delivered' with no
    // active return already filed — pickup is excluded (inspected in person).
    public function getEligibleOrders(): array
    {
        return $this->db->table('sales_orders as so')
            ->select('so.order_id, so.order_number, gc.name as organization')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id')
            ->where('so.guest_client_id IS NOT NULL', null, false)
            ->where('so.fulfillment_type', 'delivery')
            ->where('so.status', 'delivered')
            ->where("so.order_id NOT IN (SELECT order_id FROM sales_returns WHERE status != 'rejected')", null, false)
            ->orderBy('so.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getOrderItems(int $orderId): array
    {
        return $this->db->table('sales_order_items as soi')
            ->select('soi.product_id, soi.batch_id, soi.quantity, soi.unit_price, p.name, gc.name as organization')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->join('sales_orders as so', 'so.order_id = soi.order_id')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id')
            ->where('soi.order_id', $orderId)
            ->get()->getResultArray();
    }

    // Always lands pending — staff reports it, admin still decides. The order is
    // flagged 'return_pending' the same way a client-auto-filed return would be.
    public function submitReturn(array $payload): void
    {
        $item = $this->db->table('sales_order_items')
            ->select('unit_price')
            ->where('order_id', $payload['order_id'])
            ->where('product_id', $payload['product_id'])
            ->get()->getRow();

        $payload['status'] = 'pending';
        $payload['refund_amount'] = round(($item->unit_price ?? 0) * $payload['quantity'], 2);
        $payload['source'] = 'staff_walkin';

        $this->db->transStart();
        $this->db->table('sales_returns')->insert($payload);
        $this->db->table('sales_orders')->where('order_id', $payload['order_id'])->update(['status' => 'return_pending']);
        $this->db->transComplete();
    }
}