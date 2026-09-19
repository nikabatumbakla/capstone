<?php

namespace App\Controllers\Partners;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Register extends BaseController
{
    // ===== CLIENT FLOW =====

    public function client_index()
{
    session()->remove('reg_client');
    return view('auth/partners/client_register_step1');
}

public function client_save_step1()
{
    $data = [
        'organization_name' => $this->request->getPost('organization_name'),
        'organization_type' => $this->request->getPost('organization_type'),
        'complete_address'   => $this->request->getPost('complete_address'),
    ];
    if (empty($data['organization_name']) || empty($data['organization_type']) || empty($data['complete_address'])) {
        return redirect()->to('partner-gateway/register/client')->withInput()->with('error', 'Please complete all required fields.');
    }

    session()->set('reg_client', $data);
    return redirect()->to('register/client/step2');
}

public function client_step2_view()
{
    if (empty(session()->get('reg_client'))) return redirect()->to('partner-gateway/register/client');
    return view('auth/partners/client_register_step2');
}

public function client_save_step2()
{
    $existing = session()->get('reg_client') ?? [];
    if (empty($existing)) return redirect()->to('partner-gateway/register/client');

    $data = array_merge($existing, [
        'contact_person'    => $this->request->getPost('contact_person'),
        'position'          => $this->request->getPost('position'),
        'phone'             => $this->request->getPost('phone'),
        'alt_phone'         => $this->request->getPost('alt_phone'),
        'official_email'    => $this->request->getPost('official_email'),
        'delivery_address'  => $this->request->getPost('delivery_address'),
    ]);
    if (empty($data['contact_person']) || empty($data['phone']) || empty($data['official_email'])) {
        return redirect()->to('register/client/step2')->withInput()->with('error', 'Please complete all required fields.');
    }

    session()->set('reg_client', $data);
    return redirect()->to('register/client/step3');
}

public function client_step3_view()
{
    if (empty(session()->get('reg_client'))) return redirect()->to('partner-gateway/register/client');
    return view('auth/partners/client_register_step3');
}

public function client_submit()
{
    $data = session()->get('reg_client') ?? [];
    if (empty($data)) return redirect()->to('partner-gateway/register/client');

    $email = $this->request->getPost('email');
    $password = $this->request->getPost('password');
    $passwordConfirm = $this->request->getPost('password_confirm');

    if (!$this->request->getPost('agree_terms')) {
        return redirect()->to('register/client/step3')->withInput()->with('error', 'You must agree to the Terms & Conditions and Privacy Policy to continue.');
    }
    if ($password !== $passwordConfirm) {
        return redirect()->to('register/client/step3')->withInput()->with('error', 'Passwords do not match.');
    }
    if (strlen($password) < 8) {
        return redirect()->to('register/client/step3')->withInput()->with('error', 'Password must be at least 8 characters.');
    }

    $userModel = new UserModel();
    if ($userModel->where('email', $email)->first()) {
        return redirect()->to('register/client/step3')->withInput()->with('error', 'That email is already registered.');
    }

    $db = \Config\Database::connect();
    $db->transStart();

    $userId = $userModel->insert([
        'full_name'     => $data['contact_person'],
        'email'         => $email,
        'phone'         => $data['phone'],
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role'          => 'institutional_client',
        'is_active'     => 0,
        'is_verified'   => 0,
    ], true);

    $permitPath = null;
    $file = $this->request->getFile('permit');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'public/uploads/permits', $newName);
        $permitPath = 'public/uploads/permits/' . $newName;
    }

    $db->table('institutional_clients')->insert([
    'user_id'          => $userId,
    'organization'     => $data['organization_name'],
    'client_type'      => $data['organization_type'],
    'address'          => $data['complete_address'],
    'contact_person'   => $data['contact_person'],
    'position'         => $data['position'] ?: null,
    'phone'            => $data['phone'],
    'alt_phone'        => $data['alt_phone'] ?: null,
    'email'            => $data['official_email'],
    'delivery_address' => $data['delivery_address'] ?: null,
    'permit_path'      => $permitPath,
    'is_active'        => 1,
]);

    \App\Libraries\TermsAgreementService::recordAgreement($userId, $this->request->getIPAddress(), (string) $this->request->getUserAgent());

    $reference = 'CLI-' . date('Y') . '-' . str_pad($userId, 5, '0', STR_PAD_LEFT);
    $db->table('institutional_clients')->where('user_id', $userId)->update(['registration_ref' => $reference]);

    $db->transComplete();
    session()->remove('reg_client');

    \App\Libraries\AdminNotifier::newRegistration('Institutional Client', $data['organization_name'], $reference);

    return view('auth/partners/register_done', ['role' => 'Institutional Client', 'reference' => $reference]);
}


    // ===== SUPPLIER FLOW (same pattern) =====

    public function supplier_index()
{
    session()->remove('reg_supplier');
    return view('auth/partners/supplier_register_step1');
}

public function supplier_save_step1()
{
    $data = [
    'supplier_name'   => $this->request->getPost('supplier_name'),
    'contact_person'  => $this->request->getPost('contact_person'),
    'position'        => $this->request->getPost('position'),
    'address'         => $this->request->getPost('address'),
    'tin'             => $this->request->getPost('tin'),
    'phone'           => $this->request->getPost('phone'),
    'biz_email'       => $this->request->getPost('biz_email'),
];
    if (empty($data['supplier_name']) || empty($data['contact_person']) || empty($data['address']) || empty($data['phone']) || empty($data['biz_email'])) {
        return redirect()->to('partner-gateway/register/supplier')->withInput()->with('error', 'Please complete all required fields.');
    }

    session()->set('reg_supplier', $data);
    return redirect()->to('register/supplier/step2'); // real GET URL, address bar now correct
}

public function supplier_step2_view()
{
    if (empty(session()->get('reg_supplier'))) return redirect()->to('partner-gateway/register/supplier');

    $db = \Config\Database::connect();
    $viewData['categories'] = $db->table('categories')->orderBy('name', 'ASC')->get()->getResultArray();
    return view('auth/partners/supplier_register_step2', $viewData);
}

public function supplier_save_step2()
{
    $existing = session()->get('reg_supplier') ?? [];
    if (empty($existing)) return redirect()->to('partner-gateway/register/supplier');

    $file = $this->request->getFile('permit');
    $permitPath = $existing['permit_path'] ?? null;
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'public/uploads/permits', $newName);
        $permitPath = 'public/uploads/permits/' . $newName;
    }

    $existingCategoryIds = $this->request->getPost('existing_categories') ?: [];

    if (empty($existingCategoryIds)) {
        return redirect()->to('register/supplier/step2')->withInput()->with('error', 'Please select at least one product category.');
    }

    $data = array_merge($existing, [
    'existing_category_ids' => $existingCategoryIds,
    'lead_time_days'        => $this->request->getPost('lead_time') ?: 7,
    'payment_terms'         => $this->request->getPost('payment_terms'),
    'bank_name'              => $this->request->getPost('bank_name'),
    'bank_account_name'      => $this->request->getPost('bank_account_name'),
    'bank_account_number'    => $this->request->getPost('bank_account_number'),
    'permit_path'           => $permitPath,
]);

    session()->set('reg_supplier', $data);
    return redirect()->to('register/supplier/step3');
}

public function supplier_step3_view()
{
    if (empty(session()->get('reg_supplier'))) return redirect()->to('partner-gateway/register/supplier');
    return view('auth/partners/supplier_register_step3');
}

public function supplier_submit()
{
    $data = session()->get('reg_supplier') ?? [];
    if (empty($data)) return redirect()->to('partner-gateway/register/supplier');

    $email = $this->request->getPost('email');
    $password = $this->request->getPost('password');
    $passwordConfirm = $this->request->getPost('password_confirm');

    if (!$this->request->getPost('agree_terms')) {
        return redirect()->to('register/supplier/step3')->withInput()->with('error', 'You must agree to the Terms & Conditions and Privacy Policy to continue.');
    }
    if ($password !== $passwordConfirm) {
        return redirect()->to('register/supplier/step3')->withInput()->with('error', 'Passwords do not match.');
    }
    if (strlen($password) < 8) {
        return redirect()->to('register/supplier/step3')->withInput()->with('error', 'Password must be at least 8 characters.');
    }

    $userModel = new UserModel();
    if ($userModel->where('email', $email)->first()) {
        return redirect()->to('register/supplier/step3')->withInput()->with('error', 'That email is already registered.');
    }

    $db = \Config\Database::connect();
    $db->transStart();

    $userId = $userModel->insert([
        'full_name'     => $data['contact_person'],
        'email'         => $email,
        'phone'         => $data['phone'],
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role'          => 'supplier',
        'is_active'     => 0,
        'is_verified'   => 0,
    ], true);

    $reference = 'SUP-' . date('Y') . '-' . str_pad($userId, 5, '0', STR_PAD_LEFT);

    $db->table('suppliers')->insert([
    'user_id'              => $userId,
    'name'                 => $data['supplier_name'],
    'contact_person'       => $data['contact_person'],
    'phone'                => $data['phone'],
    'email'                => $data['biz_email'],
    'address'              => $data['address'],
    'tin'                  => $data['tin'] ?: null,
    'lead_time_days'       => $data['lead_time_days'],
    'payment_terms'        => $data['payment_terms'] ?: null,
    'bank_name'            => $data['bank_name'] ?: null,
    'bank_account_name'    => $data['bank_account_name'] ?: null,
    'bank_account_number'  => $data['bank_account_number'] ?: null,
    'permit_path'          => $data['permit_path'],
    'registration_ref'     => $reference,
    'is_active'            => 1,
]);
$supplierId = $db->insertID();

    foreach ($data['existing_category_ids'] as $categoryId) {
        $db->table('supplier_categories')->insert([
            'supplier_id' => $supplierId,
            'category_id' => (int) $categoryId,
        ]);
    }

    \App\Libraries\TermsAgreementService::recordAgreement($userId, $this->request->getIPAddress(), (string) $this->request->getUserAgent());

    $db->transComplete();

    session()->remove('reg_supplier');

    \App\Libraries\AdminNotifier::newRegistration('Supplier', $data['supplier_name'], $reference);
    return view('auth/partners/register_done', ['role' => 'Supplier', 'reference' => $reference]);
}
}