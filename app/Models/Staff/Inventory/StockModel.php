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

    // One row per product, total stock summed across every batch — same fix
    // applied to admin Stock Management. A product isn't "low stock" just
    // because its OLDEST batch is nearly empty if a newer batch still has plenty.
    public function getInventory(string $search = '', string $catId = '', string $status = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $builder = $this->db->table('products as p')
            ->select("p.product_id, p.name as product_name, p.barcode_value, p.unit, c.name as cat_name,
                COALESCE(SUM(ib.quantity_avail), 0) as total_stock,
                COALESCE(MAX(ib.reorder_level), 5) as reorder_level,
                MIN(CASE WHEN ib.expires_at IS NOT NULL THEN ib.expires_at END) as nearest_expiry,
                COUNT(ib.batch_id) as batch_count,
                (SELECT ib2.sell_price FROM inventory_batches ib2 WHERE ib2.product_id = p.product_id AND ib2.quantity_avail > 0 ORDER BY ib2.expires_at ASC LIMIT 1) as sell_price")
            ->join('categories as c', 'c.category_id = p.category_id')
            ->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left')
            ->where('p.is_active', 1);

        if ($search !== '') $builder->groupStart()->like('p.name', $search)->orLike('p.barcode_value', $search)->groupEnd();
        if ($catId !== '') $builder->where('p.category_id', $catId);

        $builder->groupBy('p.product_id');
        $builder->orderBy('p.name', 'ASC');

        // Pull everything, filter status + paginate in PHP — avoids the HAVING/alias
        // pitfalls CodeIgniter's countAllResults() has with correlated subqueries.
        $allRows = $builder->get()->getResultArray();

        $today = date('Y-m-d');
        $sixMonths = date('Y-m-d', strtotime('+6 months'));

        if ($status !== '') {
            $allRows = array_values(array_filter($allRows, function ($r) use ($status, $today, $sixMonths) {
                $stock = (int) $r['total_stock'];
                if ($status === 'no_stock') return $stock <= 0;
                if ($status === 'has_stock') return $stock > 0;
                if ($status === 'low_stock') return $stock > 0 && $stock <= (int) $r['reorder_level'];
                if ($status === 'near_expiry') return $r['nearest_expiry'] && $r['nearest_expiry'] >= $today && $r['nearest_expiry'] <= $sixMonths;
                return true;
            }));
        }

        usort($allRows, function ($a, $b) {
            $rank = fn($r) => $r['total_stock'] <= 0 ? 0 : ($r['total_stock'] <= $r['reorder_level'] ? 1 : 2);
            return $rank($a) <=> $rank($b) ?: strcmp($a['product_name'], $b['product_name']);
        });

        $total = count($allRows);
        $data = array_slice($allRows, $offset, $perPage);

        return ['data' => $data, 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getKpis(string $search = '', string $catId = ''): array
    {
        $builder = $this->db->table('products as p')
            ->select("p.product_id, COALESCE(SUM(ib.quantity_avail),0) as total_stock, COALESCE(MAX(ib.reorder_level),5) as reorder_level,
                MIN(CASE WHEN ib.expires_at IS NOT NULL THEN ib.expires_at END) as nearest_expiry")
            ->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left')
            ->where('p.is_active', 1);

        if ($search !== '') $builder->groupStart()->like('p.name', $search)->orLike('p.barcode_value', $search)->groupEnd();
        if ($catId !== '') $builder->where('p.category_id', $catId);
        $builder->groupBy('p.product_id');

        $rows = $builder->get()->getResultArray();
        $today = date('Y-m-d');
        $sixMonths = date('Y-m-d', strtotime('+6 months'));

        $totalItems = count($rows);
        $hasStock = 0; $noStock = 0; $lowStock = 0; $nearExpiry = 0;

        foreach ($rows as $r) {
            $stock = (int) $r['total_stock'];
            if ($stock > 0) {
                $hasStock++;
                if ($stock <= (int) $r['reorder_level']) $lowStock++;
            } else {
                $noStock++;
            }
            if ($r['nearest_expiry'] && $r['nearest_expiry'] >= $today && $r['nearest_expiry'] <= $sixMonths) {
                $nearExpiry++;
            }
        }

        return ['total_items' => $totalItems, 'has_stock' => $hasStock, 'low_stock' => $lowStock, 'near_expiry' => $nearExpiry, 'no_stock' => $noStock];
    }

    // NEW — full batch breakdown for one product, powers the View drawer
    public function getProductBatches(int $productId)
    {
        $product = $this->db->table('products as p')
            ->select('p.product_id, p.name, p.description, p.barcode_value, p.brand, p.manufacturer, p.unit, p.notes, c.name as cat_name, s.name as supplier_name, s.contact_person as supplier_contact, s.phone as supplier_phone')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->join('suppliers as s', 's.supplier_id = p.supplier_id', 'left')
            ->where('p.product_id', $productId)
            ->get()->getRow();

        if (!$product) return null;

        $img = $this->db->table('product_images')->where('product_id', $productId)->where('is_primary', 1)->get()->getRow();
        $product->image_path = $img ? $img->image_path : null;

        $product->batches = $this->db->table('inventory_batches')
            ->where('product_id', $productId)
            ->orderBy('received_at', 'DESC')
            ->get()->getResultArray();

        return $product;
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
            ->select('p.product_id, p.name, p.description, p.barcode_value, p.brand, p.manufacturer, p.unit, p.notes, c.name as cat_name, s.name as supplier_name, s.contact_person as supplier_contact, s.phone as supplier_phone')
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