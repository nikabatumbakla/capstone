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

        // System Alerts tab
        $alertType = $this->request->getGet('type') ?: '';
        $alertPage = (int) ($this->request->getGet('alert_page') ?? 1);
        $alertFeed = $this->alertsModel->getFeed('system', 'open', $alertType, '', null, $alertPage, 10);

        // My Tasks tab
        $taskPriority = $this->request->getGet('task_priority') ?: '';
        $taskStatus = $this->request->getGet('task_status') ?: 'open';
        $taskPage = (int) ($this->request->getGet('task_page') ?? 1);
        $taskFeed = $this->alertsModel->getFeed('manual', $taskStatus, '', $taskPriority, null, $taskPage, 10);

        $data['alerts'] = $alertFeed['data'];
        $data['alert_total_pages'] = $alertFeed['total_pages'];
        $data['alert_current_page'] = $alertPage;
        $data['type_filter'] = $alertType;

        $data['tasks'] = $taskFeed['data'];
        $data['task_total_pages'] = $taskFeed['total_pages'];
        $data['task_current_page'] = $taskPage;
        $data['task_priority_filter'] = $taskPriority;
        $data['task_status_filter'] = $taskStatus;

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
        return redirect()->to('admin/management/alerts-tasks')->with('success', $id ? 'Task updated.' : 'Task created.');
    }

    public function get_details($id)
    {
        $row = $this->alertsModel->getById((int) $id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($row);
    }

    // POST now, not GET — a resolution note can contain characters that don't
    // survive a URL query string cleanly, and this action changes state.
    public function resolve()
    {
        $id = (int) $this->request->getPost('alert_id');
        $note = trim((string) $this->request->getPost('note'));
        $this->alertsModel->resolve($id, $note ?: null);
        return redirect()->back()->with('success', 'Marked as resolved.');
    }

    public function delete($id)
    {
        $this->alertsModel->removeAlert((int) $id);
        return redirect()->to('admin/management/alerts-tasks')->with('success', 'Removed.');
    }

    public function header_notifications()
    {
        return $this->response->setJSON($this->alertsModel->getHeaderNotifications(6));
    }

    public function check_stockout_events()
    {
        $db = \Config\Database::connect();
        $events = $db->table('stockout_events')->where('is_seen', 0)->orderBy('created_at', 'DESC')->get()->getResultArray();

        if (!empty($events)) {
            $ids = array_column($events, 'event_id');
            $db->table('stockout_events')->whereIn('event_id', $ids)->update(['is_seen' => 1]);
        }

        return $this->response->setJSON($events);
    }
}