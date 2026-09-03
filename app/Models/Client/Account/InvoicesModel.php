<?php

namespace App\Models\Client\Account;

use CodeIgniter\Model;

class InvoicesModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getKpis(int $clientId): array
    {
        $outstanding = $this->db->table('sales_orders')
            ->selectSum('total')
            ->where('client_id', $clientId)
            ->where('payment_status', 'unpaid')
            ->get()->getRow()->total ?? 0;

        return [
            'outstanding_amount' => $outstanding,
            'unpaid_count'       => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'unpaid')->countAllResults(),
            'awaiting_clearance' => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'submitted')->countAllResults(),
            'paid_ytd'           => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'paid')->where('YEAR(created_at)', date('Y'))->countAllResults(),
        ];
    }

    public function getInvoices(int $clientId, string $status = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($clientId, $status) {
            $b->where('client_id', $clientId);
            if ($status !== '') $b->where('payment_status', $status);
            return $b;
        };

        $countBuilder = $this->db->table('sales_orders');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('sales_orders');
        $apply($builder);
        $builder->orderBy('created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getInvoiceDetails(int $orderId, int $clientId)
    {
        $order = $this->db->table('sales_orders as so')
            ->select('so.*, ic.organization, ic.address, ic.tin, ic.phone')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->where('so.order_id', $orderId)
            ->where('so.client_id', $clientId)
            ->get()->getRow();
        if (!$order) return null;

        $items = $this->db->table('sales_order_items as soi')
            ->select('soi.*, p.name')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->where('soi.order_id', $orderId)
            ->get()->getResultArray();

        return ['order' => $order, 'items' => $items];
    }

    // Client submits a payment reference (check number) — this does NOT mark the order paid.
    // It only signals "I've sent payment" so staff/admin can verify and confirm clearance.
    public function submitPaymentReference(int $orderId, int $clientId, string $reference): bool
    {
        $order = $this->db->table('sales_orders')->where('order_id', $orderId)->where('client_id', $clientId)->get()->getRow();
        if (!$order || $order->payment_status !== 'unpaid') return false;

        $this->db->table('sales_orders')->where('order_id', $orderId)->update([
            'payment_reference'    => $reference,
            'payment_submitted_at' => date('Y-m-d H:i:s'),
            'payment_status'       => 'submitted',
        ]);
        return true;
    }
}