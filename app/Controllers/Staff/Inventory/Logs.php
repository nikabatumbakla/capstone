<?php

namespace App\Controllers\Staff\Inventory;

use App\Controllers\BaseController;
use App\Models\Staff\Inventory\LogsModel;

class Logs extends BaseController
{
    protected $logsModel;

    public function __construct()
    {
        $this->logsModel = new LogsModel();
    }

    public function logs()
    {
        $staffUserId = session()->get('user_id');
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $reason = $this->request->getGet('reason') ?: '';
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->logsModel->getLogs($staffUserId, $search, $reason, $page, 10);
        $summary = $this->logsModel->getSummary($staffUserId);

        $data['logs'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['search'] = $search;
        $data['reason_filter'] = $reason;

        $data['total_adjustments'] = $summary['total'];
        $data['today_adjustments'] = $summary['today'];
        $data['damage_expired_count'] = $summary['damage_or_expired'];

        $data['title'] = "Stock Adjustment History";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "logs";

        return view('pages/staff/inventory/logs', $data);
    }
}