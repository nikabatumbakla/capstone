<?php

namespace App\Controllers\Staff\Main;

use App\Controllers\BaseController;
use App\Models\Staff\StaffProfileModel;

class ProfileController extends BaseController
{
    protected $profileModel;

    public function __construct()
    {
        $this->profileModel = new StaffProfileModel();
    }

    public function get_my_profile()
    {
        $profile = $this->profileModel->getOwnProfile(session()->get('user_id'));
        if (!$profile) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($profile);
    }

    public function update_my_profile()
{
    $userId = session()->get('user_id');
    $email = $this->request->getPost('email');

    if ($this->profileModel->emailExists($email, $userId)) {
        return redirect()->back()->withInput()->with('error', 'That email is already in use by another account.');
    }

    $payload = [
        'full_name' => $this->request->getPost('full_name'),
        'email'     => $email,
        'phone'     => $this->request->getPost('phone'),
    ];

    $file = $this->request->getFile('avatar');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'public/uploads/avatars', $newName);
        $payload['avatar_path'] = 'public/uploads/avatars/' . $newName;
    }

    $password = $this->request->getPost('password');
    $confirmPassword = $this->request->getPost('confirm_password');
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            return redirect()->back()->withInput()->with('error', 'Password and confirmation do not match.');
        }
        if (strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters.');
        }
        $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $this->profileModel->updateOwnProfile($userId, $payload);

    // Session updates belong here — in the Controller — never in the Model
    session()->set('full_name', $payload['full_name']);
    if (isset($payload['avatar_path'])) {
        session()->set('avatar_path', $payload['avatar_path']);
    }

    return redirect()->back()->with('success', 'Profile updated successfully.');
}
}