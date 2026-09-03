<?php

namespace App\Controllers\Supplier\Orders;

use App\Controllers\BaseController;
use App\Models\Supplier\Orders\PurchaseOrdersModel;

class PurchaseOrders extends BaseController
{
    protected $poModel;

    public function __construct()
    {
        $this->poModel = new PurchaseOrdersModel();
    }

    public function index()
    {
        $supplierId = session()->get('supplier_id');
        $tab = $this->request->getGet('tab') ?: 'pending';
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->poModel->getInbox($supplierId, $tab, $search, $page, 10);
        $kpis = $this->poModel->getKpis($supplierId);

        $data['pos'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['active_tab'] = $tab;
        $data['search'] = $search;
        $data['count_pending'] = $kpis['pending_ack'];
        $data['count_in_progress'] = $kpis['in_progress'];
        $data['count_completed'] = $kpis['completed'];

        $data['title'] = "Purchase Order Inbox";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "po_inbox";
        return view('pages/supplier/orders/po_inbox', $data);
    }

    public function get_po_details($id)
    {
        $supplierId = session()->get('supplier_id');
        $result = $this->poModel->getPoDetails((int) $id, $supplierId);
        if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'PO not found']);
        return $this->response->setJSON($result);
    }

    public function process_acknowledge()
    {
        $supplierId = session()->get('supplier_id');
        $poId = (int) $this->request->getPost('po_id');
        $confirmedDate = $this->request->getPost('confirmed_date');
        $notes = $this->request->getPost('notes');

        if (empty($confirmedDate)) {
            return redirect()->back()->with('error', 'Please confirm an expected delivery date.');
        }

        $success = $this->poModel->acknowledgePo($poId, $supplierId, $confirmedDate, $notes);
        return redirect()->to('supplier/orders/inbox')->with($success ? 'success' : 'error',
            $success ? 'Order acknowledged and confirmed.' : 'Unable to acknowledge — this order may already be processed.');
    }

    public function delivery()
    {
        $supplierId = session()->get('supplier_id');
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $result = $this->poModel->getDeliveryQueue($supplierId, $search, $page, 10);

        $data['orders'] = $result['data'];
        $data['total_pages'] = $result['total_pages'];
        $data['current_page'] = $page;
        $data['search'] = $search;

        $data['title'] = "Delivery Updates";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "delivery";
        return view('pages/supplier/orders/delivery', $data);
    }

    public function update_delivery()
    {
        $supplierId = session()->get('supplier_id');
        $poId = (int) $this->request->getPost('po_id');
        $drNumber = trim((string) $this->request->getPost('dr_number'));
        $dispatchDate = $this->request->getPost('dispatch_date');

        if (empty($drNumber)) {
            return redirect()->back()->with('error', 'Please provide a delivery reference / DR number.');
        }

        $success = $this->poModel->markDispatched($poId, $supplierId, $drNumber, $dispatchDate);
        return redirect()->to('supplier/orders/delivery')->with($success ? 'success' : 'error',
            $success ? 'Marked as in-transit. Robin Rose Trading has been notified.' : 'Unable to update — this order must be acknowledged first.');
    }
}