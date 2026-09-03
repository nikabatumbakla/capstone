<?php

namespace App\Models\Staff\Operations;

use CodeIgniter\Model;

class SalesOrdersModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getOrders(string $status = '', string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($status, $search) {
            if ($status !== '') $b->where('so.status', $status);
            if ($search !== '') $b->groupStart()->like('so.order_number', $search)->orLike('ic.organization', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('sales_orders as so')->join('institutional_clients as ic', 'ic.client_id = so.client_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('sales_orders as so')
            ->select('so.*, ic.organization, ic.address as client_address, ic.phone as client_phone,
                (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id');
        $apply($builder);
        $builder->orderBy('so.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getCounts(): array
    {
        return [
            'pending'    => $this->db->table('sales_orders')->where('status', 'pending')->countAllResults(),
            'processing' => $this->db->table('sales_orders')->where('status', 'processing')->countAllResults(),
            'shipped'    => $this->db->table('sales_orders')->where('status', 'shipped')->countAllResults(),
            'delivered'  => $this->db->table('sales_orders')->where('status', 'delivered')->countAllResults(),
        ];
    }

    public function getOrderDetails(int $orderId)
    {
        $order = $this->db->table('sales_orders as so')
            ->select('so.*, ic.organization, ic.address as client_address, ic.phone as client_phone')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->where('so.order_id', $orderId)
            ->get()->getRow();

        if (!$order) return null;

        $items = $this->db->table('sales_order_items as soi')
            ->select('soi.*, p.name, p.sku')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->where('soi.order_id', $orderId)
            ->get()->getResultArray();

        return ['order' => $order, 'items' => $items];
    }

    // Staff can move an order forward through the fulfillment states,
    // and log the transition to order_delivery_tracking for a real audit trail.
    public function updateStatus(int $orderId, string $newStatus, int $staffUserId, ?string $notes = null): void
{
    $this->db->transStart();

    $this->db->table('sales_orders')->where('order_id', $orderId)->update(['status' => $newStatus]);

    // Fetch fulfillment_type to pick the right tracking label
    $order = $this->db->table('sales_orders')->select('fulfillment_type')->where('order_id', $orderId)->get()->getRow();
    $isPickup = $order && $order->fulfillment_type === 'pickup';

    $trackingStatusMap = $isPickup
        ? [
            'processing' => 'preparing',
            'shipped'    => 'packed',     // "packed" = ready for pickup, nothing is actually dispatched
            'delivered'  => 'delivered',  // reused as "claimed" for pickup orders
        ]
        : [
            'processing' => 'preparing',
            'shipped'    => 'dispatched',
            'delivered'  => 'delivered',
        ];

    if (isset($trackingStatusMap[$newStatus])) {
        $this->db->table('order_delivery_tracking')->insert([
            'order_id'   => $orderId,
            'status'     => $trackingStatusMap[$newStatus],
            'updated_by' => $staffUserId,
            'notes'      => $notes,
        ]);
    }

    if ($newStatus === 'delivered') {
        $this->db->table('sales_orders')->where('order_id', $orderId)->update(['delivered_at' => date('Y-m-d H:i:s')]);
    }

    $this->db->transComplete();
}

public function confirmPayment(int $orderId, string $reference, int $staffUserId): void
{
    $this->db->table('sales_orders')->where('order_id', $orderId)->update([
        'payment_status'    => 'paid',
        'payment_reference' => $reference ?: null,
    ]);
}

}