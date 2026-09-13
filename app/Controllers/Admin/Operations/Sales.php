<?php

namespace App\Controllers\Admin\Operations;
use App\Controllers\BaseController;
use App\Models\Admin\Operations\Sales\PosModel;

class Sales extends BaseController
{
    protected $posModel;

    public function __construct()
    {
        $this->posModel = new \App\Models\Admin\Operations\Sales\PosModel();
    }

    public function clients()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();

    $search = trim((string) ($request->getGet('search') ?? ''));
    $type   = $request->getGet('type') ?? '';
    $status = $request->getGet('status') ?? '';

    $page    = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset  = ($page - 1) * $perPage;

    $baseQualified = function() use ($db) {
        return $db->table('institutional_clients as ic')
            ->join('users as u', 'u.user_id = ic.user_id')
            ->where('ic.is_active', 1)
            ->groupStart()
                ->where('ic.contact_person IS NOT NULL', null, false)->where('ic.contact_person !=', '')
                ->where('ic.phone IS NOT NULL', null, false)->where('ic.phone !=', '')
                ->where('ic.address IS NOT NULL', null, false)->where('ic.address !=', '')
            ->groupEnd();
    };

    $applyFilters = function($builder) use ($search, $type, $status) {
        if ($search !== '') {
            $builder->groupStart()
                ->like('ic.organization', $search)
                ->orLike('ic.contact_person', $search)
                ->groupEnd();
        }
        if ($type === 'hospital_clinic') {
            $builder->whereIn('ic.client_type', ['hospital', 'clinic']);
        } elseif ($type === 'lgu_sk') {
            $builder->whereIn('ic.client_type', ['lgu', 'sk']);
        } elseif ($type !== '') {
            $builder->where('ic.client_type', $type);
        }
        if ($status === 'unverified') {
            $builder->where('u.is_verified', 0);
        } elseif ($status === 'no_orders') {
            $builder->where("ic.client_id NOT IN (SELECT DISTINCT client_id FROM sales_orders)", null, false);
        }
        return $builder;
    };

    // KPIs — same qualification rules as the table, filters folded in the same way
    $data['count_total']      = $baseQualified()->countAllResults();
    $data['count_unverified'] = $baseQualified()->where('u.is_verified', 0)->countAllResults();
    $data['count_no_orders']  = $baseQualified()->where("ic.client_id NOT IN (SELECT DISTINCT client_id FROM sales_orders)", null, false)->countAllResults();
    $data['count_types']      = count(
        $db->table('institutional_clients')->select('client_type')->where('is_active', 1)->distinct()->get()->getResultArray()
    );

    $countBuilder = $baseQualified();
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $baseQualified();
    $builder->select('ic.*, u.email as login_email, u.is_verified');
    $applyFilters($builder);
    $builder->orderBy('ic.organization', 'ASC');
    $builder->limit($perPage, $offset);
    $data['clients'] = $builder->get()->getResultArray();

    $data['total_rows']   = $totalRows;
    $data['current_page'] = $page;
    $data['per_page']     = $perPage;
    $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));
    $data['search']       = $search;
    $data['type_filter']  = $type;
    $data['status_filter'] = $status;

    $data['categories'] = $db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
    $data['products'] = $db->table('products as p')
        ->select("p.product_id, p.name, p.unit, p.category_id, p.is_vat_exempt,
            (SELECT COALESCE(SUM(quantity_avail),0) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
            (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id AND ib.quantity_avail > 0 ORDER BY ib.expires_at ASC LIMIT 1) as latest_sell_price")
        ->where('p.is_active', 1)
        ->orderBy('p.name', 'ASC')
        ->get()->getResultArray();
    $rateRow = $db->table('store_settings')->where('setting_key', 'school_discount_rate')->get()->getRow();
    $data['school_discount_rate'] = $rateRow ? (float) $rateRow->setting_value : 10;

    $data['title'] = "Client Directory";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "sales";
    return view('pages/admin/operations/sales/institutional_clients', $data);
}

    public function get_client_details($id)
{
    $db = \Config\Database::connect();
    $client = $db->table('institutional_clients as ic')
        ->select('ic.*, u.email as login_email, u.is_verified')
        ->join('users as u', 'u.user_id = ic.user_id', 'left')
        ->where('ic.client_id', $id)
        ->get()->getRow();

    if (!$client) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not Found']);

    $orders = $db->table('sales_orders')
        ->where('client_id', $id)
        ->orderBy('created_at', 'DESC')
        ->limit(10)
        ->get()->getResultArray();

    return $this->response->setJSON(['client' => $client, 'orders' => $orders]);
}


    public function get_client_history($id)
    {
        $db = \Config\Database::connect();
        $client = $db->table('institutional_clients')->where('client_id', $id)->get()->getRow();
        $orders = $db->table('sales_orders')->where('client_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray();
        
        return $this->response->setJSON(['client' => $client, 'orders' => $orders]);
    }
public function orders()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();

    $search = trim((string) ($request->getGet('search') ?? ''));
    $type   = $request->getGet('type') ?? '';
    $page    = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset  = ($page - 1) * $perPage;

    $applyFilters = function($builder) use ($search, $type) {
        if ($search !== '') {
            $builder->groupStart()->like('so.order_number', $search)->orLike('ic.organization', $search)->orLike('gc.name', $search)->groupEnd();
        }
        if ($type === 'hospital_clinic') {
            $builder->whereIn('ic.client_type', ['hospital', 'clinic']);
        } elseif ($type === 'lgu_sk') {
            $builder->whereIn('ic.client_type', ['lgu', 'sk']);
        } elseif ($type === 'walkin') {
            $builder->where('so.guest_client_id IS NOT NULL', null, false);
        } elseif ($type !== '') {
            $builder->where('ic.client_type', $type);
        }
        return $builder;
    };

    $countBuilder = $db->table('sales_orders as so')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
        ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left');
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('sales_orders as so');
    $builder->select("so.*, COALESCE(ic.organization, gc.name) as client_name, ic.client_type, so.guest_client_id, (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count");
    $builder->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left');
    $builder->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left');
    $applyFilters($builder);
    $builder->orderBy('so.created_at', 'DESC');
    $builder->limit($perPage, $offset);
    $data['orders'] = $builder->get()->getResultArray();

    $data['total_rows']   = $totalRows;
    $data['current_page'] = $page;
    $data['per_page']     = $perPage;
    $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));
    $data['search']       = $search;
    $data['type_filter']  = $type;

    $data['title'] = "Sales Orders";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "sales";
    return view('pages/admin/operations/sales/sales_orders', $data);
}
    public function save_order()
{
    $db = \Config\Database::connect();
    $session = session();

    $client_id = $this->request->getPost('client_id');
    $isWalkIn = $this->request->getPost('order_mode') === 'walkin';
    $items = $this->request->getPost('items');
    $qtys  = $this->request->getPost('qtys');
    $discountType = $this->request->getPost('discount_type') ?: 'none';
    $customPercent = (float) ($this->request->getPost('discount_percent') ?? 0);
    $discountIdNumber = trim((string) $this->request->getPost('discount_id_number'));
    $discountHolderName = trim((string) $this->request->getPost('discount_holder_name'));
    $fulfillmentType = $this->request->getPost('fulfillment_type') === 'pickup' ? 'pickup' : 'delivery';

    if (empty($items)) {
        return redirect()->back()->withInput()->with('error', 'Please select at least one product.');
    }

    $guestClientId = null;
    if ($isWalkIn) {
        $guestName = trim((string) $this->request->getPost('guest_name'));
        if ($guestName === '') {
            return redirect()->back()->withInput()->with('error', 'Please provide the client/customer name.');
        }
        $guestModel = new \App\Models\Admin\GuestPartyModel();
        $guestClientId = $guestModel->findOrCreateGuestClient([
            'name' => $guestName, 'contact_person' => $this->request->getPost('guest_contact'),
            'phone' => $this->request->getPost('guest_phone'), 'email' => $this->request->getPost('guest_email'),
            'address' => $this->request->getPost('guest_address'), 'tin' => $this->request->getPost('guest_tin'),
        ]);
    } else {
        if (empty($client_id)) {
            return redirect()->back()->withInput()->with('error', 'Please select a client.');
        }
        $client = $db->table('institutional_clients')
            ->where('client_id', $client_id)
            ->where('user_id IS NOT NULL', null, false)
            ->get()->getRow();
        if (!$client) {
            return redirect()->back()->with('error', 'Selected client is not a registered account.');
        }
    }

    $db->transStart();

    $db->table('sales_orders')->insert([
        'client_id'            => $isWalkIn ? null : $client_id,
        'guest_client_id'      => $guestClientId,
        'order_number'         => 'SO-' . date('Y') . '-' . mt_rand(1000, 9999),
        'invoice_number'       => 'INV-' . date('Y') . '-' . mt_rand(1000, 9999),
        'status'               => 'pending',
        'fulfillment_type'     => $fulfillmentType,
        'payment_method'       => $this->request->getPost('payment_method'),
        'delivery_address'     => $fulfillmentType === 'delivery' ? $this->request->getPost('address') : null,
        'payment_status'       => 'unpaid',
        'discount'             => 0,
        'discount_type'        => $discountType,
        'discount_id_number'   => $discountIdNumber !== '' ? $discountIdNumber : null,
        'discount_holder_name' => $discountHolderName !== '' ? $discountHolderName : null,
        'subtotal'             => 0,
        'vat_amount'           => 0,
        'total'                => 0,
        'created_by'           => $session->get('user_id') ?? 1
    ]);
    $order_id = $db->insertID();

    $grossTotal = 0;
    $productsOrdered = [];
    $cappedCount = 0;

    foreach ($items as $index => $pid) {
        $qty = (int) ($qtys[$index] ?? 0);
        if ($qty <= 0) continue;

        $batch = $db->table('inventory_batches')
            ->where('product_id', $pid)
            ->where('quantity_avail >', 0)
            ->orderBy('expires_at', 'ASC')
            ->get()->getRow();

        if (!$batch) continue;

        if ($qty > $batch->quantity_avail) {
            $qty = $batch->quantity_avail;
            $cappedCount++;
        }
        if ($qty <= 0) continue;

        $price = $batch->sell_price;
        $lineTotal = $price * $qty;
        $grossTotal += $lineTotal;

        $db->table('sales_order_items')->insert([
            'order_id'   => $order_id,
            'product_id' => $pid,
            'batch_id'   => $batch->batch_id,
            'quantity'   => $qty,
            'unit_price' => $price,
            'subtotal'   => $lineTotal
        ]);

        $db->table('inventory_batches')
            ->where('batch_id', $batch->batch_id)
            ->set('quantity_avail', "quantity_avail - {$qty}", false)
            ->update();

        $db->table('stock_movements')->insert([
            'product_id'     => $pid,
            'batch_id'       => $batch->batch_id,
            'movement_type'  => 'outbound',
            'quantity'       => $qty,
            'reference_id'   => $order_id,
            'reference_type' => 'order',
            'scanned_by'     => $session->get('user_id') ?? 1,
            'scan_mode'      => 'outbound_order',
        ]);

        $productsOrdered[] = $pid;
    }

    if ($discountType === 'pwd' || $discountType === 'senior') {
        $vatExclusive = $grossTotal / 1.12;
        $discountAmount = $vatExclusive * 0.20;
        $netTotal = $vatExclusive - $discountAmount;
        $vatAmount = 0;
        $subtotal = $netTotal;
    } else {
        $percent = 0;
        if ($discountType === 'school') {
            $rateRow = $db->table('store_settings')->where('setting_key', 'school_discount_rate')->get()->getRow();
            $percent = $rateRow ? (float) $rateRow->setting_value : 10;
        } elseif ($discountType === 'custom') {
            $percent = $customPercent;
        }
        $discountAmount = $grossTotal * ($percent / 100);
        $netTotal = $grossTotal - $discountAmount;
        $vatAmount = $netTotal - ($netTotal / 1.12);
        $subtotal = $netTotal / 1.12;
    }

    $db->table('sales_orders')->where('order_id', $order_id)->update([
        'discount'   => $discountAmount,
        'subtotal'   => $subtotal,
        'vat_amount' => $vatAmount,
        'total'      => $netTotal
    ]);

    $db->transComplete();

    if ($db->transStatus() === false) {
        return redirect()->back()->with('error', 'Failed to create order.');
    }

    foreach (array_unique($productsOrdered) as $pid) {
        \App\Libraries\AutoReorder::check($pid);
    }

    $msg = 'Sales order created successfully.' . ($cappedCount > 0 ? " Note: {$cappedCount} item(s) were reduced to match available stock." : '');
    return redirect()->to('admin/sales/sales-orders')->with('success', $msg);
}

    public function get_order_details($id)
{
    $db = \Config\Database::connect();
    $order = $db->table('sales_orders as so')
        ->select('so.*, so.guest_client_id, COALESCE(ic.organization, gc.name) as organization, COALESCE(ic.address, gc.address) as client_addr, COALESCE(ic.phone, gc.phone) as phone, COALESCE(ic.tin, gc.tin) as client_tin, u.full_name as encoder')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
        ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
        ->join('users as u', 'u.user_id = so.created_by', 'left')
        ->where('so.order_id', $id)->get()->getRow();

    if (!$order) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Order not found']);
    }

    $items = $db->table('sales_order_items as soi')
        ->select('soi.*, p.name, p.barcode_value')
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('soi.order_id', $id)->get()->getResultArray();

    $settingsRows = $db->table('store_settings')->get()->getResultArray();
    $storeInfo = [];
    foreach ($settingsRows as $row) {
        $storeInfo[$row['setting_key']] = $row['setting_value'];
    }

    return $this->response->setJSON(['order' => $order, 'items' => $items, 'store_info' => $storeInfo]);
}

   public function returns()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();

    $search = trim((string) ($request->getGet('search') ?? ''));
    $status = $request->getGet('status') ?? 'pending';

    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $applyFilters = function($builder) use ($search, $status) {
        if ($status !== 'all') $builder->where('sr.status', $status);
        if ($search !== '') {
            $builder->groupStart()
                ->like('ic.organization', $search)
                ->orLike('so.order_number', $search)
                ->groupEnd();
        }
        return $builder;
    };

    $countBuilder = $db->table('sales_returns as sr')
        ->join('sales_orders as so', 'so.order_id = sr.order_id')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id');
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('sales_returns as sr');
    $builder->select('sr.*, so.order_number, ic.organization as client_name, p.name as product_name, u.full_name as staff');
    $builder->join('sales_orders as so', 'so.order_id = sr.order_id');
    $builder->join('institutional_clients as ic', 'ic.client_id = so.client_id');
    $builder->join('products as p', 'p.product_id = sr.product_id', 'left');
    $builder->join('users as u', 'u.user_id = sr.processed_by');
    $applyFilters($builder);
    $builder->orderBy('sr.created_at', 'DESC');
    $builder->limit($perPage, $offset);
    $data['returns'] = $builder->get()->getResultArray();

    // Delivered orders with no active (pending/approved) return already filed
    $data['delivered_orders'] = $db->table('sales_orders as so')
        ->select('so.order_id, so.order_number, ic.organization')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->where('so.status', 'delivered')
        ->where("so.order_id NOT IN (SELECT order_id FROM sales_returns WHERE status != 'rejected')", null, false)
        ->get()->getResultArray();

    $data['total_rows']    = $totalRows;
    $data['current_page']  = $page;
    $data['per_page']      = $perPage;
    $data['total_pages']   = max(1, (int) ceil($totalRows / $perPage));
    $data['active_status'] = $status;
    $data['search']        = $search;

    $data['title'] = "Sales Returns";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "sales";
    return view('pages/admin/operations/sales/sales_returns', $data);
}

public function get_return_details($id)
{
    $db = \Config\Database::connect();
    $data = $db->table('sales_returns as sr')
        ->select('sr.*, so.order_number, ic.organization, p.name, p.sku, ib.batch_number, ru.full_name as resolved_by_name')
        ->join('sales_orders as so', 'so.order_id = sr.order_id')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->join('products as p', 'p.product_id = sr.product_id', 'left')
        ->join('inventory_batches as ib', 'ib.batch_id = sr.batch_id', 'left')
        ->join('users as ru', 'ru.user_id = sr.resolved_by', 'left')
        ->where('sr.return_id', $id)
        ->get()->getRow();

    if (!$data) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
    return $this->response->setJSON($data);
}

public function approve_return($id)
{
    $db = \Config\Database::connect();
    $ret = $db->table('sales_returns as sr')->select('sr.*, so.client_id, so.guest_client_id, so.fulfillment_type')->join('sales_orders as so', 'so.order_id = sr.order_id')->where('sr.return_id', $id)->get()->getRow();

    if (!$ret || $ret->status !== 'pending') {
        return redirect()->back()->with('error', 'Only pending returns can be approved.');
    }

    $db->transStart();
    $db->table('sales_returns')->where('return_id', $id)->update([
        'status'      => 'approved',
        'resolved_by' => session()->get('user_id') ?? 1,
        'resolved_at' => date('Y-m-d H:i:s')
    ]);

    if ($ret->restock_condition === 'resellable' && $ret->batch_id) {
        $db->table('inventory_batches')->where('batch_id', $ret->batch_id)
            ->set('quantity_avail', "quantity_avail + {$ret->quantity}", false)->update();

        $db->table('stock_movements')->insert([
            'product_id' => $ret->product_id, 'batch_id' => $ret->batch_id, 'movement_type' => 'return_inbound',
            'quantity' => $ret->quantity, 'reference_id' => $ret->order_id, 'reference_type' => 'return',
            'scanned_by' => session()->get('user_id') ?? 1, 'reason' => 'Client return approved — restocked as resellable'
        ]);
    } else {
        $db->table('stock_movements')->insert([
            'product_id' => $ret->product_id, 'batch_id' => $ret->batch_id, 'movement_type' => 'adjustment',
            'quantity' => 0, 'reference_id' => $ret->order_id, 'reference_type' => 'return',
            'scanned_by' => session()->get('user_id') ?? 1, 'reason' => 'Client return approved — condition: ' . $ret->restock_condition . ' (not restocked)'
        ]);

        // Damaged/wrong-item units are unsellable — replace them via a zero-cost
        // sales order, mirroring the supplier-side exchange flow exactly.
        $db->table('sales_orders')->insert([
            'client_id'                 => $ret->client_id,
            'guest_client_id'           => $ret->guest_client_id,
            'order_number'              => 'SO-' . date('Y') . '-' . time(),
            'invoice_number'            => 'INV-' . date('Y') . '-' . time(),
            'status'                    => 'pending',
            'fulfillment_type'          => $ret->fulfillment_type,
            'payment_method'            => 'cash',
            'payment_status'            => 'paid', // zero-cost replacement — nothing owed
            'discount'                  => 0,
            'subtotal'                  => 0,
            'vat_amount'                => 0,
            'total'                     => 0,
            'replacement_for_return_id' => $id,
            'notes'                     => 'Replacement for damaged/incorrect item — see Return #' . $id,
            'created_by'                => session()->get('user_id') ?? 1,
        ]);
        $newOrderId = $db->insertID();

        $db->table('sales_order_items')->insert([
            'order_id'   => $newOrderId,
            'product_id' => $ret->product_id,
            'batch_id'   => null, // fulfilled from whatever batch is available at dispatch time
            'quantity'   => $ret->quantity,
            'unit_price' => 0,
            'subtotal'   => 0,
        ]);
    }

    $db->transComplete();
    if (!empty($ret->client_id)) {
    \App\Models\Client\NotificationModel::notify($db, (int) $ret->client_id, "Your return has been approved — a replacement order has been created.", '/client/orders/my-orders');
}
    $msg = ($ret->restock_condition === 'resellable')
        ? 'Return approved and stock restored.'
        : "Return approved. Item was NOT returned to sellable stock, and a free replacement order has been created for the client.";
    return redirect()->back()->with('success', $msg);
}

public function reject_return($id)
{
    $db = \Config\Database::connect();
    $ret = $db->table('sales_returns')->where('return_id', $id)->get()->getRow();
    if (!$ret || $ret->status !== 'pending') {
        return redirect()->back()->with('error', 'Only pending returns can be rejected.');
    }

    $db->table('sales_returns')->where('return_id', $id)->update(['status' => 'rejected']);

    $ret = $db->table('sales_returns as sr')->select('sr.*, so.client_id, so.order_number')->join('sales_orders as so', 'so.order_id = sr.order_id')->where('sr.return_id', $id)->get()->getRow();
    if (!empty($ret->client_id)) {
        \App\Models\Client\NotificationModel::notify($db, (int) $ret->client_id, "Your return request for order {$ret->order_number} was reviewed and rejected.", '/client/orders/returns');
    }

    return redirect()->back()->with('info', 'Return Request Rejected.');
}

    public function get_return_order_items($order_id)
{
    $db = \Config\Database::connect();
    $items = $db->table('sales_order_items as soi')
        ->select('soi.product_id, soi.batch_id, soi.quantity, soi.unit_price, p.name, ic.organization')
        ->join('products as p', 'p.product_id = soi.product_id')
        ->join('sales_orders as so', 'so.order_id = soi.order_id')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->where('soi.order_id', $order_id)->get()->getResultArray();
    return $this->response->setJSON($items);
}

    public function process_return()
{
    $db = \Config\Database::connect();
    $session = session();

    $order_id   = $this->request->getPost('order_id');
    $product_id = $this->request->getPost('product_id');
    $batch_id   = $this->request->getPost('batch_id');
    $qty        = (int) $this->request->getPost('qty');
    $reasonCat  = $this->request->getPost('reason_cat');
    $notes      = trim((string) $this->request->getPost('notes'));
    $condition  = $this->request->getPost('restock_condition') ?: 'resellable';
    $refund     = $this->request->getPost('refund_amount');

    if (empty($order_id) || empty($product_id) || $qty <= 0) {
        return redirect()->back()->withInput()->with('error', 'Please complete all required fields.');
    }

    $db->table('sales_returns')->insert([
        'order_id'          => $order_id,
        'product_id'        => $product_id,
        'batch_id'          => $batch_id ?: null,
        'quantity'          => $qty,
        'restock_condition' => $condition,
        'refund_amount'     => $refund !== '' ? $refund : null,
        'processed_by'      => $session->get('user_id') ?? 1,
        'reason'            => $reasonCat . ': ' . $notes,
        'status'            => 'pending',
    ]);

    return redirect()->to('admin/sales/sales-returns')->with('success', 'Return request submitted for approval.');
}

public function supplier_returns()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();

    $search = trim((string) ($request->getGet('search') ?? ''));
    $status = $request->getGet('status') ?? 'pending';

    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $applyFilters = function($builder) use ($search, $status) {
        if ($status !== 'all') $builder->where('pr.status', $status);
        if ($search !== '') {
            $builder->groupStart()
                ->like('s.name', $search)
                ->orLike('po.po_number', $search)
                ->groupEnd();
        }
        return $builder;
    };

    $countBuilder = $db->table('procurement_returns as pr')
        ->join('purchase_orders as po', 'po.po_id = pr.po_id')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id');
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('procurement_returns as pr');
    $builder->select('pr.*, po.po_number, s.name as supplier_name, p.name as product_name, u.full_name as staff');
    $builder->join('purchase_orders as po', 'po.po_id = pr.po_id');
    $builder->join('suppliers as s', 's.supplier_id = po.supplier_id');
    $builder->join('products as p', 'p.product_id = pr.product_id');
    $builder->join('users as u', 'u.user_id = pr.processed_by');
    $applyFilters($builder);
    $builder->orderBy('pr.created_at', 'DESC');
    $builder->limit($perPage, $offset);
    $data['returns'] = $builder->get()->getResultArray();

    $data['received_pos'] = $db->table('purchase_orders as po')
        ->select('po.po_id, po.po_number, s.name as sname')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->whereIn('po.status', ['received', 'partial'])
        ->get()->getResultArray();

    $data['total_rows']    = $totalRows;
    $data['current_page']  = $page;
    $data['per_page']      = $perPage;
    $data['total_pages']   = max(1, (int) ceil($totalRows / $perPage));
    $data['active_status'] = $status;
    $data['search']        = $search;

    $data['title'] = "Supplier Returns";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "supplier-returns";
    return view('pages/admin/operations/procurement/supplier_returns', $data);
}

public function get_po_items_for_return($poId)
{
    $db = \Config\Database::connect();
    $items = $db->table('purchase_order_items as poi')
        ->select("poi.product_id, poi.unit_cost, p.name, poi.qty_received,
            (SELECT ib.batch_id FROM inventory_batches ib WHERE ib.po_id = poi.po_id AND ib.product_id = poi.product_id ORDER BY ib.received_at DESC LIMIT 1) as batch_id")
        ->join('products as p', 'p.product_id = poi.product_id')
        ->where('poi.po_id', $poId)
        ->where('poi.qty_received >', 0)
        ->get()->getResultArray();
    return $this->response->setJSON($items);
}

public function save_supplier_return()
{
    $db = \Config\Database::connect();
    $session = session();

    $po_id      = $this->request->getPost('po_id');
    $product_id = $this->request->getPost('product_id');
    $batch_id   = $this->request->getPost('batch_id');
    $qty        = (int) $this->request->getPost('qty');
    $notes      = trim((string) $this->request->getPost('notes'));
    $creditNote = trim((string) $this->request->getPost('credit_note_number'));
    $refund     = $this->request->getPost('refund_amount');

    if (empty($po_id) || empty($product_id) || $qty <= 0) {
        return redirect()->back()->withInput()->with('error', 'Please complete all required fields.');
    }

    $db->table('procurement_returns')->insert([
        'po_id'              => $po_id,
        'product_id'         => $product_id,
        'batch_id'           => $batch_id ?: null,
        'quantity'           => $qty,
        'reason'             => $notes,
        'credit_note_number' => $creditNote !== '' ? $creditNote : null,
        'refund_amount'      => $refund !== '' ? $refund : null,
        'status'             => 'pending',
        'processed_by'       => $session->get('user_id') ?? 1,
    ]);

    return redirect()->to('admin/procurement/supplier-returns')->with('success', 'Supplier return request submitted for approval.');
}

public function approve_supplier_return($id)
{
    $db = \Config\Database::connect();
    $ret = $db->table('procurement_returns')->where('return_id', $id)->get()->getRow();
    if (!$ret || $ret->status !== 'pending') {
        return redirect()->back()->with('error', 'Only pending returns can be approved.');
    }

    $db->transStart();
    $db->table('procurement_returns')->where('return_id', $id)->update([
        'status'      => 'approved',
        'resolved_by' => session()->get('user_id') ?? 1,
        'resolved_at' => date('Y-m-d H:i:s')
    ]);

    if ($ret->batch_id) {
        $db->table('inventory_batches')->where('batch_id', $ret->batch_id)
            ->set('quantity_avail', "GREATEST(quantity_avail - {$ret->quantity}, 0)", false)->update();

        $db->table('stock_movements')->insert([
            'product_id'     => $ret->product_id,
            'batch_id'       => $ret->batch_id,
            'movement_type'  => 'outbound',
            'quantity'       => $ret->quantity,
            'reference_id'   => $ret->po_id,
            'reference_type' => 'supplier_return',
            'scanned_by'     => session()->get('user_id') ?? 1,
            'reason'         => 'Returned to supplier' . ($ret->credit_note_number ? ' — Credit Note: ' . $ret->credit_note_number : '')
        ]);
    }
    $db->transComplete();
    return redirect()->back()->with('success', 'Return approved — stock removed and marked for return to supplier.');
}

public function reject_supplier_return($id)
{
    $db = \Config\Database::connect();
    $ret = $db->table('procurement_returns')->where('return_id', $id)->get()->getRow();
    if (!$ret || $ret->status !== 'pending') {
        return redirect()->back()->with('error', 'Only pending returns can be rejected.');
    }
    $db->table('procurement_returns')->where('return_id', $id)->update([
        'status' => 'rejected',
        'resolved_at' => date('Y-m-d H:i:s')
    ]);
    return redirect()->back()->with('info', 'Supplier return rejected.');
}

public function get_supplier_return_details($id)
{
    $db = \Config\Database::connect();
    $data = $db->table('procurement_returns as pr')
        ->select('pr.*, po.po_number, s.name as supplier_name, p.name, p.sku, ib.batch_number, ru.full_name as resolved_by_name')
        ->join('purchase_orders as po', 'po.po_id = pr.po_id')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->join('products as p', 'p.product_id = pr.product_id')
        ->join('inventory_batches as ib', 'ib.batch_id = pr.batch_id', 'left')
        ->join('users as ru', 'ru.user_id = pr.resolved_by', 'left')
        ->where('pr.return_id', $id)
        ->get()->getRow();

    if (!$data) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
    return $this->response->setJSON($data);
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

    // Dynamic rate, not hardcoded — keeps server math in sync with whatever
    // admin has configured in store_settings, same value the client preview uses.
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
    $history = $this->posModel->getTodayTransactions(50); // <-- the actual fix
    $txn     = $this->posModel->getTransactionById($txnId);

    return $this->response->setJSON([
        'status'          => 'success',
        'txn'             => $txn,
        'items'           => $validatedItems,
        'updated_batches' => $updatedBatches,
        'daily'           => $daily,
        'history'         => $history, // <-- populates Today's Transactions immediately, no poll wait
    ]);
}

public function confirm_payment()
{
    $orderId = (int) $this->request->getPost('order_id');
    $method = $this->request->getPost('payment_method');
    $reference = trim((string) $this->request->getPost('payment_reference'));

    if (!in_array($method, ['cash', 'bank_transfer', 'cheque'])) {
        return redirect()->back()->with('error', 'Please select a valid payment method.');
    }
    if (in_array($method, ['bank_transfer', 'cheque']) && $reference === '') {
        return redirect()->back()->with('error', 'A reference number is required for bank transfer or cheque payments.');
    }

    $db = \Config\Database::connect();
    $order = $db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
    if (!$order || $order->payment_status === 'paid') {
        return redirect()->back()->with('error', 'Order not found or already marked paid.');
    }

    $db->table('sales_orders')->where('order_id', $orderId)->update([
        'payment_status'    => 'paid',
        'payment_method'    => $method,
        'payment_reference' => $reference ?: null,
        'paid_at'           => date('Y-m-d H:i:s'),
    ]);

    if (!empty($order->client_id)) {
        \App\Models\Client\NotificationModel::notify($db, (int) $order->client_id, "Payment confirmed for order {$order->order_number}.", '/client/account/invoices');
    }

    return redirect()->back()->with('success', 'Payment confirmed.');
}

public function update_order_status()
{
    $db = \Config\Database::connect();
    $orderId = (int) $this->request->getPost('order_id');
    $newStatus = $this->request->getPost('status');

    $order = $db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
    if (!$order) {
        return redirect()->back()->with('error', 'Order not found.');
    }

    $requiresPrepayment = $order->fulfillment_type === 'delivery'
        && in_array($order->payment_method, ['cheque', 'bank_transfer'])
        && $order->payment_status !== 'paid';

    if ($newStatus === 'out_for_delivery' && $requiresPrepayment) {
        return redirect()->back()->with('error',
            "This order is paid via " . strtoupper(str_replace('_', ' ', $order->payment_method)) .
            " and must be confirmed PAID before it can be dispatched. Please confirm payment first.");
    }

    // Pickup: payment must be confirmed before it can be marked picked up/delivered
    if ($order->fulfillment_type === 'pickup' && $newStatus === 'delivered' && $order->payment_status !== 'paid') {
        return redirect()->back()->with('error', 'Please confirm payment before marking this order as picked up.');
    }

    $db->table('sales_orders')->where('order_id', $orderId)->update(['status' => $newStatus]);

$updatedOrder = $db->table('sales_orders')->where('order_id', $orderId)->get()->getRow();
if (!empty($updatedOrder->client_id)) {
    $statusMessages = [
        'ready_for_pickup' => "Your order {$updatedOrder->order_number} is ready for pickup at the store.",
        'out_for_delivery' => "Your order {$updatedOrder->order_number} is out for delivery.",
        'delivered'        => "Your order {$updatedOrder->order_number} has been completed.",
    ];
    if (isset($statusMessages[$newStatus])) {
        \App\Models\Client\NotificationModel::notify($db, (int) $updatedOrder->client_id, $statusMessages[$newStatus], '/client/orders/my-orders');
    }
}

return redirect()->back()->with('success', 'Order status updated.');
}

}

