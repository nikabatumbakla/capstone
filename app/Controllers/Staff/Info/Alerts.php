<?php

namespace App\Controllers\Staff\Info;
use App\Controllers\BaseController;

class Alerts extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $session = session();
        $userId = $session->get('user_id');

        // Fetch unresolved alerts assigned to ME or to ALL
        $data['alerts'] = $db->table('alerts')
            ->where('is_resolved', 0)
            ->groupStart()
                ->where('assigned_to', $userId)
                ->orWhere('assigned_to', null)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        // Stats for tiles
        $data['unread_count'] = $db->table('alerts')->where(['assigned_to' => $userId, 'is_read' => 0])->countAllResults();

        $data['title'] = "My Alert Intelligence";
        $data['fullname'] = $session->get('full_name');
        $data['page_name'] = "alerts";
        return view('pages/staff/info/alerts', $data);
    }

    public function mark_as_read($id)
    {
        $db = \Config\Database::connect();
        $db->table('alerts')->where('alert_id', $id)->update(['is_read' => 1]);
        return redirect()->back();
    }
}