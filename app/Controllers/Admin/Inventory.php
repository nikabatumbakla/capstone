<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Inventory extends BaseController
{
    public function stock_management()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();
    $categoryFilter = $request->getGet('category');
    $search = trim((string) $request->getGet('search'));

    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 15;
    $offset = ($page - 1) * $perPage;

    $data['categories'] = $db->table('categories')->get()->getResultArray();

    $applyFilters = function($builder) use ($categoryFilter, $search) {
        if ($categoryFilter) $builder->where('p.category_id', $categoryFilter);
        if ($search !== '') {
            $builder->groupStart()
                ->like('p.name', $search)
                ->orLike('p.sku', $search)
                ->orLike('p.barcode_value', $search)
                ->orLike('p.brand', $search)
            ->groupEnd();
        }
        return $builder;
    };

    $countBuilder = $db->table('products as p')->where('p.is_active', 1);
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

    $data['title'] = "Stock Management";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "inventory";

    return view('pages/admin/inventory/stock_management', $data);
}


    // 5. 1-Form-1-Process: Save Product
    public function save_product()
    {
        $db = \Config\Database::connect();
        $data = [
            'category_id'   => $this->request->getPost('category_id'),
            'name'          => $this->request->getPost('name'),
            'sku'           => $this->request->getPost('sku'),
            'barcode_value' => $this->request->getPost('barcode'),
            'unit'          => $this->request->getPost('unit'),
            'is_active'     => 1
        ];

        $db->table('products')->insert($data);
        return redirect()->back()->with('success', 'New product added successfully!');
    }

    // 6. AJAX: Get Product Details for Slide-out
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

    // attach dropdown source lists so Edit form works from this same payload
    $row->all_categories = $db->table('categories')->select('category_id, name')->get()->getResultArray();
    $row->all_suppliers  = $db->table('suppliers')->select('supplier_id, name')->where('is_active', 1)->get()->getResultArray();

    return $this->response->setJSON($row);
}

public function get_product($id)
{
    $db = \Config\Database::connect();
    $product = $db->table('products')->where('product_id', $id)->get()->getRow();
    $product->suppliers = $db->table('suppliers')->select('supplier_id, name')->where('is_active', 1)->get()->getResultArray();
    return $this->response->setJSON($product);
}

    // FUNCTIONAL DELETE
    public function delete_product($product_id)
{
    $db = \Config\Database::connect();
    $db->transStart();

    // Clear dependent rows that don't cascade automatically
    $db->table('inventory_batches')->where('product_id', $product_id)->delete();
    $db->table('stock_adjustment_logs')->where('product_id', $product_id)->delete();
    $db->table('supplier_product_catalog')->where('product_id', $product_id)->delete();

    // product_images and product_info_content already ON DELETE CASCADE, no need to touch

    $db->table('products')->where('product_id', $product_id)->delete();

    $db->transComplete();

    if ($db->transStatus() === false) {
        return redirect()->back()->with('error', 'Could not delete product — it may be linked to existing orders.');
    }

    return redirect()->to('admin/inventory/stock-management')->with('success', 'Product deleted.');
}

    // FUNCTIONAL EDIT
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
        return redirect()->back()->with('success', 'Record Updated.');
    }

    public function adjustment_logs()
    {
        $db = \Config\Database::connect();
        $session = session();

        // 1. Fetch Logs with Product and User details
        $builder = $db->table('stock_adjustment_logs as sal');
        $builder->select('sal.*, p.name as product_name, p.sku, u.full_name as staff_name');
        $builder->join('products as p', 'p.product_id = sal.product_id');
        $builder->join('users as u', 'u.user_id = sal.adjusted_by');
        $builder->orderBy('sal.adjusted_at', 'DESC');
        
        $data['logs'] = $builder->get()->getResultArray();

        // 2. Stats for Logs Banner
        $data['total_logs'] = $db->table('stock_adjustment_logs')->countAllResults();
        $data['recent_adjustments'] = $db->table('stock_adjustment_logs')
                                        ->where('adjusted_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                                        ->countAllResults();

        $data['title'] = "Adjustment Logs";
        $data['fullname'] = $session->get('full_name');
        $data['page_name'] = "inventory"; // Keeps inventory dropdown open

        return view('pages/admin/inventory/adjustment_logs', $data);
    }

    // AJAX: Get Log details for Drawer
    public function get_log_details($id)
    {
        $db = \Config\Database::connect();
        $log = $db->table('stock_adjustment_logs as sal')
            ->select('sal.*, p.name, p.sku, u.full_name, b.batch_number')
            ->join('products as p', 'p.product_id = sal.product_id')
            ->join('users as u', 'u.user_id = sal.adjusted_by')
            ->join('inventory_batches as b', 'b.batch_id = sal.batch_id', 'left')
            ->where('sal.log_id', $id)
            ->get()->getRow();

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

    // Update current stock
    $db->table('inventory_batches')->where('batch_id', $batch_id)->update(['quantity_avail' => $qty_after]);

    // Create Audit Log entry (Reason and Notes are saved here)
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

    return redirect()->to('admin/inventory/stock-management')->with('success', 'Adjustment Complete.');
}

// EDIT PRODUCT INFO (name, category, sku, barcode, unit)
public function update_product_info()
{
    $db = \Config\Database::connect();
    $product_id = $this->request->getPost('product_id');

    $sku     = trim($this->request->getPost('sku'));
    $barcode = trim($this->request->getPost('barcode'));

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
        return redirect()->back()->with('error', 'That SKU or barcode is already used by another product.');
    }

    return redirect()->to('admin/inventory/stock-management')->with('success', 'Product info updated.');
}
public function create_batch()
{
    $db = \Config\Database::connect();

    $data = [
        'product_id'     => $this->request->getPost('product_id'),
        'batch_number'   => $this->request->getPost('batch_number'),
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


}
