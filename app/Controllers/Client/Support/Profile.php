<?php

namespace App\Controllers\Client\Support;

use App\Controllers\BaseController;
use App\Models\Client\Support\ProfileModel;

class Profile extends BaseController
{
    protected $profileModel;

    public function __construct()
    {
        $this->profileModel = new ProfileModel();
    }

    public function index()
{
    $clientId = session()->get('client_id');
    $data['client'] = $this->profileModel->getProfile($clientId);
    $data['stats'] = $this->profileModel->getStats($clientId);
    $data['title'] = "My Profile";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "profile";
    return view('pages/client/support/profile', $data);
}

    public function update()
{
    $clientId = session()->get('client_id');
    $userId = session()->get('user_id');
    $email = $this->request->getPost('email');

    if ($this->profileModel->emailExists($email, $userId)) {
        return redirect()->back()->withInput()->with('error', 'That email is already in use by another account.');
    }

    $clientPayload = [
        'contact_person'   => $this->request->getPost('contact'),
        'phone'            => $this->request->getPost('phone'),
        'alt_phone'        => $this->request->getPost('alt_phone'),
        'address'          => $this->request->getPost('address'),
        'delivery_address' => $this->request->getPost('delivery_address'),
    ];

    $file = $this->request->getFile('avatar');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'public/uploads/avatars', $newName);
        $clientPayload['avatar_path'] = 'public/uploads/avatars/' . $newName;
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

    $this->profileModel->updateProfile($clientId, $userId, $clientPayload, $userPayload);
    session()->set('full_name', $clientPayload['contact_person']);

    return redirect()->to('client/support/profile')->with('success', 'Profile updated successfully.');
}
}