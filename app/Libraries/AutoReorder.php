<?php

namespace App\Libraries;

class AutoReorder
{
    // Fires automatically whenever a product's stock changes, from ANY controller.
    // Creates exactly ONE auto-generated PO for ONE product — never bundled — for admin to approve.
    public static function check($productId)
    {
        $db = \Config\Database::connect();

        $product = $db->table('products')->where('product_id', $productId)->get()->getRow();
        if (!$product || !$product->supplier_id) {
            return; // cannot reorder a product with no supplier assigned
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

        $supplier = $db->table('suppliers')->where('supplier_id', $product->supplier_id)->get()->getRow();
        if (!$supplier) {
            return;
        }

        $catalog = $db->table('supplier_product_catalog')
            ->where('supplier_id', $product->supplier_id)
            ->where('product_id', $productId)
            ->get()->getRow();
        $unitCost = $catalog ? $catalog->unit_cost : ($batch->cost_price ?: 0);

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
    }
}