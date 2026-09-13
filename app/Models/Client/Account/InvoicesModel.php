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
            // Genuinely unpaid, no reference submitted yet
            'unpaid_count'       => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'unpaid')->where('client_payment_ref IS NULL', null, false)->countAllResults(),
            // Client submitted proof, awaiting admin confirmation
            'awaiting_clearance' => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'unpaid')->where('client_payment_ref IS NOT NULL', null, false)->countAllResults(),
            'paid_ytd'           => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'paid')->where('YEAR(created_at)', date('Y'))->countAllResults(),
        ];
    }

    public function getInvoices(int $clientId, string $status = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($clientId, $status) {
            $b->where('client_id', $clientId);
            if ($status === 'unpaid') {
                $b->where('payment_status', 'unpaid')->where('client_payment_ref IS NULL', null, false);
            } elseif ($status === 'submitted') {
                $b->where('payment_status', 'unpaid')->where('client_payment_ref IS NOT NULL', null, false);
            } elseif ($status === 'paid') {
                $b->where('payment_status', 'paid');
            }
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
            ->select('soi.*, p.name, p.barcode_value')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->where('soi.order_id', $orderId)
            ->get()->getResultArray();

        return ['order' => $order, 'items' => $items];
    }


    public function getStoreInfo(): array
{
    $rows = $this->db->table('store_settings')->get()->getResultArray();
    $info = [];
    foreach ($rows as $row) $info[$row['setting_key']] = $row['setting_value'];
    return $info;
}

}