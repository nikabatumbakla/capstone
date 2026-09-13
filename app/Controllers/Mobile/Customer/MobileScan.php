<?php

namespace App\Controllers\Mobile\Customer;

use App\Controllers\BaseController;
use App\Models\Mobile\CustomerMobileModel;

class MobileScan extends BaseController
{
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
    $model = new CustomerMobileModel();
    $product = $model->findByBarcode($barcode);

    if (!$product) return $this->response->setJSON(['found' => false]);
    return $this->response->setJSON(['found' => true, 'product_id' => $product->product_id, 'product_name' => $product->name]);
}

public function findByBarcode(string $barcode)
{
    return $this->db->table('products as p')
        ->select("p.product_id")
        ->where('p.barcode_value', $barcode)
        ->where('p.is_active', 1)
        ->get()->getRow();
}

}