<?php

namespace App\Models\Supplier;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getScorecard(int $supplierId)
    {
        $row = $this->db->table('supplier_scorecards')->where('supplier_id', $supplierId)->get()->getRow();
        if (!$row) {
            return (object) [
                'on_time_rate'         => null,
                'accuracy_rate'        => null,
                'total_orders'         => 0,
                'avg_lead_time_actual' => null,
            ];
        }
        return $row;
    }

    public function getPoHistory(int $supplierId, int $limit = 10): array
    {
        return $this->db->table('purchase_orders')
            ->where('supplier_id', $supplierId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function getKpis(int $supplierId): array
    {
        return [
            'total_pos'    => $this->db->table('purchase_orders')->where('supplier_id', $supplierId)->countAllResults(),
            'received_pos' => $this->db->table('purchase_orders')->where('supplier_id', $supplierId)->where('status', 'received')->countAllResults(),
        ];
    }

    public function getProfile(int $supplierId)
    {
        return $this->db->table('suppliers as s')
            ->select('s.*, u.email as login_email')
            ->join('users as u', 'u.user_id = s.user_id', 'left')
            ->where('s.supplier_id', $supplierId)
            ->get()->getRow();
    }

    public function emailExists(string $email, int $userId): bool
    {
        return $this->db->table('users')->where('email', $email)->where('user_id !=', $userId)->countAllResults() > 0;
    }

    public function updateProfile(int $supplierId, int $userId, array $supplierPayload, array $userPayload): void
    {
        $this->db->transStart();
        $this->db->table('suppliers')->where('supplier_id', $supplierId)->update($supplierPayload);
        if (!empty($userPayload)) {
            $this->db->table('users')->where('user_id', $userId)->update($userPayload);
        }
        $this->db->transComplete();
    }
}