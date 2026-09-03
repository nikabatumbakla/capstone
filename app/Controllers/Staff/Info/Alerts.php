<?php

namespace App\Controllers\Staff\Info;

use App\Controllers\BaseController;
use App\Models\Staff\Info\AlertsModel;

class Alerts extends BaseController
{
    protected $alertsModel;

    public function __construct()
    {
        $this->alertsModel = new AlertsModel();
    }

    public function index()
{
    $userId = session()->get('user_id');
    $type = $this->request->getGet('type') ?: '';
    $status = $this->request->getGet('status') ?: 'active';
    $priority = $this->request->getGet('priority') ?: '';
    $unread = $this->request->getGet('unread') ?: '';
    $page = (int) ($this->request->getGet('page') ?? 1);

    $result = $this->alertsModel->getFeed($userId, $type, $status, $priority, $unread, $page, 10);
    $counts = $this->alertsModel->getCounts($userId);

    $data['alerts'] = $result['data'];
    $data['total_pages'] = $result['total_pages'];
    $data['current_page'] = $page;
    $data['type_filter'] = $type;
    $data['status_filter'] = $status;
    $data['priority_filter'] = $priority;
    $data['unread_filter'] = $unread;

    $data['total_active'] = $counts['total_active'];
    $data['high_priority'] = $counts['high_priority'];
    $data['unread_count'] = $counts['unread'];

    $data['title'] = "My Alert Intelligence";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "alerts";
    return view('pages/staff/info/alerts', $data);
}

    public function mark_as_read($id)
    {
        $this->alertsModel->markAsRead((int) $id);
        return redirect()->back()->with('success', 'Marked as read.');
    }

    public function complete_task($id)
    {
        $success = $this->alertsModel->completeTask((int) $id, session()->get('user_id'));
        return redirect()->back()->with($success ? 'success' : 'error', $success ? 'Task marked complete.' : 'You can only complete tasks assigned directly to you.');
    }

    public function header_notifications()
{
    $data = $this->alertsModel->getHeaderNotifications(session()->get('user_id'), 8);
    return $this->response->setJSON($data);
}
}