<?php

namespace App\Controllers\Mobile\Staff;

use App\Controllers\BaseController;
use App\Models\Mobile\StaffMobileModel;

class MobileDashboard extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'staff') return redirect()->to('m/');

        $model = new StaffMobileModel();
        $userId = session()->get('user_id');

        $data['summary'] = $model->getHomeSummary($userId);
        $data['recent_scans'] = $model->getRecentScans($userId, 5);
        $data['my_tasks'] = $model->getMyTasks($userId, 5);
        $data['title'] = 'Staff Home';
        $data['page_name'] = 'home';

        return view('mobile/staff/home', $data);
    }

    public function tasks()
    {
        if (session()->get('role') !== 'staff') return redirect()->to('m/');

        $model = new StaffMobileModel();
        $userId = session()->get('user_id');

        $counts = $model->getTaskPageCounts($userId);
        $data['pending_count'] = $counts['pending'];
        $data['urgent_count'] = $counts['urgent'];
        $data['tasks'] = $model->getMyTasks($userId, 20);
        $data['title'] = 'Operations Tasks';
        $data['page_name'] = 'tasks';

        return view('mobile/staff/tasks', $data);
    }
}