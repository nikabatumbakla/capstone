<?php

namespace App\Controllers\Partners;
use App\Controllers\BaseController;

class Register extends BaseController
{
    protected $db;
    public function __construct() { $this->db = \Config\Database::connect(); }

    // STEP 1: Show Organization View (GET)
    public function client_index() {
        $data = ['title' => 'Client Registration | Step 1', 'pgSubtitle' => 'Robin Rose Trading · New Client Registration'];
        return view('auth/partners/client_register_step1', $data);
    }

    // STEP 2: Receive Step 1 Data -> Show Contact View (POST)
    public function client_step2() {
        session()->set('temp_client_data', $this->request->getPost()); // Store Org Info
        $data = ['title' => 'Client Registration | Step 2', 'pgSubtitle' => 'Robin Rose Trading · Contact Details'];
        return view('auth/partners/client_register_step2', $data);
    }

    // STEP 3: Receive Step 2 Data -> Show Account View (POST)
    public function client_step3()
    {
        $old_data = session()->get('temp_client_data');
        session()->set('temp_client_data', array_merge($old_data, $this->request->getPost()));

        $data = [
            'title'      => 'Account Setup | PharMediSync',
            'pgSubtitle' => 'Robin Rose Trading · New Client Registration',
            'active_tab' => 'register',
            'step'       => 3
        ];
        
        return view('auth/partners/client_register_step3', $data);
    }

    // STEP 4: Final Database Save (POST)
    public function client_submit()
    {
        $all_data = array_merge(session()->get('temp_client_data'), $this->request->getPost());

        $this->db->transStart();

        // 1. Create User Account (is_active = 0 for Admin Approval)
        $userData = [
            'full_name'     => $all_data['contact_person'],
            'email'         => $all_data['email'],
            'password_hash' => password_hash($all_data['password'], PASSWORD_DEFAULT),
            'role'          => 'institutional_client',
            'is_active'     => 0, 
            'created_at'    => date('Y-m-d H:i:s')
        ];
        $this->db->table('users')->insert($userData);
        $user_id = $this->db->insertID();

        // 2. Create Institutional Client Profile
        $profileData = [
            'user_id'      => $user_id,
            'organization' => $all_data['organization_name'],
            'client_type'  => $all_data['organization_type'],
            'tin'          => $all_data['tin'],
            'address'      => $all_data['complete_address'],
            'phone'        => $all_data['phone'],
            'created_at'   => date('Y-m-d H:i:s')
        ];
        $this->db->table('institutional_clients')->insert($profileData);

        $this->db->transComplete();

        // 3. Clear Session and show Done screen
        session()->remove('temp_client_data');
        $reference = 'APP-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(2)));

return view('auth/partners/register_done', [
    'reference' => $reference
]);
    }

    public function supplier_index() {
        return view('auth/partners/supplier_register_step1');
    }

    public function supplier_step2() {
        session()->set('temp_sup_data', $this->request->getPost());
        return view('auth/partners/supplier_register_step2');
    }

    public function supplier_step3() {
        $old = session()->get('temp_sup_data');
        session()->set('temp_sup_data', array_merge($old, $this->request->getPost()));
        return view('auth/partners/supplier_register_step3');
    }


public function supplier_submit()
{
    $all_data = array_merge(session()->get('temp_sup_data'), $this->request->getPost());

    $this->db->transStart();

    // 1. Create User Account (is_active = 0 for Review)
    $userData = [
        'full_name'     => $all_data['contact_person'],
        'email'         => $all_data['email'],
        'password_hash' => password_hash($all_data['password'], PASSWORD_DEFAULT),
        'role'          => 'supplier',
        'is_active'     => 0, 
        'created_at'    => date('Y-m-d H:i:s')
    ];
    $this->db->table('users')->insert($userData);
    $user_id = $this->db->insertID();

    // 2. Create Supplier Profile
    $this->db->table('suppliers')->insert([
        'user_id'        => $user_id,
        'name'           => $all_data['supplier_name'],
        'contact_person' => $all_data['contact_person'],
        'phone'          => $all_data['phone'],
        'email'          => $all_data['biz_email'],
        'address'        => $all_data['address'],
        'is_active'      => 1
    ]);

    $this->db->transComplete();

    // 3. Generate Figma Reference (SUP-YYYY-RAND)
    $reference = 'SUP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    session()->remove('temp_sup_data');
    return view('auth/partners/supplier_register_done', ['reference' => $reference]);
}

}