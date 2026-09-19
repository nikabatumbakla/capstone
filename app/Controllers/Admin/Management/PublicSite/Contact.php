<?php

namespace App\Controllers\Admin\Management\PublicSite;

use App\Controllers\BaseController;
use App\Models\Admin\Management\PublicSite\SiteContentModel;

class Contact extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SiteContentModel();
    }

    public function index()
    {
        $data['contact'] = $this->model->getContactInfo();
        $data['title'] = "Public Site — Contact Info";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "site-content";
        $data['active_section'] = 'contact';
        return view('pages/admin/management/site_content/contact', $data);
    }

    public function save()
    {
        $this->model->saveContactInfo([
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
            'full_address' => $this->request->getPost('full_address'),
            'business_hours' => $this->request->getPost('business_hours'),
            'facebook_url' => $this->request->getPost('facebook_url') ?: null,
            'instagram_url' => $this->request->getPost('instagram_url') ?: null,
            'viber_number' => $this->request->getPost('viber_number') ?: null,
            'map_embed_url' => $this->request->getPost('map_embed_url'),
        ]);
        return redirect()->to('admin/management/site-content/contact')->with('success', 'Contact information updated.');
    }
}