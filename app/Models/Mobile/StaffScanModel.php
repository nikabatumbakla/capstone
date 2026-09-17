<?php

namespace App\Models\Mobile;

use CodeIgniter\Model;

class StaffScanModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function findByBarcode(string $barcode)
    {
        return $this->db->table('products as p')
            ->select('p.product_id, p.name, p.unit, p.barcode_value,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as total_stock')
            ->where('p.barcode_value', $barcode)
            ->where('p.is_active', 1)
            ->get()->getRow();
    }

    public function generateBarcode(): string
    {
        do {
            $code = '200' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $exists = $this->db->table('products')->where('barcode_value', $code)->countAllResults();
        } while ($exists > 0);
        return $code;
    }

    public function createProductWithBarcode(string $name, int $categoryId, string $unit): array
    {
        $barcode = $this->generateBarcode();
        $this->db->table('products')->insert([
            'category_id'    => $categoryId,
            'name'           => $name,
            'unit'           => $unit,
            'barcode_value'  => $barcode,
            'barcode_type'   => 'CODE128',
            'is_active'      => 1,
        ]);
        return ['product_id' => $this->db->insertID(), 'barcode' => $barcode];
    }

    public function getSuppliers(): array
    {
        return $this->db->table('suppliers')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();
    }

    public function getSupplierProducts(int $supplierId): array
    {
        return $this->db->table('supplier_product_catalog as spc')
            ->select('p.product_id, p.name, p.unit, p.barcode_value')
            ->join('products as p', 'p.product_id = spc.product_id')
            ->where('spc.supplier_id', $supplierId)
            ->get()->getResultArray();
    }

    public function getCategories(): array
    {
        return $this->db->table('categories')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();
    }

    public function processInbound(int $productId, int $qty, ?int $supplierId, ?string $batchNumber, ?string $expiresAt, int $staffUserId): void
    {
        $this->db->transStart();

        $lastBatch = $this->db->table('inventory_batches')->where('product_id', $productId)->orderBy('received_at', 'DESC')->get()->getRow();

        $this->db->table('inventory_batches')->insert([
            'product_id'     => $productId,
            'supplier_id'    => $supplierId,
            'batch_number'   => $batchNumber ?: ('MOB-' . date('Ymd') . '-' . mt_rand(100, 999)),
            'quantity_in'    => $qty,
            'quantity_avail' => $qty,
            'sell_price'     => $lastBatch->sell_price ?? 0,
            'reorder_level'  => $lastBatch->reorder_level ?? 5,
            'expires_at'     => $expiresAt ?: null,
        ]);
        $batchId = $this->db->insertID();

        $this->db->table('stock_movements')->insert([
            'product_id'     => $productId,
            'batch_id'       => $batchId,
            'movement_type'  => 'inbound',
            'quantity'       => $qty,
            'reference_type' => 'mobile_manual',
            'scanned_by'     => $staffUserId,
            'scan_mode'      => 'mobile_inbound',
        ]);

        $this->db->transComplete();
    }

    public function processOutbound(int $productId, int $qty, string $reason, int $staffUserId): array
    {
        $batch = $this->db->table('inventory_batches')->where('product_id', $productId)->where('quantity_avail >', 0)->orderBy('expires_at', 'ASC')->get()->getRow();
        if (!$batch || $batch->quantity_avail < $qty) {
            return ['success' => false, 'message' => 'Not enough stock available for this deduction.'];
        }

        $this->db->transStart();

        $this->db->table('inventory_batches')->where('batch_id', $batch->batch_id)
            ->set('quantity_avail', "quantity_avail - {$qty}", false)->update();

        $this->db->table('stock_movements')->insert([
            'product_id'     => $productId,
            'batch_id'       => $batch->batch_id,
            'movement_type'  => 'outbound',
            'quantity'       => $qty,
            'reference_type' => 'mobile_manual',
            'scanned_by'     => $staffUserId,
            'scan_mode'      => 'mobile_outbound',
            'reason'         => $reason,
        ]);

        $this->db->transComplete();
        \App\Libraries\AutoReorder::check($productId);

        return ['success' => true, 'message' => 'Stock deducted successfully.'];
    }

    public function getOpenPurchaseOrders(): array
{
    return $this->db->table('purchase_orders as po')
        ->select('po.po_id, po.po_number, s.name as supplier_name')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->whereIn('po.status', ['sent', 'acknowledged', 'in_transit', 'partial'])
        ->orderBy('po.created_at', 'DESC')
        ->get()->getResultArray();
}

    public function getPoItems(int $poId): array
    {
        return $this->db->table('purchase_order_items as poi')
            ->select('poi.po_id, poi.product_id, poi.qty_ordered, poi.qty_received, p.name, p.barcode_value, p.unit')
            ->join('products as p', 'p.product_id = poi.product_id')
            ->where('poi.po_id', $poId)
            ->get()->getResultArray();
    }

public function processGrrScan(int $poId, int $productId, int $qtyReceived, string $condition, int $qtyRejected, ?string $notes, int $staffUserId): array
{
    if ($this->isDuplicateScan($productId, $poId, 'po', 'mobile_grr', $staffUserId)) {
        return ['success' => false, 'message' => 'This item was just scanned — please wait a moment before scanning again to avoid double counting.'];
    }

    $this->db->transStart();

    $poItem = $this->db->table('purchase_order_items')->where('po_id', $poId)->where('product_id', $productId)->get()->getRow();
    if (!$poItem) { $this->db->transComplete(); return ['success' => false, 'message' => 'Item not found on this PO.']; }

    $newReceivedTotal = ($poItem->qty_received ?? 0) + $qtyReceived;
    $this->db->table('purchase_order_items')->where('po_id', $poId)->where('product_id', $productId)
        ->update(['qty_received' => $newReceivedTotal]);

    $qtyRejected = min($qtyRejected, $qtyReceived);
    $goodQty = $qtyReceived - $qtyRejected;

    $lastBatch = $this->db->table('inventory_batches')->where('product_id', $productId)->orderBy('received_at', 'DESC')->get()->getRow();

    $batchId = null;
    if ($goodQty > 0) {
        $this->db->table('inventory_batches')->insert([
            'product_id'     => $productId,
            'po_id'          => $poId,
            'batch_number'   => 'GRR-' . date('Ymd') . '-' . mt_rand(100, 999),
            'quantity_in'    => $goodQty,
            'quantity_avail' => $goodQty,
            'sell_price'     => $lastBatch->sell_price ?? 0,
            'reorder_level'  => $lastBatch->reorder_level ?? 5,
        ]);
        $batchId = $this->db->insertID();

        $this->db->table('stock_movements')->insert([
            'product_id'     => $productId,
            'batch_id'       => $batchId,
            'movement_type'  => 'inbound',
            'quantity'       => $goodQty,
            'reference_id'   => $poId,
            'reference_type' => 'po',
            'scanned_by'     => $staffUserId,
            'scan_mode'      => 'mobile_grr',
        ]);
    }

    $thisScanHadIssue = $condition !== 'good' || $qtyReceived < ($poItem->qty_ordered - ($poItem->qty_received ?? 0));

    $this->db->table('goods_receipts')->insert([
        'po_id'        => $poId,
        'received_by'  => $staffUserId,
        'delivery_date'=> date('Y-m-d'),
        'status'       => $thisScanHadIssue ? 'discrepancy' : 'complete',
        'notes'        => $notes ?: 'Received via mobile iScan.',
    ]);
    $grrId = $this->db->insertID();

    $this->db->table('goods_receipt_items')->insert([
        'grr_id'           => $grrId,
        'product_id'       => $productId,
        'batch_id'         => $batchId,
        'qty_expected'     => $poItem->qty_ordered,
        'qty_received'     => $qtyReceived,
        'condition_status' => $condition,
        'qty_rejected'     => $qtyRejected,
        'notes'            => $notes,
    ]);

    if ($condition !== 'good' && $qtyRejected > 0) {
        $labels = ['damaged' => 'Damaged', 'wrong_item' => 'Wrong Item', 'expired' => 'Expired on Arrival'];
        $label = $labels[$condition] ?? ucfirst($condition);
        $this->db->table('procurement_returns')->insert([
            'po_id'          => $poId,
            'product_id'     => $productId,
            'batch_id'       => $batchId,
            'quantity'       => $qtyRejected,
            'reason'         => "{$label} — flagged via mobile iScan." . ($notes ? " Notes: {$notes}" : ''),
            'status'         => 'pending',
            'source'         => 'staff_mobile_grr',
            'discrepancy_type' => $condition,
            'processed_by'   => $staffUserId,
        ]);
    }

    $allItems = $this->db->table('purchase_order_items')->where('po_id', $poId)->get()->getResultArray();
    $fullyReceived = true;
    foreach ($allItems as $item) {
        if ($item['qty_received'] < $item['qty_ordered']) { $fullyReceived = false; break; }
    }
    $newStatus = $fullyReceived ? 'received' : 'partial';
    $this->db->table('purchase_orders')->where('po_id', $poId)->update(['status' => $newStatus, 'received_date' => date('Y-m-d')]);

    $this->db->transComplete();

    return ['success' => true, 'message' => 'GRR item recorded.'];
}

    public function getOpenSalesOrders(): array
{
    return $this->db->table('sales_orders as so')
        ->select("so.order_id, so.order_number, so.fulfillment_type, so.payment_method, so.payment_status,
            COALESCE(ic.organization, gc.name) as client_name")
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
        ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
        ->where('so.status', 'pending')
        ->orderBy('so.created_at', 'ASC')
        ->get()->getResultArray();
}

public function updateSoStatus(int $orderId, int $staffUserId): array
{
    $order = $this->db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
    if (!$order) return ['success' => false, 'message' => 'Order not found.'];
    if ($order->status !== 'pending') return ['success' => false, 'message' => 'This order has already been processed.'];

    $items = $this->db->table('sales_order_items')->where('order_id', $orderId)->get()->getResultArray();
    foreach ($items as $i) {
        if ($i['scanned_qty'] < $i['quantity']) {
            return ['success' => false, 'message' => 'Not all items have been scanned and verified yet.'];
        }
    }

    $isPickup = $order->fulfillment_type === 'pickup';
    $newStatus = $isPickup ? 'ready_for_pickup' : 'out_for_delivery';

    $requiresPrepayment = !$isPickup
        && in_array($order->payment_method, ['cheque', 'bank_transfer'])
        && $order->payment_status !== 'paid';

    if ($requiresPrepayment) {
        return ['success' => false, 'message' =>
            "This order is paid via " . strtoupper(str_replace('_', ' ', $order->payment_method)) .
            " and must be confirmed PAID before it can be dispatched."];
    }

    $this->db->table('sales_orders')->where('order_id', $orderId)->update(['status' => $newStatus]);

    if (!empty($order->client_id)) {
        $message = $isPickup
            ? "Your order {$order->order_number} is ready for pickup at the store."
            : "Your order {$order->order_number} is out for delivery.";
        \App\Models\Client\NotificationModel::notify($this->db, (int) $order->client_id, $message, '/client/orders/my-orders');
    }

    return ['success' => true, 'status' => $newStatus, 'label' => $isPickup ? 'Ready for Pickup' : 'Out for Delivery'];
}

    public function getSoItems(int $orderId): array
{
    return $this->db->table('sales_order_items as soi')
        ->select('soi.order_id, soi.product_id, soi.quantity as qty_ordered, soi.scanned_qty, p.name, p.barcode_value, p.unit')
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('soi.order_id', $orderId)
        ->get()->getResultArray();
}

    public function processOutboundScan(int $orderId, int $productId, int $qtyScanned, int $staffUserId): array
{
    if ($this->isDuplicateScan($productId, $orderId, 'sales_order', 'mobile_outbound', $staffUserId)) {
        return ['success' => false, 'message' => 'This item was just scanned — please wait a moment before scanning again to avoid double counting.'];
    }

    $item = $this->db->table('sales_order_items')->where('order_id', $orderId)->where('product_id', $productId)->get()->getRow();
    if (!$item) return ['success' => false, 'message' => 'Item not found on this order.'];

    $remaining = $item->quantity - ($item->scanned_qty ?? 0);
    if ($qtyScanned > $remaining) {
        return ['success' => false, 'message' => "Only {$remaining} units remaining to scan for this item."];
    }

    $batch = $this->db->table('inventory_batches')->where('product_id', $productId)->where('quantity_avail >', 0)->orderBy('expires_at', 'ASC')->get()->getRow();
    if (!$batch || $batch->quantity_avail < $qtyScanned) {
        return ['success' => false, 'message' => 'Not enough stock available to fulfill this item.'];
    }

    $this->db->transStart();

    $this->db->table('inventory_batches')->where('batch_id', $batch->batch_id)
        ->set('quantity_avail', "quantity_avail - {$qtyScanned}", false)->update();

    $this->db->table('sales_order_items')->where('order_id', $orderId)->where('product_id', $productId)
        ->set('scanned_qty', "scanned_qty + {$qtyScanned}", false)->update();

    $this->db->table('stock_movements')->insert([
        'product_id'     => $productId,
        'batch_id'       => $batch->batch_id,
        'movement_type'  => 'outbound',
        'quantity'       => $qtyScanned,
        'reference_id'   => $orderId,
        'reference_type' => 'sales_order',
        'scanned_by'     => $staffUserId,
        'scan_mode'      => 'mobile_outbound',
    ]);

    $this->db->transComplete();
    \App\Libraries\AutoReorder::check($productId);

    $allItems = $this->db->table('sales_order_items')->where('order_id', $orderId)->get()->getResultArray();
    $orderFullyScanned = true;
    foreach ($allItems as $i) {
        if ($i['scanned_qty'] < $i['quantity']) { $orderFullyScanned = false; break; }
    }

    return ['success' => true, 'message' => 'Item verified and deducted from stock.', 'order_fully_scanned' => $orderFullyScanned];
}

private function isDuplicateScan(int $productId, int $referenceId, string $referenceType, string $scanMode, int $staffUserId): bool
{
    $recent = $this->db->table('stock_movements')
        ->where('product_id', $productId)
        ->where('reference_id', $referenceId)
        ->where('reference_type', $referenceType)
        ->where('scan_mode', $scanMode)
        ->where('scanned_by', $staffUserId)
        ->where('moved_at >=', date('Y-m-d H:i:s', time() - 5))
        ->countAllResults();
    return $recent > 0;
}


}