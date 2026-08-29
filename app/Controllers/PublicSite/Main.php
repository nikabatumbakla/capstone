<?php

namespace App\Controllers\PublicSite;
use App\Controllers\BaseController;

class Main extends BaseController
{
    protected $db;

    public function __construct() {
        $this->db = \Config\Database::connect();
    }

    public function index() {
        // Fetch dynamic categories and featured products for Home
        $data['categories'] = $this->db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
        $data['featured'] = $this->db->table('products as p')
            ->select('p.*, ib.sell_price as price')
            ->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left')
            ->limit(6)->get()->getResultArray();
        
        $data['title'] = 'Robin Rose Trading – Your Ultimate Healthcare Partner';
        $data['active_nav'] = 'home';
        return view('public_site/pages/home', $data);
    }

    public function about() {
        $data['title'] = 'About Us - Robin Rose Trading';
        $data['active_nav'] = 'about';
        return view('public_site/pages/about', $data);
    }

    public function products() {
        $data['categories'] = $this->db->table('categories')->get()->getResultArray();
        $data['products'] = $this->db->table('products as p')
            ->select('p.*, ib.sell_price as price')
            ->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left')
            ->get()->getResultArray();
            
        $data['title'] = 'Product Catalog - Robin Rose Trading';
        $data['active_nav'] = 'products';
        $data['active_cat'] = 'all';
        return view('public_site/pages/products', $data);
    }

    public function services() {
        $data['title'] = 'Services - Robin Rose Trading';
        $data['active_nav'] = 'services';
        return view('public_site/pages/services', $data);
    }

    public function contact() {
        $data['title'] = 'Contact Us - Robin Rose Trading';
        $data['active_nav'] = 'contact';
        return view('public_site/pages/contact', $data);
    }
}