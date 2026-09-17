<?php

namespace App\Models\Staff\Operations;

use CodeIgniter\Model;

class QuickLookupModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function search(string $term): array
    {
        return $this->db->table('products as p')
            ->select("p.product_id, p.name, p.barcode_value, p.unit,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as sell_price,
                (SELECT SUM(quantity_avail) FROM inventory_batches WHERE product_id = p.product_id) as total_stock")
            ->where('p.is_active', 1)
            ->groupStart()->like('p.name', $term)->orLike('p.barcode_value', $term)->groupEnd()
            ->orderBy('p.name', 'ASC')
            ->limit(20)
            ->get()->getResultArray();
    }
}