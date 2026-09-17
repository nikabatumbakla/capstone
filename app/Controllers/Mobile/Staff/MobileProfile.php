<?php

namespace App\Controllers\Mobile\Staff;

use App\Controllers\BaseController;

class MobileProfile extends BaseController
{
    public function index()
{
    if (session()->get('role') !== 'staff') return redirect()->to('m/');
    $model = new \App\Models\Mobile\StaffMobileModel();

    $data['stats'] = $model->getProfileStats(session()->get('user_id'));
    $data['title'] = 'My Profile';
    $data['page_name'] = 'profile';
    return view('mobile/staff/profile', $data);
}
}