<?php

namespace App\Models\Staff\Operations;

use CodeIgniter\Model;

class GoodsReceiptModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getPendingDeliveries(string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($search) {
            $b->whereIn('po.status', ['sent', 'acknowledged', 'in_transit']);
            if ($search !== '') $b->groupStart()->like('po.po_number', $search)->orLike('s.name', $search)->orLike('gs.name', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('purchase_orders as po')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
            ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('purchase_orders as po')
            ->select('po.*, COALESCE(s.name, gs.name) as supplier')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
            ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left');
        $apply($builder);
        $builder->orderBy('po.expected_date', 'ASC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getPoItemsForInspection(int $poId)
    {
        $po = $this->db->table('purchase_orders as po')
            ->select('po.po_id, po.po_number, COALESCE(s.name, gs.name) as sname, po.supplier_dr_number')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
            ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left')
            ->where('po.po_id', $poId)
            ->get()->getRow();

        if (!$po) return null;

        $items = $this->db->table('purchase_order_items as poi')
            ->select("poi.product_id, poi.unit_cost, poi.qty_ordered, p.name, p.barcode_value, p.unit,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = poi.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as last_sell_price")
            ->join('products as p', 'p.product_id = poi.product_id')
            ->where('poi.po_id', $poId)
            ->get()->getResultArray();

        return ['po' => $po, 'items' => $items];
    }

    public function saveGrr(array $data, int $staffUserId): array
    {
        $poId = $data['po_id'];
        $productIds = $data['product_ids'];
        $qtyReceived = $data['qty_received'];
        $qtyExpected = $data['qty_expected'];
        $unitCosts = $data['unit_costs'];
        $lotNumbers = $data['lot_numbers'];
        $expiresAts = $data['expires_ats'];
        $sellPrices = $data['sell_prices'];
        $conditions = $data['conditions'] ?? [];
        $qtyRejected = $data['qty_rejected'] ?? [];
        $deliveryRef = $data['delivery_ref'];
        $notes = $data['notes'];

        $conditionLabels = ['damaged' => 'Damaged', 'wrong_item' => 'Wrong Item', 'expired' => 'Expired on Arrival'];

        $po = $this->db->table('purchase_orders')->where('po_id', $poId)->get()->getRow();
        if (!$po) return ['success' => false, 'message' => 'Purchase Order not found.'];
        if ($po->status !== 'in_transit') {
            return ['success' => false, 'message' => 'This order cannot be verified yet — the supplier must dispatch it first.'];
        }

        $hasDiscrepancy = false;
        foreach ($productIds as $index => $pid) {
            if ((int) $qtyReceived[$index] !== (int) $qtyExpected[$index]) { $hasDiscrepancy = true; break; }
            $cond = $conditions[$index] ?? 'good';
            $rejected = (int) ($qtyRejected[$index] ?? 0);
            if ($cond !== 'good' && $rejected > 0) { $hasDiscrepancy = true; break; }
        }
        $grrStatus = $hasDiscrepancy ? 'discrepancy' : 'complete';
        $poStatus = $hasDiscrepancy ? 'partial' : 'received';

        $this->db->transStart();

        $this->db->table('goods_receipts')->insert([
            'po_id' => $poId, 'received_by' => $staffUserId,
            'delivery_ref' => $deliveryRef !== '' ? $deliveryRef : null,
            'delivery_date' => date('Y-m-d'), 'status' => $grrStatus,
            'notes' => $notes !== '' ? $notes : null,
        ]);
        $grrId = $this->db->insertID();

        foreach ($productIds as $index => $pid) {
            $receivedQty = (int) $qtyReceived[$index];
            $expectedQty = (int) $qtyExpected[$index];
            $condition = $conditions[$index] ?? 'good';
            $rejectedQty = min((int) ($qtyRejected[$index] ?? 0), $receivedQty);
            $goodQty = $receivedQty - $rejectedQty;

            $itemNote = ($receivedQty !== $expectedQty)
                ? ($receivedQty < $expectedQty ? 'Short-delivered' : 'Over-delivered')
                : null;
            if ($condition !== 'good' && $rejectedQty > 0) {
                $label = $conditionLabels[$condition] ?? ucfirst($condition);
                $itemNote = trim(($itemNote ? $itemNote . ' — ' : '') . "{$label} ({$rejectedQty} units)");
            }

            $this->db->table('goods_receipt_items')->insert([
                'grr_id' => $grrId, 'product_id' => $pid, 'batch_id' => null,
                'qty_expected' => $expectedQty, 'qty_received' => $receivedQty,
                'condition_status' => $condition, 'qty_rejected' => $rejectedQty,
                'notes' => $itemNote,
            ]);
            $griId = $this->db->insertID();

            $this->db->table('purchase_order_items')->where('po_id', $poId)->where('product_id', $pid)->update(['qty_received' => $receivedQty]);

            if ($receivedQty > 0) {
                $lastBatch = $this->db->table('inventory_batches')->where('product_id', $pid)->orderBy('received_at', 'DESC')->get()->getRow();

                $this->db->table('inventory_batches')->insert([
                    'product_id' => $pid, 'supplier_id' => $po->supplier_id, 'po_id' => $poId,
                    // Uses grr-item id (auto-increment) instead of date+index — guaranteed globally
                    // unique, unlike the old scheme which could collide across two deliveries
                    // of the same product received on the same day.
                    'batch_number' => 'BAT-' . $poId . '-' . $griId,
                    'lot_number' => $lotNumbers[$index] !== '' ? $lotNumbers[$index] : null,
                    'expires_at' => $expiresAts[$index] !== '' ? $expiresAts[$index] : null,
                    'cost_price' => $unitCosts[$index] ?? 0, 'sell_price' => $sellPrices[$index],
                    'quantity_in' => $receivedQty, 'quantity_avail' => $goodQty,
                    'reorder_level' => $lastBatch ? $lastBatch->reorder_level : 5,
                    'received_at' => date('Y-m-d H:i:s'),
                ]);
                $batchId = $this->db->insertID();

                $this->db->table('goods_receipt_items')->where('gri_id', $griId)->update(['batch_id' => $batchId]);

                if ($goodQty > 0) {
                    $this->db->table('stock_movements')->insert([
                        'product_id' => $pid, 'batch_id' => $batchId, 'movement_type' => 'inbound',
                        'quantity' => $goodQty, 'reference_id' => $poId, 'reference_type' => 'po',
                        'scanned_by' => $staffUserId, 'scan_mode' => 'inbound_stock_in',
                        'reason' => $itemNote, 'notes' => $notes !== '' ? $notes : null,
                    ]);
                }

                // Auto-file a Supplier Return for anything flagged — same pattern as admin's GRR.
                // Staff reports the physical condition; admin still decides approve/reject.
                if ($condition !== 'good' && $rejectedQty > 0) {
                    $label = $conditionLabels[$condition] ?? ucfirst($condition);
                    $reasonText = "{$label} — flagged by staff during Goods Receipt Inspection."
                        . ($notes !== '' ? ' Notes: ' . $notes : '');

                    $this->db->table('procurement_returns')->insert([
                        'po_id' => $poId, 'product_id' => $pid, 'batch_id' => $batchId,
                        'quantity' => $rejectedQty, 'reason' => $reasonText,
                        'refund_amount' => round((float) ($unitCosts[$index] ?? 0) * $rejectedQty, 2),
                        'status' => 'pending', 'source' => 'staff_grr', 'discrepancy_type' => $condition,
                        'processed_by' => $staffUserId,
                    ]);
                }
            }
        }

        $this->db->table('purchase_orders')->where('po_id', $poId)->update(['status' => $poStatus, 'received_date' => date('Y-m-d')]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'message' => 'Failed to record delivery — please try again.'];
        }

        $message = $hasDiscrepancy
            ? 'Delivery recorded with discrepancies — PO marked Partial. Inventory updated, and any flagged items have been sent to Supplier Returns for admin review.'
            : 'Delivery fully verified — inventory updated and PO closed.';
        return ['success' => true, 'message' => $message];
    }
}