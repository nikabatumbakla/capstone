<?php

namespace App\Controllers\Staff\Operations;

use App\Controllers\BaseController;
use App\Models\Staff\Operations\ReturnsModel;

class Returns extends BaseController
{
    protected $returnsModel;

    public function __construct()
    {
        $this->returnsModel = new ReturnsModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status') ?: '';
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->returnsModel->getReturns($status, $search, $page, 10);
        $counts = $this->returnsModel->getCounts();

        $data['returns'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['status_filter'] = $status;
        $data['search'] = $search;

        $data['count_pending'] = $counts['pending'];
        $data['count_approved'] = $counts['approved'];
        $data['count_rejected'] = $counts['rejected'];

        $data['eligible_orders'] = $this->returnsModel->getEligibleOrders();

        $data['title'] = "Sales Returns";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "returns";
        return view('pages/staff/operations/sales_returns', $data);
    }

    public function get_order_items($orderId)
    {
        return $this->response->setJSON($this->returnsModel->getOrderItems((int) $orderId));
    }

    public function process_return()
    {
        $orderId = (int) $this->request->getPost('order_id');
        $productId = (int) $this->request->getPost('product_id');
        $batchId = $this->request->getPost('batch_id') ?: null;
        $qty = (int) $this->request->getPost('qty');
        $reasonCat = $this->request->getPost('reason_cat');
        $notes = trim((string) $this->request->getPost('notes'));
        $condition = $this->request->getPost('restock_condition') ?: 'resellable';

        if (empty($orderId) || empty($productId) || $qty <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please complete all required fields.');
        }

        $this->returnsModel->submitReturn([
            'order_id'          => $orderId,
            'product_id'        => $productId,
            'batch_id'          => $batchId,
            'quantity'          => $qty,
            'restock_condition' => $condition,
            'processed_by'      => session()->get('user_id'),
            'reason'            => $reasonCat . ': ' . $notes,
        ]);

        return redirect()->to('staff/operations/sales-returns')->with('success', 'Return request submitted — awaiting admin approval.');
    }
}