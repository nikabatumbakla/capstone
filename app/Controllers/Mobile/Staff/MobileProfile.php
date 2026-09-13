<?php

namespace App\Controllers\Mobile\Staff;

use App\Controllers\BaseController;

class MobileProfile extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'staff') return redirect()->to('m/');

        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $staffProfile = $db->table('staff_profiles')->where('user_id', $userId)->get()->getRow();
        $data['employee_id'] = $staffProfile->employee_id ?? null;
        $data['position'] = $staffProfile->position ?? null;

        $data['stats'] = [
            'scans' => $db->table('stock_movements')->where('scanned_by', $userId)->where('DATE(moved_at)', date('Y-m-d'))->countAllResults(),
            'pos'   => $db->table('pos_transactions')->where('cashier_id', $userId)->where('DATE(created_at)', date('Y-m-d'))->countAllResults(),
            'grr'   => $db->table('stock_movements')->where('scanned_by', $userId)->where('scan_mode', 'mobile_grr')->where('DATE(moved_at)', date('Y-m-d'))->countAllResults(),
        ];

        $data['title'] = 'My Profile';
        $data['page_name'] = 'profile';
        return view('mobile/staff/profile', $data);
    }
}