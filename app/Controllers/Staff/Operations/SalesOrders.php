<?php

namespace App\Controllers\Staff\Operations;

use App\Controllers\BaseController;
use App\Models\Staff\Operations\SalesOrdersModel;

class SalesOrders extends BaseController
{
    protected $salesOrdersModel;

    public function __construct()
    {
        $this->salesOrdersModel = new SalesOrdersModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status') ?: '';
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->salesOrdersModel->getOrders($status, $search, $page, 10);
        $counts = $this->salesOrdersModel->getCounts();

        $data['orders'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['status_filter'] = $status;
        $data['search'] = $search;

        $data['count_pending'] = $counts['pending'];
        $data['count_processing'] = $counts['processing'];
        $data['count_shipped'] = $counts['shipped'];
        $data['count_delivered'] = $counts['delivered'];

        $data['title'] = "Distribution Queue";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "orders";

        return view('pages/staff/operations/sales_orders', $data);
    }

    public function get_details($orderId)
    {
        $result = $this->salesOrdersModel->getOrderDetails((int) $orderId);
        if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'Order not found']);
        return $this->response->setJSON($result);
    }

    public function update_status()
    {
        $orderId = (int) $this->request->getPost('order_id');
        $newStatus = $this->request->getPost('status');
        $notes = $this->request->getPost('notes');

        $allowed = ['processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $allowed)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $this->salesOrdersModel->updateStatus($orderId, $newStatus, session()->get('user_id'), $notes);
        return redirect()->to('staff/operations/sales-orders')->with('success', 'Order status updated.');
    }

    public function confirm_payment()
{
    $orderId = (int) $this->request->getPost('order_id');
    $reference = trim((string) $this->request->getPost('payment_reference'));

    $this->salesOrdersModel->confirmPayment($orderId, $reference, session()->get('user_id'));
    return redirect()->back()->with('success', 'Payment confirmed.');
}

}