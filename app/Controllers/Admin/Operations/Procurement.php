<?php

namespace App\Controllers\Admin\Operations;

use App\Controllers\BaseController;
use App\Models\Admin\Operations\Procurement\SupplierModel;

class Procurement extends BaseController
{
    protected $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
    }

    public function suppliers()
    {
        $request = \Config\Services::request();
        $search = trim((string) $request->getGet('search'));
        $categoryFilter = $request->getGet('category');
        $page = max(1, (int) ($request->getGet('page') ?? 1));
        $perPage = 8;

        $result = $this->supplierModel->getSuppliers($search, $categoryFilter, $page, $perPage);

        $data['suppliers']  = $result['data'];
        $data['categories'] = $this->supplierModel->getCategories();

        $data['current_page'] = $page;
        $data['per_page']     = $perPage;
        $data['total_rows']   = $result['total_rows'];
        $data['total_pages']  = $result['total_pages'];
        $data['search'] = $search;
        $data['category_filter'] = $categoryFilter;

        $data['title'] = "Supplier Management";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "procurement";

        return view('pages/admin/operations/procurement/suppliers', $data);
    }

    public function get_supplier_products($supplierId)
    {
        $result = $this->supplierModel->getSupplierProducts((int) $supplierId);
        if (isset($result['error'])) {
            return $this->response->setStatusCode(404)->setJSON(['error' => $result['error']]);
        }
        return $this->response->setJSON($result);
    }

    public function get_supplier_details($id)
    {
        $supplier = $this->supplierModel->getSupplierDetails((int) $id);
        if (!$supplier) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }
        return $this->response->setJSON($supplier);
    }

    public function save_supplier()
    {
        $this->supplierModel->saveSupplier($this->request->getPost());
        return redirect()->to('admin/procurement/suppliers')->with('success', 'Supplier registered.');
    }

    public function purchase_orders()
    {
        $request = \Config\Services::request();
        $status = $request->getGet('status');
        $page = max(1, (int) ($request->getGet('page') ?? 1));
        $perPage = 10;

        $result = $this->supplierModel->getPurchaseOrders($status, $page, $perPage);
        $stats  = $this->supplierModel->getPOStats();

        $data['pos'] = $result['data'];
        $data['count_pending']            = $stats['count_pending'];
        $data['po_this_month']            = $stats['po_this_month'];
        $data['auto_reorders_this_month'] = $stats['auto_reorders_this_month'];
        $data['spend_this_month']         = $stats['spend_this_month'];

        $data['total_rows']   = $result['total_rows'];
        $data['current_page'] = $page;
        $data['total_pages']  = $result['total_pages'];
        $data['per_page']     = $perPage;
        $data['status_filter'] = $status;

        $data['title'] = "Purchase Orders";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "procurement";

        $data['categories'] = $this->supplierModel->getCategories();
        $data['walkin_products'] = $this->supplierModel->getAllActiveProducts();

        return view('pages/admin/operations/procurement/purchase_orders', $data);
    }

    public function save_po()
    {
        $result = $this->supplierModel->savePO($this->request->getPost());
        if (isset($result['error'])) {
            return redirect()->to('admin/procurement/suppliers')->with('error', $result['error']);
        }
        return redirect()->to('admin/procurement/purchase-orders')->with('success', 'Purchase Order Created.');
    }

    public function reject_po($id)
    {
        $result = $this->supplierModel->rejectPO((int) $id);
        if (isset($result['error'])) {
            return redirect()->to('admin/procurement/purchase-orders')->with('error', $result['error']);
        }
        return redirect()->to('admin/procurement/purchase-orders')->with('success', 'Purchase Order rejected and cancelled.');
    }

    public function approve_po($id)
    {
        $this->supplierModel->approvePO((int) $id);
        return redirect()->to('admin/procurement/purchase-orders')->with('success', 'PO Approved and Sent.');
    }

    public function get_po_details($id)
    {
        $result = $this->supplierModel->getPODetails((int) $id);
        if (isset($result['error'])) {
            return $this->response->setStatusCode(404)->setJSON($result);
        }
        return $this->response->setJSON($result);
    }

    public function goods_receipt()
    {
        $request = \Config\Services::request();
        $categoryFilter = $request->getGet('category');
        $page = max(1, (int) ($request->getGet('page') ?? 1));
        $perPage = 10;

        $result = $this->supplierModel->getGoodsReceiptList($categoryFilter, $page, $perPage);

        $data['pending_receipts'] = $result['data'];
        $data['categories'] = $this->supplierModel->getCategories();
        $data['category_filter'] = $categoryFilter;

        $data['current_page'] = $page;
        $data['per_page']     = $perPage;
        $data['total_rows']   = $result['total_rows'];
        $data['total_pages']  = $result['total_pages'];

        $data['title'] = "Goods Receipt Recording";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "procurement";
        return view('pages/admin/operations/procurement/goods_receipt', $data);
    }

    public function save_grr()
{
    $result = $this->supplierModel->saveGRR($this->request->getPost(), $this->request->getFile('delivery_photo'));
    if (isset($result['error'])) {
        return redirect()->to('admin/procurement/goods-receipt')->with('error', $result['error']);
    }
    return redirect()->to('admin/procurement/goods-receipt')->with('success', $result['message']);
}

    public function mark_paid()
{
    $poId = (int) $this->request->getPost('po_id');
    $method = $this->request->getPost('payment_method');
    $reference = trim((string) $this->request->getPost('payment_reference'));
    $adminId = session()->get('user_id') ?? 1;

    if (empty($method) || $reference === '') {
        return redirect()->back()->with('error', 'Please select a payment method and provide a reference number.');
    }

    $success = $this->supplierModel->markAsPaid($poId, $method, $reference, $adminId);
    return redirect()->to('admin/procurement/purchase-orders')->with($success ? 'success' : 'error',
        $success ? 'Payment recorded.' : 'Unable to record payment — this order may not be acknowledged yet, or is already paid.');
}

public function supplier_returns()
{
    $request = \Config\Services::request();
    $status = $request->getGet('status') ?: 'pending';
    $search = trim((string) $request->getGet('search'));
    $page = max(1, (int) ($request->getGet('page') ?? 1));
    $perPage = 10;

    $result = $this->supplierModel->getSupplierReturns($status, $search, $page, $perPage);

    $data['returns'] = $result['data'];
    $data['received_pos'] = $this->supplierModel->getReceivedPOs();
    $data['active_status'] = $status;
    $data['search'] = $search;
    $data['current_page'] = $page;
    $data['per_page'] = $perPage;
    $data['total_rows'] = $result['total'];
    $data['total_pages'] = $result['total_pages'];

    $data['title'] = "Supplier Returns";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "supplier-returns";
    return view('pages/admin/operations/procurement/supplier_returns', $data);
}

public function get_po_items_for_return($poId)
{
    return $this->response->setJSON($this->supplierModel->getPoItemsForReturn((int) $poId));
}

public function save_supplier_return()
{
    $this->supplierModel->saveSupplierReturn($this->request->getPost(), session()->get('user_id') ?? 1);
    return redirect()->to('admin/procurement/supplier-returns')->with('success', 'Return request submitted for approval.');
}

public function approve_supplier_return($id)
{
    $success = $this->supplierModel->approveSupplierReturn((int) $id, session()->get('user_id') ?? 1);
    return redirect()->to('admin/procurement/supplier-returns')->with($success ? 'success' : 'error',
        $success ? 'Return approved — stock deducted.' : 'Unable to approve this return.');
}

public function reject_supplier_return($id)
{
    $success = $this->supplierModel->rejectSupplierReturn((int) $id, session()->get('user_id') ?? 1);
    return redirect()->to('admin/procurement/supplier-returns')->with($success ? 'success' : 'error',
        $success ? 'Return rejected.' : 'Unable to reject this return.');
}

public function get_supplier_return_details($id)
{
    $details = $this->supplierModel->getSupplierReturnDetails((int) $id);
    if (!$details) return $this->response->setStatusCode(404)->setJSON(['error' => 'Return not found']);
    return $this->response->setJSON($details);
}

public function create_replacement_po($id)
{
    $result = $this->supplierModel->createReplacementPO((int) $id, session()->get('user_id') ?? 1);
    return redirect()->to('admin/procurement/supplier-returns')->with(isset($result['error']) ? 'error' : 'success',
        $result['error'] ?? 'Replacement PO created and sent to supplier.');
}

public function mark_return_sent($id)
{
    $success = $this->supplierModel->markReturnSentToSupplier((int) $id);
    return redirect()->to('admin/procurement/supplier-returns')->with($success ? 'success' : 'error',
        $success ? 'Marked as sent back to supplier.' : 'Unable to update this return.');
}

public function save_walkin_po()
{
    $guestModel = new \App\Models\Admin\GuestPartyModel();
    $post = $this->request->getPost();

    if (empty(trim($post['guest_name'] ?? ''))) {
        return redirect()->back()->withInput()->with('error', 'Please provide the supplier name.');
    }

    $guestSupplierId = $guestModel->findOrCreateGuestSupplier([
        'name' => $post['guest_name'], 'contact_person' => $post['guest_contact'] ?? '',
        'phone' => $post['guest_phone'] ?? '', 'email' => $post['guest_email'] ?? '',
        'address' => $post['guest_address'] ?? '', 'tin' => $post['guest_tin'] ?? '',
    ]);

    $result = $this->supplierModel->saveWalkInPO($guestSupplierId, $post);
    if (isset($result['error'])) {
        return redirect()->back()->withInput()->with('error', $result['error']);
    }
    return redirect()->to('admin/procurement/purchase-orders')->with('success', 'Walk-in Purchase completed and added to inventory.');
}

}