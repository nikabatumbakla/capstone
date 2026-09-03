<?php

namespace App\Controllers\Staff\Main;

use App\Controllers\BaseController;
use App\Models\Staff\StaffDashboardModel;

class Dashboard extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new StaffDashboardModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');

        $kpis = $this->dashboardModel->getKpis($userId);

        $data['pos_txns']         = $kpis['pos_txns'];
        $data['pending_grr']      = $kpis['pending_grr'];
        $data['orders_to_process'] = $kpis['orders_to_process'];
        $data['assigned_alerts']  = $kpis['assigned_alerts'];

        $data['tasks'] = $this->dashboardModel->getTodaysTasks($userId, 5);
        $data['low_stock'] = $this->dashboardModel->getLowStock(5);

        $data['recent_activity'] = $this->dashboardModel->getRecentActivity($userId, 6);

        $data['title']     = 'Staff Terminal';
        $data['fullname']  = session()->get('full_name');
        $data['page_name'] = 'dashboard';

        return view('pages/staff/main/dashboard', $data);
    }
}