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

    // One row per product, using its latest batch for price/stock, and its primary image if one exists.
    // LEFT JOINs so a product with zero batches or zero images still appears — just with no price/photo.
    public function getFeaturedProducts(int $limit = 6): array
    {
        return $this->db->table('products as p')
            ->select("p.product_id, p.name, p.description, p.unit, c.name as cat_name,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as stock,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
            ->join('categories as c', 'c.category_id = p.category_id')
            ->where('p.is_active', 1)
            ->orderBy('p.product_id', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getProducts(string $catSlug = '', string $search = '', int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($catSlug, $search) {
            $b->where('p.is_active', 1);
            if ($catSlug !== '' && $catSlug !== 'all') $b->where('c.slug', $catSlug);
            if ($search !== '') $b->groupStart()->like('p.name', $search)->orLike('p.description', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('products as p')->join('categories as c', 'c.category_id = p.category_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('products as p')
            ->select("p.product_id, p.name, p.description, p.unit, c.name as cat_name, c.slug as cat_slug,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as stock,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_path")
            ->join('categories as c', 'c.category_id = p.category_id');
        $apply($builder);
        $builder->orderBy('p.name', 'ASC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    // Only currently-live posts targeted at the public site — same live-window logic used on the staff bulletin board
    public function getAnnouncements(int $page = 1, int $perPage = 6): array
    {
        $now = date('Y-m-d H:i:s');
        $apply = function ($b) use ($now) {
            $b->where('bp.is_published', 1)
              ->groupStart()->where('bp.target_audience', 'all')->orWhere('bp.target_audience', 'customers')->groupEnd()
              ->groupStart()->where('bp.starts_at IS NULL', null, false)->orWhere('bp.starts_at <=', $now)->groupEnd()
              ->groupStart()->where('bp.ends_at IS NULL', null, false)->orWhere('bp.ends_at >=', $now)->groupEnd();
            return $b;
        };

        $offset = ($page - 1) * $perPage;

        $countBuilder = $this->db->table('bulletin_posts as bp');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('bulletin_posts as bp')->select('bp.*');
        $apply($builder);
        $builder->orderBy('bp.is_pinned', 'DESC')->orderBy('bp.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }
}