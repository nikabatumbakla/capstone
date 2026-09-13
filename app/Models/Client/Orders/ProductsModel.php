<?php

namespace App\Models\Client\Orders;

use CodeIgniter\Model;

class ProductsModel extends Model
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

    // Only genuinely in-stock products are ever shown to a client — HAVING filters
    // on the computed stock total, since it's a correlated subquery, not a real column.
    public function getProducts(string $search = '', string $catId = '', int $page = 1, int $perPage = 12): array
{
    $offset = ($page - 1) * $perPage;
    $inStockCondition = "(SELECT COALESCE(SUM(quantity_avail),0) FROM inventory_batches WHERE product_id = p.product_id) > 0";

    $apply = function ($b) use ($search, $catId, $inStockCondition) {
        $b->where('p.is_active', 1);
        $b->where($inStockCondition, null, false);
        if ($catId !== '') $b->where('p.category_id', $catId);
        if ($search !== '') $b->like('p.name', $search);
        return $b;
    };

    $countBuilder = $this->db->table('products as p')->join('categories as c', 'c.category_id = p.category_id');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('products as p')
        ->select("p.product_id, p.name, p.description, p.unit, p.brand, p.manufacturer, p.is_vat_exempt, c.name as category_name,
            (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as sell_price,
            (SELECT COALESCE(SUM(quantity_avail),0) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
        ->join('categories as c', 'c.category_id = p.category_id');
    $apply($builder);
    $builder->orderBy('p.name', 'ASC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

    public function getProductsByIds(array $productIds): array
    {
        if (empty($productIds)) return [];
        return $this->db->table('products as p')
            ->select("p.product_id, p.name, p.unit,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as sell_price,
                (SELECT COALESCE(SUM(quantity_avail),0) FROM inventory_batches WHERE product_id = p.product_id) as total_stock")
            ->whereIn('p.product_id', $productIds)
            ->where('p.is_active', 1)
            ->get()->getResultArray();
    }

    public function getProductDetails(int $productId)
    {
        $product = $this->db->table('products as p')
            ->select("p.product_id, p.name, p.description, p.unit, p.brand, p.manufacturer, p.is_vat_exempt,
                c.name as category_name,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as sell_price,
                (SELECT COALESCE(SUM(quantity_avail),0) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
            ->join('categories as c', 'c.category_id = p.category_id')
            ->where('p.product_id', $productId)
            ->get()->getRow();

        if (!$product) return null;

        $product->content = $this->db->table('product_info_content')->where('product_id', $productId)->get()->getRow();
        return $product;
    }
}