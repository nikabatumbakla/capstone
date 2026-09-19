<?php

namespace App\Controllers\Admin\Management\PublicSite;

use App\Controllers\BaseController;
use App\Models\Admin\Management\PublicSite\SiteContentModel;

class Services extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SiteContentModel();
    }

    public function index()
    {
        $data['services'] = $this->model->getServiceCards();
        $data['title'] = "Public Site — Services";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "site-content";
        $data['active_section'] = 'services';
        return view('pages/admin/management/site_content/services', $data);
    }

    public function save()
    {
        $id = $this->request->getPost('service_id');
        $this->model->saveServiceCard($this->request->getPost(), $id ?: null);
        return redirect()->to('admin/management/site-content/services')->with('success', $id ? 'Service updated.' : 'Service added.');
    }

    public function edit($id)
    {
        $row = $this->model->getServiceCard((int) $id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($row);
    }

    public function delete($id)
    {
        $this->model->deleteServiceCard((int) $id);
        return redirect()->to('admin/management/site-content/services')->with('success', 'Service removed.');
    }
}