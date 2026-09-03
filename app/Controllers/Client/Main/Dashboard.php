<?php

namespace App\Controllers\Client\Main;

use App\Controllers\BaseController;
use App\Models\Client\DashboardModel;

class Dashboard extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        $clientId = session()->get('client_id');

        $data['client'] = $this->dashboardModel->getClientProfile($clientId);

        $kpis = $this->dashboardModel->getKpis($clientId);
        $data['active_orders'] = $kpis['active_orders'];
        $data['total_orders_ytd'] = $kpis['total_orders_ytd'];
        $data['pending_payment'] = $kpis['pending_payment'];
        $data['total_spend_ytd'] = $kpis['total_spend_ytd'];

        $data['recent_orders'] = $this->dashboardModel->getRecentOrders($clientId, 5);
        $data['pending_clearance'] = $this->dashboardModel->getPendingClearance($clientId, 5);
        $data['announcements'] = $this->dashboardModel->getActiveAnnouncements(3);

        $data['title'] = "Client Dashboard";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "dashboard";
        return view('pages/client/main/dashboard', $data);
    }
}