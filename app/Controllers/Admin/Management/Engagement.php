<?php

namespace App\Controllers\Admin\Management;
use App\Controllers\BaseController;

class Engagement extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // 1. STATS & ANALYTICS
        $data['avg_rating'] = $db->table('store_feedback')->selectAvg('rating')->get()->getRow()->rating ?? 4.6;
        $data['total_feedback'] = $db->table('store_feedback')->countAllResults();
        $data['pending_suggestions'] = $db->table('product_suggestions')->where('status', 'pending')->countAllResults();

        // Star Percentage Breakdown for Progress Bars
        $stars = [];
        for($i = 5; $i >= 3; $i--) {
            $count = $db->table('store_feedback')->where('rating', $i)->countAllResults();
            $stars[$i] = ($data['total_feedback'] > 0) ? ($count / $data['total_feedback']) * 100 : 0;
        }
        $data['star_breakdown'] = $stars;

        // 2. DATA FOR SNAPSHOTS (Middle Row)
        $data['recent_reviews'] = $db->table('product_reviews as pr')
            ->select('pr.*, p.name as pname, u.full_name as customer')
            ->join('products as p', 'p.product_id = pr.product_id')
            ->join('users as u', 'u.user_id = pr.user_id')
            ->orderBy('pr.created_at', 'DESC')->limit(2)->get()->getResultArray();

        // FIXED: Added missing join for users table to access 'role'
        $data['recent_suggestions'] = $db->table('product_suggestions as ps')
            ->select('ps.*, u.role as user_role')
            ->join('users as u', 'u.user_id = ps.user_id', 'left') 
            ->orderBy('ps.created_at', 'DESC')
            ->limit(3)
            ->get()->getResultArray();

        // 3. DATA FOR FULL TABLES (Tabs)
        $data['all_reviews'] = $db->table('product_reviews as pr')
            ->select('pr.*, p.name as pname, u.full_name as customer')
            ->join('products as p', 'p.product_id = pr.product_id')
            ->join('users as u', 'u.user_id = pr.user_id')
            ->orderBy('pr.created_at', 'DESC')->get()->getResultArray();

        $data['all_suggestions'] = $db->table('product_suggestions as ps')
            ->select('ps.*, u.full_name as requester')
            ->join('users as u', 'u.user_id = ps.user_id', 'left')
            ->orderBy('ps.created_at', 'DESC')->get()->getResultArray();

        $data['title'] = "Engagement Intelligence";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "engagement";
        return view('pages/admin/management/customer_engagement', $data);
    }

    public function approve_review($id)
    {
        $db = \Config\Database::connect();
        $db->table('product_reviews')->where('review_id', $id)->update(['is_approved' => 1]);
        return redirect()->back()->with('success', 'Review published.');
    }

    public function delete_review($id)
    {
        $db = \Config\Database::connect();
        $db->table('product_reviews')->where('review_id', $id)->delete();
        return redirect()->back()->with('success', 'Review removed.');
    }

    public function update_suggestion($id, $status)
    {
        $db = \Config\Database::connect();
        $db->table('product_suggestions')->where('suggestion_id', $id)->update([
            'status' => $status,
            'reviewed_by' => session()->get('user_id') ?? 1
        ]);
        return redirect()->back()->with('success', 'Suggestion status updated.');
    }
}