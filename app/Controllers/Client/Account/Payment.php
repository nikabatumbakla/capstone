<?php
namespace App\Controllers\Client\Account;
use App\Controllers\BaseController;

class Payment extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        $clientId = session()->get('client_id');
        
        // Fetch Outstanding Invoices (Unpaid or Partial)
        $data['unpaid_invoices'] = $db->table('sales_orders')
            ->where('client_id', $clientId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->get()->getResultArray();

        $data['total_outstanding'] = $db->table('institutional_clients')->where('client_id', $clientId)->get()->getRow()->credit_used;
        $data['title'] = "Settle Payment";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "payment";
        return view('pages/client/account/payment', $data);
    }
    
}