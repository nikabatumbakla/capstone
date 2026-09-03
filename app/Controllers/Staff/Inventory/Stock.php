<?php

namespace App\Controllers\Staff\Inventory;

use App\Controllers\BaseController;
use App\Models\Staff\Inventory\StockModel;

class Stock extends BaseController
{
    protected $stockModel;

    public function __construct()
    {
        $this->stockModel = new StockModel();
    }

    public function index()
{
    $search = trim((string) ($this->request->getGet('search') ?? ''));
    $catId = $this->request->getGet('category') ?: '';
    $status = $this->request->getGet('status') ?: '';
    $page = (int) ($this->request->getGet('page') ?? 1);

    $result = $this->stockModel->getInventory($search, $catId, $status, $page, 10);
    $kpis = $this->stockModel->getKpis($search, $catId);

    $data['categories'] = $this->stockModel->getCategories();
    $data['inventory'] = $result['data'];
    $data['total_pages'] = $result['total_pages'];
    $data['current_page'] = $page;
    $data['search'] = $search;
    $data['category_filter'] = $catId;
    $data['status_filter'] = $status;

    $data['total_items'] = $kpis['total_items'];
    $data['low_stock'] = $kpis['low_stock'];
    $data['no_stock'] = $kpis['no_stock'];
    $data['has_stock'] = $kpis['has_stock'];
    $data['near_expiry'] = $kpis['near_expiry'];

    $data['title'] = "Inventory Stock View";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "stock";

    return view('pages/staff/inventory/stock_view', $data);
}

    public function get_details($batchId)
    {
        $row = $this->stockModel->getBatchDetails((int) $batchId);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Batch not found']);
        return $this->response->setJSON($row);
    }

    public function adjust_stock()
    {
        $batchId = (int) $this->request->getPost('batch_id');
        $productId = (int) $this->request->getPost('product_id');
        $qtyBefore = (int) $this->request->getPost('qty_before');
        $qtyAfter = (int) $this->request->getPost('qty_after');
        $reason = $this->request->getPost('reason');
        $notes = $this->request->getPost('notes');
        $staffUserId = session()->get('user_id');

        if ($qtyAfter < 0) {
            return redirect()->back()->with('error', 'Quantity cannot be negative.');
        }

        $this->stockModel->adjustStock($batchId, $productId, $qtyBefore, $qtyAfter, $reason, $notes, $staffUserId);

        // Same hook used by admin inventory/sales/POS — a staff-triggered adjustment
        // that drops stock below reorder level should trip auto-reorder too.
        \App\Libraries\AutoReorder::check($productId);

        return redirect()->to('staff/inventory/logs')->with('success', 'Adjustment processed and logged.');
    }

    public function create_batch()
{
    $productId = (int) $this->request->getPost('product_id');
    $batchNumber = trim((string) $this->request->getPost('batch_number'));
    $quantity = (int) $this->request->getPost('quantity');
    $costPrice = (float) $this->request->getPost('cost_price');
    $sellPrice = (float) $this->request->getPost('sell_price');
    $reorderLevel = (int) ($this->request->getPost('reorder_level') ?: 5);
    $expiresAt = $this->request->getPost('expires_at');

    if ($batchNumber === '' || $quantity <= 0 || $sellPrice <= 0) {
        return redirect()->back()->with('error', 'Please provide a batch number, quantity, and sell price.');
    }

    $this->stockModel->createBatch($productId, $batchNumber, $quantity, $costPrice, $sellPrice, $reorderLevel, $expiresAt, session()->get('user_id'));
    return redirect()->to('staff/inventory/stock')->with('success', 'New batch recorded successfully.');
}

public function get_product_info($productId)
{
    $row = $this->stockModel->getProductInfo((int) $productId);
    if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);

    $row->image_path = $this->stockModel->getProductImage((int) $productId);
    return $this->response->setJSON($row);
}

}