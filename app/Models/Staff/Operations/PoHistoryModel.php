<?php

namespace App\Models\Staff\Operations;

use CodeIgniter\Model;

class PoHistoryModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getHistory(string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($search) {
            $b->whereIn('po.status', ['received', 'partial', 'cancelled']);
            if ($search !== '') $b->groupStart()->like('po.po_number', $search)->orLike('s.name', $search)->orLike('gs.name', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('purchase_orders as po')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
            ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('purchase_orders as po')
            ->select("po.po_id, po.po_number, po.status, po.total_amount, po.received_date, po.expected_date,
                COALESCE(s.name, gs.name) as supplier_name,
                (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.po_id) as item_count")
            ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
            ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left');
        $apply($builder);
        $builder->orderBy('po.received_date', 'DESC')->orderBy('po.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getDetails(int $poId)
    {
        $po = $this->db->table('purchase_orders as po')
            ->select("po.*, COALESCE(s.name, gs.name) as supplier_name")
            ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
            ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left')
            ->where('po.po_id', $poId)->get()->getRow();

        if (!$po) return null;

        $items = $this->db->table('purchase_order_items as poi')
            ->select('poi.*, p.name, p.barcode_value')
            ->join('products as p', 'p.product_id = poi.product_id')
            ->where('poi.po_id', $poId)->get()->getResultArray();

        $grr = $this->db->table('goods_receipts')->where('po_id', $poId)->get()->getRow();

        return ['po' => $po, 'items' => $items, 'grr' => $grr];
    }
}