<?php

namespace App\Models\Client\Orders;

use CodeIgniter\Model;

class ReturnsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getReturns(int $clientId, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $builder = $this->db->table('sales_returns as sr')
            ->select('sr.*, so.order_number, p.name as product_name, p.barcode_value')
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('products as p', 'p.product_id = sr.product_id', 'left')
            ->where('so.client_id', $clientId);

        $total = (clone $builder)->countAllResults(false);
        $data = $builder->orderBy('sr.created_at', 'DESC')->limit($perPage, $offset)->get()->getResultArray();

        return ['data' => $data, 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }
}