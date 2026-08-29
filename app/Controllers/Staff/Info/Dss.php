<?php

namespace App\Controllers\Staff\Info;
use App\Controllers\BaseController;

class Dss extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Fetch products hitting Reorder Point (Priority Tasks for Staff)
        $data['recommendations'] = $db->table('inventory_batches as ib')
            ->select('ib.*, p.name, p.sku, c.name as cat_name')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->where('ib.quantity_avail <= ib.reorder_level')
            ->orderBy('ib.quantity_avail', 'ASC')
            ->get()->getResultArray();

        $data['title'] = "Operational Intelligence (DSS)";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "dss";
        return view('pages/staff/info/dss_view', $data);
    }
}