<?php

namespace App\Libraries;

class AutoReorder
{
    public static function check($productId)
    {
        $db = \Config\Database::connect();

        $product = $db->table('products')->where('product_id', $productId)->get()->getRow();
        if (!$product) {
            return;
        }

        // FIXED: products.supplier_id is a legacy column that's frequently null under
        // the current model — the real, current supplier relationship lives in
        // supplier_product_catalog. Picks the fastest available supplier for this
        // product, same convention used in the DSS forecast's lead-time lookup.
        $catalogEntry = $db->table('supplier_product_catalog as spc')
            ->select('spc.supplier_id, spc.unit_cost')
            ->join('suppliers as s', 's.supplier_id = spc.supplier_id')
            ->where('spc.product_id', $productId)
            ->orderBy('s.lead_time_days', 'ASC')
            ->limit(1)
            ->get()->getRow();

        if (!$catalogEntry) {
            return; // cannot reorder a product with no supplier catalog entry
        }

        $batch = $db->table('inventory_batches')
            ->where('product_id', $productId)
            ->orderBy('received_at', 'DESC')
            ->get()->getRow();

        if (!$batch || $batch->quantity_avail > $batch->reorder_level) {
            return; // stock is fine
        }

        $alreadyQueued = $db->table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.po_id = poi.po_id')
            ->where('poi.product_id', $productId)
            ->where('po.is_auto_generated', 1)
            ->whereIn('po.status', ['pending_approval', 'approved', 'sent', 'acknowledged', 'in_transit'])
            ->countAllResults();
        if ($alreadyQueued > 0) {
            return; // don't duplicate an already-pending auto reorder
        }

        $supplier = $db->table('suppliers')->where('supplier_id', $catalogEntry->supplier_id)->get()->getRow();
        if (!$supplier) {
            return;
        }

        $unitCost = $catalogEntry->unit_cost ?: ($batch->cost_price ?: 0);
        $suggestedQty = max(1, ($batch->reorder_level * 2) - $batch->quantity_avail);

        $leadDays = (int) ($supplier->lead_time_days ?: 7);
        $expectedDate = date('Y-m-d', strtotime("+{$leadDays} days"));

        $db->table('purchase_orders')->insert([
            'supplier_id'       => $supplier->supplier_id,
            'po_number'         => 'PO-' . date('Y') . '-' . mt_rand(1000, 9999),
            'status'            => 'pending_approval',
            'is_auto_generated' => 1,
            'expected_date'     => $expectedDate,
            'total_amount'      => $suggestedQty * $unitCost,
            'created_by'        => session()->get('user_id') ?? 1,
            'notes'             => "Auto-reorder: {$product->name} at {$batch->quantity_avail} units (reorder level {$batch->reorder_level}, {$leadDays}-day lead time)."
        ]);
        $poId = $db->insertID();

        $db->table('purchase_order_items')->insert([
            'po_id'       => $poId,
            'product_id'  => $productId,
            'qty_ordered' => $suggestedQty,
            'unit_cost'   => $unitCost
        ]);

        $totalStock = $db->table('inventory_batches')->selectSum('quantity_avail')->where('product_id', $productId)->get()->getRow()->quantity_avail ?? 0;

        if ($totalStock <= 0) {
            $pendingPo = $db->table('purchase_order_items as poi')
                ->select('po.po_id, po.po_number, po.status')
                ->join('purchase_orders as po', 'po.po_id = poi.po_id')
                ->where('poi.product_id', $productId)
                ->whereIn('po.status', ['pending_approval', 'approved', 'sent', 'acknowledged', 'in_transit'])
                ->orderBy('po.created_at', 'DESC')->get()->getRow();

            $db->table('stockout_events')->insert([
                'product_id'   => $productId,
                'product_name' => $product->name ?? 'Unknown product',
                'po_id'        => $pendingPo->po_id ?? null,
                'po_number'    => $pendingPo->po_number ?? null,
                'is_seen'      => 0,
            ]);
        }
    }
}