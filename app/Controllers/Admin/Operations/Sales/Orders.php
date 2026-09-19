<?php

namespace App\Controllers\Admin\Operations\Sales;

use App\Controllers\BaseController;
use App\Models\Admin\Operations\Sales\SalesOrdersModel;

class Orders extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SalesOrdersModel();
    }

    public function orders()
    {
        $request = \Config\Services::request();

        $data['categories'] = $this->model->getCategories();
        $data['products'] = $this->model->getSellableProducts();
        $data['school_discount_rate'] = $this->model->getSchoolDiscountRate();

        $search = trim((string) ($request->getGet('search') ?? ''));
        $type = $request->getGet('type') ?? '';
        $page = max(1, (int) ($request->getGet('page') ?? 1));
        $perPage = 10;

        $result = $this->model->getOrders($search, $type, $page, $perPage);

        $data['orders'] = $result['data'];
        $data['total_rows'] = $result['total'];
        $data['current_page'] = $page;
        $data['per_page'] = $perPage;
        $data['total_pages'] = $result['total_pages'];
        $data['search'] = $search;
        $data['type_filter'] = $type;

        $data['title'] = "Sales Orders";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "sales";
        return view('pages/admin/operations/sales/sales_orders', $data);
    }

    public function save_order()
    {
        $result = $this->model->saveOrder($this->request->getPost(), session()->get('user_id') ?? 1);

        if (!$result['success']) return redirect()->back()->withInput()->with('error', $result['message']);
        return redirect()->to('admin/sales/sales-orders')->with('success', $result['message']);
    }

    public function get_order_details($id)
    {
        $data = $this->model->getOrderDetails((int) $id);
        if (!$data) return $this->response->setStatusCode(404)->setJSON(['error' => 'Order not found']);
        return $this->response->setJSON($data);
    }

    public function update_order_item()
    {
        $itemId = (int) $this->request->getPost('item_id');
        $productId = (int) $this->request->getPost('product_id');
        $qty = (int) $this->request->getPost('qty');

        $result = $this->model->updateOrderItem($itemId, $productId, $qty);
        return $this->response->setJSON($result);
    }

    public function confirm_payment()
    {
        $orderId = (int) $this->request->getPost('order_id');
        $method = $this->request->getPost('payment_method');
        $reference = trim((string) $this->request->getPost('payment_reference'));

        if (!in_array($method, ['cash', 'bank_transfer', 'cheque'])) {
            return redirect()->back()->with('error', 'Please select a valid payment method.');
        }
        if (in_array($method, ['bank_transfer', 'cheque']) && $reference === '') {
            return redirect()->back()->with('error', 'A reference number is required for bank transfer or cheque payments.');
        }

        $db = \Config\Database::connect();
        $order = $db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
        if (!$order || $order->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Order not found or already marked paid.');
        }

        $db->table('sales_orders')->where('order_id', $orderId)->update([
            'payment_status'    => 'paid',
            'payment_method'    => $method,
            'payment_reference' => $reference ?: null,
            'paid_at'           => date('Y-m-d H:i:s'),
        ]);

        if (!empty($order->client_id)) {
            \App\Models\Client\NotificationModel::notify($db, (int) $order->client_id, "Payment confirmed for order {$order->order_number}.", '/client/account/invoices');
        }

        return redirect()->back()->with('success', 'Payment confirmed.');
    }

    public function update_order_status()
    {
        $db = \Config\Database::connect();
        $orderId = (int) $this->request->getPost('order_id');
        $newStatus = $this->request->getPost('status');

        $order = $db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        $requiresPrepayment = $order->fulfillment_type === 'delivery'
            && in_array($order->payment_method, ['cheque', 'bank_transfer'])
            && $order->payment_status !== 'paid';

        if ($newStatus === 'out_for_delivery' && $requiresPrepayment) {
            return redirect()->back()->with('error',
                "This order is paid via " . strtoupper(str_replace('_', ' ', $order->payment_method)) .
                " and must be confirmed PAID before it can be dispatched. Please confirm payment first.");
        }

        if ($order->fulfillment_type === 'pickup' && $newStatus === 'delivered' && $order->payment_status !== 'paid') {
            return redirect()->back()->with('error', 'Please confirm payment before marking this order as picked up.');
        }

        $db->table('sales_orders')->where('order_id', $orderId)->update(['status' => $newStatus]);

        $updatedOrder = $db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
        if (!empty($updatedOrder->client_id)) {
            $statusMessages = [
                'ready_for_pickup' => "Your order {$updatedOrder->order_number} is ready for pickup at the store.",
                'out_for_delivery' => "Your order {$updatedOrder->order_number} is out for delivery.",
                'delivered'        => "Your order {$updatedOrder->order_number} has been completed.",
            ];
            if (isset($statusMessages[$newStatus])) {
                \App\Models\Client\NotificationModel::notify($db, (int) $updatedOrder->client_id, $statusMessages[$newStatus], '/client/orders/my-orders');
            }
        }

        return redirect()->back()->with('success', 'Order status updated.');
    }
}