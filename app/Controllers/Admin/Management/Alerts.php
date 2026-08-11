<?php

namespace App\Controllers\Admin\Management;
use App\Controllers\BaseController;

class Alerts extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // 1. Fetch Summary for Figma Tiles
        $data['count_low_stock'] = $db->table('alerts')->where('alert_type', 'low_stock')->countAllResults();
        $data['count_near_expiry'] = $db->table('alerts')->where('alert_type', 'near_expiry')->countAllResults();
        $data['count_expired'] = $db->table('alerts')->where('alert_type', 'expired')->countAllResults();
        $data['count_po'] = $db->table('alerts')->where('alert_type', 'po_approval')->countAllResults();

        // 2. Main Feed
        $data['alerts'] = $db->table('alerts')->orderBy('created_at', 'DESC')->get()->getResultArray();

        $data['title'] = "Alerts & Tasks";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "alerts";
        
        return view('pages/admin/management/alerts_tasks', $data);
    }

    public function save()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('alert_id');

        $payload = [
            'alert_type' => $this->request->getPost('type'),
            'notes'      => $this->request->getPost('priority'), // This matches the new column
            'message'    => $this->request->getPost('message'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($id) {
            $db->table('alerts')->where('alert_id', $id)->update($payload);
        } else {
            $db->table('alerts')->insert($payload);
        }

        return redirect()->to('admin/management/alerts-tasks')->with('success', 'Task Synchronized.');
    }

    public function get_details($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('alerts')->where('alert_id', $id)->get()->getRow();
        return $this->response->setJSON($row);
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $db->table('alerts')->where('alert_id', $id)->delete();
        return redirect()->to('admin/management/alerts-tasks')->with('success', 'Removed.');
    }
}