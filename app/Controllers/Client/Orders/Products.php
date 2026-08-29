<?php

namespace App\Controllers\Client\Orders;
use App\Controllers\BaseController;

class Products extends BaseController
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

        // 3. Build Product Query (Joining with Batches for real-time prices)
        $builder = $db->table('products as p');
        $builder->select('p.*, c.name as category_name, ib.sell_price, ib.quantity_avail');
        $builder->join('categories as c', 'c.category_id = p.category_id');
        // Join with the latest batch to show price
        $builder->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left');
        
        if ($search) $builder->like('p.name', $search)->orLike('p.sku', $search);
        if ($catID)  $builder->where('p.category_id', $catID);

        $data['products'] = $builder->where('p.is_active', 1)->groupBy('p.product_id')->get()->getResultArray();

        $data['title'] = "Browse Medical Supplies";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "browse"; // For sidebar highlight

        return view('pages/client/orders/browse', $data);
    }
}