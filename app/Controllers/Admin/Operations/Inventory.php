<?php

namespace App\Controllers\Admin\Operations;

use App\Controllers\BaseController;
use App\Models\Admin\Operations\Inventory\StockModel;

class Inventory extends BaseController
{
    protected $stockModel;

    public function __construct()
    {
        $this->stockModel = new StockModel();
    }

    public function stock_management()
{
    $request = \Config\Services::request();
    $categoryFilter = $request->getGet('category');
    $search = trim((string) $request->getGet('search'));
    $statusFilter = $request->getGet('status');
    $page = max(1, (int) ($request->getGet('page') ?? 1));
    $perPage = 10;

    $result = $this->stockModel->getInventory($categoryFilter, $search, $statusFilter, $page, $perPage);
    $counts = $this->stockModel->getCounts();

    $data['categories'] = $this->stockModel->getCategories();
    $data['suppliers'] = $this->stockModel->getSuppliers();
    $data['inventory'] = $result['data'];
    $data['total_products'] = $counts['total_products'];
    $data['low_stock'] = $counts['low_stock'];
    $data['near_expiry'] = $counts['near_expiry'];

    $data['current_page'] = $page;
    $data['per_page'] = $perPage;
    $data['total_rows'] = $result['total_rows'];
    $data['total_pages'] = $result['total_pages'];
    $data['category_filter'] = $categoryFilter;
    $data['search'] = $search;
    $data['status_filter'] = $statusFilter;

    $data['title'] = "Stock Management";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "inventory";

    return view('pages/admin/operations/inventory/stock_management', $data);
}

public function stock_table_data()
{
    $request = \Config\Services::request();
    $categoryFilter = $request->getGet('category');
    $search = trim((string) $request->getGet('search'));
    $statusFilter = $request->getGet('status');
    $page = max(1, (int) ($request->getGet('page') ?? 1));
    $perPage = 10;

    $result = $this->stockModel->getInventory($categoryFilter, $search, $statusFilter, $page, $perPage);
    $counts = $this->stockModel->getCounts();

    $rangeStart = $result['total_rows'] > 0 ? (($page - 1) * $perPage) + 1 : 0;
    $rangeEnd = min($page * $perPage, $result['total_rows']);

    $rowsHtml = view('pages/admin/operations/inventory/_stock_rows', ['inventory' => $result['data']]);

    return $this->response->setJSON([
        'rows_html'      => $rowsHtml,
        'total_products' => $counts['total_products'],
        'low_stock'      => $counts['low_stock'],
        'near_expiry'    => $counts['near_expiry'],
        'range_start'    => $rangeStart,
        'range_end'      => $rangeEnd,
        'total_rows'     => $result['total_rows'],
    ]);
}

// NEW
public function get_product_batches($product_id)
{
    $product = $this->stockModel->getProductBatches((int) $product_id);
    if (!$product) return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
    return $this->response->setJSON($product);
}

    public function save_product()
    {
        $post = $this->request->getPost();
        $barcode = trim((string) $this->request->getPost('barcode'));

        $result = $this->stockModel->saveProduct($post, $barcode);
        if (isset($result['error'])) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }
        $productId = $result['product_id'];

        $batchError = $this->stockModel->saveOpeningBatch($productId, $post);
        $this->stockModel->handleImageUpload($this->request->getFile('product_image'), $productId);
        $this->stockModel->saveProductInfoContent($productId, $post);

        if ($batchError) {
            return redirect()->to('admin/inventory/stock-management')->with('error', $batchError);
        }
        return redirect()->to('admin/inventory/stock-management')->with('success', 'New product added successfully!');
    }

    public function get_stock_context($product_id)
    {
        $product = $this->stockModel->getStockContext((int) $product_id);
        if (!$product) return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
        return $this->response->setJSON($product);
    }

    public function get_details($batch_id)
    {
        $row = $this->stockModel->getBatchDetails((int) $batch_id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Batch not found']);
        return $this->response->setJSON($row);
    }

    public function get_education($product_id)
    {
        $data = $this->stockModel->getEducation((int) $product_id);
        if (!$data) return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
        return $this->response->setJSON($data);
    }

    public function get_product($id)
    {
        $product = $this->stockModel->getProduct((int) $id);
        if (!$product) return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
        return $this->response->setJSON($product);
    }

    public function delete_product($product_id)
    {
        $success = $this->stockModel->deleteProduct((int) $product_id);
        return redirect()->to('admin/inventory/stock-management')->with(
            $success ? 'success' : 'error',
            $success ? 'Product deleted.' : 'Could not delete product — it may be linked to existing orders.'
        );
    }

    public function update_product_info()
    {
        $productId = (int) $this->request->getPost('product_id');
        $barcode = trim((string) $this->request->getPost('barcode'));
        $error = $this->stockModel->updateProductInfo($productId, $this->request->getPost(), $barcode);

        if ($error) return redirect()->to('admin/inventory/stock-management')->with('error', $error);

        $this->stockModel->handleImageUpload($this->request->getFile('product_image'), $productId);
        $this->stockModel->saveProductInfoContent($productId, $this->request->getPost());

        return redirect()->to('admin/inventory/stock-management')->with('success', 'Product info updated.');
    }

    public function create_batch()
    {
        $this->stockModel->createBatch($this->request->getPost());
        return redirect()->to('admin/inventory/stock-management')->with('success', 'Stock batch added.');
    }

    public function adjust_stock()
    {
        $post = $this->request->getPost();
        $post['adjusted_by'] = session()->get('user_id');
        $this->stockModel->adjustStock($post);
        \App\Libraries\AutoReorder::check($post['product_id']);
        return redirect()->to('admin/inventory/adjustment-logs')->with('success', 'Adjustment Complete.');
    }

    public function adjustment_logs()
    {
        $request = \Config\Services::request();
        $search = trim((string) $request->getGet('search'));
        $reasonFilter = $request->getGet('reason');
        $page = max(1, (int) ($request->getGet('page') ?? 1));
        $perPage = 10;

        $result = $this->stockModel->getAdjustmentLogs($search, $reasonFilter, $page, $perPage);
        $kpis = $this->stockModel->getAdjustmentKpis();

        $data['logs'] = $result['data'];
        $data['total_logs'] = $kpis['total_logs'];
        $data['recent_adjustments'] = $kpis['recent_adjustments'];
        $data['week_adjustments'] = $kpis['week_adjustments'];
        $data['loss_flagged'] = $kpis['loss_flagged'];

        $data['current_page'] = $page;
        $data['per_page'] = $perPage;
        $data['total_rows'] = $result['total_rows'];
        $data['total_pages'] = $result['total_pages'];
        $data['search'] = $search;
        $data['reason_filter'] = $reasonFilter;
        $data['store_info'] = $this->stockModel->getStoreInfo();

        $data['title'] = "Adjustment Logs";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "inventory";

        return view('pages/admin/operations/inventory/adjustment_logs', $data);
    }

    public function get_log_details($id)
    {
        $log = $this->stockModel->getLogDetails((int) $id);
        if (!$log) return $this->response->setStatusCode(404)->setJSON(['error' => 'Log not found']);
        return $this->response->setJSON($log);
    }

    public function check_stock_updated()
{
    return $this->response->setJSON(['last_updated' => $this->stockModel->getLastStockUpdateTime()]);
}
}