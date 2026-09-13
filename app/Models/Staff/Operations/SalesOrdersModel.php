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

    public function getCounts(): array
    {
        return [
            'pending'     => $this->db->table('sales_orders')->where('status', 'pending')->countAllResults(),
            'in_progress' => $this->db->table('sales_orders')->whereIn('status', ['ready_for_pickup', 'out_for_delivery'])->countAllResults(),
            'returns'     => $this->db->table('sales_orders')->where('status', 'return_pending')->countAllResults(),
            'delivered'   => $this->db->table('sales_orders')->where('status', 'delivered')->countAllResults(),
        ];
    }

    public function getOrders(string $status = '', string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($status, $search) {
            if ($status === 'pending') $b->where('so.status', 'pending');
            elseif ($status === 'in_progress') $b->whereIn('so.status', ['ready_for_pickup', 'out_for_delivery']);
            elseif ($status === 'returns') $b->where('so.status', 'return_pending');
            elseif ($status === 'delivered') $b->where('so.status', 'delivered');
            if ($search !== '') $b->like('so.order_number', $search);
            return $b;
        };

        $countBuilder = $this->db->table('sales_orders as so')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('sales_orders as so')
            ->select("so.*, COALESCE(ic.organization, gc.name) as client_name,
                (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count")
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left');
        $apply($builder);
        $builder->orderBy('so.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getOrderDetails(int $orderId)
    {
        $order = $this->db->table('sales_orders as so')
            ->select('so.*, so.guest_client_id, COALESCE(ic.organization, gc.name) as organization, COALESCE(ic.address, gc.address) as client_addr, COALESCE(ic.phone, gc.phone) as phone, COALESCE(ic.tin, gc.tin) as client_tin, u.full_name as encoder')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
            ->join('users as u', 'u.user_id = so.created_by', 'left')
            ->where('so.order_id', $orderId)->get()->getRow();

        if (!$order) return null;

        $items = $this->db->table('sales_order_items as soi')
            ->select('soi.*, p.name, p.barcode_value')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->where('soi.order_id', $orderId)->get()->getResultArray();

        $settingsRows = $this->db->table('store_settings')->get()->getResultArray();
        $storeInfo = [];
        foreach ($settingsRows as $row) $storeInfo[$row['setting_key']] = $row['setting_value'];

        return ['order' => $order, 'items' => $items, 'store_info' => $storeInfo];
    }

    // Same status/prepayment rules as Admin\Operations\Sales::update_order_status().
    // No fake in-between states — only real statuses this app's status enum supports.
    public function updateStatus(int $orderId, string $newStatus, int $staffUserId): array
    {
        $order = $this->db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
        if (!$order) return ['success' => false, 'message' => 'Order not found.'];

        $requiresPrepayment = $order->fulfillment_type === 'delivery'
            && in_array($order->payment_method, ['cheque', 'bank_transfer'])
            && $order->payment_status !== 'paid';

        if ($newStatus === 'out_for_delivery' && $requiresPrepayment) {
            return ['success' => false, 'message' =>
                "This order is paid via " . strtoupper(str_replace('_', ' ', $order->payment_method)) .
                " and must be confirmed PAID before it can be dispatched."];
        }

        if ($order->fulfillment_type === 'pickup' && $newStatus === 'delivered' && $order->payment_status !== 'paid') {
            return ['success' => false, 'message' => 'Please confirm payment before marking this order as picked up.'];
        }

        $this->db->table('sales_orders')->where('order_id', $orderId)->update(['status' => $newStatus]);

        $updated = $this->db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
        if (!empty($updated->client_id)) {
            $statusMessages = [
                'ready_for_pickup' => "Your order {$updated->order_number} is ready for pickup at the store.",
                'out_for_delivery' => "Your order {$updated->order_number} is out for delivery.",
                'delivered'        => "Your order {$updated->order_number} has been completed.",
            ];
            if (isset($statusMessages[$newStatus])) {
                \App\Models\Client\NotificationModel::notify($this->db, (int) $updated->client_id, $statusMessages[$newStatus], '/client/orders/my-orders');
            }
        }

        return ['success' => true];
    }

    // Cash / bank_transfer / cheque only — matches admin's Sales::confirm_payment() exactly.
    public function confirmPayment(int $orderId, string $method, string $reference, int $staffUserId): array
    {
        if (!in_array($method, ['cash', 'bank_transfer', 'cheque'])) {
            return ['success' => false, 'message' => 'Please select a valid payment method.'];
        }
        if (in_array($method, ['bank_transfer', 'cheque']) && $reference === '') {
            return ['success' => false, 'message' => 'A reference number is required for bank transfer or cheque payments.'];
        }

        $order = $this->db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
        if (!$order || $order->payment_status === 'paid') {
            return ['success' => false, 'message' => 'Order not found or already marked paid.'];
        }

        $this->db->table('sales_orders')->where('order_id', $orderId)->update([
            'payment_status'    => 'paid',
            'payment_method'    => $method,
            'payment_reference' => $reference ?: null,
            'paid_at'           => date('Y-m-d H:i:s'),
        ]);

        if (!empty($order->client_id)) {
            \App\Models\Client\NotificationModel::notify($this->db, (int) $order->client_id, "Payment confirmed for order {$order->order_number}.", '/client/account/invoices');
        }

        return ['success' => true];
    }
}