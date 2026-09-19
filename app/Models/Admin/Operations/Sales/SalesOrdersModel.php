<?php

namespace App\Models\Admin\Operations\Sales;

use CodeIgniter\Model;

class SalesOrdersModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getCategories(): array
    {
        return $this->db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    public function getSellableProducts(): array
    {
        return $this->db->table('products as p')
            ->select("p.product_id, p.name, p.unit, p.category_id, p.is_vat_exempt,
                (SELECT COALESCE(SUM(quantity_avail),0) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
                (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as latest_sell_price")
            ->where('p.is_active', 1)
            ->orderBy('p.name', 'ASC')
            ->get()->getResultArray();
    }

    public function getSchoolDiscountRate(): float
    {
        $row = $this->db->table('store_settings')->where('setting_key', 'school_discount_rate')->get()->getRow();
        return $row ? (float) $row->setting_value : 10;
    }

    public function getOrders(string $search, string $type, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($search, $type) {
            if ($search !== '') {
                $b->groupStart()->like('so.order_number', $search)->orLike('ic.organization', $search)->orLike('gc.name', $search)->groupEnd();
            }
            if ($type === 'hospital_clinic') {
                $b->whereIn('ic.client_type', ['hospital', 'clinic']);
            } elseif ($type === 'lgu_sk') {
                $b->whereIn('ic.client_type', ['lgu', 'sk']);
            } elseif ($type === 'walkin') {
                $b->where('so.guest_client_id IS NOT NULL', null, false);
            } elseif ($type !== '') {
                $b->where('ic.client_type', $type);
            }
            return $b;
        };

        $countBuilder = $this->db->table('sales_orders as so')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('sales_orders as so')
            ->select("so.*, COALESCE(ic.organization, gc.name) as client_name, ic.client_type, so.guest_client_id, (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count")
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left');
        $apply($builder);
        $builder->orderBy('so.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getOrderDetails(int $id)
    {
        $order = $this->db->table('sales_orders as so')
            ->select('so.*, so.guest_client_id, COALESCE(ic.organization, gc.name) as organization, COALESCE(ic.address, gc.address) as client_addr, COALESCE(ic.phone, gc.phone) as phone, u.full_name as encoder')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
            ->join('users as u', 'u.user_id = so.created_by', 'left')
            ->where('so.order_id', $id)->get()->getRow();

        if (!$order) return null;

        $items = $this->db->table('sales_order_items as soi')
            ->select('soi.*, p.name, p.barcode_value')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->where('soi.order_id', $id)->get()->getResultArray();

        $settingsRows = $this->db->table('store_settings')->get()->getResultArray();
        $storeInfo = [];
        foreach ($settingsRows as $row) $storeInfo[$row['setting_key']] = $row['setting_value'];

        return ['order' => $order, 'items' => $items, 'store_info' => $storeInfo];
    }

    public function saveOrder(array $post, int $createdBy): array
    {
        $isWalkIn = ($post['order_mode'] ?? '') === 'walkin';
        $items = $post['items'] ?? [];
        $qtys = $post['qtys'] ?? [];
        $discountType = $post['discount_type'] ?: 'none';
        $customPercent = (float) ($post['discount_percent'] ?? 0);
        $fulfillmentType = ($post['fulfillment_type'] ?? '') === 'pickup' ? 'pickup' : 'delivery';

        if (empty($items)) return ['success' => false, 'message' => 'Please select at least one product.'];

        $guestClientId = null;
        $clientId = null;

        if ($isWalkIn) {
            $guestName = trim((string) ($post['guest_name'] ?? ''));
            if ($guestName === '') return ['success' => false, 'message' => 'Please provide the client/customer name.'];
            $guestModel = new \App\Models\Admin\GuestPartyModel();
            $guestClientId = $guestModel->findOrCreateGuestClient([
                'name' => $guestName, 'contact_person' => $post['guest_contact'] ?? null,
                'phone' => $post['guest_phone'] ?? null, 'email' => $post['guest_email'] ?? null,
                'address' => $post['guest_address'] ?? null,
            ]);
        } else {
            $clientId = $post['client_id'] ?? null;
            if (empty($clientId)) return ['success' => false, 'message' => 'Please select a client.'];
            $client = $this->db->table('institutional_clients')->where('client_id', $clientId)->where('user_id IS NOT NULL', null, false)->get()->getRow();
            if (!$client) return ['success' => false, 'message' => 'Selected client is not a registered account.'];
        }

        $this->db->transStart();

        $this->db->table('sales_orders')->insert([
            'client_id'            => $isWalkIn ? null : $clientId,
            'guest_client_id'      => $guestClientId,
            'order_number'         => 'SO-' . date('Y') . '-' . mt_rand(1000, 9999),
            'invoice_number'       => 'INV-' . date('Y') . '-' . mt_rand(1000, 9999),
            'status'               => 'pending',
            'fulfillment_type'     => $fulfillmentType,
            'payment_method'       => $post['payment_method'] ?? 'cash',
            'delivery_address'     => $fulfillmentType === 'delivery' ? ($post['address'] ?? null) : null,
            'payment_status'       => 'unpaid',
            'discount'             => 0,
            'discount_type'        => $discountType,
            'discount_id_number'   => trim((string) ($post['discount_id_number'] ?? '')) ?: null,
            'discount_holder_name' => trim((string) ($post['discount_holder_name'] ?? '')) ?: null,
            'subtotal'             => 0,
            'vat_amount'           => 0,
            'total'                => 0,
            'created_by'           => $createdBy,
        ]);
        $orderId = $this->db->insertID();

        $grossTotal = 0;
        $productsOrdered = [];
        $cappedCount = 0;

        foreach ($items as $index => $pid) {
            $qty = (int) ($qtys[$index] ?? 0);
            if ($qty <= 0) continue;

            $batch = $this->db->table('inventory_batches')->where('product_id', $pid)->where('quantity_avail >', 0)->orderBy('expires_at', 'ASC')->get()->getRow();
            if (!$batch) continue;

            if ($qty > $batch->quantity_avail) { $qty = $batch->quantity_avail; $cappedCount++; }
            if ($qty <= 0) continue;

            $price = $batch->sell_price;
            $lineTotal = $price * $qty;
            $grossTotal += $lineTotal;

            $this->db->table('sales_order_items')->insert([
                'order_id' => $orderId, 'product_id' => $pid, 'batch_id' => $batch->batch_id,
                'quantity' => $qty, 'unit_price' => $price, 'subtotal' => $lineTotal,
            ]);

            $this->db->table('inventory_batches')->where('batch_id', $batch->batch_id)
                ->set('quantity_avail', "quantity_avail - {$qty}", false)->update();

            $this->db->table('stock_movements')->insert([
                'product_id' => $pid, 'batch_id' => $batch->batch_id, 'movement_type' => 'outbound',
                'quantity' => $qty, 'reference_id' => $orderId, 'reference_type' => 'order',
                'scanned_by' => $createdBy, 'scan_mode' => 'outbound_order',
            ]);

            $productsOrdered[] = $pid;
        }

        [$discountAmount, $subtotal, $vatAmount, $netTotal] = $this->computeTotals($grossTotal, $discountType, $customPercent);

        $this->db->table('sales_orders')->where('order_id', $orderId)->update([
            'discount' => $discountAmount, 'subtotal' => $subtotal, 'vat_amount' => $vatAmount, 'total' => $netTotal,
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) return ['success' => false, 'message' => 'Failed to create order.'];

        foreach (array_unique($productsOrdered) as $pid) \App\Libraries\AutoReorder::check($pid);

        $msg = 'Sales order created successfully.' . ($cappedCount > 0 ? " Note: {$cappedCount} item(s) were reduced to match available stock." : '');
        return ['success' => true, 'message' => $msg];
    }

    private function computeTotals(float $gross, string $discountType, float $customPercent): array
    {
        if ($discountType === 'pwd' || $discountType === 'senior') {
            $vatExclusive = $gross / 1.12;
            $discount = $vatExclusive * 0.20;
            $net = $vatExclusive - $discount;
            return [$discount, $net, 0, $net];
        }
        $percent = 0;
        if ($discountType === 'school') $percent = $this->getSchoolDiscountRate();
        elseif ($discountType === 'custom') $percent = $customPercent;

        $discount = $gross * ($percent / 100);
        $net = $gross - $discount;
        $vat = $net - ($net / 1.12);
        $subtotal = $net / 1.12;
        return [$discount, $subtotal, $vat, $net];
    }

    public function updateStatus(int $orderId, string $newStatus): void
    {
        $this->db->table('sales_orders')->where('order_id', $orderId)->update(['status' => $newStatus]);
    }

    // ============ NEW: edit a pending order's item — swap product and/or
    // quantity, reversing stock on the old product and deducting on the new
    // one. Only ever callable while the order is still 'pending'. ============
    public function updateOrderItem(int $itemId, int $newProductId, int $newQty): array
    {
        $item = $this->db->table('sales_order_items as soi')
            ->select('soi.*, so.status')
            ->join('sales_orders as so', 'so.order_id = soi.order_id')
            ->where('soi.item_id', $itemId)->get()->getRow();

        if (!$item) return ['success' => false, 'message' => 'Item not found.'];
        if ($item->status !== 'pending') return ['success' => false, 'message' => 'This order is no longer pending — items can only be changed before dispatch or pickup.'];
        if ($newQty <= 0) return ['success' => false, 'message' => 'Quantity must be at least 1.'];

        $newBatch = $this->db->table('inventory_batches')->where('product_id', $newProductId)->where('quantity_avail >', 0)->orderBy('expires_at', 'ASC')->get()->getRow();
        if (!$newBatch || $newBatch->quantity_avail < $newQty) {
            return ['success' => false, 'message' => 'Not enough stock available for the replacement product.'];
        }

        $this->db->transStart();

        // Reverse stock on the old product/batch
        if ($item->batch_id) {
            $this->db->table('inventory_batches')->where('batch_id', $item->batch_id)
                ->set('quantity_avail', "quantity_avail + {$item->quantity}", false)->update();
        }

        // Deduct stock on the new product/batch
        $this->db->table('inventory_batches')->where('batch_id', $newBatch->batch_id)
            ->set('quantity_avail', "quantity_avail - {$newQty}", false)->update();

        $newSubtotal = $newBatch->sell_price * $newQty;
        $this->db->table('sales_order_items')->where('item_id', $itemId)->update([
            'product_id' => $newProductId, 'batch_id' => $newBatch->batch_id,
            'quantity' => $newQty, 'unit_price' => $newBatch->sell_price, 'subtotal' => $newSubtotal,
        ]);

        $this->db->table('stock_movements')->insert([
            'product_id' => $newProductId, 'batch_id' => $newBatch->batch_id, 'movement_type' => 'outbound',
            'quantity' => $newQty, 'reference_id' => $item->order_id, 'reference_type' => 'order',
            'scanned_by' => session()->get('user_id') ?? 1, 'scan_mode' => 'order_item_swap',
        ]);

        // Recompute order totals from scratch, same discount logic as save
        $order = $this->db->table('sales_orders')->where('order_id', $item->order_id)->get()->getRow();
        $allItems = $this->db->table('sales_order_items')->where('order_id', $item->order_id)->get()->getResultArray();
        $gross = array_sum(array_column($allItems, 'subtotal'));

        [$discountAmount, $subtotal, $vatAmount, $netTotal] = $this->computeTotals(
            $gross, $order->discount_type ?? 'none', 0
        );

        $this->db->table('sales_orders')->where('order_id', $item->order_id)->update([
            'discount' => $discountAmount, 'subtotal' => $subtotal, 'vat_amount' => $vatAmount, 'total' => $netTotal,
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) return ['success' => false, 'message' => 'Failed to update item.'];

        \App\Libraries\AutoReorder::check($newProductId);
        return ['success' => true, 'message' => 'Item updated successfully.'];
    }
}