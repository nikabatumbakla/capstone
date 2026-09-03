<?php

namespace App\Controllers\Auth;
use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\TermsAgreementService;

class External extends BaseController
{
    public function index() {
        if (session()->get('isLoggedIn')) return $this->_redirectUser(session()->get('role'));
        return view('auth/partners/partner_login');
    }

    public function login()
    {
        $session = session();
        $model = new UserModel();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $user = $model->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'The email or password you entered is incorrect.');
        }

        if (!in_array($user['role'], ['supplier', 'institutional_client'])) {
            return redirect()->back()->withInput()->with('error', 'The email or password you entered is incorrect.');
        }
        if (!$user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'This account has been disabled. Please contact Robin Rose Trading.');
        }
        if (!$user['is_verified']) {
            return redirect()->back()->withInput()->with('error', 'Your account is pending verification. We will notify you once approved.');
        }

        if (!TermsAgreementService::hasAgreedToCurrent($user)) {
            if (!$this->request->getVar('agree_terms')) {
                return redirect()->back()->withInput()->with('error', 'You must agree to the Terms & Conditions and Privacy Policy to continue.')->with('require_terms', true);
            }
            TermsAgreementService::recordAgreement(
                $user['user_id'],
                $this->request->getIPAddress(),
                (string) $this->request->getUserAgent()
            );
        }

        $db = \Config\Database::connect();
        $sessionData = [
            'user_id'     => $user['user_id'],
            'role'        => $user['role'],
            'isLoggedIn'  => true,
            'avatar_path' => $user['avatar_path'] ?? null,
        ];

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
        return $this->_redirectUser($user['role']);
    }

    public function forgot_password_send()
    {
        \App\Libraries\PasswordResetService::requestReset($this->request->getPost('email'));
        return redirect()->to('partner-gateway')->with('info', 'If that email is registered, a verification code has been sent.')->with('show_reset_form', true);
    }

    public function forgot_password_verify()
    {
        $result = \App\Libraries\PasswordResetService::verifyCode(
            $this->request->getPost('email'),
            trim((string) $this->request->getPost('code'))
        );

        if (!$result['success']) {
            return redirect()->to('partner-gateway')->withInput()->with('error', $result['message'])->with('show_reset_form', true);
        }

        $user = $result['user'];

        if (!TermsAgreementService::hasAgreedToCurrent($user)) {
            TermsAgreementService::recordAgreement(
                $user['user_id'],
                $this->request->getIPAddress(),
                (string) $this->request->getUserAgent()
            );
        }

        $db = \Config\Database::connect();
        $sessionData = ['user_id' => $user['user_id'], 'role' => $user['role'], 'isLoggedIn' => true, 'avatar_path' => $user['avatar_path'] ?? null];

        if ($user['role'] === 'supplier') {
            $supplier = $db->table('suppliers')->where('user_id', $user['user_id'])->get()->getRow();
            $sessionData['supplier_id'] = $supplier->supplier_id ?? null;
            $sessionData['full_name']   = $supplier->name ?? $user['full_name'];
        } else if ($user['role'] === 'institutional_client') {
            $client = $db->table('institutional_clients')->where('user_id', $user['user_id'])->get()->getRow();
            $sessionData['client_id'] = $client->client_id ?? null;
            $sessionData['full_name'] = $client->organization ?? $user['full_name'];
        }

        $session = session();
        $session->set($sessionData);
        return $this->_redirectUser($user['role'])->with('info', 'Verified successfully. You can update your password anytime from your account settings.');
    }

    private function _redirectUser($role) {
        if ($role === 'supplier') {
            return redirect()->to('supplier/dashboard');
        } else {
            return redirect()->to('client/dashboard');
        }
    }
}