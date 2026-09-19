<?php

namespace App\Controllers\Admin\Management\PublicSite;

use App\Controllers\BaseController;
use App\Models\Admin\Management\PublicSite\SiteContentModel;

class Testimonials extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SiteContentModel();
    }

    public function index()
    {
        $data['testimonials'] = $this->model->getTestimonials();
        $data['title'] = "Public Site — Testimonials";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "site-content";
        $data['active_section'] = 'testimonials';
        return view('pages/admin/management/site_content/testimonials', $data);
    }

    public function toggle_publish($id)
{
    $publish = $this->request->getGet('publish') === '1';
    $this->model->togglePublish((int) $id, $publish);
    return redirect()->back()->with('success', $publish ? 'Testimonial published.' : 'Testimonial hidden.');
}

    public function update_order($id)
    {
        $order = (int) $this->request->getPost('sort_order');
        $this->model->updateSortOrder((int) $id, $order);
        return $this->response->setJSON(['success' => true]);
    }

    public function delete($id)
{
    $this->model->deleteTestimonial((int) $id);
    return redirect()->to('admin/management/site-content/testimonials')->with('success', 'Testimonial removed.');
}

}