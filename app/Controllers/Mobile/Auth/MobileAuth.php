<?php

namespace App\Controllers\Mobile\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class MobileAuth extends BaseController
{
    public function index()
{
    if (session()->get('isLoggedIn') && session()->get('role') === 'staff') {
        return redirect()->to('m/staff/home');
    }
    return view('mobile/auth/select', ['isAuthPage' => true]);
}

    public function staff_login_view()
    {
        return view('mobile/auth/staff_login', ['isAuthPage' => true]);
    }

    public function staff_login()
    {
        $email = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'The email or password you entered is incorrect.');
        }
        if ($user['role'] !== 'staff') {
            return redirect()->back()->withInput()->with('error', 'This portal is for staff accounts only.');
        }
        if (!$user['is_active']) {
            return redirect()->back()->with('error', 'This account has been disabled. Please contact the administrator.');
        }
        if (!$user['is_verified']) {
            return redirect()->back()->with('error', 'Your account is pending verification.');
        }

        session()->set([
            'user_id'    => $user['user_id'],
            'full_name'  => $user['full_name'],
            'role'       => $user['role'],
            'isLoggedIn' => true,
        ]);
        \App\Libraries\SessionTrackerService::recordLogin($user['user_id'], $user['role'], $this->request);

        return redirect()->to('m/staff/home');
    }

    public function walkin_view()
    {
        return view('mobile/auth/walkin', ['isAuthPage' => true]);
    }

    // Lightweight guest capture — not a real account, just records the person for this visit
    public function walkin_submit()
{
    $fullName = trim((string) $this->request->getPost('full_name'));
    $address = trim((string) $this->request->getPost('address'));

    if (empty($fullName) || empty($address)) {
        return redirect()->back()->withInput()->with('error', 'Please provide your name and address.');
    }

    $db = \Config\Database::connect();
    $userModel = new \App\Models\UserModel();

    // Walk-ins don't have a real email — generate a unique placeholder tied to this session
    $placeholderEmail = 'walkin_' . uniqid() . '@guest.local';

    $db->transStart();

    $userId = $userModel->insert([
        'full_name'     => $fullName,
        'email'         => $placeholderEmail,
        'password_hash' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT), // random, unused password
        'role'          => 'customer',
        'is_active'     => 1,
        'is_verified'   => 1,
    ], true);

    $db->table('customer_profiles')->insert([
        'user_id' => $userId,
        'address' => $address,
    ]);

    $db->transComplete();

    session()->set([
        'user_id'        => $userId,
        'customer_id'    => $userId, // profile_id isn't needed elsewhere; user_id is the stable reference
        'walkin_name'    => $fullName,
        'walkin_address' => $address,
        'isWalkinGuest'  => true,
        'role'           => 'customer',
        'full_name'      => $fullName,
    ]);
    \App\Libraries\SessionTrackerService::recordLogin($userId, 'customer', $this->request);

    return redirect()->to('m/customer/home')->with('success', 'Welcome, ' . $fullName . '!');
}

    public function logout()
    {
        \App\Libraries\SessionTrackerService::endSession();
        session()->destroy();
        return redirect()->to('m/');
    }
}