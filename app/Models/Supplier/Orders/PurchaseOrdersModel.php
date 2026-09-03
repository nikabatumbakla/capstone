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
            ->select('poi.*, p.name, p.sku, p.unit')
            ->join('products as p', 'p.product_id = poi.product_id')
            ->where('poi.po_id', $poId)
            ->get()->getResultArray();

        return ['po' => $po, 'items' => $items];
    }

    // sent -> acknowledged. Supplier confirms they can fulfill, with a confirmed delivery date.
    public function acknowledgePo(int $poId, int $supplierId, string $confirmedDate, ?string $notes): bool
    {
        $po = $this->db->table('purchase_orders')->where('po_id', $poId)->where('supplier_id', $supplierId)->where('status', 'sent')->get()->getRow();
        if (!$po) return false;

        $this->db->table('purchase_orders')->where('po_id', $poId)->update([
            'status'        => 'acknowledged',
            'expected_date' => $confirmedDate,
            'notes'         => $notes,
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
    public function markDispatched(int $poId, int $supplierId, string $drNumber, ?string $dispatchDate): bool
    {
        $po = $this->db->table('purchase_orders')->where('po_id', $poId)->where('supplier_id', $supplierId)->where('status', 'acknowledged')->get()->getRow();
        if (!$po) return false;

        $this->db->table('purchase_orders')->where('po_id', $poId)->update([
            'status'                 => 'in_transit',
            'supplier_dr_number'     => $drNumber,
            'supplier_dispatched_at' => $dispatchDate ?: date('Y-m-d H:i:s'),
        ]);
        return true;
    }
}