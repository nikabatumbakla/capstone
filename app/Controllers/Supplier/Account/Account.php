<?php

namespace App\Controllers\Supplier\Account; // Modular Namespace
use App\Controllers\BaseController;

class Account extends BaseController
{
    public function scorecard()
{
    $db = \Config\Database::connect();
    $supplierId = session()->get('supplier_id');

    // 1. Fetch scorecard
    $scorecard = $db->table('supplier_scorecards')->where('supplier_id', $supplierId)->get()->getRow();

    // 2. FIX: If no scorecard exists, create a "Default" object with zero values
    if (!$scorecard) {
        $scorecard = (object)[
            'on_time_rate' => 0,
            'accuracy_rate' => 0,
            'total_orders' => 0,
            'avg_lead_time_actual' => 0
        ];
    }

    $data['scorecard'] = $scorecard;
    $data['po_history'] = $db->table('purchase_orders')->where('supplier_id', $supplierId)->orderBy('created_at','DESC')->limit(10)->get()->getResultArray();
    
    $data['title'] = "Performance Scorecard";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "scorecard";
    
    return view('pages/supplier/account/scorecard', $data);
}

    public function profile()
    {
        $db = \Config\Database::connect();
        $supplierId = session()->get('supplier_id');

        $data['profile'] = $db->table('suppliers')->where('supplier_id', $supplierId)->get()->getRow();
        
        $data['title'] = "Profile Settings";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "profile";
        return view('pages/supplier/account/profile', $data);
    }
}