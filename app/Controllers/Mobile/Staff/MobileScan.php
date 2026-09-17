<?php

namespace App\Controllers\Mobile\Staff;

use App\Controllers\BaseController;
use App\Models\Mobile\StaffScanModel;

class MobileScan extends BaseController
{
    protected $scanModel;

    public function __construct()
    {
        $this->scanModel = new StaffScanModel();
    }

    public function index()
{
    $data['suppliers'] = $this->scanModel->getSuppliers();
    $data['categories'] = $this->scanModel->getCategories();
    $data['open_pos'] = $this->scanModel->getOpenPurchaseOrders();
    $data['open_sos'] = $this->scanModel->getOpenSalesOrders();
    $data['title'] = 'iScan';
    $data['page_name'] = 'scan';
    return view('mobile/staff/scan', $data);
}

    public function get_supplier_products($supplierId)
    {
        return $this->response->setJSON($this->scanModel->getSupplierProducts((int) $supplierId));
    }

    public function get_po_items($poId)
    {
        return $this->response->setJSON($this->scanModel->getPoItems((int) $poId));
    }

    public function lookup()
    {
        $barcode = trim((string) $this->request->getPost('barcode'));
        $product = $this->scanModel->findByBarcode($barcode);

        if (!$product) {
            return $this->response->setJSON(['found' => false]);
        }
        return $this->response->setJSON(['found' => true, 'product' => $product]);
    }

    public function create_product()
    {
        $result = $this->scanModel->createProductWithBarcode(
            $this->request->getPost('name'),
            (int) $this->request->getPost('category_id'),
            $this->request->getPost('unit') ?: 'piece'
        );
        return $this->response->setJSON($result);
    }

    public function submit_inbound()
    {
        $this->scanModel->processInbound(
            (int) $this->request->getPost('product_id'),
            (int) $this->request->getPost('qty'),
            $this->request->getPost('supplier_id') ?: null,
            $this->request->getPost('batch_number'),
            $this->request->getPost('expires_at'),
            session()->get('user_id')
        );
        return redirect()->to('m/staff/scan')->with('success', 'Stock added successfully.');
    }

    public function submit_outbound()
{
    $result = $this->scanModel->processOutboundScan(
        (int) $this->request->getPost('order_id'),
        (int) $this->request->getPost('product_id'),
        (int) $this->request->getPost('qty'),
        session()->get('user_id')
    );
    return $this->response->setJSON($result);
}

  public function submit_grr()
{
    $result = $this->scanModel->processGrrScan(
        (int) $this->request->getPost('po_id'),
        (int) $this->request->getPost('product_id'),
        (int) $this->request->getPost('qty'),
        $this->request->getPost('condition') ?: 'good',
        (int) ($this->request->getPost('qty_rejected') ?? 0),
        trim((string) $this->request->getPost('notes')) ?: null,
        session()->get('user_id')
    );
    return $this->response->setJSON($result);
}

    public function get_so_items($orderId)
{
    return $this->response->setJSON($this->scanModel->getSoItems((int) $orderId));
}

public function update_so_status()
{
    $result = $this->scanModel->updateSoStatus(
        (int) $this->request->getPost('order_id'),
        session()->get('user_id')
    );
    return $this->response->setJSON($result);
}


}