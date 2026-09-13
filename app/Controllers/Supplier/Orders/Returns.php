<?php

namespace App\Controllers\Supplier\Orders;

use App\Controllers\BaseController;
use App\Models\Supplier\Orders\ReturnsModel;

class Returns extends BaseController
{
    protected $returnsModel;

    public function __construct()
    {
        $this->returnsModel = new ReturnsModel();
    }

    public function index()
    {
        $supplierId = session()->get('supplier_id');
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $statusFilter = $this->request->getGet('status');
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->returnsModel->getReturns($supplierId, $search, $statusFilter, $page, 10);
        $kpis = $this->returnsModel->getKpis($supplierId);

        $data['returns'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['search'] = $search;
        $data['status_filter'] = $statusFilter;
        $data['count_pending'] = $kpis['pending'];
        $data['count_approved'] = $kpis['approved'];
        $data['count_rejected'] = $kpis['rejected'];

        $data['title'] = "Supplier Returns";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "supplier_returns";
        return view('pages/supplier/orders/returns', $data);
    }

    public function confirm_received($id)
{
    $supplierId = session()->get('supplier_id');
    $success = $this->returnsModel->confirmReceived((int) $id, $supplierId);
    return redirect()->to('supplier/orders/returns')->with($success ? 'success' : 'error',
        $success ? 'Confirmed — thanks for letting us know it arrived.' : 'Unable to update this return.');
}

public function get_details($id)
{
    $supplierId = session()->get('supplier_id');
    $details = $this->returnsModel->getReturnDetails((int) $id, $supplierId);
    if (!$details) return $this->response->setStatusCode(404)->setJSON(['error' => 'Return not found']);
    return $this->response->setJSON($details);
}

}