<?php
namespace App\Controllers\Client\Support;
use App\Controllers\BaseController;

class Announcements extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        // Fetch posts for 'all' or 'clients'
        $data['posts'] = $db->table('bulletin_posts')
            ->where('is_published', 1)
            ->whereIn('target_audience', ['all', 'clients'])
            ->orderBy('is_pinned', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $data['title'] = "Bulletin Board";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "announcements";
        return view('pages/client/support/announcements', $data);
    }
}