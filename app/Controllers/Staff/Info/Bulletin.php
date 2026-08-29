<?php

namespace App\Controllers\Staff\Info;
use App\Controllers\BaseController;

class Bulletin extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Fetch published posts for staff or all
        $data['posts'] = $db->table('bulletin_posts as bp')
            ->select('bp.*, u.full_name as author')
            ->join('users as u', 'u.user_id = bp.created_by', 'left')
            ->where('bp.is_published', 1)
            ->groupStart()
                ->where('bp.target_audience', 'staff')
                ->orWhere('bp.target_audience', 'all')
            ->groupEnd()
            ->orderBy('bp.is_pinned', 'DESC')
            ->orderBy('bp.created_at', 'DESC')
            ->get()->getResultArray();

        $data['title'] = "Staff Bulletin Board";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "bulletin";
        return view('pages/staff/info/bulletin_board', $data);
    }
}