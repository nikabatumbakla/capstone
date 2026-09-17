<?php

namespace App\Controllers\Mobile\Customer;

use App\Controllers\BaseController;
use App\Models\Mobile\CustomerMobileModel;

class MobileProduct extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new CustomerMobileModel();
    }

    public function show($id)
    {
        if (session()->get('role') !== 'customer') return redirect()->to('m/');

        $product = $this->model->getProductDetails((int) $id);
        if (!$product) return redirect()->to('m/customer/home')->with('error', 'Product not found.');

        $data['p'] = $product;
        $data['title'] = 'Product Info';
        $data['page_name'] = 'browse';
        return view('mobile/customer/product', $data);
    }
}