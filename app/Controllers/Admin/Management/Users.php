<?php

namespace App\Controllers\Admin\Management;
use App\Controllers\BaseController;

class Users extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();

        // 1. Get Role Filter
        $roleFilter = $request->getGet('role');

        // 2. Fetch KPI Stats
        $data['count_active'] = $db->table('users')->where('is_active', 1)->countAllResults();
        $data['count_staff']  = $db->table('users')->where('role', 'staff')->countAllResults();
        $data['count_clients'] = $db->table('users')->where('role', 'institutional_client')->countAllResults();
        $data['count_suppliers'] = $db->table('users')->where('role', 'supplier')->countAllResults();

        // 3. Fetch Users List with Filter
        $builder = $db->table('users');
        if ($roleFilter) {
            $builder->where('role', $roleFilter);
        }
        $data['users'] = $builder->orderBy('role', 'ASC')->get()->getResultArray();

        $data['active_role'] = $roleFilter;
        $data['title'] = "User Access Control";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "users";
        return view('pages/admin/management/user_management', $data);
    }

    public function save()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('user_id');
        
        $payload = [
            'full_name' => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'phone'     => $this->request->getPost('phone'),
            'role'      => $this->request->getPost('role'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($id) {
            $db->table('users')->where('user_id', $id)->update($payload);
            $msg = "Identity updated.";
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $db->table('users')->insert($payload);
            $msg = "New account provisioned.";
        }

        return redirect()->to('admin/management/user-management')->with('success', $msg);
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        // Prevent deleting yourself
        if ($id == session()->get('user_id')) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }
        $db->table('users')->where('user_id', $id)->delete();
        return redirect()->to('admin/management/user-management')->with('success', 'User removed from database.');
    }

    public function get_details($id)
    {
        $db = \Config\Database::connect();
        $row = $db->table('users')->where('user_id', $id)->get()->getRow();
        if($row) unset($row->password_hash);
        return $this->response->setJSON($row);
    }
}