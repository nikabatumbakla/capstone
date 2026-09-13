<?php

namespace App\Controllers\Supplier\Main;

use App\Controllers\BaseController;
use App\Models\Supplier\DashboardModel;

class Dashboard extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        $supplierId = session()->get('supplier_id');
        $request = \Config\Services::request();
        $statusFilter = $request->getGet('status');

        $data['scorecard'] = $this->dashboardModel->getScorecard($supplierId);

        $kpis = $this->dashboardModel->getKpis($supplierId);
        $data['open_pos_count'] = $kpis['open_pos'];
        $data['pending_ack']    = $kpis['pending_ack'];
        $data['completed_ytd']  = $kpis['completed_ytd'];

        $data['recent_pos'] = $this->dashboardModel->getFilteredPos($supplierId, $statusFilter, 10);
        $data['status_filter'] = $statusFilter;

        $data['announcements'] = $this->dashboardModel->getActiveAnnouncements(3);

        $data['title'] = "Supplier Dashboard";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "dashboard";

        return view('pages/supplier/main/dashboard', $data);
    }
}