<?php

namespace App\Controllers\Client\Orders;

use App\Controllers\BaseController;
use App\Models\Client\Orders\ReturnsModel;

class Returns extends BaseController
{
    protected $returnsModel;

    public function __construct()
    {
        $this->returnsModel = new ReturnsModel();
    }

    public function index()
    {
        $clientId = session()->get('client_id');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $this->returnsModel->getReturns($clientId, $page, 10);

        $data['returns'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;

        $data['title'] = "My Returns";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "returns";
        return view('pages/client/orders/returns', $data);
    }
}