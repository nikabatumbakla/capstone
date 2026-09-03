<?php

namespace App\Controllers\Staff\Info;

use App\Controllers\BaseController;
use App\Models\Staff\Info\BulletinModel;

class Bulletin extends BaseController
{
    protected $bulletinModel;

    public function __construct()
    {
        $this->bulletinModel = new BulletinModel();
    }

    public function index()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $this->bulletinModel->getActivePosts($page, 10);

        $data['posts'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;

        $data['title'] = "Staff Bulletin Board";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "bulletin";
        return view('pages/staff/info/bulletin_board', $data);
    }
}