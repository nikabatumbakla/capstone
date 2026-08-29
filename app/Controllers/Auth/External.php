<?php

namespace App\Controllers\Auth;
use App\Controllers\BaseController;
use App\Models\UserModel;

class External extends BaseController
{
    public function index() {
        if (session()->get('isLoggedIn')) return $this->_redirectUser(session()->get('role'));
        return view('auth/partners/partner_login');
    }

    public function login()
    {
        $session = session();
        $model = new \App\Models\UserModel();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $role = $this->request->getVar('role'); // institutional_client or supplier

        $user = $model->where('email', $email)->first();

        if ($user && password_verify($password, $user['password_hash'])) {
            // SECURITY: Ensure the role chosen on the card matches the database role
            if ($user['role'] !== $role) {
                return redirect()->back()->with('error', "Unauthorized: Account is " . strtoupper($user['role']));
            }
            
            $db = \Config\Database::connect();
            $sessionData = [
                'user_id'     => $user['user_id'],
                'role'        => $user['role'],
                'isLoggedIn'  => true
            ];

            // FIXED: Fetch the correct ID and Name based on the role
            if ($user['role'] === 'supplier') {
                $supplier = $db->table('suppliers')->where('user_id', $user['user_id'])->get()->getRow();
                $sessionData['supplier_id'] = $supplier->supplier_id ?? null;
                $sessionData['full_name']   = $supplier->name ?? $user['full_name'];
            } else if ($user['role'] === 'institutional_client') {
                $client = $db->table('institutional_clients')->where('user_id', $user['user_id'])->get()->getRow();
                $sessionData['client_id'] = $client->client_id ?? null;
                $sessionData['full_name'] = $client->organization ?? $user['full_name'];
            }

            $session->set($sessionData);

            // FIXED: Use the redirect helper instead of a hardcoded link
            return $this->_redirectUser($user['role']);
        }
        return redirect()->back()->with('error', 'Invalid Credentials.');
    }

    private function _redirectUser($role) {
        if ($role === 'supplier') {
            return redirect()->to('supplier/dashboard');
        } else {
            return redirect()->to('client/dashboard');
        }
    }
}