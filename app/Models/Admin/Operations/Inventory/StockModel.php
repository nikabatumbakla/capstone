<?php

namespace App\Models\Admin\Operations\Inventory;

use CodeIgniter\Model;

class StockModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function generateBarcode(): string
    {
        do {
            $code = '200' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $exists = $this->db->table('products')->where('barcode_value', $code)->countAllResults();
        } while ($exists > 0);
        return $code;
    }

    public function getCategories(): array
    {
        return $this->db->table('categories')->get()->getResultArray();
    }

    public function getSuppliers(): array
    {
        return $this->db->table('suppliers')->select('supplier_id, name')->where('is_active', 1)->get()->getResultArray();
    }

    private function applyFilters($builder, ?string $categoryFilter, string $search)
{
    if ($categoryFilter) $builder->where('p.category_id', $categoryFilter);
    if ($search !== '') {
        $builder->groupStart()
            ->like('p.name', $search)
            ->orLike('p.barcode_value', $search)
            ->orLike('p.brand', $search)
        ->groupEnd();
    }
    return $builder;
}

    public function getInventory(?string $categoryFilter, string $search, ?string $statusFilter, int $page, int $perPage): array
{
    $offset = ($page - 1) * $perPage;

    $builder = $this->db->table('products as p')
        ->select("p.product_id as pid, p.name as product_name, p.barcode_value, c.name as category_name,
            COALESCE(SUM(ib.quantity_avail), 0) as total_stock,
            COALESCE(MAX(ib.reorder_level), 5) as reorder_level,
            MIN(CASE WHEN ib.expires_at IS NOT NULL THEN ib.expires_at END) as nearest_expiry,
            COUNT(ib.batch_id) as batch_count,
            (SELECT ib2.sell_price FROM inventory_batches ib2 WHERE ib2.product_id = p.product_id AND ib2.quantity_avail > 0 ORDER BY ib2.expires_at ASC LIMIT 1) as sell_price")
        ->join('categories as c', 'c.category_id = p.category_id')
        ->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left')
        ->where('p.is_active', 1);
    $this->applyFilters($builder, $categoryFilter, $search);
    $builder->groupBy('p.product_id');

    if ($statusFilter === 'low_stock') {
        $builder->having('total_stock <= reorder_level', null, false);
    } elseif ($statusFilter === 'near_expiry') {
        $builder->having('nearest_expiry IS NOT NULL', null, false)
                 ->having('nearest_expiry <=', date('Y-m-d', strtotime('+6 months')))
                 ->having('nearest_expiry >=', date('Y-m-d'));
    }

    $builder->orderBy('p.product_id', 'DESC');

    $allRows = $builder->get()->getResultArray();
    $totalRows = count($allRows);
    $data = array_slice($allRows, $offset, $perPage);

    return [
        'data' => $data,
        'total_rows' => $totalRows,
        'total_pages' => max(1, (int) ceil($totalRows / $perPage)),
    ];
}

    public function getCounts(): array
{
    $totalProducts = $this->db->table('products')->where('is_active', 1)->countAllResults();

    $lowStockRows = $this->db->table('products as p')
        ->select('p.product_id, COALESCE(SUM(ib.quantity_avail),0) as total_stock, COALESCE(MAX(ib.reorder_level),5) as reorder_level')
        ->join('inventory_batches as ib', 'ib.product_id = p.product_id', 'left')
        ->where('p.is_active', 1)
        ->groupBy('p.product_id')
        ->having('total_stock <= reorder_level', null, false)
        ->get()->getResultArray();

    $nearExpiryRows = $this->db->table('products as p')
        ->select('p.product_id')
        ->join('inventory_batches as ib', 'ib.product_id = p.product_id')
        ->where('p.is_active', 1)
        ->where('ib.expires_at IS NOT NULL', null, false)
        ->where('ib.expires_at <=', date('Y-m-d', strtotime('+6 months')))
        ->where('ib.expires_at >=', date('Y-m-d'))
        ->groupBy('p.product_id')
        ->get()->getResultArray();

    return [
        'total_products' => $totalProducts,
        'low_stock'       => count($lowStockRows),
        'near_expiry'     => count($nearExpiryRows),
    ];
}

public function getProductBatches(int $productId)
{
    $product = $this->db->table('products as p')
        ->select('p.product_id, p.name, p.barcode_value, p.unit, c.name as cat_name')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->where('p.product_id', $productId)
        ->get()->getRow();

    if (!$product) return null;

    $primaryImage = $this->db->table('product_images')->where('product_id', $productId)->where('is_primary', 1)->get()->getRow();
    $product->image_path = $primaryImage ? $primaryImage->image_path : null;

    $product->batches = $this->db->table('inventory_batches')
        ->where('product_id', $productId)
        ->orderBy('received_at', 'DESC')
        ->get()->getResultArray();

    return $product;
}

    public function saveProduct(array $post, ?string $barcode): array
{
    $categoryId = $post['category_id'] ?? null;
    $newCategoryName = trim((string) ($post['new_category_name'] ?? ''));

    if ($categoryId === '__new__') {
        if ($newCategoryName === '') return ['error' => 'Please enter a name for the new category.'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $newCategoryName), '-'));
        $existingCat = $this->db->table('categories')->where('slug', $slug)->get()->getRow();
        $categoryId = $existingCat ? $existingCat->category_id : (function() use ($newCategoryName, $slug) {
            $this->db->table('categories')->insert(['name' => $newCategoryName, 'slug' => $slug]);
            return $this->db->insertID();
        })();
    }

    $finalBarcode = $barcode !== '' ? $barcode : $this->generateBarcode();

    // FIX: empty-string select values must become NULL for nullable FK columns
    $supplierId = (!empty($post['supplier_id'])) ? $post['supplier_id'] : null;

    $data = [
        'category_id'    => $categoryId,
        'supplier_id'    => $supplierId,
        'name'           => $post['name'] ?? '',
        'description'    => $post['description'] ?? null,
        'barcode_value'  => $finalBarcode,
        'barcode_type'   => $post['barcode_type'] ?? 'CODE128',
        'brand'          => $post['brand'] ?? null,
        'manufacturer'   => $post['manufacturer'] ?? null,
        'unit'           => $post['unit'] ?? 'piece',
        'is_vat_exempt'  => ($post['is_vat_exempt'] ?? false) ? 1 : 0,
        'notes'          => $post['notes'] ?? null,
        'is_active'      => 1,
    ];

    try {
        $this->db->table('products')->insert($data);
    } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
        // FIX: surface the real DB error instead of guessing "barcode already exists"
        return ['error' => 'Could not save product: ' . $e->getMessage()];
    }

    return ['product_id' => $this->db->insertID()];
}

    public function saveOpeningBatch(int $productId, array $post): ?string
{
    $quantity = $post['quantity'] ?? null;
    $sellPrice = $post['sell_price'] ?? null;
    $batchNumber = trim((string) ($post['batch_number'] ?? ''));

    if ($quantity === null || $quantity === '' || $sellPrice === null || $sellPrice === '') return null;

    if ($batchNumber === '') {
        return 'Product was added, but Batch Number is required to record stock. Use "Add Stock" to add it.';
    }

    $this->db->table('inventory_batches')->insert([
        'product_id'     => $productId,
        'batch_number'   => $batchNumber,
        'quantity_in'    => $quantity,
        'quantity_avail' => $quantity,
        'reorder_level'  => $post['reorder_level'] ?? 5,
        'cost_price'     => $post['cost_price'] ?? 0,
        'sell_price'     => $sellPrice,
        'expires_at'     => $post['expires_at'] ?? null,
    ]);
    return null;
}

    public function getStockContext(int $productId)
    {
        $product = $this->db->table('products as p')
            ->select('p.product_id, p.name, p.barcode_value, p.unit, c.name as cat_name')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->where('p.product_id', $productId)
            ->get()->getRow();

        if (!$product) return null;

        $product->last_batch = $this->db->table('inventory_batches')
            ->where('product_id', $productId)
            ->orderBy('received_at', 'DESC')
            ->get()->getRow();

        return $product;
    }

    public function getBatchDetails(int $batchId)
    {
        $row = $this->db->table('inventory_batches as ib')
            ->select('ib.batch_id, ib.batch_number, ib.quantity_avail, ib.reorder_level, ib.sell_price, ib.expires_at,
                      p.product_id, p.name, p.description, p.barcode_value, p.brand, p.manufacturer, p.unit,
                      p.is_vat_exempt, p.notes, p.category_id, p.supplier_id,
                      c.name as cat_name, s.name as supplier_name')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->join('categories as c', 'c.category_id = p.category_id')
            ->join('suppliers as s', 's.supplier_id = p.supplier_id', 'left')
            ->where('ib.batch_id', $batchId)
            ->get()->getRow();

        if (!$row) return null;

        $primaryImage = $this->db->table('product_images')->where('product_id', $row->product_id)->where('is_primary', 1)->get()->getRow();
        $row->image_path = $primaryImage ? $primaryImage->image_path : null;
        $row->all_categories = $this->db->table('categories')->select('category_id, name')->get()->getResultArray();
        $row->all_suppliers = $this->db->table('suppliers')->select('supplier_id, name')->where('is_active', 1)->get()->getResultArray();

        return $row;
    }

    public function getEducation(int $productId)
    {
        $product = $this->db->table('products')->where('product_id', $productId)->get()->getRow();
        if (!$product) return null;

        $images = $this->db->table('product_images')->where('product_id', $productId)->orderBy('sort_order', 'ASC')->get()->getResultArray();
        $content = $this->db->table('product_info_content')->where('product_id', $productId)->get()->getRow();

        return ['product_id' => $product->product_id, 'name' => $product->name, 'barcode_value' => $product->barcode_value, 'images' => $images, 'content' => $content];
    }

    public function getProduct(int $id)
    {
        $product = $this->db->table('products')->where('product_id', $id)->get()->getRow();
        if (!$product) return null;

        $product->all_categories = $this->db->table('categories')->select('category_id, name')->get()->getResultArray();
        $product->all_suppliers = $this->db->table('suppliers')->select('supplier_id, name')->where('is_active', 1)->get()->getResultArray();

        $primaryImage = $this->db->table('product_images')->where('product_id', $id)->where('is_primary', 1)->get()->getRow();
        $product->image_path = $primaryImage ? $primaryImage->image_path : null;
        $product->content = $this->db->table('product_info_content')->where('product_id', $id)->get()->getRow();

        return $product;
    }

    public function deleteProduct(int $productId): bool
    {
        $this->db->transStart();
        $this->db->table('inventory_batches')->where('product_id', $productId)->delete();
        $this->db->table('stock_adjustment_logs')->where('product_id', $productId)->delete();
        $this->db->table('supplier_product_catalog')->where('product_id', $productId)->delete();
        $this->db->table('products')->where('product_id', $productId)->delete();
        $this->db->transComplete();
        return $this->db->transStatus() !== false;
    }

    public function updateProductInfo(int $productId, array $post, ?string $barcode): ?string
{
    $finalBarcode = $barcode !== '' ? $barcode : $this->generateBarcode();

    // FIX: same empty-string → NULL conversion here
    $supplierId = (!empty($post['supplier_id'])) ? $post['supplier_id'] : null;

    $data = [
        'category_id'    => $post['category_id'] ?? null,
        'supplier_id'    => $supplierId,
        'name'           => $post['name'] ?? '',
        'description'    => $post['description'] ?? null,
        'barcode_value'  => $finalBarcode,
        'brand'          => $post['brand'] ?? null,
        'manufacturer'   => $post['manufacturer'] ?? null,
        'unit'           => $post['unit'] ?? 'piece',
        'is_vat_exempt'  => ($post['is_vat_exempt'] ?? false) ? 1 : 0,
        'notes'          => $post['notes'] ?? null,
    ];

    try {
        $this->db->table('products')->where('product_id', $productId)->update($data);
    } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
        return 'Could not update product: ' . $e->getMessage();
    }
    return null;
}

    public function createBatch(array $post): void
{
    $this->db->table('inventory_batches')->insert([
        'product_id'      => $post['product_id'] ?? null,
        'supplier_id'     => $post['supplier_id'] ?? null,
        'batch_number'    => $post['batch_number'] ?? '',
        'lot_number'      => $post['lot_number'] ?? null,
        'manufactured_at' => $post['manufactured_at'] ?? null,
        'quantity_in'     => $post['quantity'] ?? 0,
        'quantity_avail'  => $post['quantity'] ?? 0,
        'reorder_level'   => $post['reorder_level'] ?? 5,
        'cost_price'      => $post['cost_price'] ?? 0,
        'sell_price'      => $post['sell_price'] ?? 0,
        'expires_at'      => $post['expires_at'] ?? null,
    ]);
}

    public function saveProductInfoContent(int $productId, array $post): void
    {
        $fields = ['medical_description', 'usage_purpose', 'usage_guide', 'warnings', 'storage_info', 'healthcare_tips', 'warranty_info', 'video_url'];
        $contentData = [];
        $hasAny = false;
        foreach ($fields as $f) {
            $val = $post[$f] ?? null;
            $contentData[$f] = $val;
            if ($val !== null && trim((string) $val) !== '') $hasAny = true;
        }
        if (!$hasAny) return;

        $existing = $this->db->table('product_info_content')->where('product_id', $productId)->get()->getRow();
        if ($existing) {
            $this->db->table('product_info_content')->where('product_id', $productId)->update($contentData);
        } else {
            $contentData['product_id'] = $productId;
            $this->db->table('product_info_content')->insert($contentData);
        }
    }

    public function handleImageUpload($file, int $productId): void
    {
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'public/uploads/products', $newName);
            $imagePath = 'public/uploads/products/' . $newName;

            $this->db->table('product_images')->where('product_id', $productId)->update(['is_primary' => 0]);
            $this->db->table('product_images')->insert([
                'product_id' => $productId, 'image_path' => $imagePath, 'is_primary' => 1, 'sort_order' => 0,
            ]);
        }
    }

    public function adjustStock(array $post): void
{
    $this->db->transStart();
    $this->db->table('inventory_batches')->where('batch_id', $post['batch_id'] ?? null)->update(['quantity_avail' => $post['qty_after'] ?? 0]);
    $this->db->table('stock_adjustment_logs')->insert([
        'product_id'  => $post['product_id'] ?? null,
        'batch_id'    => $post['batch_id'] ?? null,
        'adjusted_by' => $post['adjusted_by'] ?? null,
        'qty_before'  => $post['qty_before'] ?? 0,
        'qty_after'   => $post['qty_after'] ?? 0,
        'reason'      => $post['reason'] ?? null,
        'notes'       => $post['notes'] ?? null,
    ]);
    $this->db->transComplete();
}

    public function getAdjustmentLogs(string $search, ?string $reasonFilter, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function($builder) use ($search, $reasonFilter) {
            if ($reasonFilter) $builder->where('sal.reason', $reasonFilter);
            if ($search !== '') {
                $builder->groupStart()->like('p.name', $search)->orLike('u.full_name', $search)->groupEnd();
            }
            return $builder;
        };

        $countBuilder = $this->db->table('stock_adjustment_logs as sal');
        $countBuilder->join('products as p', 'p.product_id = sal.product_id');
        $countBuilder->join('users as u', 'u.user_id = sal.adjusted_by');
        $apply($countBuilder);
        $totalRows = $countBuilder->countAllResults();

        $builder = $this->db->table('stock_adjustment_logs as sal');
        $builder->select('sal.log_id, sal.qty_before, sal.qty_after, sal.reason, sal.notes, sal.adjusted_at, p.name as product_name, u.full_name as staff_name');
        $builder->join('products as p', 'p.product_id = sal.product_id');
        $builder->join('users as u', 'u.user_id = sal.adjusted_by');
        $apply($builder);
        $builder->orderBy('sal.adjusted_at', 'DESC');
        $builder->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total_rows' => $totalRows, 'total_pages' => max(1, (int) ceil($totalRows / $perPage))];
    }

    public function getAdjustmentKpis(): array
    {
        return [
            'total_logs' => $this->db->table('stock_adjustment_logs')->countAllResults(),
            'recent_adjustments' => $this->db->table('stock_adjustment_logs')->where('adjusted_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->countAllResults(),
            'week_adjustments' => $this->db->table('stock_adjustment_logs')->where('adjusted_at >=', date('Y-m-d 00:00:00', strtotime('monday this week')))->countAllResults(),
            'loss_flagged' => $this->db->table('stock_adjustment_logs')->where('reason', 'Loss')->countAllResults(),
        ];
    }

    public function getLogDetails(int $id)
{
    $log = $this->db->table('stock_adjustment_logs as sal')
        ->select('sal.log_id, sal.qty_before, sal.qty_after, sal.reason, sal.notes, sal.adjusted_at, p.name as product_name, p.product_id, u.full_name, b.batch_number')
        ->join('products as p', 'p.product_id = sal.product_id')
        ->join('users as u', 'u.user_id = sal.adjusted_by')
        ->join('inventory_batches as b', 'b.batch_id = sal.batch_id', 'left')
        ->where('sal.log_id', $id)
        ->get()->getRow();

    if (!$log) return null;

    $primaryImage = $this->db->table('product_images')->where('product_id', $log->product_id)->where('is_primary', 1)->get()->getRow();
    $log->image_path = $primaryImage ? $primaryImage->image_path : null;

    return $log;
}

    public function getStoreInfo(): array
    {
        $rows = $this->db->table('store_settings')->get()->getResultArray();
        $info = [];
        foreach ($rows as $row) $info[$row['setting_key']] = $row['setting_value'];
        return $info;
    }
    public function getLastStockUpdateTime(): string
{
    $latestBatch = $this->db->table('inventory_batches')->selectMax('updated_at')->get()->getRow();
    $latestMovement = $this->db->table('stock_movements')->selectMax('moved_at')->get()->getRow();

    $times = array_filter([$latestBatch->updated_at ?? null, $latestMovement->moved_at ?? null]);
    return !empty($times) ? max($times) : '';
}
}