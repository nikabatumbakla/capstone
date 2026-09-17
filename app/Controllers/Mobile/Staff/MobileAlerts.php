<?php

namespace App\Controllers\Mobile\Staff;

use App\Controllers\BaseController;
use App\Models\Mobile\StaffMobileModel;

class MobileAlerts extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new StaffMobileModel();
    }

    public function index()
    {
        if (session()->get('role') !== 'staff') return redirect()->to('m/');

        $data['alerts'] = $this->model->getSystemAlerts(50);
        $data['title'] = 'Alerts';
        $data['page_name'] = 'alerts';

        return view('mobile/staff/alerts', $data);
    }
}