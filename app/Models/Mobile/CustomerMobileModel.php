<?php

namespace App\Models\Mobile;

use CodeIgniter\Model;

class CustomerMobileModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getCategories(): array
{
    return $this->db->table('categories')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();
}

    public function getFeaturedProducts(int $limit = 6): array
    {
        return $this->db->table('products as p')
            ->select("p.product_id, p.name,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as stock,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
            ->where('p.is_active', 1)
            ->orderBy('p.product_id', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }

    public function getActiveAnnouncements(int $limit = 2): array
{
    $now = date('Y-m-d H:i:s');
    return $this->db->table('bulletin_posts')
        ->where('is_published', 1)
        ->groupStart()->where('target_audience', 'all')->orWhere('target_audience', 'customers')->groupEnd()
        ->groupStart()->where('starts_at IS NULL', null, false)->orWhere('starts_at <=', $now)->groupEnd()
        ->groupStart()->where('ends_at IS NULL', null, false)->orWhere('ends_at >=', $now)->groupEnd()
        ->orderBy('is_pinned', 'DESC')->orderBy('created_at', 'DESC')
        ->limit($limit)->get()->getResultArray();
}

    public function findByBarcode(string $barcode)
{
    return $this->db->table('products as p')
        ->select("p.product_id, p.name")
        ->where('p.barcode_value', $barcode)
        ->where('p.is_active', 1)
        ->get()->getRow();
}

    public function getProductDetails(int $productId)
    {
        $product = $this->db->table('products as p')
            ->select("p.product_id, p.name, p.sku, p.unit, p.brand, p.manufacturer, c.name as category_name,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as sell_price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
                (SELECT reorder_level FROM inventory_batches WHERE product_id = p.product_id ORDER BY received_at DESC LIMIT 1) as reorder_level,
                (SELECT expires_at FROM inventory_batches WHERE product_id = p.product_id ORDER BY expires_at ASC LIMIT 1) as expires_at,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
            ->join('categories as c', 'c.category_id = p.category_id')
            ->where('p.product_id', $productId)
            ->get()->getRow();

        if (!$product) return null;

        $product->video_url = null;
        $product->medical_description = null;
        $product->usage_purpose = null;
        $product->usage_guide = null;
        $product->warnings = null;
        $product->storage_info = null;
        $product->healthcare_tips = null;
        $product->warranty_info = null;
        $product->contraindications = null;

        if ($this->db->tableExists('product_educational_content')) {
            $edu = $this->db->table('product_educational_content')->where('product_id', $productId)->get()->getRow();
            if ($edu) {
                foreach (['video_url','medical_description','usage_purpose','usage_guide','warnings','storage_info','healthcare_tips','warranty_info','contraindications'] as $field) {
                    if (isset($edu->$field)) $product->$field = $edu->$field;
                }
            }
        }

        return $product;
    }


    public function getProducts(string $search = '', string $catId = '', int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($search, $catId) {
            $b->where('p.is_active', 1);
            if ($catId !== '') $b->where('p.category_id', $catId);
            if ($search !== '') $b->groupStart()->like('p.name', $search)->orLike('p.sku', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('products as p');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('products as p')
            ->select("p.product_id, p.name,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as stock,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path");
        $apply($builder);
        $builder->orderBy('p.name', 'ASC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getStoreRating(): array
{
    $row = $this->db->table('store_ratings')->selectAvg('rating')->selectCount('rating_id', 'total')->get()->getRow();
    return ['avg' => $row->rating ? round($row->rating, 1) : null, 'count' => $row->total ?? 0];
}

public function submitStoreRating(int $userId, int $rating): void
{
    $this->db->table('store_ratings')->insert(['user_id' => $userId, 'rating' => $rating]);
}

public function getChatHistory(int $userId, int $limit = 30): array
{
    return $this->db->table('chatbot_logs')
        ->where('user_id', $userId)
        ->orderBy('created_at', 'ASC')
        ->limit($limit)
        ->get()->getResultArray();
}

}