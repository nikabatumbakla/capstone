<?php

namespace App\Controllers\Admin\Management\PublicSite;

use App\Controllers\BaseController;
use App\Models\Admin\Management\PublicSite\SiteContentModel;

class WhyUs extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SiteContentModel();
    }

    public function index()
    {
        $data['cards'] = $this->model->getWhyUsCards();
        $data['title'] = "Public Site — Why Us";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "site-content";
        $data['active_section'] = 'why-us';
        return view('pages/admin/management/site_content/why_us', $data);
    }

    public function save()
    {
        $id = $this->request->getPost('card_id');
        $this->model->saveWhyUsCard($this->request->getPost(), $id ?: null);
        return redirect()->to('admin/management/site-content/why-us')->with('success', $id ? 'Card updated.' : 'Card added.');
    }

    public function edit($id)
    {
        $row = $this->model->getWhyUsCard((int) $id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($row);
    }

    public function delete($id)
    {
        $this->model->deleteWhyUsCard((int) $id);
        return redirect()->to('admin/management/site-content/why-us')->with('success', 'Card removed.');
    }
}