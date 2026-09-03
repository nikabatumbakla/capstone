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

    public function getProductRatingSummary(): array
    {
        $total = $this->db->table('product_reviews')->countAllResults();
        $avg = $this->db->table('product_reviews')->selectAvg('rating')->get()->getRow()->rating;

        $breakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $this->db->table('product_reviews')->where('rating', $star)->countAllResults();
            $breakdown[$star] = $total > 0 ? ($count / $total) * 100 : 0;
        }

        return ['avg' => $avg ? (float) $avg : 0, 'total' => $total, 'breakdown' => $breakdown];
    }

    public function getStoreFeedbackSummary(): array
    {
        $total = $this->db->table('store_feedback')->countAllResults();
        $avg = $this->db->table('store_feedback')->selectAvg('rating')->get()->getRow()->rating;
        return ['avg' => $avg ? (float) $avg : 0, 'total' => $total];
    }

    public function getPendingSuggestionsCount(): int
    {
        return $this->db->table('product_suggestions')->where('status', 'pending')->countAllResults();
    }

    public function getRecentReviews(int $limit = 3): array
    {
        return $this->db->table('product_reviews as pr')
            ->select('pr.*, p.name as pname, u.full_name as customer')
            ->join('products as p', 'p.product_id = pr.product_id')
            ->join('users as u', 'u.user_id = pr.user_id')
            ->where('pr.is_approved', 1)
            ->orderBy('pr.created_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    public function getRecentSuggestions(int $limit = 3): array
    {
        return $this->db->table('product_suggestions as ps')
            ->select('ps.*, u.role as user_role')
            ->join('users as u', 'u.user_id = ps.user_id', 'left')
            ->orderBy('ps.created_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    public function getReviews(string $status = 'all', string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($status, $search) {
            if ($status === 'pending') $b->where('pr.is_approved', 0);
            if ($status === 'approved') $b->where('pr.is_approved', 1);
            if ($search !== '') $b->groupStart()->like('p.name', $search)->orLike('u.full_name', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('product_reviews as pr')
            ->join('products as p', 'p.product_id = pr.product_id')
            ->join('users as u', 'u.user_id = pr.user_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('product_reviews as pr')
            ->select('pr.*, p.name as pname, u.full_name as customer')
            ->join('products as p', 'p.product_id = pr.product_id')
            ->join('users as u', 'u.user_id = pr.user_id');
        $apply($builder);
        $builder->orderBy('pr.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
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

    public function approveReview(int $id): void
    {
        $this->db->table('product_reviews')->where('review_id', $id)->update(['is_approved' => 1]);
    }

    public function removeReview(int $id): void
    {
        $this->db->table('product_reviews')->where('review_id', $id)->delete();
    }

    public function setSuggestionStatus(int $id, string $status, int $reviewerId): void
    {
        $this->db->table('product_suggestions')->where('suggestion_id', $id)->update([
            'status' => $status,
            'reviewed_by' => $reviewerId,
        ]);
    }
}