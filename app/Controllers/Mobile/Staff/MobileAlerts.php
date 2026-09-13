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

        $userId = session()->get('user_id');
        $data['alerts'] = $this->model->getFullAlertsList($userId);
        $data['title'] = 'My Alerts';
        $data['page_name'] = 'alerts';

        return view('mobile/staff/alerts', $data);
    }

    public function complete($id)
    {
        $success = $this->model->completeTask((int) $id, session()->get('user_id'));
        return redirect()->back()->with($success ? 'success' : 'error',
            $success ? 'Task marked complete.' : 'Unable to complete this task.');
    }
}