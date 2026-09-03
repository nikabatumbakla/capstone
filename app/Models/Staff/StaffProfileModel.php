<?php

namespace App\Models\Staff;

use CodeIgniter\Model;

class StaffProfileModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getOwnProfile(int $userId)
    {
        $user = $this->db->table('users')->where('user_id', $userId)->get()->getRow();
        if (!$user) return null;
        unset($user->password_hash);

        $staffProfile = $this->db->table('staff_profiles')->where('user_id', $userId)->get()->getRow();
        $user->position = $staffProfile->position ?? null;
        $user->employee_id = $staffProfile->employee_id ?? null;

        return $user;
    }

    public function emailExists(string $email, int $excludeId): bool
    {
        return $this->db->table('users')->where('email', $email)->where('user_id !=', $excludeId)->countAllResults() > 0;
    }

    public function updateOwnProfile(int $userId, array $payload): void
    {
        $this->db->table('users')->where('user_id', $userId)->update($payload);
    }

    public function updateAvatar(int $userId, string $path): void
{
    $this->db->table('users')->where('user_id', $userId)->update(['avatar_path' => $path]);
}

}