<?php

namespace App\Controllers\Client\Orders;

use App\Controllers\BaseController;
use App\Models\Client\Orders\ProductsModel;

class Products extends BaseController
{
    protected $productsModel;

    public function __construct()
    {
        $this->productsModel = new ProductsModel();
    }

    public function index()
    {
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $catId = $this->request->getGet('category') ?: '';
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->productsModel->getProducts($search, $catId, $page, 12);
        $cart = session()->get('client_cart') ?? [];

        $data['categories'] = $this->productsModel->getCategories();
        $data['products'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['search'] = $search;
        $data['category_filter'] = $catId;
        $data['cart_count'] = array_sum($cart);

        $data['title'] = "Browse Medical Supplies";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "browse";
        return view('pages/client/orders/browse', $data);
    }

    public function add_to_cart()
    {
        $productId = (int) $this->request->getPost('product_id');
        $qty = max(1, (int) $this->request->getPost('qty'));

        $cart = session()->get('client_cart') ?? [];
        $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
        session()->set('client_cart', $cart);

        return redirect()->back()->with('success', 'Added to your order cart.');
    }

    public function get_product_details($id)
{
    $product = $this->productsModel->getProductDetails((int) $id);
    if (!$product) return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
    return $this->response->setJSON($product);
}
}