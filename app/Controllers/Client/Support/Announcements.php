<?php

namespace App\Controllers\Client\Support;

use App\Controllers\BaseController;
use App\Models\Client\Support\AnnouncementsModel;

class Announcements extends BaseController
{
    protected $announcementsModel;

    public function __construct()
    {
        $this->announcementsModel = new AnnouncementsModel();
    }

    public function index()
{
    $search = trim((string) ($this->request->getGet('search') ?? ''));
    $page = (int) ($this->request->getGet('page') ?? 1);
    $result = $this->announcementsModel->getActivePosts($search, $page, 8);

    $data['posts'] = $result['data'];
    $data['total_pages'] = $result['total_pages'];
    $data['current_page'] = $page;
    $data['search'] = $search;

    $data['title'] = "Announcements";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "announcements";
    return view('pages/client/support/announcements', $data);
}
}