<?php

namespace App\Controllers\Staff\Operations;

use App\Controllers\BaseController;
use App\Models\Staff\Operations\GoodsReceiptModel;

class GoodsReceipt extends BaseController
{
    protected $grrModel;

    public function __construct()
    {
        $this->grrModel = new GoodsReceiptModel();
    }

    public function index()
    {
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->grrModel->getPendingDeliveries($search, $page, 10);

        $data['deliveries'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['search'] = $search;

        $data['title'] = "Inbound Verification (GRR)";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "grr";
        return view('pages/staff/operations/goods_receipt', $data);
    }

    public function get_po_items($poId)
    {
        $result = $this->grrModel->getPoItemsForInspection((int) $poId);
        if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'PO not found']);
        return $this->response->setJSON($result);
    }

    public function save_grr()
    {
        $data = [
            'po_id'        => (int) $this->request->getPost('po_id'),
            'product_ids'  => $this->request->getPost('product_ids'),
            'qty_received' => $this->request->getPost('qty_received'),
            'qty_expected' => $this->request->getPost('qty_expected'),
            'unit_costs'   => $this->request->getPost('unit_costs'),
            'lot_numbers'  => $this->request->getPost('lot_numbers'),
            'expires_ats'  => $this->request->getPost('expires_ats'),
            'sell_prices'  => $this->request->getPost('sell_prices'),
            'delivery_ref' => trim((string) $this->request->getPost('delivery_ref')),
            'notes'        => trim((string) $this->request->getPost('notes')),
        ];

        $result = $this->grrModel->saveGrr($data, session()->get('user_id'));

        return redirect()->to('staff/operations/goods-receipt')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}