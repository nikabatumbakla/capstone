<?php

namespace App\Models\Staff\Inventory;

use CodeIgniter\Model;

class StockModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getCategories(): array
    {
        return $this->db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    public function getInventory(string $search = '', string $catId = '', string $status = '', int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function ($b) use ($search, $catId, $status) {
        $b->where('p.is_active', 1);
        if ($search !== '') {
            $b->groupStart()->like('p.name', $search)->orLike('p.sku', $search)->groupEnd();
        }
        if ($catId !== '') $b->where('p.category_id', $catId);

        if ($status === 'no_stock') {
    $b->where("p.product_id NOT IN (SELECT product_id FROM inventory_batches WHERE quantity_avail > 0)", null, false);
} elseif ($status === 'has_stock') {
    $b->where("p.product_id IN (SELECT product_id FROM inventory_batches WHERE quantity_avail > 0)", null, false);
} elseif ($status === 'low_stock') {
    $b->where("p.product_id IN (SELECT product_id FROM inventory_batches WHERE quantity_avail <= reorder_level AND quantity_avail > 0)", null, false);
} elseif ($status === 'near_expiry') {
    $b->where("p.product_id IN (SELECT product_id FROM inventory_batches WHERE expires_at IS NOT NULL AND expires_at >= '" . date('Y-m-d') . "' AND expires_at <= '" . date('Y-m-d', strtotime('+6 months')) . "')", null, false);
}
        return $b;
    };

    $countBuilder = $this->db->table('products as p');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('products as p')
        ->select("p.product_id, p.name as product_name, p.sku, p.barcode_value, p.unit, c.name as cat_name,
            ib.batch_id, ib.batch_number, ib.quantity_avail, ib.reorder_level, ib.sell_price, ib.expires_at")
        ->join('categories as c', 'c.category_id = p.category_id')
        ->join('inventory_batches as ib', "ib.batch_id = (SELECT ib2.batch_id FROM inventory_batches ib2 WHERE ib2.product_id = p.product_id ORDER BY ib2.received_at DESC LIMIT 1)", 'left');
    $apply($builder);
    $builder->orderBy("CASE WHEN ib.batch_id IS NULL THEN 0 WHEN ib.quantity_avail <= ib.reorder_level THEN 1 ELSE 2 END", 'ASC', false)
        ->orderBy('p.name', 'ASC')
        ->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}
    public function getKpis(string $search = '', string $catId = ''): array
{
    $apply = function ($b) use ($search, $catId) {
        if ($search !== '') $b->groupStart()->like('p.name', $search)->orLike('p.sku', $search)->groupEnd();
        if ($catId !== '') $b->where('p.category_id', $catId);
        return $b;
    };

    $totalBuilder = $this->db->table('products as p')->where('p.is_active', 1);
    $apply($totalBuilder);
    $totalItems = $totalBuilder->countAllResults();

    $noStockBuilder = $this->db->table('products as p')->where('p.is_active', 1);
    $apply($noStockBuilder);
    $noStock = $noStockBuilder
        ->where("p.product_id NOT IN (SELECT product_id FROM inventory_batches WHERE quantity_avail > 0)", null, false)
        ->countAllResults();

    $hasStockBuilder = $this->db->table('products as p')->where('p.is_active', 1);
    $apply($hasStockBuilder);
    $hasStock = $hasStockBuilder
        ->where("p.product_id IN (SELECT product_id FROM inventory_batches WHERE quantity_avail > 0)", null, false)
        ->countAllResults();

    $lowStockBuilder = $this->db->table('inventory_batches as ib')->join('products as p', 'p.product_id = ib.product_id')->where('p.is_active', 1);
    $apply($lowStockBuilder);
    $lowStock = $lowStockBuilder->where('ib.quantity_avail <= ib.reorder_level', null, false)->where('ib.quantity_avail >', 0)->countAllResults();

    $nearExpiryBuilder = $this->db->table('inventory_batches as ib')->join('products as p', 'p.product_id = ib.product_id')->where('p.is_active', 1);
    $apply($nearExpiryBuilder);
    $nearExpiry = $nearExpiryBuilder
        ->where('ib.expires_at IS NOT NULL', null, false)
        ->where('ib.expires_at >=', date('Y-m-d'))
        ->where('ib.expires_at <=', date('Y-m-d', strtotime('+6 months')))
        ->countAllResults();

    return ['total_items' => $totalItems, 'has_stock' => $hasStock, 'low_stock' => $lowStock, 'near_expiry' => $nearExpiry, 'no_stock' => $noStock];
}

    public function getBatchDetails(int $batchId)
{
    $row = $this->db->table('inventory_batches as ib')
        ->select('ib.*, p.product_id, p.name, p.description, p.sku, p.barcode_value, p.brand, p.manufacturer, p.unit, p.notes, c.name as cat_name, s.name as supplier_name, s.contact_person as supplier_contact, s.phone as supplier_phone')
        ->join('products as p', 'p.product_id = ib.product_id')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->join('suppliers as s', 's.supplier_id = p.supplier_id', 'left')
        ->where('ib.batch_id', $batchId)
        ->get()->getRow();

    if ($row) {
        $img = $this->db->table('product_images')->where('product_id', $row->product_id)->where('is_primary', 1)->get()->getRow();
        $row->image_path = $img ? $img->image_path : null;
    }

    return $row;
}

    public function adjustStock(int $batchId, int $productId, int $qtyBefore, int $qtyAfter, string $reason, string $notes, int $staffUserId): void
    {
        $this->db->transStart();

        $this->db->table('inventory_batches')->where('batch_id', $batchId)->update(['quantity_avail' => $qtyAfter]);

        $this->db->table('stock_adjustment_logs')->insert([
            'product_id'  => $productId,
            'batch_id'    => $batchId,
            'adjusted_by' => $staffUserId,
            'qty_before'  => $qtyBefore,
            'qty_after'   => $qtyAfter,
            'reason'      => $reason,
            'notes'       => $notes,
        ]);

        $this->db->table('stock_movements')->insert([
            'product_id'     => $productId,
            'batch_id'       => $batchId,
            'movement_type'  => 'adjustment',
            'quantity'       => $qtyAfter - $qtyBefore,
            'reference_type' => 'staff_adjustment',
            'scanned_by'     => $staffUserId,
            'reason'         => $reason,
            'notes'          => $notes,
        ]);

        $this->db->transComplete();
    }

    public function createBatch(int $productId, string $batchNumber, int $quantity, float $costPrice, float $sellPrice, int $reorderLevel, ?string $expiresAt, int $staffUserId): int
    {
        $this->db->transStart();

        $this->db->table('inventory_batches')->insert([
            'product_id'     => $productId,
            'batch_number'   => $batchNumber,
            'quantity_in'    => $quantity,
            'quantity_avail' => $quantity,
            'reorder_level'  => $reorderLevel,
            'cost_price'     => $costPrice,
            'sell_price'     => $sellPrice,
            'expires_at'     => $expiresAt ?: null,
        ]);
        $batchId = $this->db->insertID();

        $this->db->table('stock_movements')->insert([
            'product_id'     => $productId,
            'batch_id'       => $batchId,
            'movement_type'  => 'inbound',
            'quantity'       => $quantity,
            'reference_type' => 'staff_new_batch',
            'scanned_by'     => $staffUserId,
            'notes'          => 'Initial batch created by staff via Stock Registry',
        ]);

        $this->db->transComplete();
        return $batchId;
    }

    public function getProductInfo(int $productId)
{
    return $this->db->table('products as p')
        ->select('p.product_id, p.name, p.description, p.sku, p.barcode_value, p.brand, p.manufacturer, p.unit, p.notes, c.name as cat_name, s.name as supplier_name, s.contact_person as supplier_contact, s.phone as supplier_phone')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->join('suppliers as s', 's.supplier_id = p.supplier_id', 'left')
        ->where('p.product_id', $productId)
        ->get()->getRow();
}

public function getProductImage(int $productId): ?string
{
    $img = $this->db->table('product_images')->where('product_id', $productId)->where('is_primary', 1)->get()->getRow();
    return $img ? $img->image_path : null;
}

}