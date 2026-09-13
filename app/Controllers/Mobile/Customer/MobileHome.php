<?php

namespace App\Controllers\Mobile\Customer;

use App\Controllers\BaseController;
use App\Models\Mobile\CustomerMobileModel;

class MobileHome extends BaseController
{
    public function index()
    {
        helper('category');
        if (session()->get('role') !== 'customer') return redirect()->to('m/');

        $model = new CustomerMobileModel();
        $data['categories'] = $model->getCategories();
        $data['featured'] = $model->getFeaturedProducts(6);
        $data['announcements'] = $model->getActiveAnnouncements(2);
        $data['title'] = 'Customer Home';
        $data['page_name'] = 'home';

        return view('mobile/customer/home', $data);
    }
}