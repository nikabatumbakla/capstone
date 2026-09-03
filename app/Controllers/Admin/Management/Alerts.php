<?php

namespace App\Controllers\Admin\Management;

use App\Controllers\BaseController;
use App\Models\Admin\Management\AlertsModel;

class Alerts extends BaseController
{
    protected $alertsModel;

    public function __construct()
    {
        $this->alertsModel = new AlertsModel();
    }

    public function index()
{
    $this->alertsModel->syncSystemAlerts();

    $status = $this->request->getGet('status') ?: 'open';
    $type = $this->request->getGet('type') ?: '';
    $priority = $this->request->getGet('priority') ?: '';
    $assignedTo = $this->request->getGet('assigned_to') ? (int) $this->request->getGet('assigned_to') : null;
    $page = (int) ($this->request->getGet('page') ?? 1);

    $feed = $this->alertsModel->getFeed($status, $type, $priority, $assignedTo, $page, 10);

    $data['alerts'] = $feed['data'];
    $data['total_pages'] = $feed['total_pages'];
    $data['current_page'] = $page;
    $data['status_filter'] = $status;
    $data['type_filter'] = $type;
    $data['priority_filter'] = $priority;
    $data['assigned_filter'] = $assignedTo;

    $counts = $this->alertsModel->getCounts();
    $data['count_low_stock'] = $counts['low_stock'];
    $data['count_near_expiry'] = $counts['near_expiry'];
    $data['count_expired'] = $counts['expired'];
    $data['count_po'] = $counts['po_approval'];

    $data['assignable_staff'] = $this->alertsModel->getAssignableStaff();

    $data['title'] = "Alerts & Tasks";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "alerts";
    return view('pages/admin/management/alerts_tasks', $data);
}

    public function save()
{
    $id = $this->request->getPost('alert_id');
    $payload = [
        'message'     => $this->request->getPost('message'),
        'priority'    => $this->request->getPost('priority'),
        'notes'       => $this->request->getPost('notes') ?: null,
        'assigned_to' => $this->request->getPost('assigned_to') ?: null,
        'due_date'    => $this->request->getPost('due_date') ?: null,
    ];
    $this->alertsModel->saveManual($payload, $id ?: null);
    return redirect()->to('admin/management/alerts-tasks')->with('success', 'Task saved.');
}

    public function get_details($id)
    {
        $row = $this->alertsModel->getById($id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($row);
    }

    public function resolve($id)
{
    $note = $this->request->getGet('note');
    $this->alertsModel->resolve($id, $note);
    return redirect()->back()->with('success', 'Marked as resolved.');
}

    public function delete($id)
    {
        $this->alertsModel->delete($id);
        return redirect()->to('admin/management/alerts-tasks')->with('success', 'Removed.');
    }

    public function header_notifications()
{
    return $this->response->setJSON($this->alertsModel->getHeaderNotifications(6));
}
}