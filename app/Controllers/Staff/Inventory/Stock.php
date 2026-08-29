<?php

namespace App\Controllers\Staff\Inventory;
use App\Controllers\BaseController;

class Stock extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();

        // 1. Capture Filters
        $search = $request->getGet('search');
        $catID  = $request->getGet('category');

        // 2. Fetch Categories for Filter
        $data['categories'] = $db->table('categories')->get()->getResultArray();

        // 3. Build Inventory Query
        $builder = $db->table('inventory_batches as ib');
        $builder->select('ib.*, p.name as product_name, p.sku, p.barcode_value, p.unit, c.name as cat_name');
        $builder->join('products as p', 'p.product_id = ib.product_id');
        $builder->join('categories as c', 'c.category_id = p.category_id');

        if ($search) $builder->like('p.name', $search)->orLike('p.sku', $search);
        if ($catID) $builder->where('p.category_id', $catID);

        $data['inventory'] = $builder->orderBy('ib.quantity_avail', 'ASC')->get()->getResultArray();

        // 4. KPI Aggregations
        $data['total_items'] = $db->table('products')->countAllResults();
        $data['low_stock'] = $db->table('inventory_batches')->where('quantity_avail <= reorder_level')->countAllResults();
        $data['near_expiry'] = $db->table('inventory_batches')->where('expires_at <=', date('Y-m-d', strtotime('+6 months')))->countAllResults();

        $data['title'] = "Inventory Stock View";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "stock"; // Matches sidebar highlight logic

        return view('pages/staff/inventory/stock_view', $data);
    }

    public function adjust_stock()
    {
        $db = \Config\Database::connect();
        $session = session();

        $batch_id = $this->request->getPost('batch_id');
        $qty_after = $this->request->getPost('qty_after');

        $db->transStart();

        // 1. Update Inventory
        $db->table('inventory_batches')->where('batch_id', $batch_id)->update(['quantity_avail' => $qty_after]);

        // 2. Create Audit Log
        $db->table('stock_adjustment_logs')->insert([
            'product_id'  => $this->request->getPost('product_id'), 
            'batch_id'    => $batch_id,
            'adjusted_by' => $session->get('user_id'),
            'qty_before'  => $this->request->getPost('qty_before'),
            'qty_after'   => $qty_after,
            'reason'      => $this->request->getPost('reason'),
            'notes'       => $this->request->getPost('notes'),
            'adjusted_at' => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        return redirect()->to('staff/inventory/stock')->with('success', 'Adjustment Processed.');
    }
}