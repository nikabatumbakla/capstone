<?php
namespace App\Controllers\Staff\Operations;
use App\Controllers\BaseController;

class GoodsReceipt extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        $data['deliveries'] = $db->table('purchase_orders as po')
            ->select('po.*, s.name as supplier')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id')
            ->where('po.status', 'sent')->get()->getResultArray();

        $data['title'] = "Inbound Verification (GRR)";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "grr";
        return view('pages/staff/operations/goods_receipt', $data);
    }
}