<?php

namespace App\Controllers\Staff\Operations;

use App\Controllers\BaseController;
use App\Models\Staff\Operations\PoHistoryModel;

class PoHistory extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new PoHistoryModel();
    }

    public function index()
    {
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->model->getHistory($search, $page, 10);

        $data['history'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['search'] = $search;

        $data['title'] = "Purchase Order History";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "po-history";
        return view('pages/staff/operations/po_history', $data);
    }

    public function get_details($id)
    {
        $result = $this->model->getDetails((int) $id);
        if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($result);
    }
}