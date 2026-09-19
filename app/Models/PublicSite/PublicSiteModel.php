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

    public function getPublishedTestimonials(int $limit = 3): array
    {
        return $this->db->table('client_testimonials')
            ->where('is_published', 1)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getStoreRatingSummary(): array
    {
        $row = $this->db->table('store_ratings')->selectAvg('rating')->selectCount('rating_id', 'total')->get()->getRow();
        return ['avg' => $row->rating ? round((float) $row->rating, 1) : null, 'count' => (int) ($row->total ?? 0)];
    }

    public function getHeroContent()
    {
        return $this->db->table('site_hero_content')->get()->getRow();
    }

    public function getWhyUsCards(): array
    {
        return $this->db->table('site_why_us_cards')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    public function getServiceCards(): array
    {
        return $this->db->table('site_service_cards')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    public function getAboutContent()
{
    return $this->db->table('site_about_content')->get()->getRow();
}

public function getTeamMembers(): array
{
    return $this->db->table('site_team_members')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();
}

public function getContactInfo()
{
    return $this->db->table('site_contact_info')->get()->getRow();
}

}