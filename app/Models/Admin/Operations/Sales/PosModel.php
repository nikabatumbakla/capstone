<?php

namespace App\Models\Admin\Operations\Sales;

use CodeIgniter\Model;

class PosModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }


    public function getCategories(): array
    {
        return $this->db->table('categories')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()->getResultArray();
    }

    public function getStoreSettings(): array
    {
        $rows = $this->db->table('store_settings')->get()->getResultArray();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function getVatRate(): float
    {
        $row = $this->db->table('store_settings')->where('setting_key', 'vat_rate')->get()->getRow();
        return $row ? (float) $row->setting_value : 12.0;
    }

    public function getSellableProducts(): array
{
    return $this->db->table('products as p')
        ->select("p.product_id, p.name, p.barcode_value, p.unit, p.category_id, p.is_vat_exempt, p.brand,
            (SELECT ib.batch_id FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as batch_id,
            (SELECT ib.batch_number FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as batch_number,
            (SELECT ib.expires_at FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as expires_at,
            (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as sell_price,
            (SELECT SUM(ib2.quantity_avail) FROM inventory_batches ib2 WHERE ib2.product_id = p.product_id) as quantity_avail")
        ->where('p.is_active', 1)
        ->orderBy('p.name', 'ASC')
        ->get()->getResultArray();
}

public function searchSellableProducts(string $query): array
{
    return $this->db->table('products as p')
        ->select('p.product_id, p.name, p.barcode_value, ib.batch_id, ib.batch_number, ib.expires_at, ib.quantity_avail, ib.sell_price, c.name as cat_name')
        ->join('inventory_batches as ib', 'ib.product_id = p.product_id')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->where('ib.quantity_avail >', 0)
        ->groupStart()
            ->like('p.name', $query)
            ->orLike('p.barcode_value', $query)
        ->groupEnd()
        ->orderBy('ib.expires_at', 'ASC')
        ->get()->getResultArray();
}


    public function getTodaySummary(): array
    {
        $today = date('Y-m-d');

        $totalTxns = $this->db->table('pos_transactions')
            ->where('DATE(created_at)', $today)
            ->where('status', 'completed')
            ->countAllResults();

        $gross = $this->db->table('pos_transactions')
            ->selectSum('total')
            ->where('DATE(created_at)', $today)
            ->where('status', 'completed')
            ->get()->getRow()->total ?? 0;

        $cash = $this->db->table('pos_transactions')
            ->selectSum('total')
            ->where(['DATE(created_at)' => $today, 'payment_method' => 'cash', 'status' => 'completed'])
            ->get()->getRow()->total ?? 0;

        $gcash = $this->db->table('pos_transactions')
            ->selectSum('total')
            ->where(['DATE(created_at)' => $today, 'payment_method' => 'gcash', 'status' => 'completed'])
            ->get()->getRow()->total ?? 0;

        return [
            'total_txns'  => (int) $totalTxns,
            'gross_sales' => (float) $gross,
            'cash_sales'  => (float) $cash,
            'gcash_sales' => (float) $gcash,
        ];
    }

    public function getTodayTransactions(int $limit = 50): array
    {
        return $this->db->table('pos_transactions as pt')
            ->select("pt.txn_id, pt.or_number, pt.txn_number, pt.customer_name, pt.payment_method,
                pt.total, pt.status, pt.created_at,
                (SELECT COUNT(*) FROM pos_transaction_items WHERE txn_id = pt.txn_id) as item_count")
            ->where('DATE(pt.created_at)', date('Y-m-d'))
            ->orderBy('pt.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function validateCartItems(array $items): array
    {
        $vatableGross = 0.0;
        $exemptGross  = 0.0;
        $validated    = [];

        foreach ($items as $item) {
            $qty = (int) ($item['qty'] ?? 0);
            if ($qty <= 0) {
                throw new \RuntimeException('Invalid quantity for one or more items.');
            }

            $batch = $this->db->table('inventory_batches as ib')
                ->select('ib.batch_id, ib.quantity_avail, ib.sell_price, ib.product_id, ib.batch_number, p.is_vat_exempt, p.name')
                ->join('products as p', 'p.product_id = ib.product_id')
                ->where('ib.batch_id', $item['batch_id'] ?? 0)
                ->get()->getRow();

            if (!$batch || $batch->quantity_avail < $qty) {
                throw new \RuntimeException('Insufficient stock for one or more items. Please refresh and try again.');
            }

            $lineTotal = $batch->sell_price * $qty;
            if ($batch->is_vat_exempt) {
                $exemptGross += $lineTotal;
            } else {
                $vatableGross += $lineTotal;
            }

            $validated[] = [
                'product_id'   => $batch->product_id,
                'batch_id'     => $batch->batch_id,
                'batch_number' => $batch->batch_number,
                'name'         => $batch->name,
                'qty'          => $qty,
                'price'        => (float) $batch->sell_price,
                'subtotal'     => (float) $lineTotal,
            ];
        }

        return [$validated, $vatableGross, $exemptGross];
    }

    public function computeTotals(float $vatableGross, float $exemptGross, string $discountType, float $vatRate): array
    {
        $gross      = $vatableGross + $exemptGross;
        $vatDivisor = 1 + ($vatRate / 100);

        if ($discountType === 'pwd' || $discountType === 'senior') {
            $vatExclusiveBase = ($vatableGross / $vatDivisor) + $exemptGross;
            $discountAmount   = $vatExclusiveBase * 0.20;
            $netTotal         = $vatExclusiveBase - $discountAmount;
            $vatAmount        = 0.0;
            $subtotal         = $netTotal;
        } else {
            $discountAmount = 0.0;
            $vatAmount      = $vatableGross - ($vatableGross / $vatDivisor);
            $subtotal       = ($vatableGross / $vatDivisor) + $exemptGross;
            $netTotal       = $gross;
        }

        return [
            'gross'          => $gross,
            'discountAmount' => $discountAmount,
            'vatAmount'      => $vatAmount,
            'subtotal'       => $subtotal,
            'netTotal'       => $netTotal,
        ];
    }

    public function saveTransaction(array $header, array $validatedItems): int
    {
        $this->db->transStart();

        $this->db->table('pos_transactions')->insert($header);
        $txnId = $this->db->insertID();

        foreach ($validatedItems as $item) {
            $this->db->table('pos_transaction_items')->insert([
                'txn_id'     => $txnId,
                'product_id' => $item['product_id'],
                'batch_id'   => $item['batch_id'],
                'quantity'   => $item['qty'],
                'unit_price' => $item['price'],
                'subtotal'   => $item['subtotal'],
            ]);

            $this->db->table('inventory_batches')->where('batch_id', $item['batch_id'])
                ->set('quantity_avail', "quantity_avail - {$item['qty']}", false)->update();

            $this->db->table('stock_movements')->insert([
                'product_id'     => $item['product_id'],
                'batch_id'       => $item['batch_id'],
                'movement_type'  => 'pos_sale',
                'quantity'       => $item['qty'],
                'reference_id'   => $txnId,
                'reference_type' => 'pos',
                'scanned_by'     => $header['cashier_id'],
                'scan_mode'      => 'pos',
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Transaction failed. Please try again.');
        }

        return $txnId;
    }

    public function getUpdatedBatchStocks(array $batchIds): array
    {
        $out = [];
        foreach (array_unique($batchIds) as $bid) {
            $row = $this->db->table('inventory_batches')
                ->select('batch_id, quantity_avail')
                ->where('batch_id', $bid)
                ->get()->getRow();
            if ($row) {
                $out[] = ['batch_id' => (int) $row->batch_id, 'quantity_avail' => (int) $row->quantity_avail];
            }
        }
        return $out;
    }

    public function getTransactionById(int $txnId)
    {
        return $this->db->table('pos_transactions as pt')
            ->select('pt.*, u.full_name as cashier_name')
            ->join('users as u', 'u.user_id = pt.cashier_id', 'left')
            ->where('pt.txn_id', $txnId)
            ->get()->getRow();
    }

    public function getReceiptData(int $txnId): ?array
    {
        $txn = $this->getTransactionById($txnId);
        if (!$txn) {
            return null;
        }

        $items = $this->db->table('pos_transaction_items as ti')
            ->select('ti.quantity as qty, ti.unit_price as price, ti.subtotal, p.name')
            ->join('products as p', 'p.product_id = ti.product_id')
            ->where('ti.txn_id', $txnId)
            ->get()->getResultArray();

        return [
            'txn'   => $txn,
            'items' => $items,
            'store' => $this->getStoreSettings(),
        ];
    }
}