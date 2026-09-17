<?php

namespace App\Controllers\Mobile\Customer;

use App\Controllers\BaseController;
use App\Models\Mobile\CustomerMobileModel;

class MobileScan extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new CustomerMobileModel();
    }

    public function index()
    {
        if (session()->get('role') !== 'customer') return redirect()->to('m/');
        $data['title'] = 'Scan Product';
        $data['page_name'] = 'scan';
        return view('mobile/customer/scan', $data);
    }

    public function lookup()
    {
        $barcode = trim((string) $this->request->getPost('barcode'));
        $product = $this->model->findByBarcode($barcode);

        if (!$product) return $this->response->setJSON(['found' => false]);
        return $this->response->setJSON(['found' => true, 'product_id' => $product->product_id, 'product_name' => $product->name]);
    }
}