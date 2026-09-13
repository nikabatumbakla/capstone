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
            ->select('p.product_id, p.name, p.sku, p.unit, p.barcode_value,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as total_stock')
            ->where('p.barcode_value', $barcode)
            ->where('p.is_active', 1)
            ->get()->getRow();
    }

    public function generateBarcode(): string
    {
        do {
            $code = '200' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT); // EAN13-style, prefixed 200 = internal-use range
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
            ->select('p.product_id, p.name, p.sku, p.unit, p.barcode_value')
            ->join('products as p', 'p.product_id = spc.product_id')
            ->where('spc.supplier_id', $supplierId)
            ->get()->getResultArray();
    }

    public function getCategories(): array
    {
        return $this->db->table('categories')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();
    }

    // Inbound — no PO, manual/ad-hoc stock entry, optionally tied to a supplier for record-keeping
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

    // Outbound — removing stock for a non-sale reason (damage, expiry, write-off, return)
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

    // GRR — the accountable path: only ever tied to a real, open PO
    public function getOpenPurchaseOrders(): array
    {
        return $this->db->table('purchase_orders as po')
            ->select('po.po_id, po.po_number, s.name as supplier_name')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id')
            ->whereIn('po.status', ['sent', 'acknowledged', 'in_transit'])
            ->orderBy('po.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getPoItems(int $poId): array
{
    return $this->db->table('purchase_order_items as poi')
        ->select('poi.po_id, poi.product_id, poi.qty_ordered, poi.qty_received, p.name, p.sku, p.barcode_value, p.unit')
        ->join('products as p', 'p.product_id = poi.product_id')
        ->where('poi.po_id', $poId)
        ->get()->getResultArray();
}

    public function processGrrScan(int $poId, int $productId, int $qtyReceived, int $staffUserId): void
    {
        $this->db->transStart();

        $this->db->table('purchase_order_items')->where('po_id', $poId)->where('product_id', $productId)
            ->set('qty_received', 'qty_received + ' . $qtyReceived, false)->update();

        $poItem = $this->db->table('purchase_order_items')->where('po_id', $poId)->where('product_id', $productId)->get()->getRow();
        $lastBatch = $this->db->table('inventory_batches')->where('product_id', $productId)->orderBy('received_at', 'DESC')->get()->getRow();

        $this->db->table('inventory_batches')->insert([
            'product_id'     => $productId,
            'po_id'          => $poId,
            'batch_number'   => 'GRR-' . date('Ymd') . '-' . mt_rand(100, 999),
            'quantity_in'    => $qtyReceived,
            'quantity_avail' => $qtyReceived,
            'sell_price'     => $lastBatch->sell_price ?? 0,
            'reorder_level'  => $lastBatch->reorder_level ?? 5,
        ]);
        $batchId = $this->db->insertID();

        $this->db->table('stock_movements')->insert([
            'product_id'     => $productId,
            'batch_id'       => $batchId,
            'movement_type'  => 'inbound',
            'quantity'       => $qtyReceived,
            'reference_id'   => $poId,
            'reference_type' => 'po',
            'scanned_by'     => $staffUserId,
            'scan_mode'      => 'mobile_grr',
        ]);

        // Auto-complete the PO if fully received
        $allItems = $this->db->table('purchase_order_items')->where('po_id', $poId)->get()->getResultArray();
        $fullyReceived = true;
        foreach ($allItems as $item) {
            if ($item['qty_received'] < $item['qty_ordered']) { $fullyReceived = false; break; }
        }
        if ($fullyReceived) {
            $this->db->table('purchase_orders')->where('po_id', $poId)->update(['status' => 'received', 'received_date' => date('Y-m-d')]);
        }

        $this->db->transComplete();
    }

    public function getOpenSalesOrders(): array
{
    return $this->db->table('sales_orders as so')
        ->select('so.order_id, so.order_number, ic.organization as client_name')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->whereIn('so.status', ['pending', 'processing'])
        ->orderBy('so.created_at', 'DESC')
        ->get()->getResultArray();
}

public function getSoItems(int $orderId): array
{
    return $this->db->table('sales_order_items as soi')
        ->select('soi.order_id, soi.product_id, soi.quantity as qty_ordered, p.name, p.sku, p.barcode_value, p.unit')
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('soi.order_id', $orderId)
        ->get()->getResultArray();
}

// Outbound — verifying items against a Sales Order before dispatch. Deducts stock once confirmed scanned.
public function processOutboundScan(int $orderId, int $productId, int $qtyScanned, int $staffUserId): array
{
    $batch = $this->db->table('inventory_batches')->where('product_id', $productId)->where('quantity_avail >', 0)->orderBy('expires_at', 'ASC')->get()->getRow();
    if (!$batch || $batch->quantity_avail < $qtyScanned) {
        return ['success' => false, 'message' => 'Not enough stock available to fulfill this item.'];
    }

    $this->db->transStart();

    $this->db->table('inventory_batches')->where('batch_id', $batch->batch_id)
        ->set('quantity_avail', "quantity_avail - {$qtyScanned}", false)->update();

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

    return ['success' => true, 'message' => 'Item verified and deducted from stock.'];
}

}