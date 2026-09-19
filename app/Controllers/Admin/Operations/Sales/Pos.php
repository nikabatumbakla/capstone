<?php

namespace App\Controllers\Admin\Operations\Sales;

use App\Controllers\BaseController;
use App\Models\Admin\Operations\Sales\PosModel;

class Pos extends BaseController
{
    protected $posModel;

    public function __construct()
    {
        $this->posModel = new PosModel();
    }

    public function pos()
    {
        $data['categories']  = $this->posModel->getCategories();
        $data['products']    = $this->posModel->getSellableProducts();
        $data['store_info']  = $this->posModel->getStoreSettings();
        $data['vat_rate']    = $this->posModel->getVatRate();

        $summary = $this->posModel->getTodaySummary();
        $data['total_txns']  = $summary['total_txns'];
        $data['gross_sales'] = $summary['gross_sales'];
        $data['cash_sales']  = $summary['cash_sales'];
        $data['gcash_sales'] = $summary['gcash_sales'];

        $data['history'] = $this->posModel->getTodayTransactions(50);

        $data['title'] = "Point of Sale";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "pos";
        return view('pages/admin/operations/sales/pos', $data);
    }

    public function pos_summary()
    {
        $daily   = $this->posModel->getTodaySummary();
        $history = $this->posModel->getTodayTransactions(50);

        return $this->response->setJSON([
            'status'      => 'success',
            'daily'       => $daily,
            'history'     => $history,
            'server_time' => date('h:i:s A'),
        ]);
    }

    public function pos_receipt($id)
    {
        $data = $this->posModel->getReceiptData((int) $id);
        if (!$data) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Receipt not found.']);
        }
        return $this->response->setJSON($data);
    }

    public function get_product_pos($query)
    {
        return $this->response->setJSON($this->posModel->searchSellableProducts($query));
    }

    public function process_pos()
    {
        $session = session();

        $items              = json_decode($this->request->getPost('items'), true) ?? [];
        $discountType       = $this->request->getPost('discount_type') ?: 'none';
        $discountIdNumber   = trim((string) $this->request->getPost('discount_id_number'));
        $discountHolderName = trim((string) $this->request->getPost('discount_holder_name'));
        $customerName       = trim((string) $this->request->getPost('customer_name'));
        $paymentMethod      = $this->request->getPost('payment_method');
        $tendered           = (float) $this->request->getPost('tendered');
        $gcashRef           = trim((string) $this->request->getPost('gcash_ref'));

        if (empty($items)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Cart is empty.']);
        }
        if ($paymentMethod === 'gcash' && $gcashRef === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'GCash reference number is required.']);
        }

        try {
            [$validatedItems, $vatableGross, $exemptGross] = $this->posModel->validateCartItems($items);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(422)->setJSON(['error' => $e->getMessage()]);
        }

        $vatRate = $this->posModel->getVatRate();
        $totals = $this->posModel->computeTotals($vatableGross, $exemptGross, $discountType, $vatRate);
        $netTotal = $totals['netTotal'];

        if ($paymentMethod === 'cash' && $tendered < $netTotal) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Amount tendered is less than the total due.']);
        }
        if ($paymentMethod === 'gcash') {
            $tendered = $netTotal;
        }

        $header = [
            'txn_number'           => 'TXN-' . date('Ymd') . '-' . mt_rand(1000, 9999),
            'cashier_id'           => $session->get('user_id') ?? 1,
            'customer_name'        => $customerName !== '' ? $customerName : null,
            'subtotal'             => $totals['subtotal'],
            'discount'             => $totals['discountAmount'],
            'discount_type'        => $discountType,
            'discount_id_number'   => $discountIdNumber !== '' ? $discountIdNumber : null,
            'discount_holder_name' => $discountHolderName !== '' ? $discountHolderName : null,
            'vat_amount'           => $totals['vatAmount'],
            'total'                => $netTotal,
            'payment_method'       => $paymentMethod,
            'gcash_ref'            => $paymentMethod === 'gcash' ? $gcashRef : null,
            'amount_tendered'      => $tendered,
            'change_due'           => $paymentMethod === 'cash' ? ($tendered - $netTotal) : 0,
            'or_number'            => 'OR-' . date('Ymd') . '-' . mt_rand(1000, 9999),
            'status'               => 'completed',
            'created_at'           => date('Y-m-d H:i:s'),
        ];

        try {
            $txnId = $this->posModel->saveTransaction($header, $validatedItems);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }

        foreach (array_unique(array_column($validatedItems, 'product_id')) as $pid) {
            \App\Libraries\AutoReorder::check($pid);
        }

        $updatedBatches = $this->posModel->getUpdatedBatchStocks(array_column($validatedItems, 'batch_id'));
        $daily   = $this->posModel->getTodaySummary();
        $history = $this->posModel->getTodayTransactions(50);
        $txn     = $this->posModel->getTransactionById($txnId);

        return $this->response->setJSON([
            'status'          => 'success',
            'txn'             => $txn,
            'items'           => $validatedItems,
            'updated_batches' => $updatedBatches,
            'daily'           => $daily,
            'history'         => $history,
        ]);
    }
}