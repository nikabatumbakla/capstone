<?php

namespace App\Controllers\Admin\Management\PublicSite;

use App\Controllers\BaseController;
use App\Models\Admin\Management\PublicSite\SiteContentModel;

class Hero extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SiteContentModel();
    }

    public function index()
    {
        $data['hero'] = $this->model->getHero();
        $data['title'] = "Public Site — Hero Section";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "site-content";
        $data['active_section'] = 'hero';
        return view('pages/admin/management/site_content/hero', $data);
    }

    public function save()
    {
        $this->model->saveHero($this->request->getPost());
        return redirect()->to('admin/management/site-content/hero')->with('success', 'Hero section updated.');
    }
}