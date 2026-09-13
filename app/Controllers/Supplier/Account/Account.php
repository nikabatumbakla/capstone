<?php

namespace App\Controllers\Supplier\Account;

use App\Controllers\BaseController;
use App\Models\Supplier\AccountModel;

class Account extends BaseController
{
    protected $accountModel;

    public function __construct()
    {
        $this->accountModel = new AccountModel();
    }

    public function scorecard()
{
    $supplierId = session()->get('supplier_id');

    $data['scorecard']  = $this->accountModel->getScorecard($supplierId);
    $data['po_history'] = $this->accountModel->getPoHistory($supplierId, 15);

    $data['title']     = "My Scorecard";   // ← this line specifically
    $data['fullname']  = session()->get('full_name');
    $data['page_name'] = "scorecard";

    return view('pages/supplier/account/scorecard', $data);
}

    public function profile()
    {
        $supplierId = session()->get('supplier_id');

        $data['profile']             = $this->accountModel->getProfile($supplierId);
        $data['all_categories']      = $this->accountModel->getAllCategories();
        $data['selected_categories'] = $this->accountModel->getSupplierCategories($supplierId);
        $data['scorecard']           = $this->accountModel->getScorecard($supplierId);
        $data['kpis']                = $this->accountModel->getKpis($supplierId);
        $data['title']               = "My Profile";
        $data['fullname']            = session()->get('full_name');
        $data['page_name']           = "profile";

        return view('pages/supplier/account/profile', $data);
    }

    public function update()
    {
        $supplierId = session()->get('supplier_id');
        $userId = session()->get('user_id');
        $email = $this->request->getPost('email');

        if ($this->accountModel->emailExists($email, $userId)) {
            return redirect()->back()->withInput()->with('error', 'That email is already in use by another account.');
        }

        $supplierPayload = [
    'contact_person'       => $this->request->getPost('contact_person'),
    'phone'                => $this->request->getPost('phone'),
    'address'              => $this->request->getPost('address'),
    'tin'                  => $this->request->getPost('tin'),
    'payment_terms'        => $this->request->getPost('payment_terms'),
    'lead_time_days'       => $this->request->getPost('lead_time_days') ?: 7,
    'bank_name'            => $this->request->getPost('bank_name'),
    'bank_account_name'    => $this->request->getPost('bank_account_name'),
    'bank_account_number'  => $this->request->getPost('bank_account_number'),
];

        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $newName = $avatar->getRandomName();
            $avatar->move(FCPATH . 'public/uploads/avatars', $newName);
            $supplierPayload['avatar_path'] = 'public/uploads/avatars/' . $newName;
        }

        $permit = $this->request->getFile('permit');
        if ($permit && $permit->isValid() && !$permit->hasMoved()) {
            $newName = $permit->getRandomName();
            $permit->move(FCPATH . 'public/uploads/permits', $newName);
            $supplierPayload['permit_path'] = 'public/uploads/permits/' . $newName;
        }

        $userPayload = ['email' => $email];

        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');
        if (!empty($password)) {
            if ($password !== $confirmPassword) {
                return redirect()->back()->withInput()->with('error', 'Passwords do not match.');
            }
            if (strlen($password) < 8) {
                return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters.');
            }
            $userPayload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $existingCategoryIds = $this->request->getPost('existing_categories') ?: [];

if (empty($existingCategoryIds)) {
    return redirect()->back()->withInput()->with('error', 'Please select at least one product category.');
}

        $this->accountModel->updateProfile($supplierId, $userId, $supplierPayload, $userPayload, $existingCategoryIds);
        session()->set('full_name', $supplierPayload['contact_person']);

        return redirect()->to('supplier/account/profile')->with('success', 'Profile updated successfully.');
    }
}