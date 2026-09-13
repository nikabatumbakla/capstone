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
        'active'    => $this->db->table('sales_orders')->where('client_id', $clientId)->whereNotIn('status', ['delivered', 'cancelled'])->countAllResults(),
        'ytd'       => $this->db->table('sales_orders')->where('client_id', $clientId)->where('YEAR(created_at)', date('Y'))->countAllResults(),
        'unpaid'    => $this->db->table('sales_orders')->where('client_id', $clientId)->where('payment_status', 'unpaid')->countAllResults(),
        'completed' => $this->db->table('sales_orders')->where('client_id', $clientId)->where('status', 'delivered')->countAllResults(),
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
        if ($status === 'completed') $b->where('so.status', 'delivered');
        if ($search !== '') $b->like('so.order_number', $search);
        return $b;
    };

    $countBuilder = $this->db->table('sales_orders as so');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('sales_orders as so')
        ->select("so.*, (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count,
            (SELECT COUNT(*) FROM sales_returns WHERE order_id = so.order_id) as has_return");
    $apply($builder);
    $builder->orderBy('so.created_at', 'DESC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

public function reportIssue(int $orderId, int $clientId, array $productIds, array $batchIds, array $qtyOrdered, array $conditions, array $qtyFlagged, int $userId): array
{
    $order = $this->db->table('sales_orders')
        ->where('order_id', $orderId)->where('client_id', $clientId)
        ->where('fulfillment_type', 'delivery')->where('status', 'delivered')
        ->get()->getRow();
    if (!$order) return ['success' => false, 'message' => 'Unable to file a return for this order.'];

    $alreadyFiled = $this->db->table('sales_returns')->where('order_id', $orderId)->countAllResults();
    if ($alreadyFiled > 0) return ['success' => false, 'message' => 'A return has already been filed for this order.'];

    $conditionLabels = ['damaged' => 'Damaged', 'wrong_item' => 'Wrong Item', 'missing' => 'Never Received'];
    $anyFiled = false;

    foreach ($productIds as $index => $pid) {
        $condition = $conditions[$index] ?? 'good';
        if ($condition === 'good') continue;
        $flaggedQty = min((int) ($qtyFlagged[$index] ?? 0), (int) $qtyOrdered[$index]);
        if ($flaggedQty <= 0) continue;

        $unitPrice = $this->db->table('sales_order_items')->select('unit_price')->where('order_id', $orderId)->where('product_id', $pid)->get()->getRow()->unit_price ?? 0;
        $restockCondition = $condition === 'missing' ? 'resellable' : $condition;
        $label = $conditionLabels[$condition] ?? ucfirst($condition);

        $this->db->table('sales_returns')->insert([
            'order_id'          => $orderId,
            'product_id'        => $pid,
            'batch_id'          => $batchIds[$index] ?: null,
            'quantity'          => $flaggedQty,
            'restock_condition' => $restockCondition,
            'refund_amount'     => round($unitPrice * $flaggedQty, 2),
            'processed_by'      => $userId,
            'reason'            => "{$label} — reported by client after delivery.",
            'source'            => 'client_post_delivery',
            'status'            => 'pending',
        ]);
        $anyFiled = true;
    }

    if (!$anyFiled) return ['success' => false, 'message' => 'Please flag at least one item to report an issue.'];
    return ['success' => true];
}

public function getOrderForIssueReport(int $orderId, int $clientId)
{
    $order = $this->db->table('sales_orders')
        ->where('order_id', $orderId)->where('client_id', $clientId)
        ->where('fulfillment_type', 'delivery')->where('status', 'delivered')
        ->get()->getRow();
    if (!$order) return null;

    $items = $this->db->table('sales_order_items as soi')
        ->select('soi.product_id, soi.batch_id, soi.quantity, soi.unit_price, p.name, p.barcode_value, p.unit')
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('soi.order_id', $orderId)->get()->getResultArray();

    return ['order' => $order, 'items' => $items];
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
        ->select("soi.*, p.name, p.barcode_value, p.unit,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('soi.order_id', $orderId)
        ->get()->getResultArray();

    return ['order' => $order, 'items' => $items];
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

public function getOrderForConfirm(int $orderId, int $clientId)
{
    $order = $this->db->table('sales_orders')
        ->where('order_id', $orderId)
        ->where('client_id', $clientId)
        ->where('fulfillment_type', 'delivery')
        ->where('status', 'out_for_delivery')
        ->get()->getRow();

    if (!$order) return null;

    $items = $this->db->table('sales_order_items as soi')
        ->select('soi.product_id, soi.batch_id, soi.quantity, soi.unit_price, p.name, p.barcode_value, p.unit')
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('soi.order_id', $orderId)
        ->get()->getResultArray();

    return ['order' => $order, 'items' => $items];
}

public function confirmReceipt(int $orderId, int $clientId, array $productIds, array $batchIds, array $qtyOrdered, array $conditions, array $qtyFlagged, int $userId): array
{
    $order = $this->db->table('sales_orders')
        ->where('order_id', $orderId)->where('client_id', $clientId)
        ->where('fulfillment_type', 'delivery')
        ->where('status', 'out_for_delivery')
        ->get()->getRow();

    if (!$order) return ['success' => false, 'message' => 'This order cannot be confirmed right now.'];

    $conditionLabels = ['damaged' => 'Damaged', 'wrong_item' => 'Wrong Item', 'missing' => 'Never Received'];
    $anyIssueFiled = false;
    $this->db->transStart();

    foreach ($productIds as $index => $pid) {
        $condition = $conditions[$index] ?? 'good';
        if ($condition === 'good') continue;

        $flaggedQty = min((int) ($qtyFlagged[$index] ?? 0), (int) $qtyOrdered[$index]);
        if ($flaggedQty <= 0) continue;

        $anyIssueFiled = true;
        $unitPrice = $this->db->table('sales_order_items')
            ->select('unit_price')->where('order_id', $orderId)->where('product_id', $pid)
            ->get()->getRow()->unit_price ?? 0;

        $restockCondition = $condition === 'missing' ? 'resellable' : $condition;
        $label = $conditionLabels[$condition] ?? ucfirst($condition);

        $this->db->table('sales_returns')->insert([
            'order_id'          => $orderId,
            'product_id'        => $pid,
            'batch_id'          => $batchIds[$index] ?: null,
            'quantity'          => $flaggedQty,
            'restock_condition' => $restockCondition,
            'refund_amount'     => round($unitPrice * $flaggedQty, 2),
            'processed_by'      => $userId,
            'reason'            => "{$label} — reported by client during delivery confirmation.",
            'source'            => 'client_confirmation',
            'status'            => 'pending',
        ]);
    }

    // Only a fully clean delivery closes as 'delivered'. Any flagged item keeps
    // the order open at 'return_pending' until the return itself is resolved —
    // it is NOT done just because the client received something.
    $this->db->table('sales_orders')->where('order_id', $orderId)->update([
        'status' => $anyIssueFiled ? 'return_pending' : 'delivered',
    ]);

    $this->db->transComplete();
    if ($this->db->transStatus() === false) return ['success' => false, 'message' => 'Failed to confirm receipt. Please try again.'];

    return ['success' => true, 'has_issue' => $anyIssueFiled];
}

public function getOrderForPayment(int $orderId, int $clientId)
{
    return $this->db->table('sales_orders')
        ->where('order_id', $orderId)
        ->where('client_id', $clientId)
        ->where('fulfillment_type', 'delivery')
        ->whereIn('payment_method', ['cheque', 'bank_transfer'])
        ->where('payment_status', 'unpaid')
        ->get()->getRow();
}

public function submitPaymentReference(int $orderId, int $clientId, string $reference): bool
{
    $order = $this->getOrderForPayment($orderId, $clientId);
    if (!$order) return false;

    $this->db->table('sales_orders')->where('order_id', $orderId)->update([
        'client_payment_ref'          => $reference,
        'client_payment_submitted_at' => date('Y-m-d H:i:s'),
    ]);
    return true;
}


}