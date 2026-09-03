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
            if ($search !== '') $b->groupStart()->like('po.po_number', $search)->orLike('s.name', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('purchase_orders as po')->join('suppliers as s', 's.supplier_id = po.supplier_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('purchase_orders as po')
            ->select('po.*, s.name as supplier')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id');
        $apply($builder);
        $builder->orderBy('po.expected_date', 'ASC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getPoItemsForInspection(int $poId)
    {
        $po = $this->db->table('purchase_orders as po')
            ->select('po.po_id, po.po_number, s.name as sname, po.supplier_dr_number')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id')
            ->where('po.po_id', $poId)
            ->get()->getRow();

        if (!$po) return null;

        $items = $this->db->table('purchase_order_items as poi')
    ->select('poi.product_id, poi.unit_cost, p.name, p.barcode_value, p.unit, poi.qty_ordered')
    ->join('products as p', 'p.product_id = poi.product_id')
    ->where('poi.po_id', $poId)
    ->get()->getResultArray();

        return ['po' => $po, 'items' => $items];
    }

    // Mirrors the exact logic in Admin\Operations\Procurement::save_grr() so both
    // portals write identical GRR/inventory/movement records — one implementation, two entry points.
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
        $deliveryRef = $data['delivery_ref'];
        $notes = $data['notes'];

        $po = $this->db->table('purchase_orders')->where('po_id', $poId)->get()->getRow();
        if (!$po) return ['success' => false, 'message' => 'Purchase Order not found.'];

        $hasDiscrepancy = false;
        foreach ($productIds as $index => $pid) {
            if ((int) $qtyReceived[$index] !== (int) $qtyExpected[$index]) { $hasDiscrepancy = true; break; }
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
            $itemNote = ($receivedQty !== $expectedQty) ? ($receivedQty < $expectedQty ? 'Short-delivered' : 'Over-delivered') : null;

            $this->db->table('goods_receipt_items')->insert([
                'grr_id' => $grrId, 'product_id' => $pid, 'batch_id' => null,
                'qty_expected' => $expectedQty, 'qty_received' => $receivedQty, 'notes' => $itemNote,
            ]);
            $griId = $this->db->insertID();

            $this->db->table('purchase_order_items')->where('po_id', $poId)->where('product_id', $pid)->update(['qty_received' => $receivedQty]);

            if ($receivedQty > 0) {
                $lastBatch = $this->db->table('inventory_batches')->where('product_id', $pid)->orderBy('received_at', 'DESC')->get()->getRow();

                $this->db->table('inventory_batches')->insert([
                    'product_id' => $pid, 'supplier_id' => $po->supplier_id, 'po_id' => $poId,
                    'batch_number' => 'BAT-' . date('Ymd') . '-' . str_pad($pid, 4, '0', STR_PAD_LEFT) . '-' . $index,
                    'lot_number' => $lotNumbers[$index] !== '' ? $lotNumbers[$index] : null,
                    'expires_at' => $expiresAts[$index] !== '' ? $expiresAts[$index] : null,
                    'cost_price' => $unitCosts[$index] ?? 0, 'sell_price' => $sellPrices[$index],
                    'quantity_in' => $receivedQty, 'quantity_avail' => $receivedQty,
                    'reorder_level' => $lastBatch ? $lastBatch->reorder_level : 5,
                    'received_at' => date('Y-m-d H:i:s'),
                ]);
                $batchId = $this->db->insertID();

                $this->db->table('goods_receipt_items')->where('gri_id', $griId)->update(['batch_id' => $batchId]);

                $this->db->table('stock_movements')->insert([
                    'product_id' => $pid, 'batch_id' => $batchId, 'movement_type' => 'inbound',
                    'quantity' => $receivedQty, 'reference_id' => $poId, 'reference_type' => 'po',
                    'scanned_by' => $staffUserId, 'scan_mode' => 'inbound_stock_in',
                    'reason' => $itemNote, 'notes' => $notes !== '' ? $notes : null,
                ]);
            }
        }

        $this->db->table('purchase_orders')->where('po_id', $poId)->update(['status' => $poStatus, 'received_date' => date('Y-m-d')]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'message' => 'Failed to record delivery — please try again.'];
        }

        $message = $hasDiscrepancy
            ? 'Delivery recorded with discrepancies — PO marked Partial. Inventory updated with actual quantities received.'
            : 'Delivery fully verified — inventory updated and PO closed.';
        return ['success' => true, 'message' => $message];
    }
}