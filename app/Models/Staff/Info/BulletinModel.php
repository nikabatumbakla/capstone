<?php

namespace App\Models\Staff\Info;

use CodeIgniter\Model;

class BulletinModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getActivePosts(int $page = 1, int $perPage = 10): array
    {
        $now = date('Y-m-d H:i:s');

        $apply = function ($b) use ($now) {
            $b->where('bp.is_published', 1)
              ->groupStart()->where('bp.target_audience', 'staff')->orWhere('bp.target_audience', 'all')->groupEnd()
              ->groupStart()->where('bp.starts_at IS NULL', null, false)->orWhere('bp.starts_at <=', $now)->groupEnd()
              ->groupStart()->where('bp.ends_at IS NULL', null, false)->orWhere('bp.ends_at >=', $now)->groupEnd();
            return $b;
        };

        $offset = ($page - 1) * $perPage;

        $countBuilder = $this->db->table('bulletin_posts as bp');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('bulletin_posts as bp')
            ->select('bp.*, u.full_name as author')
            ->join('users as u', 'u.user_id = bp.created_by', 'left');
        $apply($builder);
        $builder->orderBy('bp.is_pinned', 'DESC')->orderBy('bp.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }
}