<?php

namespace App\Models\Supplier;

use CodeIgniter\Model;

class CatalogModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getKpis(int $supplierId): array
    {
        return [
            'total_items'    => $this->db->table('supplier_product_catalog')->where('supplier_id', $supplierId)->countAllResults(),
            'preferred_items'=> $this->db->table('supplier_product_catalog')->where('supplier_id', $supplierId)->where('is_preferred', 1)->countAllResults(),
        ];
    }

    public function getCatalog(int $supplierId, string $search = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($supplierId, $search) {
            $b->where('spc.supplier_id', $supplierId);
            if ($search !== '') $b->groupStart()->like('p.name', $search)->orLike('spc.supplier_sku', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('supplier_product_catalog as spc')->join('products as p', 'p.product_id = spc.product_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('supplier_product_catalog as spc')
            ->select('spc.*, p.name as product_name, p.sku as global_sku, p.unit, c.name as category_name')
            ->join('products as p', 'p.product_id = spc.product_id')
            ->join('categories as c', 'c.category_id = p.category_id');
        $apply($builder);
        $builder->orderBy('p.name', 'ASC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getAddableProducts(int $supplierId): array
{
    $categoryIds = $this->db->table('supplier_categories')->select('category_id')->where('supplier_id', $supplierId)->get()->getResultArray();
    $categoryIds = array_column($categoryIds, 'category_id');
    if (empty($categoryIds)) return [];

    $existingProductIds = $this->db->table('supplier_product_catalog')->select('product_id')->where('supplier_id', $supplierId)->get()->getResultArray();
    $existingProductIds = array_column($existingProductIds, 'product_id');

    $builder = $this->db->table('products as p')
        ->select('p.product_id, p.name, p.sku, p.unit, c.name as category_name')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->whereIn('p.category_id', $categoryIds)
        ->where('p.is_active', 1);
    if (!empty($existingProductIds)) $builder->whereNotIn('p.product_id', $existingProductIds);

    return $builder->orderBy('c.name', 'ASC')->orderBy('p.name', 'ASC')->get()->getResultArray();
}

    public function addToCatalog(int $supplierId, int $productId, array $payload): bool
    {
        $exists = $this->db->table('supplier_product_catalog')->where('supplier_id', $supplierId)->where('product_id', $productId)->countAllResults();
        if ($exists) return false;

        $payload['supplier_id'] = $supplierId;
        $payload['product_id'] = $productId;
        $this->db->table('supplier_product_catalog')->insert($payload);
        return true;
    }

    public function getCatalogEntry(int $catalogId, int $supplierId)
    {
        return $this->db->table('supplier_product_catalog as spc')
            ->select('spc.*, p.name as product_name, p.sku as global_sku, p.unit')
            ->join('products as p', 'p.product_id = spc.product_id')
            ->where('spc.catalog_id', $catalogId)
            ->where('spc.supplier_id', $supplierId)
            ->get()->getRow();
    }

    public function updateCatalogEntry(int $catalogId, int $supplierId, array $payload): bool
    {
        $entry = $this->db->table('supplier_product_catalog')->where('catalog_id', $catalogId)->where('supplier_id', $supplierId)->get()->getRow();
        if (!$entry) return false;

        $this->db->table('supplier_product_catalog')->where('catalog_id', $catalogId)->update($payload);
        return true;
    }

    public function removeCatalogEntry(int $catalogId, int $supplierId): bool
    {
        $entry = $this->db->table('supplier_product_catalog')->where('catalog_id', $catalogId)->where('supplier_id', $supplierId)->get()->getRow();
        if (!$entry) return false;

        $this->db->table('supplier_product_catalog')->where('catalog_id', $catalogId)->delete();
        return true;
    }

    public function getRegisteredCategoryNames(int $supplierId): array
{
    return $this->db->table('supplier_categories as sc')
        ->select('c.name')
        ->join('categories as c', 'c.category_id = sc.category_id')
        ->where('sc.supplier_id', $supplierId)
        ->get()->getResultArray();
}
}