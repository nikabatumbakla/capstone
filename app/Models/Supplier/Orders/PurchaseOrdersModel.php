<?php

namespace App\Models\Supplier\Orders;

use CodeIgniter\Model;

class PurchaseOrdersModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getKpis(int $supplierId): array
    {
        return [
            'pending_ack' => $this->db->table('purchase_orders')->where('supplier_id', $supplierId)->where('status', 'sent')->countAllResults(),
            'in_progress' => $this->db->table('purchase_orders')->where('supplier_id', $supplierId)->whereIn('status', ['acknowledged', 'in_transit'])->countAllResults(),
            'completed'   => $this->db->table('purchase_orders')->where('supplier_id', $supplierId)->where('status', 'received')->countAllResults(),
        ];
    }

    public function getInbox(int $supplierId, string $tab = 'pending', string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($supplierId, $tab, $search) {
            $b->where('po.supplier_id', $supplierId);
            if ($tab === 'pending') $b->where('po.status', 'sent');
            if ($tab === 'in_progress') $b->whereIn('po.status', ['acknowledged', 'in_transit']);
            if ($tab === 'history') $b->whereIn('po.status', ['received', 'partial', 'cancelled']);
            if ($search !== '') $b->like('po.po_number', $search);
            return $b;
        };

        $countBuilder = $this->db->table('purchase_orders as po');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('purchase_orders as po')
            ->select("po.*, (SELECT SUM(qty_ordered) FROM purchase_order_items WHERE po_id = po.po_id) as total_qty,
                (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.po_id) as item_count");
        $apply($builder);
        $builder->orderBy('po.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getPoDetails(int $poId, int $supplierId)
    {
        $po = $this->db->table('purchase_orders')->where('po_id', $poId)->where('supplier_id', $supplierId)->get()->getRow();
        if (!$po) return null;

        $items = $this->db->table('purchase_order_items as poi')
            ->select('poi.*, p.name, p.barcode_value, p.unit')
            ->join('products as p', 'p.product_id = poi.product_id')
            ->where('poi.po_id', $poId)
            ->get()->getResultArray();

        return ['po' => $po, 'items' => $items];
    }

    // sent -> acknowledged. Supplier confirms they can fulfill, with a confirmed delivery date.
    public function acknowledgePo(int $poId, int $supplierId, string $confirmedDate, ?string $notes, array $poiIds = [], array $itemQtys = []): bool
{
    $po = $this->db->table('purchase_orders')->where('po_id', $poId)->where('supplier_id', $supplierId)->where('status', 'sent')->get()->getRow();
    if (!$po) return false;

    $this->db->transStart();

    $hasShortage = false;
    foreach ($poiIds as $index => $poiId) {
        $confirmedQty = (int) ($itemQtys[$index] ?? 0);
        $this->db->table('purchase_order_items')->where('poi_id', (int) $poiId)->update(['qty_confirmed' => $confirmedQty]);

        $original = $this->db->table('purchase_order_items')->select('qty_ordered')->where('poi_id', (int) $poiId)->get()->getRow();
        if ($original && $confirmedQty < (int) $original->qty_ordered) $hasShortage = true;
    }

    $finalNotes = $notes;
    if ($hasShortage) {
        $finalNotes = trim(($notes ? $notes . ' ' : '') . '[One or more items confirmed below ordered quantity.]');
    }

    $this->db->table('purchase_orders')->where('po_id', $poId)->update([
        'status'          => 'acknowledged',
        'acknowledged_at' => date('Y-m-d H:i:s'),
        'expected_date'   => $confirmedDate,
        'notes'           => $finalNotes,
    ]);

    $this->db->transComplete();
    return $this->db->transStatus() !== false;
}

    // sent -> cancelled. Supplier declines an order they cannot fulfill — a required
    // reason is stored so admin knows why, rather than the order just vanishing.
    public function declinePo(int $poId, int $supplierId, string $reason): bool
    {
        $po = $this->db->table('purchase_orders')->where('po_id', $poId)->where('supplier_id', $supplierId)->where('status', 'sent')->get()->getRow();
        if (!$po) return false;

        $this->db->table('purchase_orders')->where('po_id', $poId)->update([
            'status' => 'cancelled',
            'notes'  => $reason,
        ]);
        return true;
    }

    // Delivery queue: only orders the supplier has ALREADY acknowledged — matches the real lifecycle
    public function getDeliveryQueue(int $supplierId, string $search = '', int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function ($b) use ($supplierId, $search) {
        $b->where('supplier_id', $supplierId)->whereIn('status', ['acknowledged', 'in_transit']);
        if ($search !== '') $b->like('po_number', $search);
        return $b;
    };

    $countBuilder = $this->db->table('purchase_orders');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('purchase_orders');
    $apply($builder);
    $builder->orderBy('expected_date', 'ASC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

    // acknowledged -> in_transit. Real dispatch, using the actual columns built for this.
    public function markDispatched(int $poId, int $supplierId, string $drNumber, ?string $dispatchDate, ?string $carrierName, ?string $driverContact): bool
{
    $po = $this->db->table('purchase_orders')
        ->where('po_id', $poId)
        ->where('supplier_id', $supplierId)
        ->where('status', 'acknowledged')
        ->where('payment_status', 'paid')
        ->get()->getRow();
    if (!$po) return false;

    $this->db->table('purchase_orders')->where('po_id', $poId)->update([
        'status'                 => 'in_transit',
        'supplier_dr_number'     => $drNumber,
        'carrier_name'           => $carrierName ?: null,
        'driver_contact'         => $driverContact ?: null,
        'supplier_dispatched_at' => $dispatchDate ?: date('Y-m-d H:i:s'),
    ]);
    return true;
}

    public function getPoForAcknowledge(int $poId, int $supplierId)
{
    $po = $this->db->table('purchase_orders')->where('po_id', $poId)->where('supplier_id', $supplierId)->where('status', 'sent')->get()->getRow();
    if (!$po) return null;

    $items = $this->db->table('purchase_order_items as poi')
        ->select('poi.poi_id, poi.product_id, poi.qty_ordered, poi.unit_cost, p.name, p.barcode_value, p.unit')
        ->join('products as p', 'p.product_id = poi.product_id')
        ->where('poi.po_id', $poId)
        ->get()->getResultArray();

    return ['po' => $po, 'items' => $items];
}

public function getStoreInfo(): array
{
    $rows = $this->db->table('store_settings')->get()->getResultArray();
    $info = [];
    foreach ($rows as $row) $info[$row['setting_key']] = $row['setting_value'];
    return $info;
}

public function getPaymentHistory(int $supplierId, string $search = '', int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function($b) use ($supplierId, $search) {
        $b->where('supplier_id', $supplierId)->where('payment_status', 'paid');
        if ($search !== '') $b->like('po_number', $search);
        return $b;
    };

    $countBuilder = $this->db->table('purchase_orders');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('purchase_orders');
    $apply($builder);
    $builder->orderBy('paid_at', 'DESC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

public function getPaymentKpis(int $supplierId): array
{
    $totalPaidRow = $this->db->table('purchase_orders')
        ->selectSum('total_amount')
        ->where('supplier_id', $supplierId)->where('payment_status', 'paid')
        ->get()->getRow();

    $countPaid = $this->db->table('purchase_orders')
        ->where('supplier_id', $supplierId)->where('payment_status', 'paid')
        ->countAllResults();

    $thisMonthRow = $this->db->table('purchase_orders')
        ->selectSum('total_amount')
        ->where('supplier_id', $supplierId)->where('payment_status', 'paid')
        ->where('YEAR(paid_at) = YEAR(CURDATE()) AND MONTH(paid_at) = MONTH(CURDATE())', null, false)
        ->get()->getRow();

    return [
        'total_paid'      => $totalPaidRow->total_amount ?? 0,
        'count_paid'      => $countPaid,
        'paid_this_month' => $thisMonthRow->total_amount ?? 0,
    ];
}

}