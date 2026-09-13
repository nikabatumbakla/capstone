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

    public function submit_review($id)
    {
        $rating = (int) $this->request->getPost('rating');
        $comment = trim((string) $this->request->getPost('comment'));

        if ($rating < 1 || $rating > 5) {
            return redirect()->back()->with('error', 'Please select a rating.');
        }

        $this->model->submitReview((int) $id, session()->get('customer_id'), $rating, $comment);
        return redirect()->to('m/customer/product/'.$id)->with('success', 'Thank you for your review!');
    }
}