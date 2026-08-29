<?php
namespace App\Controllers\Client\Account;
use App\Controllers\BaseController;

class Invoices extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        $clientId = session()->get('client_id');

        // Fetch orders to display as Invoices
        $data['invoices'] = $db->table('sales_orders')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $data['title'] = "My Invoices";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "invoices";
        return view('pages/client/account/invoices', $data);
    }
}