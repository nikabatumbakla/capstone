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
        $data['categories'] = $this->productsModel->getCategories();
        $data['all_products'] = $this->productsModel->getProducts('', '', 1, 500)['data']; // for the "add another item" dropdown

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
    $paymentMethod = $this->request->getPost('payment_method') ?: 'check';
    $notes = trim((string) $this->request->getPost('order_notes'));

    if (empty($productIds)) {
        return redirect()->back()->withInput()->with('error', 'Your cart is empty.');
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
}