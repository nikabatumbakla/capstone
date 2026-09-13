<?php

namespace App\Controllers\Client\Orders;

use App\Controllers\BaseController;
use App\Models\Client\Orders\OrdersModel;
use App\Models\Client\Orders\ProductsModel;

class Orders extends BaseController
{
    protected $ordersModel;
    protected $productsModel;

    public function __construct()
    {
        $this->ordersModel = new OrdersModel();
        $this->productsModel = new ProductsModel();
    }

    public function index()
    {
        $clientId = session()->get('client_id');
        $status = $this->request->getGet('status') ?: '';
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->ordersModel->getMyOrders($clientId, $status, $search, $page, 10);
        $kpis = $this->ordersModel->getKpis($clientId);

        $data['orders'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['status_filter'] = $status;
        $data['search'] = $search;
        $data['count_active'] = $kpis['active'];
        $data['count_ytd'] = $kpis['ytd'];
        $data['count_unpaid'] = $kpis['unpaid'];
        $data['count_completed'] = $kpis['completed'];

        $data['title'] = "My Order History";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "orders";
        return view('pages/client/orders/my_orders', $data);
    }

    public function get_order_details($orderId)
    {
        $clientId = session()->get('client_id');
        $result = $this->ordersModel->getOrderDetails((int) $orderId, $clientId);
        if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'Order not found']);

        $result['store_info'] = $this->ordersModel->getStoreInfo();
        return $this->response->setJSON($result);
    }

    public function place_order_view()
{
    $cart = session()->get('client_cart') ?? [];
    $cartProducts = $this->productsModel->getProductsByIds(array_keys($cart));

    $cartItems = [];
    foreach ($cartProducts as $p) {
        $cartItems[] = array_merge($p, ['qty' => $cart[$p['product_id']]]);
    }

    $data['cart_items'] = $cartItems;
    $data['store_info'] = $this->ordersModel->getStoreInfo();

    $data['title'] = "Place New Sales Order";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "place";
    return view('pages/client/orders/place_order', $data);
}

    public function update_cart_qty()
    {
        $productId = (int) $this->request->getPost('product_id');
        $qty = (int) $this->request->getPost('qty');
        $cart = session()->get('client_cart') ?? [];

        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }
        session()->set('client_cart', $cart);
        return $this->response->setJSON(['status' => 'ok']);
    }

    public function remove_from_cart($productId)
    {
        $cart = session()->get('client_cart') ?? [];
        unset($cart[(int) $productId]);
        session()->set('client_cart', $cart);
        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    public function save_order()
{
    $clientId = session()->get('client_id');
    $productIds = $this->request->getPost('product_ids');
    $qtys = $this->request->getPost('qtys');
    $fulfillmentType = $this->request->getPost('fulfillment_type') ?: 'delivery';
    $deliveryAddress = trim((string) $this->request->getPost('delivery_address'));
    $paymentMethod = $this->request->getPost('payment_method');
    $notes = trim((string) $this->request->getPost('order_notes'));

    if (empty($productIds)) {
        return redirect()->back()->withInput()->with('error', 'Your cart is empty.');
    }
    if (empty($paymentMethod)) {
        return redirect()->back()->withInput()->with('error', 'Please select a payment method.');
    }
    if ($fulfillmentType === 'delivery' && $deliveryAddress === '') {
        return redirect()->back()->withInput()->with('error', 'Please provide a delivery address, or select Pickup instead.');
    }

    $result = $this->ordersModel->saveOrder(
        $clientId, $productIds, $qtys, $fulfillmentType, $deliveryAddress, $paymentMethod, $notes, session()->get('user_id')
    );

    if (!$result['success']) {
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    foreach ($result['products_ordered'] as $pid) {
        \App\Libraries\AutoReorder::check($pid);
    }

    session()->remove('client_cart');

    $msg = 'Order placed successfully.' . ($result['capped'] > 0 ? " Note: {$result['capped']} item(s) were reduced to match available stock." : '');
    return redirect()->to('client/orders/my-orders')->with('success', $msg);
}

public function get_confirm_receipt_info($orderId)
{
    $clientId = session()->get('client_id');
    $result = $this->ordersModel->getOrderForConfirm((int) $orderId, $clientId);
    if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'This order cannot be confirmed right now.']);
    return $this->response->setJSON($result);
}

public function process_confirm_receipt()
{
    $clientId = session()->get('client_id');
    $orderId = (int) $this->request->getPost('order_id');

    $result = $this->ordersModel->confirmReceipt(
        $orderId, $clientId,
        $this->request->getPost('product_id') ?: [],
        $this->request->getPost('batch_id') ?: [],
        $this->request->getPost('qty_ordered') ?: [],
        $this->request->getPost('condition') ?: [],
        $this->request->getPost('qty_flagged') ?: [],
        session()->get('user_id')
    );

    if (!$result['success']) {
        return redirect()->back()->with('error', $result['message']);
    }

    if ($result['has_issue']) {
        return redirect()->to('client/orders/returns')->with('success', 'Thank you — your report has been filed. Robin Rose Trading will review it and arrange a resolution.');
    }
    return redirect()->to('client/orders/my-orders')->with('success', 'Delivery confirmed — thank you!');
}

public function get_payment_info($orderId)
{
    $clientId = session()->get('client_id');
    $order = $this->ordersModel->getOrderForPayment((int) $orderId, $clientId);
    if (!$order) return $this->response->setStatusCode(404)->setJSON(['error' => 'This order does not require a payment reference right now.']);

    return $this->response->setJSON([
        'order' => $order,
        'store_info' => $this->ordersModel->getStoreInfo(),
    ]);
}

public function process_submit_payment()
{
    $clientId = session()->get('client_id');
    $orderId = (int) $this->request->getPost('order_id');
    $reference = trim((string) $this->request->getPost('reference'));

    if ($reference === '') {
        return redirect()->back()->with('error', 'Please enter your payment reference number.');
    }

    $success = $this->ordersModel->submitPaymentReference($orderId, $clientId, $reference);
    return redirect()->to('client/orders/my-orders')->with($success ? 'success' : 'error',
        $success ? 'Payment reference submitted — Robin Rose Trading will confirm it shortly.' : 'Unable to submit — this order may not need one.');
}

public function get_issue_report_info($orderId)
{
    $clientId = session()->get('client_id');
    $result = $this->ordersModel->getOrderForIssueReport((int) $orderId, $clientId);
    if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'This order is not eligible for a return report.']);
    return $this->response->setJSON($result);
}

public function process_report_issue()
{
    $clientId = session()->get('client_id');
    $orderId = (int) $this->request->getPost('order_id');

    $result = $this->ordersModel->reportIssue(
        $orderId, $clientId,
        $this->request->getPost('product_id') ?: [],
        $this->request->getPost('batch_id') ?: [],
        $this->request->getPost('qty_ordered') ?: [],
        $this->request->getPost('condition') ?: [],
        $this->request->getPost('qty_flagged') ?: [],
        session()->get('user_id')
    );

    if (!$result['success']) {
        return redirect()->back()->with('error', $result['message']);
    }
    return redirect()->to('client/orders/my-orders')->with('success', 'Thank you — your report has been sent to Robin Rose Trading for review.');
}

public function get_cart_count()
{
    $cart = session()->get('client_cart') ?? [];
    return $this->response->setJSON(['count' => array_sum($cart)]);
}

}