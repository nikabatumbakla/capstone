<?php

namespace App\Controllers\Staff\Inventory;
use App\Controllers\BaseController;

class Logs extends BaseController
{

    public function logs()
{
    $db = \Config\Database::connect();
    $session = session();

    // 1. Fetch ALL Adjustment Logs from Database (Joining products for names)
    $builder = $db->table('stock_adjustment_logs as sal');
    $builder->select('sal.*, p.name as product_name, u.full_name as staff_name');
    $builder->join('products as p', 'p.product_id = sal.product_id');
    $builder->join('users as u', 'u.user_id = sal.adjusted_by');
    $builder->orderBy('sal.adjusted_at', 'DESC');
    
    $data['logs'] = $builder->get()->getResultArray();

    // 2. Fetch Active Product Batches for the "New Adjustment" Drawer
    $data['available_stocks'] = $db->table('inventory_batches as ib')
        ->select('ib.batch_id, ib.batch_number, p.name, ib.quantity_avail')
        ->join('products as p', 'p.product_id = ib.product_id')
        ->where('ib.quantity_avail >', 0)
        ->get()->getResultArray();

    $data['title'] = "Stock Adjustment History";
    $data['fullname'] = $session->get('full_name');
    $data['page_name'] = "logs";

    return view('pages/staff/inventory/logs', $data);
}

}