<?php

namespace App\Controllers\Admin\Strategy;
use App\Controllers\BaseController;

class Compliance extends BaseController
{
    public function bir()
    {
        $db = \Config\Database::connect();
        $month = date('m');
        $year = date('Y');

        // 1. COMPUTE OUTPUT VAT (From Walk-in POS + Institutional Sales)
        $pos = $db->table('pos_transactions')->selectSum('vat_amount', 'v')->selectSum('total', 't')->get()->getRow();
        $so = $db->table('sales_orders')->selectSum('vat_amount', 'v')->selectSum('total', 't')->get()->getRow();
        
        $data['total_gross_sales'] = ($pos->t ?? 0) + ($so->t ?? 0);
        $data['output_vat'] = ($pos->v ?? 0) + ($so->v ?? 0);

        // 2. COMPUTE INPUT VAT (From Supplier Purchases)
        // Note: Assumes 12% VAT on all purchases for calculation
        $purchases = $db->table('purchase_orders')->selectSum('total_amount', 't')->where('status', 'received')->get()->getRow();
        $data['total_purchases'] = $purchases->t ?? 0;
        $data['input_vat'] = $data['total_purchases'] * 0.12;

        // 3. NET VAT PAYABLE
        $data['net_vat_payable'] = $data['output_vat'] - $data['input_vat'];

        // 4. CURRENT OR SEQUENCE
        $data['or_control'] = $db->table('bir_or_control')->get()->getRow();

        $data['title'] = "BIR Compliance & Tax";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "compliance";
        return view('pages/admin/strategy/bir_hub', $data);
    }
}