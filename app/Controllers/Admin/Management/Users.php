<?php

namespace App\Controllers\Admin\Management;

use App\Controllers\BaseController;
use App\Models\Admin\Management\UsersModel;

class Users extends BaseController
{
    protected $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function index()
{
    $roleGroup = $this->request->getGet('group') ?: '';
    $search = trim((string) ($this->request->getGet('search') ?? ''));
    $page = (int) ($this->request->getGet('page') ?? 1);

    $registry = $this->usersModel->getRegistry($roleGroup, $search, $page, 10);
    $counts = $this->usersModel->getCounts();

    $data['users'] = $registry['data'];
    $data['total_pages'] = $registry['total_pages'];
    $data['current_page'] = $page;
    $data['active_group'] = $roleGroup;
    $data['search'] = $search;
    $data['count_active'] = $counts['active'];
    $data['count_staff'] = $counts['staff'];
    $data['count_clients'] = $counts['clients'];
    $data['count_suppliers'] = $counts['suppliers'];
    $data['count_pending'] = $counts['pending'];

    $data['title'] = "User Access Control";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "users";
    return view('pages/admin/management/user_management', $data);
}

public function pending_applications()
{
    $roleFilter = $this->request->getGet('role') ?: '';
    $page = (int) ($this->request->getGet('page') ?? 1);
    $result = $this->usersModel->getPendingApplications($roleFilter, $page, 10);

    $data['applications'] = $result['data'];
    $data['total_pages'] = $result['total_pages'];
    $data['current_page'] = $page;
    $data['role_filter'] = $roleFilter;

    $data['title'] = "Pending Applications";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "users";
    return view('pages/admin/management/pending_applications', $data);
}

public function approve_application($id)
{
    $id = (int) $id;
    $user = $this->usersModel->getUserDetails($id);
    if (!$user) return redirect()->back()->with('error', 'Not found.');

    $this->usersModel->updateAccessFlags($id, true, true, null); // sets is_active=1, is_verified=1

    $db = \Config\Database::connect();
    $reference = 'N/A';
    if ($user->role === 'supplier') {
        $supplier = $db->table('suppliers')->where('user_id', $id)->get()->getRow();
        $reference = $supplier->registration_ref ?? 'N/A';
    } else {
        $client = $db->table('institutional_clients')->where('user_id', $id)->get()->getRow();
        $reference = $client->registration_ref ?? 'N/A';
    }

    \App\Libraries\AccountNotificationService::notifyApproved($user->email, $user->full_name, $reference);

    return redirect()->to('admin/management/users/pending-applications')->with('success', 'Application approved — account moved to the Identity Registry.');
}

public function reject_application($id)
{
    $id = (int) $id;
    $user = $this->usersModel->getUserDetails($id);
    if (!$user) return redirect()->back()->with('error', 'Not found.');

    $db = \Config\Database::connect();
    $reference = 'N/A';
    if ($user->role === 'supplier') {
        $supplier = $db->table('suppliers')->where('user_id', $id)->get()->getRow();
        $reference = $supplier->registration_ref ?? 'N/A';
    } else {
        $client = $db->table('institutional_clients')->where('user_id', $id)->get()->getRow();
        $reference = $client->registration_ref ?? 'N/A';
    }

    try {
        $this->usersModel->hardDeleteAccount($id); // delete FIRST
    } catch (\Throwable $e) {
        log_message('error', 'Hard delete failed for user_id ' . $id . ': ' . $e->getMessage());
        return redirect()->back()->with('error', 'Could not delete this application: ' . $e->getMessage());
    }

    try {
        \App\Libraries\AccountNotificationService::notifyRejected($user->email, $user->full_name, $reference);
    } catch (\Throwable $e) {
        log_message('error', 'Rejection email failed for user_id ' . $id . ': ' . $e->getMessage());
    }

    return redirect()->to('admin/management/users/pending-applications')->with('success', 'Application rejected and removed.');
}

    public function create()
{
    $email = $this->request->getPost('email');
    $role = $this->request->getPost('role');
    $password = $this->request->getPost('password');
    $confirmPassword = $this->request->getPost('confirm_password');

    if ($this->usersModel->emailExists($email)) {
        return redirect()->back()->withInput()->with('error', 'That email address is already registered to another account.');
    }

    if (empty($password)) {
        return redirect()->back()->withInput()->with('error', 'A password is required.');
    }
    if ($password !== $confirmPassword) {
        return redirect()->back()->withInput()->with('error', 'Password and confirmation do not match.');
    }
    if (strlen($password) < 8) {
        return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters.');
    }

    $empId = $this->request->getPost('employee_id');
    if ($role === 'staff' && $empId && $this->usersModel->employeeIdExists($empId)) {
        return redirect()->back()->withInput()->with('error', 'That Employee ID is already assigned to another staff member.');
    }

    // Admin-provisioned accounts (staff/admin created here directly) are active + verified immediately,
    // since an admin creating the account IS the verification. Self-registered accounts from the
    // public Partner Gateway remain unverified/inactive until reviewed — see note below.
    $userPayload = [
        'full_name'   => $this->request->getPost('name'),
        'email'       => $email,
        'phone'       => $this->request->getPost('phone'),
        'role'        => $role,
        'is_active'   => 1,
        'is_verified' => 1,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'created_at'  => date('Y-m-d H:i:s'),
    ];

    $profilePayload = $this->buildProfilePayload($role, $email);

    $this->usersModel->saveAccount($userPayload, $profilePayload, null);
    return redirect()->to('admin/management/user-management')->with('success', 'New account provisioned.');
}

// The ONLY thing an admin can change on an existing account: access + verification status.
// Every other field belongs to the account holder, as they submitted it at registration/verification.
public function updateAccess()
{
    $id = (int) $this->request->getPost('user_id');

    if ($id == session()->get('user_id')) {
        return redirect()->back()->with('error', 'You cannot modify your own account access.');
    }

    $isActive = (bool) $this->request->getPost('is_active');
    $isVerified = (bool) $this->request->getPost('is_verified');
    $notes = $this->request->getPost('verification_notes');

    // Check the CURRENT state before updating, so we know if this action is a fresh approval
    $before = $this->usersModel->getUserDetails($id);
    $wasAlreadyVerified = $before && $before->is_verified;

    $this->usersModel->updateAccessFlags($id, $isActive, $isVerified, $notes ?: null);

    // Only fire the email the moment verification actually flips from off to on
    if ($isVerified && $isActive && !$wasAlreadyVerified && in_array($before->role, ['supplier', 'institutional_client'])) {
        $db = \Config\Database::connect();
        $reference = null;
        $name = $before->full_name;

        if ($before->role === 'supplier') {
            $supplier = $db->table('suppliers')->where('user_id', $id)->get()->getRow();
            $reference = $supplier->registration_ref ?? 'N/A';
        } else {
            $client = $db->table('institutional_clients')->where('user_id', $id)->get()->getRow();
            $reference = $client->registration_ref ?? 'N/A';
        }

        \App\Libraries\AccountNotificationService::notifyApproved($before->email, $name, $reference);
    }

    $redirectUrl = 'admin/management/user-management' . ($this->request->getPost('return_role') ? '?group=' . $this->request->getPost('return_role') : '');
    return redirect()->to($redirectUrl)->with('success', 'Account access updated.');
}


    private function buildProfilePayload(string $role, string $email): array
{
    switch ($role) {
        case 'staff':
            return [
                'employee_id'    => $this->request->getPost('employee_id'),
                'position'       => $this->request->getPost('position'),
                'assigned_roles' => implode(',', $this->request->getPost('assigned_roles') ?: []),
            ];
        case 'customer':
            return [
                'address'   => $this->request->getPost('address'),
                'id_type'   => $this->request->getPost('id_type'),
                'id_number' => $this->request->getPost('id_number'),
                'is_id_verified' => $this->request->getPost('is_id_verified') ? 1 : 0,
            ];
        case 'supplier':
            return [
                'name'           => $this->request->getPost('name'),
                'contact_person' => $this->request->getPost('name'),
                'phone'          => $this->request->getPost('phone'),
                'email'          => $email,
                'address'        => $this->request->getPost('address'),
                'payment_terms'  => $this->request->getPost('payment_terms'),
                'lead_time_days' => $this->request->getPost('lead_time_days') ?: 7,
            ];
        case 'institutional_client':
            return [
                'organization'   => $this->request->getPost('organization'),
                'client_type'    => $this->request->getPost('client_type') ?: 'other',
                'contact_person' => $this->request->getPost('name'),
                'phone'          => $this->request->getPost('phone'),
                'email'          => $email,
                'address'        => $this->request->getPost('address'),
                'tin'            => $this->request->getPost('tin'),
                'credit_limit'   => $this->request->getPost('credit_limit') ?: 0,
            ];
        default:
            return [];
    }
}

public function delete($id)
{
    if ($id == session()->get('user_id')) {
        return redirect()->back()->with('error', 'You cannot deactivate your own account.');
    }
    $this->usersModel->removeAccount($id);
    return redirect()->to('admin/management/user-management')->with('success', 'Account deactivated.');
}

public function get_details($id)
{
    $row = $this->usersModel->getUserDetails((int) $id);
    if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
    return $this->response->setJSON($row);
}

public function hard_delete($id)
{
    $id = (int) $id;
    if ($id == session()->get('user_id')) {
        return redirect()->back()->with('error', 'You cannot delete your own account.');
    }

    if ($this->usersModel->hasActivity($id)) {
        return redirect()->back()->with('error', 'This account has recorded activity (orders, adjustments, transactions, etc.) and cannot be permanently deleted. Deactivate it instead to preserve history.');
    }

    $this->usersModel->hardDeleteAccount($id);
    return redirect()->to('admin/management/user-management')->with('success', 'Account permanently deleted.');
}

public function get_my_profile()
{
    $profile = $this->usersModel->getOwnProfile(session()->get('user_id'));
    if (!$profile) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
    return $this->response->setJSON($profile);
}

public function update_my_profile()
{
    $userId = session()->get('user_id');
    $email = $this->request->getPost('email');

    if ($this->usersModel->emailExists($email, $userId)) {
        return redirect()->back()->withInput()->with('error', 'That email is already in use by another account.');
    }

    $payload = [
        'full_name' => $this->request->getPost('full_name'),
        'email'     => $email,
        'phone'     => $this->request->getPost('phone'),
    ];

    // Avatar upload
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

    $this->usersModel->updateOwnProfile($userId, $payload);
    session()->set('full_name', $payload['full_name']);
    if (isset($payload['avatar_path'])) {
        session()->set('avatar_path', $payload['avatar_path']);
    }

    return redirect()->back()->with('success', 'Profile updated successfully.');
}

}