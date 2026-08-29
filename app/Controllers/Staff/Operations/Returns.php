<?php
namespace App\Controllers\Staff\Operations;
use App\Controllers\BaseController;

class Returns extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        $data['returns'] = $db->table('sales_returns as sr')
            ->select('sr.*, so.order_number, ic.organization')
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->orderBy('sr.created_at', 'DESC')->get()->getResultArray();

        $data['title'] = "Returns Intelligence";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "returns";
        return view('pages/staff/operations/sales_returns', $data);
    }
}