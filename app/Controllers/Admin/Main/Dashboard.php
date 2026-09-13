<?php

namespace App\Controllers\Admin\Main;

use App\Controllers\BaseController;
use App\Models\Admin\Main\DashboardModel;

class Dashboard extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        $kpis = $this->dashboardModel->getKpis();
        $data['total_sales_today'] = $kpis['total_sales_today'];
        $data['active_products'] = $kpis['active_products'];
        $data['low_stock_count'] = $kpis['low_stock_count'];
        $data['pending_orders'] = $kpis['pending_orders'];

        $data['weekly_trend'] = $this->dashboardModel->getWeeklyTrend();
        $data['top_clients'] = $this->dashboardModel->getTopClients(5);
        $data['active_alerts'] = $this->dashboardModel->getActiveAlerts(3);
        $data['category_sales'] = $this->dashboardModel->getCategorySales(6);
        $data['pending_deliveries'] = $this->dashboardModel->getPendingDeliveries(3);

        $data['fullname'] = session()->get('full_name');
        $data['title'] = "Admin Dashboard";
        $data['page_name'] = "dashboard";

        return view('pages/admin/main/dashboard', $data);
    }

    public function weekly_trend_data()
{
    return $this->response->setJSON($this->dashboardModel->getWeeklyTrend());
}
}