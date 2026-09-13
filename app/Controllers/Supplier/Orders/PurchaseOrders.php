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
    $result['store_info'] = $this->poModel->getStoreInfo(); // needed for the printable summary, below
    return $this->response->setJSON($result);
}

    public function acknowledge_form($id)
{
    $supplierId = session()->get('supplier_id');
    $result = $this->poModel->getPoForAcknowledge((int) $id, $supplierId);
    if (!$result) return redirect()->to('supplier/orders/inbox')->with('error', 'Order not found or already processed.');

    $data['po'] = $result['po'];
    $data['items'] = $result['items'];
    $data['title'] = "Acknowledge Purchase Order";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "po_inbox";
    return view('pages/supplier/orders/acknowledge_po', $data);
}

public function process_acknowledge()
{
    $supplierId = session()->get('supplier_id');
    $poId = (int) $this->request->getPost('po_id');
    $confirmedDate = $this->request->getPost('confirmed_date');
    $notes = $this->request->getPost('notes');
    $poiIds = $this->request->getPost('poi_id') ?: [];
    $itemQtys = $this->request->getPost('item_qty') ?: [];

    if (empty($confirmedDate)) {
        return redirect()->back()->withInput()->with('error', 'Please confirm an expected delivery date.');
    }

    $success = $this->poModel->acknowledgePo($poId, $supplierId, $confirmedDate, $notes, $poiIds, $itemQtys);
    return redirect()->to('supplier/orders/inbox')->with($success ? 'success' : 'error',
        $success ? 'Order acknowledged and confirmed.' : 'Unable to acknowledge — this order may already be processed.');
}

    public function process_decline()
    {
        $supplierId = session()->get('supplier_id');
        $poId = (int) $this->request->getPost('po_id');
        $reason = trim((string) $this->request->getPost('reason'));

        if ($reason === '') {
            return redirect()->back()->with('error', 'Please provide a reason for declining this order.');
        }

        $success = $this->poModel->declinePo($poId, $supplierId, $reason);
        return redirect()->to('supplier/orders/inbox')->with($success ? 'success' : 'error',
            $success ? 'Order declined. Robin Rose Trading has been notified.' : 'Unable to decline — this order may already be processed.');
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
    $carrierName = trim((string) $this->request->getPost('carrier_name'));
    $driverContact = trim((string) $this->request->getPost('driver_contact'));

    if (empty($drNumber)) {
        return redirect()->back()->with('error', 'Please provide a delivery reference / DR number.');
    }

    $success = $this->poModel->markDispatched($poId, $supplierId, $drNumber, $dispatchDate, $carrierName, $driverContact);

    if (!$success) {
        return redirect()->to('supplier/orders/delivery')->with('error', 'Unable to update — this order must be acknowledged and payment must be completed by Robin Rose Trading first.');
    }

    return redirect()->to('supplier/orders/inbox?tab=in_progress')->with('success', 'Marked as in-transit. Robin Rose Trading has been notified.');
}

public function payments()
{
    $supplierId = session()->get('supplier_id');
    $search = trim((string) ($this->request->getGet('search') ?? ''));
    $page = (int) ($this->request->getGet('page') ?? 1);

    $result = $this->poModel->getPaymentHistory($supplierId, $search, $page, 10);
    $kpis = $this->poModel->getPaymentKpis($supplierId);

    $data['payments'] = $result['data'];
    $data['total_pages'] = $result['total_pages'];
    $data['current_page'] = $page;
    $data['search'] = $search;
    $data['total_paid'] = $kpis['total_paid'];
    $data['count_paid'] = $kpis['count_paid'];
    $data['paid_this_month'] = $kpis['paid_this_month'];

    $data['title'] = "Payment History";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "payments";
    return view('pages/supplier/orders/payments', $data);
}


}