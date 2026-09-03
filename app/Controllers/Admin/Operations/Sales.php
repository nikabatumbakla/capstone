<?php

namespace App\Controllers\Admin\Operations;
use App\Controllers\BaseController;

class Sales extends BaseController
{
    public function clients()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();

    $search = trim((string) ($request->getGet('search') ?? ''));
    $type   = $request->getGet('type') ?? '';

    $page    = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset  = ($page - 1) * $perPage;

    // Single source of truth for "is this a real, usable client record" —
    // used by the KPIs AND the table, so they can never disagree again.
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

    $applyFilters = function($builder) use ($search, $type) {
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
        return $builder;
    };

    // KPIs now use the SAME qualification rules as the table
    $data['count_schools']   = $baseQualified()->where('ic.client_type', 'school')->countAllResults();
    $data['count_hospitals'] = $baseQualified()->whereIn('ic.client_type', ['hospital', 'clinic'])->countAllResults();
    $data['count_lgu']       = $baseQualified()->whereIn('ic.client_type', ['lgu', 'sk'])->countAllResults();
    $data['count_brgy']      = $baseQualified()->where('ic.client_type', 'barangay')->countAllResults();

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

    // Data for the "New Sales Order" drawer, launched from the client View panel
$data['categories'] = $db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
$data['products'] = $db->table('products as p')
    ->select("p.product_id, p.name, p.unit, p.category_id, p.is_vat_exempt,
        (SELECT COALESCE(SUM(quantity_avail),0) FROM inventory_batches WHERE product_id = p.product_id) as total_stock,
        (SELECT ib.sell_price FROM inventory_batches ib WHERE ib.product_id = p.product_id ORDER BY ib.received_at DESC LIMIT 1) as latest_sell_price")
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
            $builder->groupStart()->like('so.order_number', $search)->orLike('ic.organization', $search)->groupEnd();
        }
        if ($type === 'hospital_clinic') {
            $builder->whereIn('ic.client_type', ['hospital', 'clinic']);
        } elseif ($type === 'lgu_sk') {
            $builder->whereIn('ic.client_type', ['lgu', 'sk']);
        } elseif ($type !== '') {
            $builder->where('ic.client_type', $type);
        }
        return $builder;
    };

    $countBuilder = $db->table('sales_orders as so')->join('institutional_clients as ic', 'ic.client_id = so.client_id');
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('sales_orders as so');
    $builder->select('so.*, ic.organization as client_name, ic.client_type, (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count');
    $builder->join('institutional_clients as ic', 'ic.client_id = so.client_id');
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
    $items = $this->request->getPost('items');
    $qtys  = $this->request->getPost('qtys');
    $discountType = $this->request->getPost('discount_type') ?: 'none';
    $customPercent = (float) ($this->request->getPost('discount_percent') ?? 0);
    $discountIdNumber = trim((string) $this->request->getPost('discount_id_number'));
    $discountHolderName = trim((string) $this->request->getPost('discount_holder_name'));

    if (empty($client_id) || empty($items)) {
        return redirect()->back()->withInput()->with('error', 'Please select a client and at least one product.');
    }

    // Re-verify server-side: only a real, linked-account client can ever receive an order
    $client = $db->table('institutional_clients')
        ->where('client_id', $client_id)
        ->where('user_id IS NOT NULL', null, false)
        ->get()->getRow();
    if (!$client) {
        return redirect()->back()->with('error', 'Selected client is not a registered account.');
    }

    $db->transStart();

    $db->table('sales_orders')->insert([
        'client_id'            => $client_id,
        'order_number'         => 'SO-' . date('Y') . '-' . mt_rand(1000, 9999),
        'invoice_number'       => 'INV-' . date('Y') . '-' . mt_rand(1000, 9999),
        'status'               => 'pending',
        'payment_method'       => $this->request->getPost('payment_method'),
        'delivery_address'     => $this->request->getPost('address'),
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

        // Never deduct more than what's actually on hand
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
        // RA 10754 / RA 9994: 20% off the VAT-exclusive price, and VAT-exempt entirely
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
        ->select('so.*, ic.organization, ic.address as client_addr, ic.phone, ic.tin as client_tin, u.full_name as encoder')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->join('users as u', 'u.user_id = so.created_by', 'left')
        ->where('so.order_id', $id)->get()->getRow();

    if (!$order) {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Order not found']);
    }

    $items = $db->table('sales_order_items as soi')
        ->select('soi.*, p.name, p.sku')
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
    $ret = $db->table('sales_returns')->where('return_id', $id)->get()->getRow();

    if (!$ret || $ret->status !== 'pending') {
        return redirect()->back()->with('error', 'Only pending returns can be approved.');
    }

    $db->transStart();
    $db->table('sales_returns')->where('return_id', $id)->update([
        'status'      => 'approved',
        'resolved_by' => session()->get('user_id') ?? 1,
        'resolved_at' => date('Y-m-d H:i:s')
    ]);

    // Only put it back into sellable stock if it's actually fit to sell
    if ($ret->restock_condition === 'resellable' && $ret->batch_id) {
        $db->table('inventory_batches')->where('batch_id', $ret->batch_id)
            ->set('quantity_avail', "quantity_avail + {$ret->quantity}", false)->update();

        $db->table('stock_movements')->insert([
            'product_id'     => $ret->product_id,
            'batch_id'       => $ret->batch_id,
            'movement_type'  => 'return_inbound',
            'quantity'       => $ret->quantity,
            'reference_id'   => $ret->order_id,
            'reference_type' => 'return',
            'scanned_by'     => session()->get('user_id') ?? 1,
            'reason'         => 'Client return approved — restocked as resellable'
        ]);
    } else {
        // Damaged/expired/disposed: approved, but NOT put back into sellable inventory
        $db->table('stock_movements')->insert([
            'product_id'     => $ret->product_id,
            'batch_id'       => $ret->batch_id,
            'movement_type'  => 'adjustment',
            'quantity'       => 0,
            'reference_id'   => $ret->order_id,
            'reference_type' => 'return',
            'scanned_by'     => session()->get('user_id') ?? 1,
            'reason'         => 'Client return approved — condition: ' . $ret->restock_condition . ' (not restocked)'
        ]);
    }

    $db->transComplete();
    $msg = ($ret->restock_condition === 'resellable')
        ? 'Return approved and stock restored.'
        : "Return approved — item marked '{$ret->restock_condition}' and was NOT returned to sellable stock.";
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
    $db = \Config\Database::connect();
    $today = date('Y-m-d');

    $data['total_txns']  = $db->table('pos_transactions')->where('DATE(created_at)', $today)->where('status', 'completed')->countAllResults();
    $data['gross_sales']  = $db->table('pos_transactions')->selectSum('total')->where('DATE(created_at)', $today)->where('status', 'completed')->get()->getRow()->total ?? 0;
    $data['cash_sales']   = $db->table('pos_transactions')->selectSum('total')->where(['DATE(created_at)' => $today, 'payment_method' => 'cash', 'status' => 'completed'])->get()->getRow()->total ?? 0;
    $data['gcash_sales']  = $db->table('pos_transactions')->selectSum('total')->where(['DATE(created_at)' => $today, 'payment_method' => 'gcash', 'status' => 'completed'])->get()->getRow()->total ?? 0;

    $data['history'] = $db->table('pos_transactions as pt')
        ->select('pt.*, (SELECT COUNT(*) FROM pos_transaction_items WHERE txn_id = pt.txn_id) as item_count')
        ->where('DATE(pt.created_at)', $today)
        ->orderBy('pt.created_at', 'DESC')->get()->getResultArray();

    // Real category → product browsing, not search-only
    $data['categories'] = $db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
    $data['products'] = $db->table('products as p')
        ->select("p.product_id, p.name, p.sku, p.barcode_value, p.unit, p.category_id, p.is_vat_exempt,
            ib.batch_id, ib.batch_number, ib.expires_at, ib.quantity_avail, ib.sell_price")
        ->join('inventory_batches as ib', 'ib.product_id = p.product_id')
        ->where('p.is_active', 1)
        ->where('ib.quantity_avail >', 0)
        ->orderBy('ib.expires_at', 'ASC') // FEFO — earliest expiry surfaces first per product
        ->get()->getResultArray();

    $rateRow = $db->table('store_settings')->where('setting_key', 'vat_rate')->get()->getRow();
    $data['vat_rate'] = $rateRow ? (float) $rateRow->setting_value : 12;

    $storeRows = $db->table('store_settings')->get()->getResultArray();
    $storeInfo = [];
    foreach ($storeRows as $row) $storeInfo[$row['setting_key']] = $row['setting_value'];
    $data['store_info'] = $storeInfo;

    $data['title'] = "Point of Sale";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "pos";
    return view('pages/admin/operations/sales/pos', $data);
}


// AJAX: Intelligent Search (Returns full details: Expiry, Batch, Stock)
public function get_product_pos($query)
{
    $db = \Config\Database::connect();
    $products = $db->table('products as p')
        ->select('p.product_id, p.name, p.sku, p.barcode_value, ib.batch_id, ib.batch_number, ib.expires_at, ib.quantity_avail, ib.sell_price, c.name as cat_name')
        ->join('inventory_batches as ib', 'ib.product_id = p.product_id')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->where('ib.quantity_avail >', 0)
        ->groupStart()
            ->like('p.name', $query)
            ->orLike('p.barcode_value', $query)
            ->orLike('p.sku', $query)
        ->groupEnd()
        ->orderBy('ib.expires_at', 'ASC')
        ->get()->getResultArray();

    return $this->response->setJSON($products);
}

public function process_pos()
{
    $db = \Config\Database::connect();
    $session = session();

    $items = json_decode($this->request->getPost('items'), true) ?? [];
    $discountType = $this->request->getPost('discount_type') ?: 'none';
    $discountIdNumber = trim((string) $this->request->getPost('discount_id_number'));
    $discountHolderName = trim((string) $this->request->getPost('discount_holder_name'));
    $customerName = trim((string) $this->request->getPost('customer_name'));
    $paymentMethod = $this->request->getPost('payment_method');
    $tendered = (float) $this->request->getPost('tendered');
    $gcashRef = trim((string) $this->request->getPost('gcash_ref'));

    if (empty($items)) {
        return $this->response->setStatusCode(422)->setJSON(['error' => 'Cart is empty.']);
    }
    if ($paymentMethod === 'gcash' && $gcashRef === '') {
        return $this->response->setStatusCode(422)->setJSON(['error' => 'GCash reference number is required.']);
    }

    // Re-validate stock AND VAT-exempt status server-side — never trust the client for either
    $vatableGross = 0;
    $exemptGross = 0;
    $validatedItems = [];

    foreach ($items as $item) {
        $batch = $db->table('inventory_batches as ib')
            ->select('ib.batch_id, ib.quantity_avail, ib.sell_price, ib.product_id, p.is_vat_exempt, p.name')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('ib.batch_id', $item['batch_id'])
            ->get()->getRow();

        if (!$batch || $batch->quantity_avail < $item['qty']) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Insufficient stock for one or more items. Please refresh and try again.']);
        }

        $lineTotal = $batch->sell_price * $item['qty'];
        if ($batch->is_vat_exempt) {
            $exemptGross += $lineTotal;
        } else {
            $vatableGross += $lineTotal;
        }

        $validatedItems[] = [
            'product_id' => $batch->product_id, 'batch_id' => $batch->batch_id,
            'name' => $batch->name, 'qty' => $item['qty'],
            'price' => $batch->sell_price, 'subtotal' => $lineTotal
        ];
    }

    $gross = $vatableGross + $exemptGross;

    if ($discountType === 'pwd' || $discountType === 'senior') {
        $vatExclusiveBase = ($vatableGross / 1.12) + $exemptGross;
        $discountAmount = $vatExclusiveBase * 0.20;
        $netTotal = $vatExclusiveBase - $discountAmount;
        $vatAmount = 0;
        $subtotal = $netTotal;
    } else {
        $discountAmount = 0;
        $vatAmount = $vatableGross - ($vatableGross / 1.12);
        $subtotal = ($vatableGross / 1.12) + $exemptGross;
        $netTotal = $gross;
    }

    if ($paymentMethod === 'cash' && $tendered < $netTotal) {
        return $this->response->setStatusCode(422)->setJSON(['error' => 'Amount tendered is less than the total due.']);
    }
    // For GCash, confirmation of payment IS the tender — treat as exact amount
    if ($paymentMethod === 'gcash') {
        $tendered = $netTotal;
    }

    $db->transStart();

    $db->table('pos_transactions')->insert([
        'txn_number'           => 'TXN-' . date('Ymd') . '-' . mt_rand(1000, 9999),
        'cashier_id'           => $session->get('user_id') ?? 1,
        'customer_name'        => $customerName !== '' ? $customerName : null,
        'subtotal'             => $subtotal,
        'discount'             => $discountAmount,
        'discount_type'        => $discountType,
        'discount_id_number'   => $discountIdNumber !== '' ? $discountIdNumber : null,
        'discount_holder_name' => $discountHolderName !== '' ? $discountHolderName : null,
        'vat_amount'           => $vatAmount,
        'total'                => $netTotal,
        'payment_method'       => $paymentMethod,
        'gcash_ref'            => $paymentMethod === 'gcash' ? $gcashRef : null,
        'amount_tendered'      => $tendered,
        'change_due'           => $paymentMethod === 'cash' ? ($tendered - $netTotal) : 0,
        'or_number'            => 'OR-' . date('Ymd') . '-' . mt_rand(1000, 9999),
        'status'               => 'completed'
    ]);
    $txn_id = $db->insertID();

    $productsSold = [];
    foreach ($validatedItems as $item) {
        $db->table('pos_transaction_items')->insert([
            'txn_id' => $txn_id, 'product_id' => $item['product_id'], 'batch_id' => $item['batch_id'],
            'quantity' => $item['qty'], 'unit_price' => $item['price'], 'subtotal' => $item['subtotal']
        ]);
        $db->table('inventory_batches')->where('batch_id', $item['batch_id'])
            ->set('quantity_avail', "quantity_avail - {$item['qty']}", false)->update();
        $db->table('stock_movements')->insert([
            'product_id' => $item['product_id'], 'batch_id' => $item['batch_id'], 'movement_type' => 'pos_sale',
            'quantity' => $item['qty'], 'reference_id' => $txn_id, 'reference_type' => 'pos',
            'scanned_by' => $session->get('user_id') ?? 1, 'scan_mode' => 'pos',
        ]);
        $productsSold[] = $item['product_id'];
    }

    $db->transComplete();
    if ($db->transStatus() === false) {
        return $this->response->setStatusCode(500)->setJSON(['error' => 'Transaction failed. Please try again.']);
    }

    foreach (array_unique($productsSold) as $pid) {
        \App\Libraries\AutoReorder::check($pid);
    }

    $txn = $db->table('pos_transactions')->where('txn_id', $txn_id)->get()->getRow();
    return $this->response->setJSON(['status' => 'success', 'txn' => $txn, 'items' => $validatedItems]);
}

public function confirm_payment()
{
    $orderId = (int) $this->request->getPost('order_id');
    $reference = trim((string) $this->request->getPost('payment_reference'));

    $db = \Config\Database::connect();
    $db->table('sales_orders')->where('order_id', $orderId)->update([
        'payment_status'    => 'paid',
        'payment_reference' => $reference ?: null,
    ]);

    return redirect()->back()->with('success', 'Payment confirmed.');
}

}