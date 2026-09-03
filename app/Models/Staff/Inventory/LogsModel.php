<?php

namespace App\Models\Staff\Inventory;

use CodeIgniter\Model;

class LogsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getLogs(int $staffUserId, string $search = '', string $reason = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($staffUserId, $search, $reason) {
            $b->where('sal.adjusted_by', $staffUserId);
            if ($search !== '') $b->like('p.name', $search);
            if ($reason !== '') $b->where('sal.reason', $reason);
            return $b;
        };

        $countBuilder = $this->db->table('stock_adjustment_logs as sal')->join('products as p', 'p.product_id = sal.product_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('stock_adjustment_logs as sal')
            ->select('sal.*, p.name as product_name, u.full_name as staff_name')
            ->join('products as p', 'p.product_id = sal.product_id')
            ->join('users as u', 'u.user_id = sal.adjusted_by');
        $apply($builder);
        $builder->orderBy('sal.adjusted_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    // Batches with real stock AND their product info, so the New Adjustment form
    // has both product_id and quantity_avail available per option.
    public function getAvailableStocks(): array
    {
        return $this->db->table('inventory_batches as ib')
            ->select('ib.batch_id, ib.batch_number, ib.quantity_avail, p.product_id, p.name')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('ib.quantity_avail >', 0)
            ->where('p.is_active', 1)
            ->orderBy('p.name', 'ASC')
            ->get()->getResultArray();
    }

    public function getSummary(int $staffUserId): array
    {
        return [
            'total' => $this->db->table('stock_adjustment_logs')->where('adjusted_by', $staffUserId)->countAllResults(),
            'today' => $this->db->table('stock_adjustment_logs')->where('adjusted_by', $staffUserId)->where('DATE(adjusted_at)', date('Y-m-d'))->countAllResults(),
            'damage_or_expired' => $this->db->table('stock_adjustment_logs')->where('adjusted_by', $staffUserId)->whereIn('reason', ['Damage', 'Expired'])->countAllResults(),
        ];
    }
}