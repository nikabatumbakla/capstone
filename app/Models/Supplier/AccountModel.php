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
    return \App\Libraries\ScorecardCalculator::calculate($this->db, $supplierId);
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
        $supplier = $this->db->table('suppliers as s')
            ->select('s.*, u.email as login_email')
            ->join('users as u', 'u.user_id = s.user_id', 'left')
            ->where('s.supplier_id', $supplierId)
            ->get()->getRow();

        if ($supplier) {
            $supplier->category_ids = array_column(
                $this->db->table('supplier_categories')->where('supplier_id', $supplierId)->get()->getResultArray(),
                'category_id'
            );
        }
        return $supplier;
    }

    public function getAllCategories(): array
{
    return $this->db->table('categories')->orderBy('name', 'ASC')->get()->getResultArray();
}

    public function emailExists(string $email, int $userId): bool
    {
        return $this->db->table('users')->where('email', $email)->where('user_id !=', $userId)->countAllResults() > 0;
    }

    public function updateProfile(int $supplierId, int $userId, array $supplierPayload, array $userPayload, array $existingCategoryIds = []): void
{
    $this->db->transStart();

    $this->db->table('suppliers')->where('supplier_id', $supplierId)->update($supplierPayload);

    if (!empty($userPayload)) {
        $this->db->table('users')->where('user_id', $userId)->update($userPayload);
    }

    $this->db->table('supplier_categories')->where('supplier_id', $supplierId)->delete();

    foreach ($existingCategoryIds as $categoryId) {
        $this->db->table('supplier_categories')->insert([
            'supplier_id' => $supplierId,
            'category_id' => (int) $categoryId,
        ]);
    }

    $this->db->transComplete();
}

    public function getSupplierCategories(int $supplierId): array
{
    return $this->db->table('supplier_categories as sc')
        ->select('c.category_id, c.name, c.is_active')
        ->join('categories as c', 'c.category_id = sc.category_id')
        ->where('sc.supplier_id', $supplierId)
        ->get()->getResultArray();
}

public function getComputedScorecard(int $supplierId)
{
    return \App\Libraries\ScorecardCalculator::calculate($this->db, $supplierId);
}

}