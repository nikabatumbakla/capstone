<?php

namespace App\Models\Admin\Management;

use CodeIgniter\Model;

class EngagementModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // Corrected: reads from 'store_ratings', matching exactly what
    // CustomerMobileModel::submitStoreRating() actually writes to.
    public function getStoreRatingSummary(): array
    {
        $total = $this->db->table('store_ratings')->countAllResults();
        $avg = $this->db->table('store_ratings')->selectAvg('rating')->get()->getRow()->rating;

        $breakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $this->db->table('store_ratings')->where('rating', $star)->countAllResults();
            $breakdown[$star] = $total > 0 ? ($count / $total) * 100 : 0;
        }

        return ['avg' => $avg ? (float) $avg : 0, 'total' => $total, 'breakdown' => $breakdown];
    }

    public function getRecentStoreRatings(int $limit = 5): array
    {
        return $this->db->table('store_ratings as sr')
            ->select('sr.rating, sr.created_at, u.full_name as customer')
            ->join('users as u', 'u.user_id = sr.user_id', 'left')
            ->orderBy('sr.created_at', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }

    public function getPendingSuggestionsCount(): int
    {
        return $this->db->table('product_suggestions')->where('status', 'pending')->countAllResults();
    }

    public function getRecentSuggestions(int $limit = 3): array
    {
        return $this->db->table('product_suggestions as ps')
            ->select('ps.*, u.role as user_role')
            ->join('users as u', 'u.user_id = ps.user_id', 'left')
            ->orderBy('ps.created_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    public function getSuggestions(string $status = 'all', string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($status, $search) {
            if ($status !== 'all') $b->where('ps.status', $status);
            if ($search !== '') $b->like('ps.product_name', $search);
            return $b;
        };

        $countBuilder = $this->db->table('product_suggestions as ps');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('product_suggestions as ps')
            ->select('ps.*, u.full_name as requester');
        $builder->join('users as u', 'u.user_id = ps.user_id', 'left');
        $apply($builder);
        $builder->orderBy('ps.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function setSuggestionStatus(int $id, string $status, int $reviewerId): void
    {
        $this->db->table('product_suggestions')->where('suggestion_id', $id)->update([
            'status' => $status,
            'reviewed_by' => $reviewerId,
        ]);
    }
}