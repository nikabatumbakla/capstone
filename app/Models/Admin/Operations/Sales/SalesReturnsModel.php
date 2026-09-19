<?php

namespace App\Models\Admin\Operations\Sales;

use CodeIgniter\Model;

class SalesReturnsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getReturns(string $search, string $status, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($search, $status) {
            if ($status !== 'all') $b->where('sr.status', $status);
            if ($search !== '') {
                $b->groupStart()
                    ->like('COALESCE(ic.organization, gc.name)', $search, 'both', null, true)
                    ->orLike('so.order_number', $search)
                    ->groupEnd();
            }
            return $b;
        };

        $countBuilder = $this->db->table('sales_returns as sr')
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('sales_returns as sr')
            ->select("sr.*, so.order_number, COALESCE(ic.organization, gc.name) as client_name, p.name as product_name, u.full_name as staff")
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
            ->join('products as p', 'p.product_id = sr.product_id', 'left')
            ->join('users as u', 'u.user_id = sr.processed_by', 'left');
        $apply($builder);
        $builder->orderBy('sr.created_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getDeliveredOrders(): array
    {
        return $this->db->table('sales_orders as so')
            ->select("so.order_id, so.order_number, COALESCE(ic.organization, gc.name) as organization")
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
            ->where('so.status', 'delivered')
            ->where("so.order_id NOT IN (SELECT order_id FROM sales_returns WHERE status != 'rejected')", null, false)
            ->get()->getResultArray();
    }

    public function getOrderItems(int $orderId): array
    {
        return $this->db->table('sales_order_items as soi')
            ->select("soi.product_id, soi.batch_id, soi.quantity, soi.unit_price, p.name, COALESCE(ic.organization, gc.name) as organization")
            ->join('products as p', 'p.product_id = soi.product_id')
            ->join('sales_orders as so', 'so.order_id = soi.order_id')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
            ->where('soi.order_id', $orderId)->get()->getResultArray();
    }

    public function getReturnDetails(int $id)
    {
        return $this->db->table('sales_returns as sr')
            ->select("sr.*, so.order_number, COALESCE(ic.organization, gc.name) as organization, p.name, ib.batch_number, ru.full_name as resolved_by_name")
            ->join('sales_orders as so', 'so.order_id = sr.order_id')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
            ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
            ->join('products as p', 'p.product_id = sr.product_id', 'left')
            ->join('inventory_batches as ib', 'ib.batch_id = sr.batch_id', 'left')
            ->join('users as ru', 'ru.user_id = sr.resolved_by', 'left')
            ->where('sr.return_id', $id)
            ->get()->getRow();
    }

    // Shared restock/replacement logic — used by BOTH the immediate-process
    // path (staff/admin filing directly) and the approve path (client-filed
    // returns going through the pending queue). Keeps both flows identical.
    public function applyResolution(int $returnId, object $ret): void
    {
        if ($ret->restock_condition === 'resellable' && $ret->batch_id) {
            $this->db->table('inventory_batches')->where('batch_id', $ret->batch_id)
                ->set('quantity_avail', "quantity_avail + {$ret->quantity}", false)->update();

            $this->db->table('stock_movements')->insert([
                'product_id' => $ret->product_id, 'batch_id' => $ret->batch_id, 'movement_type' => 'return_inbound',
                'quantity' => $ret->quantity, 'reference_id' => $ret->order_id, 'reference_type' => 'return',
                'scanned_by' => session()->get('user_id') ?? 1, 'reason' => 'Return resolved — restocked as resellable'
            ]);
        } else {
            $this->db->table('stock_movements')->insert([
                'product_id' => $ret->product_id, 'batch_id' => $ret->batch_id, 'movement_type' => 'adjustment',
                'quantity' => 0, 'reference_id' => $ret->order_id, 'reference_type' => 'return',
                'scanned_by' => session()->get('user_id') ?? 1, 'reason' => 'Return resolved — condition: ' . $ret->restock_condition . ' (not restocked)'
            ]);

            $this->db->table('sales_orders')->insert([
                'client_id'                 => $ret->client_id,
                'guest_client_id'           => $ret->guest_client_id,
                'order_number'              => 'SO-' . date('Y') . '-' . time(),
                'invoice_number'            => 'INV-' . date('Y') . '-' . time(),
                'status'                    => 'pending',
                'fulfillment_type'          => $ret->fulfillment_type,
                'payment_method'            => 'cash',
                'payment_status'            => 'paid',
                'discount'                  => 0,
                'subtotal'                  => 0,
                'vat_amount'                => 0,
                'total'                     => 0,
                'replacement_for_return_id' => $returnId,
                'notes'                     => 'Replacement for damaged/incorrect item — see Return #' . $returnId,
                'created_by'                => session()->get('user_id') ?? 1,
            ]);
            $newOrderId = $this->db->insertID();

            $this->db->table('sales_order_items')->insert([
                'order_id'   => $newOrderId,
                'product_id' => $ret->product_id,
                'batch_id'   => null,
                'quantity'   => $ret->quantity,
                'unit_price' => 0,
                'subtotal'   => 0,
            ]);
        }
    }
}