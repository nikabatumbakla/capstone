<?php

namespace App\Models\Supplier\Orders;

use CodeIgniter\Model;

class ReturnsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getReturns(int $supplierId, string $search = '', ?string $statusFilter = null, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($supplierId, $search, $statusFilter) {
            $b->where('po.supplier_id', $supplierId);
            if ($statusFilter) $b->where('pr.status', $statusFilter);
            if ($search !== '') $b->like('po.po_number', $search);
            return $b;
        };

        $countBuilder = $this->db->table('procurement_returns as pr')->join('purchase_orders as po', 'po.po_id = pr.po_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('procurement_returns as pr')
            ->select('pr.*, po.po_number, p.name as product_name, p.barcode_value')
            ->join('purchase_orders as po', 'po.po_id = pr.po_id')
            ->join('products as p', 'p.product_id = pr.product_id');
        $apply($builder);
        $builder->orderBy('pr.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getKpis(int $supplierId): array
    {
        $countByStatus = function(string $status) use ($supplierId) {
            return $this->db->table('procurement_returns as pr')
                ->join('purchase_orders as po', 'po.po_id = pr.po_id')
                ->where('po.supplier_id', $supplierId)
                ->where('pr.status', $status)
                ->countAllResults();
        };

        return [
            'pending'  => $countByStatus('pending'),
            'approved' => $countByStatus('approved'),
            'rejected' => $countByStatus('rejected'),
        ];
    }

    public function confirmReceived(int $returnId, int $supplierId): bool
{
    $return = $this->db->table('procurement_returns as pr')
        ->join('purchase_orders as po', 'po.po_id = pr.po_id')
        ->where('pr.return_id', $returnId)
        ->where('po.supplier_id', $supplierId)
        ->where('pr.status', 'approved')
        ->where('pr.supplier_return_status', 'sent_to_supplier')
        ->get()->getRow();
    if (!$return) return false;

    $this->db->table('procurement_returns')->where('return_id', $returnId)->update([
        'supplier_return_status'  => 'received_by_supplier',
        'received_by_supplier_at' => date('Y-m-d H:i:s'),
    ]);
    return true;
}

public function getReturnDetails(int $returnId, int $supplierId)
{
    return $this->db->table('procurement_returns as pr')
        ->select('pr.*, po.po_number, p.name as product_name, p.barcode_value')
        ->join('purchase_orders as po', 'po.po_id = pr.po_id')
        ->join('products as p', 'p.product_id = pr.product_id')
        ->where('pr.return_id', $returnId)
        ->where('po.supplier_id', $supplierId)
        ->get()->getRow();
}

}