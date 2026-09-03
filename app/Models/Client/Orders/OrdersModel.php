<?php

namespace App\Models\Client\Orders;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getKpis(int $clientId): array
    {
        return [
            'active'  => $this->db->table('sales_orders')->where('client_id', $clientId)->whereNotIn('status', ['delivered', 'cancelled'])->countAllResults(),
            'ytd'     => $this->db->table('sales_orders')->where('client_id', $clientId)->where('YEAR(created_at)', date('Y'))->countAllResults(),
            'unpaid'  => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'unpaid')->countAllResults(),
        ];
    }

    public function getMyOrders(int $clientId, string $status = '', string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($clientId, $status, $search) {
            $b->where('so.client_id', $clientId);
            if ($status === 'ytd') $b->where('YEAR(so.created_at)', date('Y'));
            if ($status === 'unpaid') $b->where('so.payment_status', 'unpaid');
            if ($status === 'active') $b->whereNotIn('so.status', ['delivered', 'cancelled']);
            if ($search !== '') $b->like('so.order_number', $search);
            return $b;
        };

        $countBuilder = $this->db->table('sales_orders as so');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('sales_orders as so')
            ->select('so.*, (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count');
        $apply($builder);
        $builder->orderBy('so.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    // Ownership check baked in — a client can never view another client's order by guessing an ID
    public function getOrderDetails(int $orderId, int $clientId)
{
    $order = $this->db->table('sales_orders as so')
        ->select('so.*, ic.organization, ic.address as client_addr, ic.phone, ic.tin')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->where('so.order_id', $orderId)
        ->where('so.client_id', $clientId)
        ->get()->getRow();

    if (!$order) return null;

    $items = $this->db->table('sales_order_items as soi')
        ->select("soi.*, p.name, p.sku, p.unit,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('soi.order_id', $orderId)
        ->get()->getResultArray();

    // Latest delivery tracking status, if any
    $tracking = $this->db->table('order_delivery_tracking')
        ->where('order_id', $orderId)
        ->orderBy('updated_at', 'DESC')
        ->get()->getRow();

    return ['order' => $order, 'items' => $items, 'tracking' => $tracking];
}

    public function getStoreInfo(): array
    {
        $rows = $this->db->table('store_settings')->get()->getResultArray();
        $info = [];
        foreach ($rows as $r) $info[$r['setting_key']] = $r['setting_value'];
        return $info;
    }

    // Server is the ONLY source of truth for price and stock — never trusts anything from the client's form.
    // Mirrors admin's Sales::save_order() FEFO logic exactly, so both entry points stay consistent.
    public function saveOrder(int $clientId, array $productIds, array $qtys, string $fulfillmentType, string $deliveryAddress, string $paymentMethod, string $notes, int $createdBy): array
{
    $client = $this->db->table('institutional_clients')->where('client_id', $clientId)->where('user_id IS NOT NULL', null, false)->get()->getRow();
    if (!$client) return ['success' => false, 'message' => 'Client account could not be verified.'];

    $this->db->transStart();

    $this->db->table('sales_orders')->insert([
        'client_id'        => $clientId,
        'order_number'     => 'SO-' . date('Y') . '-' . mt_rand(1000, 9999),
        'invoice_number'   => 'INV-' . date('Y') . '-' . mt_rand(1000, 9999),
        'status'           => 'pending',
        'payment_method'   => $paymentMethod,
        'fulfillment_type' => $fulfillmentType,
        'delivery_address' => $fulfillmentType === 'delivery' ? $deliveryAddress : null,
        'payment_status'   => 'unpaid',
        'discount'         => 0,
        'subtotal'         => 0,
        'vat_amount'       => 0,
        'total'            => 0,
        'notes'            => $notes !== '' ? $notes : null,
        'created_by'       => $createdBy,
    ]);
    $orderId = $this->db->insertID();

    $vatableGross = 0;
    $exemptGross = 0;
    $productsOrdered = [];
    $cappedCount = 0;

    foreach ($productIds as $index => $pid) {
        $qty = (int) ($qtys[$index] ?? 0);
        if ($qty <= 0) continue;

        $batch = $this->db->table('inventory_batches as ib')
            ->select('ib.batch_id, ib.quantity_avail, ib.sell_price, p.is_vat_exempt')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('ib.product_id', $pid)
            ->where('ib.quantity_avail >', 0)
            ->orderBy('ib.expires_at', 'ASC')
            ->get()->getRow();

        if (!$batch) continue;

        if ($qty > $batch->quantity_avail) {
            $qty = (int) $batch->quantity_avail;
            $cappedCount++;
        }
        if ($qty <= 0) continue;

        $lineTotal = $batch->sell_price * $qty;
        if ($batch->is_vat_exempt) $exemptGross += $lineTotal; else $vatableGross += $lineTotal;

        $this->db->table('sales_order_items')->insert([
            'order_id'   => $orderId,
            'product_id' => $pid,
            'batch_id'   => $batch->batch_id,
            'quantity'   => $qty,
            'unit_price' => $batch->sell_price,
            'subtotal'   => $lineTotal,
        ]);

        $this->db->table('inventory_batches')->where('batch_id', $batch->batch_id)
            ->set('quantity_avail', "quantity_avail - {$qty}", false)->update();

        $this->db->table('stock_movements')->insert([
            'product_id'     => $pid,
            'batch_id'       => $batch->batch_id,
            'movement_type'  => 'outbound',
            'quantity'       => $qty,
            'reference_id'   => $orderId,
            'reference_type' => 'order',
            'scanned_by'     => $createdBy,
            'scan_mode'      => 'client_self_order',
        ]);

        $productsOrdered[] = $pid;
    }

    if (empty($productsOrdered)) {
        $this->db->transComplete();
        return ['success' => false, 'message' => 'None of the selected items are currently available in the requested quantity.'];
    }

    $grossTotal = $vatableGross + $exemptGross;
    $vatAmount = $vatableGross - ($vatableGross / 1.12);
    $subtotal = ($vatableGross / 1.12) + $exemptGross;

    $this->db->table('sales_orders')->where('order_id', $orderId)->update([
        'subtotal'   => $subtotal,
        'vat_amount' => $vatAmount,
        'total'      => $grossTotal,
    ]);

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        return ['success' => false, 'message' => 'Failed to place order. Please try again.'];
    }

    return ['success' => true, 'order_id' => $orderId, 'products_ordered' => array_unique($productsOrdered), 'capped' => $cappedCount];
}
}