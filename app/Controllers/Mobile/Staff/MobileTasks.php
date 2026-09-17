<?php

namespace App\Controllers\Mobile\Staff;

use App\Controllers\BaseController;
use App\Models\Mobile\StaffMobileModel;

class MobileTasks extends BaseController
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
        $counts = $this->model->getTaskCounts($userId);

        $data['tasks'] = $this->model->getMyTasks($userId, 50);
        $data['pending_count'] = $counts['pending'];
        $data['urgent_count'] = $counts['urgent'];
        $data['title'] = 'My Tasks';
        $data['page_name'] = 'tasks';

        return view('mobile/staff/tasks', $data);
    }

    public function complete($id)
    {
        $success = $this->model->completeTask((int) $id, session()->get('user_id'));
        return redirect()->back()->with($success ? 'success' : 'error',
            $success ? 'Task marked complete.' : 'Unable to complete this task.');
    }
}