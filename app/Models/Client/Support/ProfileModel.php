<?php

namespace App\Models\Client\Support;

use CodeIgniter\Model;

class ProfileModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getProfile(int $clientId)
    {
        $client = $this->db->table('institutional_clients as ic')
            ->select('ic.*, u.email as login_email')
            ->join('users as u', 'u.user_id = ic.user_id', 'left')
            ->where('ic.client_id', $clientId)
            ->get()->getRow();
        return $client;
    }

    public function emailExists(string $email, int $userId): bool
    {
        return $this->db->table('users')->where('email', $email)->where('user_id !=', $userId)->countAllResults() > 0;
    }

    public function updateProfile(int $clientId, int $userId, array $clientPayload, array $userPayload): void
    {
        $this->db->transStart();
        $this->db->table('institutional_clients')->where('client_id', $clientId)->update($clientPayload);
        if (!empty($userPayload)) {
            $this->db->table('users')->where('user_id', $userId)->update($userPayload);
        }
        $this->db->transComplete();
    }

    public function getStats(int $clientId): array
{
    return [
        'total_orders' => $this->db->table('sales_orders')->where('client_id', $clientId)->countAllResults(),
        'member_since' => $this->db->table('institutional_clients')->select('created_at')->where('client_id', $clientId)->get()->getRow()->created_at ?? null,
    ];
}
}