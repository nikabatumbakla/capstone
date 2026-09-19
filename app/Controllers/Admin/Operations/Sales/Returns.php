<?php

namespace App\Controllers\Admin\Operations\Sales;

use App\Controllers\BaseController;
use App\Models\Admin\Operations\Sales\SalesReturnsModel;

class Returns extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SalesReturnsModel();
    }

    public function returns()
    {
        $request = \Config\Services::request();

        $search = trim((string) ($request->getGet('search') ?? ''));
        $status = $request->getGet('status') ?? 'pending';
        $page = max(1, (int) ($request->getGet('page') ?? 1));
        $perPage = 10;

        $result = $this->model->getReturns($search, $status, $page, $perPage);

        $data['returns'] = $result['data'];
        $data['delivered_orders'] = $this->model->getDeliveredOrders();
        $data['total_rows'] = $result['total'];
        $data['current_page'] = $page;
        $data['per_page'] = $perPage;
        $data['total_pages'] = $result['total_pages'];
        $data['active_status'] = $status;
        $data['search'] = $search;

        $data['title'] = "Sales Returns";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "sales";
        return view('pages/admin/operations/sales/sales_returns', $data);
    }

    public function get_return_details($id)
    {
        $data = $this->model->getReturnDetails((int) $id);
        if (!$data) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($data);
    }

    public function get_return_order_items($order_id)
    {
        return $this->response->setJSON($this->model->getOrderItems((int) $order_id));
    }

    public function process_return()
    {
        $db = \Config\Database::connect();
        $session = session();

        $order_id   = $this->request->getPost('order_id');
        $product_id = $this->request->getPost('product_id');
        $batch_id   = $this->request->getPost('batch_id');
        $qty        = (int) $this->request->getPost('qty');
        $reasonCat  = $this->request->getPost('reason_cat');
        $notes      = trim((string) $this->request->getPost('notes'));
        $condition  = $this->request->getPost('restock_condition') ?: 'resellable';
        $refund     = $this->request->getPost('refund_amount');

        if (empty($order_id) || empty($product_id) || $qty <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please complete all required fields.');
        }

        $order = $db->table('sales_orders')->where('order_id', $order_id)->get()->getRow();
        if (!$order) {
            return redirect()->back()->withInput()->with('error', 'Order not found.');
        }

        $db->transStart();

        $db->table('sales_returns')->insert([
            'order_id'          => $order_id,
            'product_id'        => $product_id,
            'batch_id'          => $batch_id ?: null,
            'quantity'          => $qty,
            'restock_condition' => $condition,
            'refund_amount'     => $refund !== '' ? $refund : null,
            'processed_by'      => $session->get('user_id') ?? 1,
            'resolved_by'       => $session->get('user_id') ?? 1,
            'resolved_at'       => date('Y-m-d H:i:s'),
            'reason'            => $reasonCat . ': ' . $notes,
            'status'            => 'approved',
            'source'            => 'staff',
        ]);
        $returnId = $db->insertID();

        $ret = (object) [
            'return_id'         => $returnId,
            'order_id'          => $order_id,
            'product_id'        => $product_id,
            'batch_id'          => $batch_id ?: null,
            'quantity'          => $qty,
            'restock_condition' => $condition,
            'client_id'         => $order->client_id,
            'guest_client_id'   => $order->guest_client_id,
            'fulfillment_type'  => $order->fulfillment_type,
        ];
        $this->model->applyResolution($returnId, $ret);

        $db->transComplete();

        if (!empty($order->client_id)) {
            \App\Models\Client\NotificationModel::notify($db, (int) $order->client_id, "A return has been processed on your order {$order->order_number}.", '/client/orders/returns');
        }

        $msg = ($condition === 'resellable')
            ? 'Return processed and stock restored.'
            : 'Return processed. Item was not restocked, and a free replacement order has been created.';
        return redirect()->to('admin/sales/sales-returns')->with('success', $msg);
    }

    public function approve_return($id)
    {
        $db = \Config\Database::connect();

        $ret = $db->table('sales_returns as sr')
            ->select('sr.*, so.client_id, so.guest_client_id, so.fulfillment_type, so.order_number')
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->where('sr.return_id', $id)->get()->getRow();

        if (!$ret || $ret->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending returns can be approved.');
        }

        $db->transStart();
        $db->table('sales_returns')->where('return_id', $id)->update([
            'status'      => 'approved',
            'resolved_by' => session()->get('user_id') ?? 1,
            'resolved_at' => date('Y-m-d H:i:s')
        ]);

        $this->model->applyResolution((int) $id, $ret);
        $db->transComplete();

        if (!empty($ret->client_id)) {
            \App\Models\Client\NotificationModel::notify($db, (int) $ret->client_id, "Your return has been approved — a replacement order has been created.", '/client/orders/my-orders');
        }

        $msg = ($ret->restock_condition === 'resellable')
            ? 'Return approved and stock restored.'
            : "Return approved. Item was NOT returned to sellable stock, and a free replacement order has been created for the client.";
        return redirect()->back()->with('success', $msg);
    }

    public function reject_return($id)
    {
        $db = \Config\Database::connect();
        $ret = $db->table('sales_returns as sr')->select('sr.*, so.client_id, so.order_number')->join('sales_orders as so', 'so.order_id = sr.order_id')->where('sr.return_id', $id)->get()->getRow();

        if (!$ret || $ret->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending returns can be rejected.');
        }

        $db->table('sales_returns')->where('return_id', $id)->update(['status' => 'rejected']);

        if (!empty($ret->client_id)) {
            \App\Models\Client\NotificationModel::notify($db, (int) $ret->client_id, "Your return request for order {$ret->order_number} was reviewed and rejected.", '/client/orders/returns');
        }

        return redirect()->back()->with('info', 'Return Request Rejected.');
    }
}