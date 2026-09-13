<?php

namespace App\Controllers\Mobile\Customer;

use App\Controllers\BaseController;
use App\Models\Mobile\CustomerMobileModel;

class MobileProfile extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'customer') return redirect()->to('m/');

        $model = new CustomerMobileModel();
        $data['name'] = session()->get('walkin_name');
        $data['address'] = session()->get('walkin_address');
        $data['store_rating'] = $model->getStoreRating();
        $data['announcements'] = $model->getActiveAnnouncements(5);
        $data['title'] = 'My Profile';
        $data['page_name'] = 'profile';
        return view('mobile/customer/profile', $data);
    }

    public function rate_store()
    {
        $rating = (int) $this->request->getPost('rating');
        if ($rating < 1 || $rating > 5) return redirect()->back()->with('error', 'Please select a rating.');

        $model = new CustomerMobileModel();
        $model->submitStoreRating(session()->get('user_id'), $rating);

        return redirect()->to('m/customer/profile')->with('success', 'Thanks for rating Robin Rose Trading!');
    }
}