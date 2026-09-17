<?php

namespace App\Models\PublicSite;

use CodeIgniter\Model;

class PublicSiteModel extends Model
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

    // One row per product, using its earliest-expiring in-stock batch (FEFO) for
    // price/stock, matching the convention used everywhere else in this app.
    public function getFeaturedProducts(int $limit = 6): array
    {
        return $this->db->table('products as p')
            ->select("p.product_id, p.name, p.description, p.unit, c.name as cat_name,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as stock,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
            ->join('categories as c', 'c.category_id = p.category_id')
            ->where('p.is_active', 1)
            ->orderBy('p.product_id', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getProducts(string $catId = '', string $search = '', int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($catId, $search) {
            $b->where('p.is_active', 1);
            if ($catId !== '' && $catId !== 'all') $b->where('p.category_id', (int) $catId);
            if ($search !== '') $b->groupStart()->like('p.name', $search)->orLike('p.description', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('products as p');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('products as p')
            ->select("p.product_id, p.name, p.description, p.unit, c.name as cat_name,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as stock,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
            ->join('categories as c', 'c.category_id = p.category_id');
        $apply($builder);
        $builder->orderBy('p.name', 'ASC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    // Real announcements from the same bulletin_posts table used by the client/staff portals
    public function getAnnouncements(int $page = 1, int $perPage = 6): array
    {
        $offset = ($page - 1) * $perPage;
        $now = date('Y-m-d H:i:s');

        $apply = function ($b) use ($now) {
            $b->where('is_published', 1)
              ->groupStart()->where('target_audience', 'all')->orWhere('target_audience', 'public')->groupEnd()
              ->groupStart()->where('starts_at IS NULL', null, false)->orWhere('starts_at <=', $now)->groupEnd()
              ->groupStart()->where('ends_at IS NULL', null, false)->orWhere('ends_at >=', $now)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('bulletin_posts');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('bulletin_posts');
        $apply($builder);
        $builder->orderBy('is_pinned', 'DESC')->orderBy('created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getRecentAnnouncements(int $limit = 3): array
    {
        return $this->getAnnouncements(1, $limit)['data'];
    }
}