<?php
namespace App\Controllers\Staff\Operations;
use App\Controllers\BaseController;

class Pos extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        $data['products'] = $db->table('inventory_batches as ib')
            ->select('ib.*, p.name, p.sku')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('ib.quantity_avail >', 0)->get()->getResultArray();

        $data['title'] = "Counter Terminal (POS)";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "pos";
        return view('pages/staff/operations/pos', $data);
    }
}