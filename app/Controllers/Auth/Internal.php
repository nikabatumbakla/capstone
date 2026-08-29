<?php

namespace App\Controllers\Auth;
use App\Controllers\BaseController;
use App\Models\UserModel;

class Internal extends BaseController
{
    public function index() {
        if (session()->get('isLoggedIn')) return $this->_redirectUser(session()->get('role'));
        return view('auth/internal_login');
    }

    public function login() {
        $session = session();
        $model = new UserModel();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $role = $this->request->getVar('role'); // admin or staff

        $user = $model->where('email', $email)->first();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (!in_array($user['role'], ['admin', 'staff']) || $user['role'] !== $role) {
                return redirect()->back()->with('error', "Invalid access for this portal.");
            }
            $session->set(['user_id'=>$user['user_id'],'full_name'=>$user['full_name'],'role'=>$user['role'],'isLoggedIn'=>true]);
            return $this->_redirectUser($user['role']);
        }
        return redirect()->back()->with('error', 'Invalid Credentials.');
    }

    private function _redirectUser($role) {
        return ($role === 'admin') ? redirect()->to('admin/dashboard') : redirect()->to('staff/dashboard');
    }

    public function logout()
{
    $session = session();
    $role = $session->get('role'); // Get role BEFORE destroying the session

    $session->destroy();

    // Determine direction based on role group
    if (in_array($role, ['admin', 'staff'])) {
        // Employees go back to the Internal Portal
        return redirect()->to('portal')->with('info', 'Logged out from System Terminal.');
    } else {
        // Partners go back to the Partner Gateway
        return redirect()->to('partner-gateway')->with('info', 'Secure session ended.');
    }
}
}