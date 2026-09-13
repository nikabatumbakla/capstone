<?php

namespace App\Controllers\Client\Account;

use App\Controllers\BaseController;
use App\Models\Client\Account\InvoicesModel;

class Invoices extends BaseController
{
    protected $invoicesModel;

    public function __construct()
    {
        $this->invoicesModel = new InvoicesModel();
    }

    public function index()
    {
        $clientId = session()->get('client_id');
        $status = $this->request->getGet('status') ?: '';
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->invoicesModel->getInvoices($clientId, $status, $page, 10);
        $kpis = $this->invoicesModel->getKpis($clientId);

        $data['invoices'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['status_filter'] = $status;

        $data['outstanding_amount'] = $kpis['outstanding_amount'];
        $data['unpaid_count'] = $kpis['unpaid_count'];
        $data['awaiting_clearance'] = $kpis['awaiting_clearance'];
        $data['paid_ytd'] = $kpis['paid_ytd'];

        $data['title'] = "Invoices & Payments";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "invoices";
        return view('pages/client/account/invoices', $data);
    }


public function get_invoice_details($orderId)
{
    $clientId = session()->get('client_id');
    $result = $this->invoicesModel->getInvoiceDetails((int) $orderId, $clientId);
    if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'Invoice not found']);

    $result['store_info'] = $this->invoicesModel->getStoreInfo();
    return $this->response->setJSON($result);
}


}