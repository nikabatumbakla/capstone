<?php

namespace App\Models\Admin\Operations\Procurement;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // ===== SUPPLIERS =====

    public function getSuppliers(string $search, ?string $categoryFilter, int $page, int $perPage): array
{
    $offset = ($page - 1) * $perPage;

    $applyFilters = function($builder) use ($search, $categoryFilter) {
        if ($search !== '') {
            $builder->like('s.name', $search);
        }
        if ($categoryFilter) {
            $catId = (int) $categoryFilter;
            $builder->where(
                "(s.supplier_id IN (SELECT supplier_id FROM supplier_categories WHERE category_id = {$catId})
                  OR s.supplier_id IN (SELECT supplier_id FROM products WHERE category_id = {$catId} AND supplier_id IS NOT NULL))",
                null, false
            );
        }
        return $builder;
    };

    $countBuilder = $this->db->table('suppliers as s')->where('s.is_active', 1);
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $this->db->table('suppliers as s')->select('s.*')->where('s.is_active', 1);
    $applyFilters($builder);
    $builder->orderBy('s.supplier_id', 'DESC');
    $builder->limit($perPage, $offset);
    $suppliers = $builder->get()->getResultArray();

    // Live-computed performance — no more stale supplier_scorecards cache
    foreach ($suppliers as &$s) {
        $stats = \App\Libraries\ScorecardCalculator::calculate($this->db, (int) $s['supplier_id']);
        $s['on_time_rate']         = $stats->on_time_rate;
        $s['accuracy_rate']        = $stats->accuracy_rate;
        $s['total_orders']         = $stats->total_orders;
        $s['avg_lead_time_actual'] = $stats->avg_lead_time_actual;
    }
    unset($s);

    return [
        'data' => $suppliers,
        'total_rows' => $totalRows,
        'total_pages' => max(1, (int) ceil($totalRows / $perPage)),
    ];
}

    public function getCategories(): array
    {
        return $this->db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    // Products belonging to ONE supplier only — used to scope the Create PO form
    public function getSupplierProducts(int $supplierId): array
{
    $supplier = $this->db->table('suppliers')->where('supplier_id', $supplierId)->get()->getRow();
    if (!$supplier) {
        return ['error' => 'Supplier not found'];
    }

    $products = $this->db->table('supplier_product_catalog as spc')
        ->select("p.product_id, p.name, p.barcode_value, p.unit, p.category_id, c.name as cat_name,
            spc.unit_cost, spc.minimum_order_qty,
            (SELECT ib.quantity_avail FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as current_stock,
            (SELECT ib.reorder_level FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as reorder_level")
        ->join('products as p', 'p.product_id = spc.product_id')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->where('spc.supplier_id', $supplierId)
        ->where('p.is_active', 1)
        ->orderBy('c.name', 'ASC')
        ->orderBy('p.name', 'ASC')
        ->get()->getResultArray();

    return ['supplier' => $supplier, 'products' => $products];
}

    public function getSupplierDetails(int $id)
{
    $supplier = $this->db->table('suppliers as s')->where('s.supplier_id', $id)->get()->getRow();
    if (!$supplier) return null;

    $stats = \App\Libraries\ScorecardCalculator::calculate($this->db, $id);
    $supplier->on_time_rate  = $stats->on_time_rate;
    $supplier->accuracy_rate = $stats->accuracy_rate;

    $poStats = $this->db->table('purchase_orders')
        ->select('COUNT(*) as po_count, COALESCE(SUM(total_amount),0) as total_spent')
        ->where('supplier_id', $id)
        ->get()->getRow();

    $recentPOs = $this->db->table('purchase_orders')
        ->select('po_id, po_number, status, total_amount, expected_date, created_at')
        ->where('supplier_id', $id)
        ->orderBy('created_at', 'DESC')
        ->limit(5)
        ->get()->getResultArray();

    $supplier->po_count    = (int) $poStats->po_count;
    $supplier->total_spent = (float) $poStats->total_spent;
    $supplier->recent_pos  = $recentPOs;

    return $supplier;
}

    public function saveSupplier(array $post): void
    {
        $this->db->table('suppliers')->insert([
            'name'           => $post['name'] ?? '',
            'contact_person' => $post['contact'] ?? null,
            'phone'          => $post['phone'] ?? null,
            'email'          => $post['email'] ?? null,
            'address'        => $post['address'] ?? null,
            'lead_time_days' => 7,
            'is_active'      => 1,
        ]);
    }

    // ===== PURCHASE ORDERS =====

    public function getPurchaseOrders(?string $status, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $countBuilder = $this->db->table('purchase_orders as po');
        if ($status) $countBuilder->where('po.status', $status);
        $totalRows = $countBuilder->countAllResults();

        $builder = $this->db->table('purchase_orders as po');
       // getPurchaseOrders() — change the select/join lines to:
        $builder->select("po.*, COALESCE(s.name, gs.name) as supplier_name, (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.po_id) as item_count");
        $builder->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left');
        $builder->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left');
        
        if ($status) $builder->where('po.status', $status);
        $builder->orderBy('po.created_at', 'DESC');
        $builder->limit($perPage, $offset);

        return [
            'data' => $builder->get()->getResultArray(),
            'total_rows' => $totalRows,
            'total_pages' => max(1, (int) ceil($totalRows / $perPage)),
        ];
    }

    public function getPOStats(): array
    {
        $countPending = $this->db->table('purchase_orders')->where('status', 'pending_approval')->countAllResults();

        $poThisMonth = $this->db->table('purchase_orders')
            ->where('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())', null, false)
            ->countAllResults();

        $autoReordersThisMonth = $this->db->table('purchase_orders')
            ->where('is_auto_generated', 1)
            ->where('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())', null, false)
            ->countAllResults();

        $spendRow = $this->db->table('purchase_orders')
            ->selectSum('total_amount')
            ->where('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())', null, false)
            ->where('status !=', 'cancelled')
            ->get()->getRow();

        return [
            'count_pending'            => $countPending,
            'po_this_month'            => $poThisMonth,
            'auto_reorders_this_month' => $autoReordersThisMonth,
            'spend_this_month'         => $spendRow->total_amount ?? 0,
        ];
    }

    public function savePO(array $post): array
    {
        $supplier_id = $post['supplier_id'] ?? null;
        $products    = $post['products'] ?? [];
        $qtys        = $post['qtys'] ?? [];
        $costs       = $post['costs'] ?? [];
        $notes       = trim((string) ($post['notes'] ?? ''));

        $this->db->transStart();

        $this->db->table('purchase_orders')->insert([
            'supplier_id'       => $supplier_id,
            'po_number'         => 'PO-' . date('Y') . '-' . time(),
            'status'            => 'sent',
            'is_auto_generated' => 0,
            'expected_date'     => $post['expected_date'] ?? null,
            'total_amount'      => 0,
            'created_by'        => session()->get('user_id') ?? 1,
            'notes'             => $notes !== '' ? $notes : null,
        ]);
        $po_id = $this->db->insertID();

        $poRow = $this->db->table('purchase_orders')->where('po_id', $po_id)->get()->getRow();
if (!empty($supplier_id)) {
    \App\Models\Supplier\NotificationModel::notify($this->db, (int) $supplier_id, "New Purchase Order {$poRow->po_number} received — please review and respond.", '/supplier/orders/inbox');
}

        $total = 0;
        foreach ($products as $index => $pid) {
            $qty  = (float) ($qtys[$index] ?? 0);
            $cost = (float) ($costs[$index] ?? 0);
            $total += $qty * $cost;

            $this->db->table('purchase_order_items')->insert([
                'po_id'       => $po_id,
                'product_id'  => $pid,
                'qty_ordered' => $qty,
                'unit_cost'   => $cost
            ]);

            // Keep the per-supplier price catalog current so next PO auto-fills correctly
            $existingCatalog = $this->db->table('supplier_product_catalog')
                ->where('supplier_id', $supplier_id)
                ->where('product_id', $pid)
                ->get()->getRow();

            if ($existingCatalog) {
                $this->db->table('supplier_product_catalog')
                    ->where('catalog_id', $existingCatalog->catalog_id)
                    ->update(['unit_cost' => $cost]);
            } else {
                $this->db->table('supplier_product_catalog')->insert([
                    'supplier_id' => $supplier_id,
                    'product_id'  => $pid,
                    'unit_cost'   => $cost
                ]);
            }
        }

        $this->db->table('purchase_orders')->where('po_id', $po_id)->update(['total_amount' => $total]);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['error' => 'Failed to create Purchase Order.'];
        }
        return ['po_id' => $po_id];
    }

    public function rejectPO(int $id): array
    {
        $po = $this->db->table('purchase_orders')->where('po_id', $id)->get()->getRow();

        if (!$po || $po->status !== 'pending_approval') {
            return ['error' => 'Only pending purchase orders can be rejected.'];
        }

        $this->db->table('purchase_orders')->where('po_id', $id)->update(['status' => 'cancelled']);
        return ['success' => true];
    }

    public function approvePO(int $id): void
    {
        $this->db->table('purchase_orders')->where('po_id', $id)->update(['status' => 'sent']);
    }

    public function getPODetails(int $id): array
    {
        $po = $this->db->table('purchase_orders as po')
    ->select('po.*, COALESCE(s.name, gs.name) as sname, u.full_name as creator')
    ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
    ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left')
    ->join('users as u', 'u.user_id = po.created_by', 'left')
    ->where('po.po_id', $id)
    ->get()->getRow();

        if (!$po) {
            return ['error' => 'PO Not Found'];
        }

        $items = $this->db->table('purchase_order_items as poi')
    ->select("poi.*, p.name, p.barcode_value,
        (SELECT ib.quantity_avail FROM inventory_batches ib WHERE ib.product_id = poi.product_id ORDER BY ib.received_at DESC LIMIT 1) as current_stock,
        (SELECT ib.reorder_level FROM inventory_batches ib WHERE ib.product_id = poi.product_id ORDER BY ib.received_at DESC LIMIT 1) as reorder_level,
        (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = poi.product_id ORDER BY ib.received_at DESC LIMIT 1) as last_sell_price")
    ->join('products as p', 'p.product_id = poi.product_id')
    ->where('poi.po_id', $id)
    ->get()->getResultArray();

        $settingsRows = $this->db->table('store_settings')->get()->getResultArray();
        $storeInfo = [];
        foreach ($settingsRows as $row) {
            $storeInfo[$row['setting_key']] = $row['setting_value'];
        }

        return [
            'po'          => $po,
            'items'       => $items,
            'store_info'  => $storeInfo,
            'server_time' => date('Y-m-d H:i:s'),
        ];
    }

    // ===== GOODS RECEIPT =====

    public function getGoodsReceiptList(?string $categoryFilter, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $applyFilter = function($builder) use ($categoryFilter) {
            $builder->whereIn('po.status', ['sent', 'acknowledged', 'in_transit']);
            if ($categoryFilter) {
                $catId = (int) $categoryFilter;
                $builder->where(
                    "po.po_id IN (SELECT poi.po_id FROM purchase_order_items poi JOIN products p ON p.product_id = poi.product_id WHERE p.category_id = {$catId})",
                    null, false
                );
            }
            return $builder;
        };

        $countBuilder = $this->db->table('purchase_orders as po');
        $applyFilter($countBuilder);
        $totalRows = $countBuilder->countAllResults();

        $builder = $this->db->table('purchase_orders as po');
        $builder->select("po.*, COALESCE(s.name, gs.name) as supplier_name");
        $builder->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left');
        $builder->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left');
       
        $applyFilter($builder);
        $builder->orderBy('po.expected_date', 'ASC');
        $builder->limit($perPage, $offset);

        return [
            'data' => $builder->get()->getResultArray(),
            'total_rows' => $totalRows,
            'total_pages' => max(1, (int) ceil($totalRows / $perPage)),
        ];
    }

    public function saveGRR(array $post, $photoFile = null): array
{
    $po_id        = $post['po_id'] ?? null;
    $product_ids  = $post['product_ids'] ?? [];
    $qty_received = $post['qty_received'] ?? [];
    $qty_expected = $post['qty_expected'] ?? [];
    $unit_costs   = $post['unit_costs'] ?? [];
    $lot_numbers  = $post['lot_numbers'] ?? [];
    $expires_ats  = $post['expires_ats'] ?? [];
    $sell_prices  = $post['sell_prices'] ?? [];
    $conditions   = $post['condition_status'] ?? [];
    $qty_rejected = $post['qty_rejected'] ?? [];
    $delivery_ref = trim((string) ($post['delivery_ref'] ?? ''));
    $notes        = trim((string) ($post['notes'] ?? ''));
    $userId       = session()->get('user_id') ?? 1;

    $conditionLabels = ['damaged' => 'Damaged', 'wrong_item' => 'Wrong Item', 'expired' => 'Expired on Arrival'];

    $po = $this->db->table('purchase_orders')->where('po_id', $po_id)->get()->getRow();
    if (!$po) {
        return ['error' => 'Purchase Order not found.'];
    }
    if ($po->status !== 'in_transit') {
        return ['error' => 'This order cannot be verified yet — the supplier must dispatch it first.'];
    }

    // Discrepancy = short/over delivery OR any item flagged with a condition issue
    $hasDiscrepancy = false;
    foreach ($product_ids as $index => $pid) {
        if ((int) $qty_received[$index] !== (int) $qty_expected[$index]) { $hasDiscrepancy = true; break; }
        $cond = $conditions[$index] ?? 'good';
        $rejected = (int) ($qty_rejected[$index] ?? 0);
        if ($cond !== 'good' && $rejected > 0) { $hasDiscrepancy = true; break; }
    }
    $grrStatus = $hasDiscrepancy ? 'discrepancy' : 'complete';
    $poStatus  = $hasDiscrepancy ? 'partial' : 'received';

    $photoPath = null;
    if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
        $newName = $photoFile->getRandomName();
        $photoFile->move(FCPATH . 'public/uploads/grr_photos', $newName);
        $photoPath = 'public/uploads/grr_photos/' . $newName;
    }

    $this->db->transStart();

    // 1. GRR header
    $this->db->table('goods_receipts')->insert([
        'po_id'         => $po_id,
        'received_by'   => $userId,
        'delivery_ref'  => $delivery_ref !== '' ? $delivery_ref : null,
        'delivery_date' => date('Y-m-d'),
        'status'        => $grrStatus,
        'notes'         => $notes !== '' ? $notes : null,
        'photo_path'    => $photoPath,
    ]);
    $grr_id = $this->db->insertID();

    // 2. Per-item processing
    foreach ($product_ids as $index => $pid) {
        $receivedQty = (int) $qty_received[$index];
        $expectedQty = (int) $qty_expected[$index];
        $condition   = $conditions[$index] ?? 'good';
        $rejectedQty = min((int) ($qty_rejected[$index] ?? 0), $receivedQty); // clamp — can't reject more than arrived
        $goodQty     = $receivedQty - $rejectedQty;

        $itemNote = ($receivedQty !== $expectedQty)
            ? ($receivedQty < $expectedQty ? 'Short-delivered' : 'Over-delivered')
            : null;
        if ($condition !== 'good' && $rejectedQty > 0) {
            $label = $conditionLabels[$condition] ?? ucfirst($condition);
            $itemNote = trim(($itemNote ? $itemNote . ' — ' : '') . "{$label} ({$rejectedQty} units)");
        }

        $this->db->table('goods_receipt_items')->insert([
            'grr_id'           => $grr_id,
            'product_id'       => $pid,
            'batch_id'         => null, // linked below once the batch exists
            'qty_expected'     => $expectedQty,
            'qty_received'     => $receivedQty,
            'condition_status' => $condition,
            'qty_rejected'     => $rejectedQty,
            'notes'            => $itemNote,
        ]);
        $gri_id = $this->db->insertID();

        // Reflect what actually arrived back onto the PO's own line item
        $this->db->table('purchase_order_items')
            ->where('po_id', $po_id)
            ->where('product_id', $pid)
            ->update(['qty_received' => $receivedQty]);

        // Only create a stock batch if something actually arrived
        if ($receivedQty > 0) {
            $lastBatch = $this->db->table('inventory_batches')
                ->where('product_id', $pid)
                ->orderBy('received_at', 'DESC')
                ->get()->getRow();

            $this->db->table('inventory_batches')->insert([
                'product_id'     => $pid,
                'supplier_id'    => $po->supplier_id,
                'po_id'          => $po_id,
                'batch_number'   => 'BAT-' . $po_id . '-' . $gri_id,
                'lot_number'     => $lot_numbers[$index] !== '' ? $lot_numbers[$index] : null,
                'expires_at'     => $expires_ats[$index] !== '' ? $expires_ats[$index] : null,
                'cost_price'     => $unit_costs[$index] ?? 0,
                'sell_price'     => $sell_prices[$index],
                'quantity_in'    => $receivedQty,
                'quantity_avail' => $goodQty, // rejected units are excluded from sellable stock
                'reorder_level'  => $lastBatch ? $lastBatch->reorder_level : 5,
                'received_at'    => date('Y-m-d H:i:s'),
            ]);
            $batch_id = $this->db->insertID();

            // Link the GRR item to the batch it created
            $this->db->table('goods_receipt_items')->where('gri_id', $gri_id)->update(['batch_id' => $batch_id]);

            // Log the inbound movement — only the GOOD quantity, since that's what's actually sellable
            if ($goodQty > 0) {
                $this->db->table('stock_movements')->insert([
                    'product_id'     => $pid,
                    'batch_id'       => $batch_id,
                    'movement_type'  => 'inbound',
                    'quantity'       => $goodQty,
                    'reference_id'   => $po_id,
                    'reference_type' => 'po',
                    'scanned_by'     => $userId,
                    'scan_mode'      => 'inbound_stock_in',
                    'reason'         => $itemNote,
                    'notes'          => $notes !== '' ? $notes : null,
                ]);
            }

            // Auto-file a Supplier Return for rejected units. Missing/short quantities are
            // NOT a return — there's nothing physical to send back, that's just a shortage.
            if ($condition !== 'good' && $rejectedQty > 0) {
                $label = $conditionLabels[$condition] ?? ucfirst($condition);
                $reasonText = "{$label} — flagged automatically during Goods Receipt Inspection."
                    . ($notes !== '' ? ' Inspector notes: ' . $notes : '');

                $this->db->table('procurement_returns')->insert([
                    'po_id'            => $po_id,
                    'product_id'       => $pid,
                    'batch_id'         => $batch_id,
                    'quantity'         => $rejectedQty,
                    'reason'           => $reasonText,
                    'refund_amount'    => round((float) ($unit_costs[$index] ?? 0) * $rejectedQty, 2),
                    'status'           => 'pending',
                    'source'           => 'grr_discrepancy',
                    'discrepancy_type' => $condition,
                    'processed_by'     => $userId,
                ]);
            }
        }
    }

    // 3. Close (or partially close) the PO
    $this->db->table('purchase_orders')->where('po_id', $po_id)->update([
        'status'        => $poStatus,
        'received_date' => date('Y-m-d')
    ]);

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        return ['error' => 'Failed to record delivery — please try again.'];
    }

    $message = $hasDiscrepancy
        ? 'Delivery recorded with discrepancies — PO marked Partial. Inventory updated with actual sellable quantities, and any damaged/incorrect items have been filed as a Supplier Return for review.'
        : 'Delivery fully verified — inventory updated and PO closed.';

    return ['success' => true, 'message' => $message];
}

    public function markAsPaid(int $poId, string $paymentMethod, string $paymentReference, int $paidBy): bool
{
    $po = $this->db->table('purchase_orders')
        ->where('po_id', $poId)
        ->whereIn('status', ['acknowledged', 'in_transit', 'partial', 'received'])
        ->where('payment_status', 'unpaid')
        ->get()->getRow();

    if (!$po) return false;

    $this->db->table('purchase_orders')->where('po_id', $poId)->update([
        'payment_status'    => 'paid',
        'payment_method'    => $paymentMethod,
        'payment_reference' => $paymentReference,
        'paid_at'           => date('Y-m-d H:i:s'),
        'paid_by'           => $paidBy,
    ]);

    // Only registered suppliers have a portal account to notify — walk-in
    // suppliers (guest_supplier_id set, supplier_id null) have nowhere to send this.
    if (!empty($po->supplier_id)) {
        \App\Models\Supplier\NotificationModel::notify($this->db, (int) $po->supplier_id, "Payment received for {$po->po_number}.", '/supplier/orders/payments');
    }

    return true;
}

public function getReceivedPOs(): array
{
    return $this->db->table('purchase_orders as po')
        ->select('po.po_id, po.po_number, s.name as sname')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->whereIn('po.status', ['partial', 'received'])
        ->orderBy('po.created_at', 'DESC')
        ->get()->getResultArray();
}

public function getPoItemsForReturn(int $poId): array
{
    return $this->db->table('purchase_order_items as poi')
        ->select("poi.product_id, poi.qty_received, poi.unit_cost, p.name,
            (SELECT ib.batch_id FROM inventory_batches ib WHERE ib.product_id = poi.product_id AND ib.po_id = poi.po_id ORDER BY ib.received_at DESC LIMIT 1) as batch_id")
        ->join('products as p', 'p.product_id = poi.product_id')
        ->where('poi.po_id', $poId)
        ->where('poi.qty_received >', 0)
        ->get()->getResultArray();
}

public function getSupplierReturns(string $status, string $search, int $page, int $perPage): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function ($b) use ($status, $search) {
        if ($status !== 'all') $b->where('pr.status', $status);
        if ($search !== '') {
            $b->groupStart()->like('po.po_number', $search)->orLike('s.name', $search)->groupEnd();
        }
        return $b;
    };

    $countBuilder = $this->db->table('procurement_returns as pr')
        ->join('purchase_orders as po', 'po.po_id = pr.po_id')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('procurement_returns as pr')
        ->select('pr.*, po.po_number, s.name as supplier_name, p.name as product_name')
        ->join('purchase_orders as po', 'po.po_id = pr.po_id')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->join('products as p', 'p.product_id = pr.product_id');
    $apply($builder);
    $builder->orderBy('pr.created_at', 'DESC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

public function saveSupplierReturn(array $post, int $adminId): void
{
    $this->db->table('procurement_returns')->insert([
        'po_id'              => $post['po_id'] ?? null,
        'product_id'         => $post['product_id'] ?? null,
        'batch_id'           => $post['batch_id'] ?: null,
        'quantity'           => (int) ($post['qty'] ?? 0),
        'reason'             => $post['notes'] ?? '',
        'resolution_type'    => $post['resolution_type'] ?? 'exchange',
        'credit_note_number' => $post['credit_note_number'] ?: null,
        'refund_amount'      => ($post['resolution_type'] ?? 'exchange') === 'refund' ? ($post['refund_amount'] ?: null) : null,
        'status'             => 'pending',
        'source'             => 'manual',
        'processed_by'       => $adminId,
    ]);
}

public function approveSupplierReturn(int $returnId, int $adminId): bool
{
    $return = $this->db->table('procurement_returns')->where('return_id', $returnId)->where('status', 'pending')->get()->getRow();
    if (!$return) return false;

    $this->db->table('procurement_returns')->where('return_id', $returnId)->update([
        'status'      => 'approved',
        'resolved_by' => $adminId,
        'resolved_at' => date('Y-m-d H:i:s'),
    ]);
    return true;
}

public function rejectSupplierReturn(int $returnId, int $adminId): bool
{
    $return = $this->db->table('procurement_returns')->where('return_id', $returnId)->where('status', 'pending')->get()->getRow();
    if (!$return) return false;

    $this->db->table('procurement_returns')->where('return_id', $returnId)->update([
        'status'      => 'rejected',
        'resolved_by' => $adminId,
        'resolved_at' => date('Y-m-d H:i:s'),
    ]);
    return true;
}

public function getSupplierReturnDetails(int $returnId)
{
    $row = $this->db->table('procurement_returns as pr')
        ->select('pr.*, po.po_number, s.name as supplier_name, p.name, b.batch_number, u.full_name as resolved_by_name')
        ->join('purchase_orders as po', 'po.po_id = pr.po_id')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->join('products as p', 'p.product_id = pr.product_id')
        ->join('inventory_batches as b', 'b.batch_id = pr.batch_id', 'left')
        ->join('users as u', 'u.user_id = pr.resolved_by', 'left')
        ->where('pr.return_id', $returnId)
        ->get()->getRow();

    if (!$row) return null;

    if ($row->replacement_po_id) {
        $replacementPo = $this->db->table('purchase_orders')->select('po_number, status')->where('po_id', $row->replacement_po_id)->get()->getRow();
        $row->replacement_po_number = $replacementPo->po_number ?? null;
        $row->replacement_po_status = $replacementPo->status ?? null;
    }

    return $row;
}

public function markReturnSentToSupplier(int $returnId): bool
{
    $return = $this->db->table('procurement_returns as pr')->select('pr.*, po.supplier_id')->join('purchase_orders as po','po.po_id=pr.po_id')->where('pr.return_id', $returnId)->get()->getRow();
if (!empty($return->supplier_id)) {
    \App\Models\Supplier\NotificationModel::notify($this->db, (int) $return->supplier_id, "A return has been sent back to you — please confirm once received.", '/supplier/orders/returns');
}
    if (!$return) return false;

    $this->db->table('procurement_returns')->where('return_id', $returnId)->update([
        'supplier_return_status' => 'sent_to_supplier',
        'sent_to_supplier_at'    => date('Y-m-d H:i:s'),
    ]);
    return true;
}

// Creates a zero-cost PO for the same product/quantity — the actual "exchange" fulfillment step
public function createReplacementPO(int $returnId, int $adminId): array
{
    $return = $this->db->table('procurement_returns as pr')
        ->select('pr.*, po.supplier_id')
        ->join('purchase_orders as po', 'po.po_id = pr.po_id')
        ->where('pr.return_id', $returnId)
        ->where('pr.status', 'approved')
        ->where('pr.supplier_return_status', 'received_by_supplier') // gate: only after supplier confirms they have the bad units
        ->get()->getRow();

    if (!$return) return ['error' => 'Replacement can only be created once the supplier has confirmed receiving the returned item(s).'];
    if ($return->replacement_po_id) return ['error' => 'A replacement PO has already been created for this return.'];

    $this->db->transStart();

    $this->db->table('purchase_orders')->insert([
        'supplier_id'                => $return->supplier_id,
        'po_number'                  => 'PO-' . date('Y') . '-' . time(),
        'status'                     => 'sent',
        'is_auto_generated'          => 0,
        'replacement_for_return_id'  => $returnId,
        'total_amount'               => 0,
        'payment_status'             => 'paid', // zero-cost swap — nothing to actually pay
        'created_by'                 => $adminId,
        'notes'                      => 'Replacement for defective/incorrect items — see Return #' . $returnId,
    ]);
    $newPoId = $this->db->insertID();

    $this->db->table('purchase_order_items')->insert([
        'po_id'       => $newPoId,
        'product_id'  => $return->product_id,
        'qty_ordered' => $return->quantity,
        'unit_cost'   => 0,
    ]);

    $this->db->table('procurement_returns')->where('return_id', $returnId)->update(['replacement_po_id' => $newPoId]);

    $this->db->transComplete();
    if ($this->db->transStatus() === false) return ['error' => 'Failed to create replacement PO.'];

    return ['success' => true];
}

public function getAllActiveProducts(): array
{
    return $this->db->table('products as p')
        ->select('p.product_id, p.name, p.barcode_value, p.unit, p.category_id, c.name as cat_name')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->where('p.is_active', 1)
        ->orderBy('c.name', 'ASC')->orderBy('p.name', 'ASC')
        ->get()->getResultArray();
}

public function saveWalkInPO(int $guestSupplierId, array $post): array
{
    $products         = $post['products'] ?? [];
    $qtys             = $post['qtys'] ?? [];
    $costs            = $post['costs'] ?? [];
    $lotNumbers       = $post['lot_numbers'] ?? [];
    $expiresAts       = $post['expires_ats'] ?? [];
    $sellPrices       = $post['sell_prices'] ?? [];
    $notes            = trim((string) ($post['notes'] ?? ''));
    $paymentMethod    = $post['payment_method'] ?? 'cash';
    $paymentReference = trim((string) ($post['payment_reference'] ?? ''));
    $adminId          = session()->get('user_id') ?? 1;

    if (empty($products)) return ['error' => 'Please add at least one item.'];
    if (in_array($paymentMethod, ['bank_transfer', 'cheque']) && $paymentReference === '') {
        return ['error' => 'A reference number is required for bank transfer or cheque payments.'];
    }

    $this->db->transStart();

    $this->db->table('purchase_orders')->insert([
        'supplier_id'       => null,
        'guest_supplier_id' => $guestSupplierId,
        'po_number'         => 'PO-' . date('Y') . '-' . time(),
        // Walk-in purchases are completed on the spot — goods and payment both
        // change hands immediately. There's no portal for acknowledge/dispatch/GRR.
        'status'            => 'received',
        'received_date'     => date('Y-m-d'),
        'is_auto_generated' => 0,
        'expected_date'     => $post['expected_date'] ?? date('Y-m-d'),
        'total_amount'      => 0,
        'payment_status'    => 'paid',
        'payment_method'    => $paymentMethod,
        'payment_reference' => $paymentReference ?: null,
        'paid_at'           => date('Y-m-d H:i:s'),
        'paid_by'           => $adminId,
        'created_by'        => $adminId,
        'notes'             => $notes !== '' ? $notes : null,
    ]);
    $po_id = $this->db->insertID();

    $total = 0;
    $anyValidItem = false;

    foreach ($products as $index => $pid) {
        $qty       = (float) ($qtys[$index] ?? 0);
        $cost      = (float) ($costs[$index] ?? 0);
        $sellPrice = (float) ($sellPrices[$index] ?? 0);
        if ($qty <= 0 || $sellPrice <= 0) continue;

        $anyValidItem = true;
        $total += $qty * $cost;

        $this->db->table('purchase_order_items')->insert([
            'po_id'        => $po_id,
            'product_id'   => $pid,
            'qty_ordered'  => $qty,
            'qty_received' => $qty,
            'unit_cost'    => $cost,
        ]);
        $poiId = $this->db->insertID();

        $lastBatch = $this->db->table('inventory_batches')
            ->where('product_id', $pid)->orderBy('received_at', 'DESC')->get()->getRow();

        $this->db->table('inventory_batches')->insert([
            'product_id'     => $pid,
            'supplier_id'    => null,
            'po_id'          => $po_id,
            'batch_number'   => 'BAT-' . $po_id . '-' . $poiId,
            'lot_number'     => $lotNumbers[$index] !== '' ? $lotNumbers[$index] : null,
            'expires_at'     => $expiresAts[$index] !== '' ? $expiresAts[$index] : null,
            'cost_price'     => $cost,
            'sell_price'     => $sellPrice,
            'quantity_in'    => $qty,
            'quantity_avail' => $qty,
            'reorder_level'  => $lastBatch ? $lastBatch->reorder_level : 5,
            'received_at'    => date('Y-m-d H:i:s'),
        ]);
        $batch_id = $this->db->insertID();

        $this->db->table('stock_movements')->insert([
            'product_id'     => $pid,
            'batch_id'       => $batch_id,
            'movement_type'  => 'inbound',
            'quantity'       => $qty,
            'reference_id'   => $po_id,
            'reference_type' => 'po',
            'scanned_by'     => $adminId,
            'scan_mode'      => 'walkin_purchase',
            'notes'          => $notes !== '' ? $notes : null,
        ]);
    }

    if (!$anyValidItem) {
        $this->db->transRollback();
        return ['error' => 'Please provide a valid quantity, cost, and sell price for at least one item.'];
    }

    $this->db->table('purchase_orders')->where('po_id', $po_id)->update(['total_amount' => $total]);
    $this->db->transComplete();

    if ($this->db->transStatus() === false) return ['error' => 'Failed to create Purchase Order.'];
    return ['success' => true, 'po_id' => $po_id];
}

}