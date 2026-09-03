<?php

namespace App\Models\Client\Support;

use CodeIgniter\Model;

class AnnouncementsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getActivePosts(string $search = '', int $page = 1, int $perPage = 8): array
{
    $now = date('Y-m-d H:i:s');
    $apply = function ($b) use ($now, $search) {
        $b->where('is_published', 1)
          ->groupStart()->where('target_audience', 'all')->orWhere('target_audience', 'clients')->groupEnd()
          ->groupStart()->where('starts_at IS NULL', null, false)->orWhere('starts_at <=', $now)->groupEnd()
          ->groupStart()->where('ends_at IS NULL', null, false)->orWhere('ends_at >=', $now)->groupEnd();
        if ($search !== '') $b->like('title', $search);
        return $b;
    };

    $offset = ($page - 1) * $perPage;
    $countBuilder = $this->db->table('bulletin_posts');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('bulletin_posts');
    $apply($builder);
    $builder->orderBy('is_pinned', 'DESC')->orderBy('created_at', 'DESC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}
}