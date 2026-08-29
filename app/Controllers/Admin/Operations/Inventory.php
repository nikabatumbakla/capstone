<?php

namespace App\Controllers\Admin\Operations;

use App\Controllers\BaseController;

class Inventory extends BaseController
{
    public function stock_management()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();
    $categoryFilter = $request->getGet('category');
    $search = trim((string) $request->getGet('search'));
    $statusFilter = $request->getGet('status'); // 'low_stock' | 'near_expiry' | null

    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $data['categories'] = $db->table('categories')->get()->getResultArray();
    $data['suppliers']  = $db->table('suppliers')->select('supplier_id, name')->where('is_active', 1)->get()->getResultArray();

    $applyFilters = function($builder) use ($categoryFilter, $search, $statusFilter) {
        if ($categoryFilter) $builder->where('p.category_id', $categoryFilter);
        if ($search !== '') {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.sku', $search)
                ->orLike('p.barcode_value', $search)
                ->orLike('p.brand', $search)
            ->groupEnd();
        }
        if ($statusFilter === 'low_stock') {
            $builder->where('ib.batch_id IS NOT NULL', null, false);
            $builder->where('ib.quantity_avail <= ib.reorder_level', null, false);
        } elseif ($statusFilter === 'near_expiry') {
            $builder->where('ib.expires_at IS NOT NULL', null, false);
            $builder->where('ib.expires_at <=', date('Y-m-d', strtotime('+6 months')));
            $builder->where('ib.expires_at >=', date('Y-m-d'));
        }
        return $builder;
    };

    $countBuilder = $db->table('products as p')->where('p.is_active', 1);
    $countBuilder->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left');
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('products as p');
    $builder->select('p.product_id as pid, p.name as product_name, p.sku, p.barcode_value,
                       c.name as category_name,
                       ib.batch_id, ib.batch_number,
                       COALESCE(ib.quantity_avail, 0) as quantity_avail,
                       COALESCE(ib.reorder_level, 0) as reorder_level,
                       ib.expires_at, ib.sell_price');
    $builder->join('categories as c', 'c.category_id = p.category_id');
    $builder->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left');
    $builder->where('p.is_active', 1);
    $applyFilters($builder);
    $builder->orderBy('p.product_id', 'DESC');
    $builder->limit($perPage, $offset);
    $data['inventory'] = $builder->get()->getResultArray();

    $data['total_products'] = $db->table('products')->where('is_active', 1)->countAllResults();
    $data['low_stock']   = $db->table('inventory_batches')->where('quantity_avail <= reorder_level')->countAllResults();
    $data['near_expiry'] = $db->table('inventory_batches')->where('expires_at <=', date('Y-m-d', strtotime('+6 months')))->countAllResults();

    $data['current_page'] = $page;
    $data['per_page']     = $perPage;
    $data['total_rows']   = $totalRows;
    $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));
    $data['category_filter'] = $categoryFilter;
    $data['search'] = $search;
    $data['status_filter'] = $statusFilter;

    $data['title'] = "Stock Management";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "inventory";

    return view('pages/admin/operations/inventory/stock_management', $data);
}

    public function save_product()
{
    $db = \Config\Database::connect();

    $sku     = trim((string) $this->request->getPost('sku'));
    $barcode = trim((string) $this->request->getPost('barcode'));

    $data = [
        'category_id'    => $this->request->getPost('category_id'),
        'supplier_id'    => $this->request->getPost('supplier_id') ?: null,
        'name'           => $this->request->getPost('name'),
        'description'    => $this->request->getPost('description'),
        'sku'            => $sku !== '' ? $sku : null,
        'barcode_value'  => $barcode !== '' ? $barcode : null,
        'barcode_type'   => $this->request->getPost('barcode_type') ?: null,
        'brand'          => $this->request->getPost('brand'),
        'manufacturer'   => $this->request->getPost('manufacturer'),
        'unit'           => $this->request->getPost('unit') ?: 'piece',
        'is_vat_exempt'  => $this->request->getPost('is_vat_exempt') ? 1 : 0,
        'notes'          => $this->request->getPost('notes'),
        'is_active'      => 1
    ];

    try {
        $db->table('products')->insert($data);
    } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
        return redirect()->back()->withInput()->with('error', 'That SKU or barcode already exists on another product.');
    }

    $productId = $db->insertID();

    // Handle product image upload (optional)
    $this->handleImageUpload($productId);

    // Handle educational content (optional)
    $this->saveProductInfoContent($productId);

    return redirect()->to('admin/inventory/stock-management')->with('success', 'New product added successfully!');
}

    public function get_stock_context($product_id)
{
    $db = \Config\Database::connect();

    $product = $db->table('products as p')
        ->select('p.product_id, p.name, p.sku, p.barcode_value, p.unit, c.name as cat_name')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->where('p.product_id', $product_id)
        ->get()->getRow();

    if (!$product) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
    }

    // Most recent batch for this product, if any — shown as reference only
    $lastBatch = $db->table('inventory_batches')
        ->where('product_id', $product_id)
        ->orderBy('received_at', 'DESC')
        ->get()->getRow();

    $product->last_batch = $lastBatch;

    return $this->response->setJSON($product);
}

    public function get_details($batch_id)
    {
        $db = \Config\Database::connect();

        $row = $db->table('inventory_batches as ib')
            ->select('
                ib.batch_id as batch_id,
                ib.batch_number as batch_number,
                ib.quantity_avail as quantity_avail,
                ib.reorder_level as reorder_level,
                ib.sell_price as sell_price,
                ib.expires_at as expires_at,
                p.product_id as product_id,
                p.name as name,
                p.description as description,
                p.sku as sku,
                p.barcode_value as barcode_value,
                p.brand as brand,
                p.manufacturer as manufacturer,
                p.unit as unit,
                p.is_vat_exempt as is_vat_exempt,
                p.notes as notes,
                p.category_id as category_id,
                p.supplier_id as supplier_id,
                c.name as cat_name,
                s.name as supplier_name
            ')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->join('suppliers as s', 's.supplier_id = p.supplier_id', 'left')
            ->where('ib.batch_id', $batch_id)
            ->get()->getRow();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Batch not found']);
        }

        $primaryImage = $db->table('product_images')->where('product_id', $row->product_id)->where('is_primary', 1)->get()->getRow();
    $row->image_path = $primaryImage ? $primaryImage->image_path : null;

    $row->all_categories = $db->table('categories')->select('category_id, name')->get()->getResultArray();
    $row->all_suppliers  = $db->table('suppliers')->select('supplier_id, name')->where('is_active', 1)->get()->getResultArray();

    return $this->response->setJSON($row);
    }

    public function get_education($product_id)
{
    $db = \Config\Database::connect();

    $product = $db->table('products')->where('product_id', $product_id)->get()->getRow();
    if (!$product) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
    }

    $images = $db->table('product_images')->where('product_id', $product_id)->orderBy('sort_order', 'ASC')->get()->getResultArray();
    $content = $db->table('product_info_content')->where('product_id', $product_id)->get()->getRow();

    return $this->response->setJSON([
        'product_id' => $product->product_id,
        'name' => $product->name,
        'sku' => $product->sku,
        'images' => $images,
        'content' => $content
    ]);
}

    public function get_product($id)
{
    $db = \Config\Database::connect();
    $product = $db->table('products')->where('product_id', $id)->get()->getRow();

    if (!$product) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
    }

    $product->all_categories = $db->table('categories')->select('category_id, name')->get()->getResultArray();
    $product->all_suppliers  = $db->table('suppliers')->select('supplier_id, name')->where('is_active', 1)->get()->getResultArray();

    $primaryImage = $db->table('product_images')->where('product_id', $id)->where('is_primary', 1)->get()->getRow();
    $product->image_path = $primaryImage ? $primaryImage->image_path : null;

    $content = $db->table('product_info_content')->where('product_id', $id)->get()->getRow();
    $product->content = $content; // may be null

    return $this->response->setJSON($product);
}

    public function delete_product($product_id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('inventory_batches')->where('product_id', $product_id)->delete();
        $db->table('stock_adjustment_logs')->where('product_id', $product_id)->delete();
        $db->table('supplier_product_catalog')->where('product_id', $product_id)->delete();

        $db->table('products')->where('product_id', $product_id)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('admin/inventory/stock-management')->with('error', 'Could not delete product — it may be linked to existing orders.');
        }

        return redirect()->to('admin/inventory/stock-management')->with('success', 'Product deleted.');
    }

    public function update_product()
    {
        $db = \Config\Database::connect();
        $batch_id = $this->request->getPost('batch_id');
        $data = [
            'quantity_avail' => $this->request->getPost('stock'),
            'sell_price'     => $this->request->getPost('price'),
            'expires_at'     => $this->request->getPost('expiry')
        ];
        $db->table('inventory_batches')->where('batch_id', $batch_id)->update($data);
        return redirect()->to('admin/inventory/stock-management')->with('success', 'Record Updated.');
    }

    public function adjustment_logs()
{
    $db = \Config\Database::connect();
    $session = session();
    $request = \Config\Services::request();

    $search = trim((string) $request->getGet('search'));
    $reasonFilter = $request->getGet('reason');

    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $applyFilters = function($builder) use ($search, $reasonFilter) {
        if ($reasonFilter) $builder->where('sal.reason', $reasonFilter);
        if ($search !== '') {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.sku', $search)
                ->orLike('u.full_name', $search)
            ->groupEnd();
        }
        return $builder;
    };

    $countBuilder = $db->table('stock_adjustment_logs as sal');
    $countBuilder->join('products as p', 'p.product_id = sal.product_id');
    $countBuilder->join('users as u', 'u.user_id = sal.adjusted_by');
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('stock_adjustment_logs as sal');
    $builder->select('sal.log_id, sal.qty_before, sal.qty_after, sal.reason, sal.notes, sal.adjusted_at,
                       p.name as product_name, p.sku,
                       u.full_name as staff_name');
    $builder->join('products as p', 'p.product_id = sal.product_id');
    $builder->join('users as u', 'u.user_id = sal.adjusted_by');
    $applyFilters($builder);
    $builder->orderBy('sal.adjusted_at', 'DESC');
    $builder->limit($perPage, $offset);
    $data['logs'] = $builder->get()->getResultArray();

    $data['total_logs'] = $db->table('stock_adjustment_logs')->countAllResults();
    $data['recent_adjustments'] = $db->table('stock_adjustment_logs')
                                    ->where('adjusted_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                                    ->countAllResults();

    // NEW: this week's count
    $data['week_adjustments'] = $db->table('stock_adjustment_logs')
                                    ->where('adjusted_at >=', date('Y-m-d 00:00:00', strtotime('monday this week')))
                                    ->countAllResults();

    // NEW: flagged as Loss/Theft (risk KPI)
    $data['loss_flagged'] = $db->table('stock_adjustment_logs')
                                ->where('reason', 'Loss')
                                ->countAllResults();

    $data['current_page'] = $page;
    $data['per_page']     = $perPage;
    $data['total_rows']   = $totalRows;
    $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));
    $data['search'] = $search;
    $data['reason_filter'] = $reasonFilter;

    // NEW: store info for the printable voucher header
    $settingsRows = $db->table('store_settings')->get()->getResultArray();
    $storeInfo = [];
    foreach ($settingsRows as $row) {
        $storeInfo[$row['setting_key']] = $row['setting_value'];
    }
    $data['store_info'] = $storeInfo;

    $data['title'] = "Adjustment Logs";
    $data['fullname'] = $session->get('full_name');
    $data['page_name'] = "inventory";

    return view('pages/admin/operations/inventory/adjustment_logs', $data);
}

    public function get_log_details($id)
{
    $db = \Config\Database::connect();
    $log = $db->table('stock_adjustment_logs as sal')
        ->select('
            sal.log_id as log_id,
            sal.qty_before as qty_before,
            sal.qty_after as qty_after,
            sal.reason as reason,
            sal.notes as notes,
            sal.adjusted_at as adjusted_at,
            p.name as product_name,
            p.sku as sku,
            u.full_name as full_name,
            b.batch_number as batch_number
        ')
        ->join('products as p', 'p.product_id = sal.product_id')
        ->join('users as u', 'u.user_id = sal.adjusted_by')
        ->join('inventory_batches as b', 'b.batch_id = sal.batch_id', 'left')
        ->where('sal.log_id', $id)
        ->get()->getRow();

    if (!$log) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Log not found']);
    }

    return $this->response->setJSON($log);
}

    public function adjust_stock()
    {
        $db = \Config\Database::connect();
        $session = session();

        $batch_id   = $this->request->getPost('batch_id');
        $product_id = $this->request->getPost('product_id');
        $qty_before = $this->request->getPost('qty_before');
        $qty_after  = $this->request->getPost('qty_after');
        $reason     = $this->request->getPost('reason');
        $notes      = $this->request->getPost('notes');

        $db->transStart();

        $db->table('inventory_batches')->where('batch_id', $batch_id)->update(['quantity_avail' => $qty_after]);

        $db->table('stock_adjustment_logs')->insert([
            'product_id'  => $product_id,
            'batch_id'    => $batch_id,
            'adjusted_by' => $session->get('user_id'),
            'qty_before'  => $qty_before,
            'qty_after'   => $qty_after,
            'reason'      => $reason,
            'notes'       => $notes
        ]);

        $db->transComplete();

        return redirect()->to('admin/inventory/adjustment-logs')->with('success', 'Adjustment Complete.');
    }

    public function update_product_info()
{
    $db = \Config\Database::connect();
    $product_id = $this->request->getPost('product_id');

    $sku     = trim((string) $this->request->getPost('sku'));
    $barcode = trim((string) $this->request->getPost('barcode'));

    $data = [
        'category_id'    => $this->request->getPost('category_id'),
        'supplier_id'    => $this->request->getPost('supplier_id') ?: null,
        'name'           => $this->request->getPost('name'),
        'description'    => $this->request->getPost('description'),
        'sku'            => $sku !== '' ? $sku : null,
        'barcode_value'  => $barcode !== '' ? $barcode : null,
        'brand'          => $this->request->getPost('brand'),
        'manufacturer'   => $this->request->getPost('manufacturer'),
        'unit'           => $this->request->getPost('unit') ?: 'piece',
        'is_vat_exempt'  => $this->request->getPost('is_vat_exempt') ? 1 : 0,
        'notes'          => $this->request->getPost('notes'),
    ];

    try {
        $db->table('products')->where('product_id', $product_id)->update($data);
    } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
        return redirect()->to('admin/inventory/stock-management')->with('error', 'That SKU or barcode is already used by another product.');
    }

    $this->handleImageUpload($product_id);
    $this->saveProductInfoContent($product_id);

    return redirect()->to('admin/inventory/stock-management')->with('success', 'Product info updated.');
}

    public function create_batch()
{
    $db = \Config\Database::connect();

    $data = [
        'product_id'     => $this->request->getPost('product_id'),
        'supplier_id'    => $this->request->getPost('supplier_id') ?: null,
        'batch_number'   => $this->request->getPost('batch_number'),
        'lot_number'     => $this->request->getPost('lot_number') ?: null,
        'manufactured_at'=> $this->request->getPost('manufactured_at') ?: null,
        'quantity_in'    => $this->request->getPost('quantity'),
        'quantity_avail' => $this->request->getPost('quantity'),
        'reorder_level'  => $this->request->getPost('reorder_level') ?: 5,
        'cost_price'     => $this->request->getPost('cost_price') ?: 0,
        'sell_price'     => $this->request->getPost('sell_price'),
        'expires_at'     => $this->request->getPost('expires_at') ?: null,
    ];

    $db->table('inventory_batches')->insert($data);

    return redirect()->to('admin/inventory/stock-management')->with('success', 'Stock batch added.');
}

// Shared helper: image upload
private function handleImageUpload($productId)
{
    $file = $this->request->getFile('product_image');

    if ($file && $file->isValid() && !$file->hasMoved()) {
        $db = \Config\Database::connect();
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'public/uploads/products', $newName);
        $imagePath = 'public/uploads/products/' . $newName;

        // Unset previous primary image, insert new one as primary
        $db->table('product_images')->where('product_id', $productId)->update(['is_primary' => 0]);
        $db->table('product_images')->insert([
            'product_id' => $productId,
            'image_path' => $imagePath,
            'is_primary' => 1,
            'sort_order' => 0
        ]);
    }
}

private function saveProductInfoContent($productId)
{
    $medical_description = $this->request->getPost('medical_description');
    $usage_purpose        = $this->request->getPost('usage_purpose');
    $usage_guide           = $this->request->getPost('usage_guide');
    $warnings              = $this->request->getPost('warnings');
    $storage_info          = $this->request->getPost('storage_info');
    $healthcare_tips       = $this->request->getPost('healthcare_tips');
    $warranty_info         = $this->request->getPost('warranty_info');
    $video_url             = $this->request->getPost('video_url');

    // Only touch this table if at least one educational field was actually submitted
    $hasAnyContent = array_filter([
        $medical_description, $usage_purpose, $usage_guide, $warnings,
        $storage_info, $healthcare_tips, $warranty_info, $video_url
    ], fn($v) => $v !== null && trim((string) $v) !== '');

    if (empty($hasAnyContent)) return;

    $db = \Config\Database::connect();
    $existing = $db->table('product_info_content')->where('product_id', $productId)->get()->getRow();

    $contentData = [
        'medical_description' => $medical_description,
        'usage_purpose'        => $usage_purpose,
        'usage_guide'          => $usage_guide,
        'warnings'             => $warnings,
        'storage_info'         => $storage_info,
        'healthcare_tips'      => $healthcare_tips,
        'warranty_info'        => $warranty_info,
        'video_url'            => $video_url,
    ];

    if ($existing) {
        $db->table('product_info_content')->where('product_id', $productId)->update($contentData);
    } else {
        $contentData['product_id'] = $productId;
        $db->table('product_info_content')->insert($contentData);
    }
}

}