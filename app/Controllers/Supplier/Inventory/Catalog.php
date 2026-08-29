<?php

namespace App\Controllers\Supplier\Inventory; 
use App\Controllers\BaseController;

class Catalog extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $supplierId = session()->get('supplier_id');

        $data['catalog'] = $db->table('supplier_product_catalog as spc')
            ->select('spc.*, p.name as product_name, p.sku as global_sku')
            ->join('products as p', 'p.product_id = spc.product_id')
            ->where('spc.supplier_id', $supplierId)->get()->getResultArray();
        
        $data['all_products'] = $db->table('products')->get()->getResultArray();
        
        $data['title'] = "My Product Catalog";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "catalog";
        
        // Match the sub-folder path
        return view('pages/supplier/inventory/catalog', $data);
    }
}