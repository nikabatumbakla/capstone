<?php

namespace App\Controllers\Mobile\Customer;

use App\Controllers\BaseController;
use App\Models\Mobile\CustomerMobileModel;

class MobileBrowse extends BaseController
{
    public function index()
{
    helper('category');
    if (session()->get('role') !== 'customer') return redirect()->to('m/');

    $model = new CustomerMobileModel();
    $search = trim((string) ($this->request->getGet('search') ?? ''));
    $catId = $this->request->getGet('cat') ?: '';
    $page = (int) ($this->request->getGet('page') ?? 1);

    $result = $model->getProducts($search, $catId, $page, 12);
    $data['categories'] = $model->getCategories();
    $data['products'] = $result['data'];
    $data['total_pages'] = $result['total_pages'];
    $data['current_page'] = $page;
    $data['search'] = $search;
    $data['active_cat'] = $catId;
    $data['title'] = 'Browse Products';
    $data['page_name'] = 'browse';

    return view('mobile/customer/browse', $data);
}
}