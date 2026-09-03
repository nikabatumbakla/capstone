<?php

namespace App\Controllers\Auth;
use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\TermsAgreementService;

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

        $user = $model->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'The email or password you entered is incorrect.');
        }

        if (!in_array($user['role'], ['admin', 'staff'])) {
            return redirect()->back()->withInput()->with('error', 'The email or password you entered is incorrect.');
        }
        if (!$user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'This account has been disabled. Please contact the system administrator.');
        }
        if (!$user['is_verified']) {
            return redirect()->back()->withInput()->with('error', 'Your account is pending verification. Please contact the system administrator.');
        }

        // Credentials are valid — but if they haven't agreed to the CURRENT terms version,
        // require it now before granting a session, regardless of whether the checkbox was ticked.
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

        $session->set([
            'user_id'     => $user['user_id'],
            'full_name'   => $user['full_name'],
            'role'        => $user['role'],
            'avatar_path' => $user['avatar_path'] ?? null,
            'isLoggedIn'  => true,
        ]);
        return $this->_redirectUser($user['role']);
    }

    public function forgot_password_send()
    {
        \App\Libraries\PasswordResetService::requestReset($this->request->getPost('email'));
        return redirect()->to('portal')->with('info', 'If that email is registered, a verification code has been sent.')->with('show_reset_form', true);
    }

    public function forgot_password_verify()
    {
        $result = \App\Libraries\PasswordResetService::verifyCode(
            $this->request->getPost('email'),
            trim((string) $this->request->getPost('code'))
        );

        if (!$result['success']) {
            return redirect()->to('portal')->withInput()->with('error', $result['message'])->with('show_reset_form', true);
        }

        $user = $result['user'];

        // Same rule applies coming in through password reset — an old-version agreement doesn't count.
        if (!TermsAgreementService::hasAgreedToCurrent($user)) {
            TermsAgreementService::recordAgreement(
                $user['user_id'],
                $this->request->getIPAddress(),
                (string) $this->request->getUserAgent()
            );
        }

        session()->set([
            'user_id'     => $user['user_id'],
            'full_name'   => $user['full_name'],
            'role'        => $user['role'],
            'avatar_path' => $user['avatar_path'] ?? null,
            'isLoggedIn'  => true,
        ]);

        return $this->_redirectUser($user['role'])->with('info', 'Verified successfully. You can update your password anytime from your account settings.');
    }

    private function _redirectUser($role) {
        return ($role === 'admin') ? redirect()->to('admin/dashboard') : redirect()->to('staff/dashboard');
    }

    public function logout()
    {
        $session = session();
        $role = $session->get('role');
        $session->destroy();

        if (in_array($role, ['admin', 'staff'])) {
            return redirect()->to('portal')->with('info', 'Logged out from System Terminal.');
        } else {
            return redirect()->to('partner-gateway')->with('info', 'Secure session ended.');
        }
    }
}