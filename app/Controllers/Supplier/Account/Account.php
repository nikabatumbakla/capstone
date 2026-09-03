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

        $data['scorecard'] = $this->accountModel->getScorecard($supplierId);
        $data['po_history'] = $this->accountModel->getPoHistory($supplierId, 10);

        $kpis = $this->accountModel->getKpis($supplierId);
        $data['total_pos'] = $kpis['total_pos'];
        $data['received_pos'] = $kpis['received_pos'];

        $data['title'] = "Performance Scorecard";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "scorecard";
        return view('pages/supplier/account/scorecard', $data);
    }

    public function profile()
    {
        $supplierId = session()->get('supplier_id');
        $data['profile'] = $this->accountModel->getProfile($supplierId);

        $data['title'] = "Profile Settings";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "profile";
        return view('pages/supplier/account/profile', $data);
    }

    public function update_profile()
    {
        $supplierId = session()->get('supplier_id');
        $userId = session()->get('user_id');
        $email = $this->request->getPost('email');

        if ($this->accountModel->emailExists($email, $userId)) {
            return redirect()->back()->withInput()->with('error', 'That email is already in use by another account.');
        }

        $supplierPayload = [
            'name'           => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact'),
            'phone'          => $this->request->getPost('phone'),
            'address'        => $this->request->getPost('address'),
        ];

        $file = $this->request->getFile('avatar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'public/uploads/avatars', $newName);
            $supplierPayload['avatar_path'] = 'public/uploads/avatars/' . $newName;
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

        $this->accountModel->updateProfile($supplierId, $userId, $supplierPayload, $userPayload);
        session()->set('full_name', $supplierPayload['name']);

        return redirect()->to('supplier/account/profile')->with('success', 'Profile updated successfully.');
    }
}