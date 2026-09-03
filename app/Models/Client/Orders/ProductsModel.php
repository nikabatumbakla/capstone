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

    // One row per product, using its latest batch for price, and total stock summed across all batches —
    // fixes the earlier version's bug of one row per batch (duplicates + arbitrary price selection).
   public function getProducts(string $search = '', string $catId = '', int $page = 1, int $perPage = 12): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function ($b) use ($search, $catId) {
        $b->where('p.is_active', 1);
        if ($catId !== '') $b->where('p.category_id', $catId);
        if ($search !== '') $b->groupStart()->like('p.name', $search)->orLike('p.sku', $search)->groupEnd();
        return $b;
    };

    $countBuilder = $this->db->table('products as p')->join('categories as c', 'c.category_id = p.category_id');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('products as p')
        ->select("p.product_id, p.name, p.sku, p.unit, p.brand, p.manufacturer, c.name as category_name,
            (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as sell_price,
            (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
            (SELECT ib2.reorder_level FROM inventory_batches ib2 WHERE ib2.product_id = p.product_id ORDER BY ib2.received_at DESC LIMIT 1) as reorder_level,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
        ->join('categories as c', 'c.category_id = p.category_id');
    $apply($builder);
    $builder->orderBy('p.name', 'ASC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

    // Used to build the cart display — real names/prices/stock for whatever product_ids are in session
    public function getProductsByIds(array $productIds): array
    {
        if (empty($productIds)) return [];
        return $this->db->table('products as p')
            ->select("p.product_id, p.name, p.sku, p.unit,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as sell_price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as total_stock")
            ->whereIn('p.product_id', $productIds)
            ->where('p.is_active', 1)
            ->get()->getResultArray();
    }

    public function getProductDetails(int $productId)
{
    $product = $this->db->table('products as p')
        ->select("p.product_id, p.name, p.description, p.sku, p.unit, p.brand, p.manufacturer, p.is_vat_exempt,
            c.name as category_name,
            (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as sell_price,
            (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
        ->join('categories as c', 'c.category_id = p.category_id')
        ->where('p.product_id', $productId)
        ->get()->getRow();

    if (!$product) return null;

    // FIXED: correct table name — was checking product_educational_content, which doesn't exist
    $product->content = $this->db->table('product_info_content')->where('product_id', $productId)->get()->getRow();

    return $product;
}

}