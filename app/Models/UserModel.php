<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['full_name', 'email', 'phone', 'password_hash', 'role', 'is_active'];

    // Find user by email for login
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)
            ->where('is_active', 1)
            ->first();
    }
}
