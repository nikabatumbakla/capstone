<?php

namespace App\Controllers\Admin\Operations;
use App\Controllers\BaseController;

class Sales extends BaseController
{
    public function clients()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();

        // 1. Capture Filters from URL
        $search = $request->getGet('search') ?? '';
        $type   = $request->getGet('type') ?? '';

        // 2. Pagination Calculation
        $page    = (int)($request->getGet('page') ?? 1);
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        // 3. KPI Counts (Static Totals)
        $data['count_schools']   = $db->table('institutional_clients')->where('client_type', 'school')->countAllResults();
        $data['count_hospitals'] = $db->table('institutional_clients')->where('client_type', 'hospital')->countAllResults();
        $data['count_lgu']       = $db->table('institutional_clients')->whereIn('client_type', ['lgu', 'sk'])->countAllResults();
        $data['count_brgy']      = $db->table('institutional_clients')->where('client_type', 'barangay')->countAllResults();

        // 4. Build Main Table Query (ACCURATE GLOBAL SEARCH)
        $builder = $db->table('institutional_clients');
        $builder->where('is_active', 1);
        
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('organization', $search)
                    ->orLike('contact_person', $search)
                    ->groupEnd();
        }
        
        if (!empty($type)) {
            $builder->where('client_type', $type);
        }

        // Count ONLY the results matching the search/filter
        $totalRows = $builder->countAllResults(false); 
        
        $data['clients'] = $builder->orderBy('organization', 'ASC')
                               ->limit($perPage, $offset)
                               ->get()->getResultArray();

        // 5. Data for View
        $data['total_rows']   = $totalRows;
        $data['current_page'] = $page;
        $data['per_page']     = $perPage;
        $data['total_pages']  = max(1, ceil($totalRows / $perPage));
        $data['search']       = $search;
        $data['type_filter']  = $type;

        $data['title'] = "Client Directory";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "sales";
        return view('pages/admin/operations/sales/institutional_clients', $data);
    }

    public function get_client_details($id)
    {
        $db = \Config\Database::connect();
        $client = $db->table('institutional_clients')->where('client_id', $id)->get()->getRow();
        if (!$client) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not Found']);

        $orders = $db->table('sales_orders')
                    ->where('client_id', $id)
                    ->orderBy('created_at', 'DESC')
                    ->limit(10)
                    ->get()->getResultArray();

        return $this->response->setJSON(['client' => $client, 'orders' => $orders]);
    }

    public function save_client()
    {
        $db = \Config\Database::connect();
        $data = [
            'organization'   => $this->request->getPost('org'),
            'client_type'    => $this->request->getPost('type'),
            'contact_person' => $this->request->getPost('contact'),
            'phone'          => $this->request->getPost('phone'),
            'address'        => $this->request->getPost('address'),
            'credit_limit'   => $this->request->getPost('limit') ?? 0,
            'is_active'      => 1
        ];
        $db->table('institutional_clients')->insert($data);
        return redirect()->back()->with('success', 'Organization Registered.');
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

        // 1. Capture Global Filters
        $search = $request->getGet('search') ?? '';
        $type   = $request->getGet('type') ?? '';
        $page    = (int)($request->getGet('page') ?? 1);
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        // 2. Build Query joined with Clients for Category filtering
        $builder = $db->table('sales_orders as so');
        $builder->select('so.*, ic.organization as client_name, ic.client_type, 
            (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.order_id) as item_count');
        $builder->join('institutional_clients as ic', 'ic.client_id = so.client_id');
        
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('so.order_number', $search)
                    ->orLike('ic.organization', $search)
                    ->groupEnd();
        }
        if (!empty($type)) $builder->where('ic.client_type', $type);

        $totalRows = $builder->countAllResults(false);
        $data['orders'] = $builder->orderBy('so.created_at', 'DESC')->limit($perPage, $offset)->get()->getResultArray();

        // 3. Data for Create Drawer
        $data['clients'] = $db->table('institutional_clients')->where('is_active', 1)->get()->getResultArray();
        $data['products'] = $db->table('inventory_batches as ib')
            ->select('ib.*, p.name')->join('products as p', 'p.product_id = ib.product_id')
            ->where('ib.quantity_avail >', 0)->get()->getResultArray();

        // 4. Pagination Data
        $data['total_rows']   = $totalRows;
        $data['current_page'] = $page;
        $data['per_page']     = $perPage;
        $data['total_pages']  = max(1, ceil($totalRows / $perPage));
        $data['search']       = $search;
        $data['type_filter']  = $type;

        $data['title'] = "Sales Order Management";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "sales";
        return view('pages/admin/operations/sales/sales_orders', $data);
    }

    public function save_order()
    {
        $db = \Config\Database::connect();
        $session = session();
        
        $total = 0;
        $items = $this->request->getPost('items');
        $qtys  = $this->request->getPost('qtys');

        $db->transStart();

        // 1. Create the Order Header
        $orderData = [
            'client_id'      => $this->request->getPost('client_id'),
            'order_number'   => 'SO-' . date('Y') . '-' . rand(1000, 9999),
            'status'         => 'pending',
            'payment_method' => $this->request->getPost('payment_method'),
            'delivery_address' => $this->request->getPost('address'),
            'payment_status' => 'unpaid',
            'discount'       => $this->request->getPost('discount') ?? 0,
            'subtotal'       => 0, // Updated later
            'vat_amount'     => 0,
            'total'          => 0,
            'created_by'     => $session->get('user_id') ?? 1
        ];
        
        $db->table('sales_orders')->insert($orderData);
        $order_id = $db->insertID();

        // 2. Process Items and Calculate Total
        foreach ($items as $index => $pid) {
            // Get current price from inventory
            $prod = $db->table('inventory_batches')->where('product_id', $pid)->get()->getRow();
            $price = $prod ? $prod->sell_price : 0;
            $line_total = $price * $qtys[$index];
            $total += $line_total;

            $db->table('sales_order_items')->insert([
                'order_id'   => $order_id,
                'product_id' => $pid,
                'quantity'   => $qtys[$index],
                'unit_price' => $price,
                'subtotal'   => $line_total
            ]);
        }

        // 3. Finalize Totals with VAT (12%)
        $vat = $total - ($total / 1.12);
        $db->table('sales_orders')->where('order_id', $order_id)->update([
            'subtotal'   => $total / 1.12,
            'vat_amount' => $vat,
            'total'      => $total
        ]);

        $db->transComplete();

        // FIXED: Redirect back to Sales Orders List, not Clients
        return redirect()->to('admin/sales/sales-orders')->with('success', 'Order Saved Successfully.');
    }

    public function get_order_details($id)
    {
        $db = \Config\Database::connect();
        $order = $db->table('sales_orders as so')
            ->select('so.*, ic.organization, ic.address as client_addr, ic.phone, u.full_name as encoder')
            ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
            ->join('users as u', 'u.user_id = so.created_by', 'left')
            ->where('so.order_id', $id)->get()->getRow();

        $items = $db->table('sales_order_items as soi')
            ->select('soi.*, p.name, p.sku')
            ->join('products as p', 'p.product_id = soi.product_id')
            ->where('soi.order_id', $id)->get()->getResultArray();

        return $this->response->setJSON(['order' => $order, 'items' => $items]);
    }



   public function returns()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();
    
    $page    = (int)($request->getGet('page') ?? 1);
    $search  = $request->getGet('search') ?? '';
    $status  = $request->getGet('status') ?? 'pending';
    $type    = $request->getGet('type') ?? 'outbound'; 
    $perPage = 10;
    $offset  = ($page - 1) * $perPage;

    $builder = $db->table('sales_returns as sr');
    $builder->select('sr.*, so.order_number, ic.organization as client_name, p.name as product_name, u.full_name as staff');
    $builder->join('sales_orders as so', 'so.order_id = sr.order_id', 'left');
    $builder->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left');
    $builder->join('products as p', 'p.product_id = sr.product_id', 'left');
    $builder->join('users as u', 'u.user_id = sr.processed_by');
    
    $builder->where('sr.return_type', $type);
    if ($status !== 'all') $builder->where('sr.status', $status);
    if ($search) $builder->like('ic.organization', $search)->orLike('so.order_number', $search);

    $totalRows = $builder->countAllResults(false);
    $data['returns'] = $builder->orderBy('sr.created_at', 'DESC')->limit($perPage, $offset)->get()->getResultArray();

    // FIXED: Filter out orders that ALREADY have an existing return record
    $data['delivered_orders'] = $db->table('sales_orders as so')
        ->select('so.order_id, so.order_number, ic.organization')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->where('so.status', 'delivered')
        ->where("so.order_id NOT IN (SELECT order_id FROM sales_returns)", null, false)
        ->get()->getResultArray();

    $data['received_pos'] = $db->table('purchase_orders as po')
        ->select('po.po_id, po.po_number, s.name as sname')
        ->join('suppliers as s', 's.supplier_id = po.supplier_id')
        ->where('po.status', 'received')->get()->getResultArray();

    $data['total_rows'] = $totalRows;
    $data['current_page'] = $page;
    $data['per_page'] = $perPage;
    $data['total_pages'] = max(1, ceil($totalRows / $perPage));
    $data['active_status'] = $status;
    $data['active_type'] = $type;
    $data['search'] = $search;

    $data['title'] = "Returns Management";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "sales";
    return view('pages/admin/operations/sales/sales_returns', $data);
}

public function get_return_details($id)
{
    $db = \Config\Database::connect();
    $data = $db->table('sales_returns as sr')
        ->select('sr.*, so.order_number, ic.organization, p.name, p.sku')
        ->join('sales_orders as so', 'so.order_id = sr.order_id')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id')
        ->join('products as p', 'p.product_id = sr.product_id')
        ->where('sr.return_id', $id)
        ->get()->getRow();
    return $this->response->setJSON($data);
}

// NEW: Process Approval
public function approve_return($id)
{
    $db = \Config\Database::connect();
    $ret = $db->table('sales_returns')->where('return_id', $id)->get()->getRow();
    
    $db->transStart();
    $db->table('sales_returns')->where('return_id', $id)->update(['status' => 'approved']);
    
    // Restoration logic: Add qty back to inventory
    $soItem = $db->table('sales_order_items')->where(['order_id' => $ret->order_id, 'product_id' => $ret->product_id])->get()->getRow();
    if($soItem) {
        $db->table('inventory_batches')->where('batch_id', $soItem->batch_id)->set('quantity_avail', "quantity_avail + {$ret->quantity}", false)->update();
    }
    $db->transComplete();
    return redirect()->back()->with('success', 'Return Approved and Stock Restored.');
}

public function reject_return($id)
{
    $db = \Config\Database::connect();
    $db->table('sales_returns')->where('return_id', $id)->update(['status' => 'rejected']);
    return redirect()->back()->with('info', 'Return Request Rejected.');
}

    public function get_return_order_items($order_id)
    {
        $db = \Config\Database::connect();
        // Fetch items and the organization name for auto-fill
        $items = $db->table('sales_order_items as soi')
            ->select('soi.*, p.name, ic.organization')
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
        
        $order_id = $this->request->getPost('order_id');
        $product_id = $this->request->getPost('product_id');
        $qty = $this->request->getPost('qty');

        $db->transStart();

        // 1. Record the Return
        $db->table('sales_returns')->insert([
            'order_id' => $order_id,
            'processed_by' => $session->get('user_id') ?? 1,
            'reason' => $this->request->getPost('reason_cat') . ': ' . $this->request->getPost('notes'),
            'status' => 'approved',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 2. RESTORE INVENTORY (Audit Integrity)
        $soItem = $db->table('sales_order_items')->where(['order_id' => $order_id, 'product_id' => $product_id])->get()->getRow();
        if($soItem && $soItem->batch_id) {
            $db->table('inventory_batches')
               ->where('batch_id', $soItem->batch_id)
               ->set('quantity_avail', "quantity_avail + {$qty}", false)
               ->update();
        }

        $db->transComplete();
        return redirect()->to('admin/sales/sales-returns')->with('success', 'Return processed and stock restored.');
    }







public function pos()
{
    $db = \Config\Database::connect();
    $today = date('Y-m-d');

    // 1. DATA FOR DAILY SUMMARY (Top Tiles)
    $data['total_txns'] = $db->table('pos_transactions')->where('DATE(created_at)', $today)->countAllResults();
    $data['gross_sales'] = $db->table('pos_transactions')->selectSum('total')->where('DATE(created_at)', $today)->get()->getRow()->total ?? 0;
    $data['cash_sales'] = $db->table('pos_transactions')->selectSum('total')->where(['DATE(created_at)' => $today, 'payment_method' => 'cash'])->get()->getRow()->total ?? 0;
    $data['gcash_sales'] = $db->table('pos_transactions')->selectSum('total')->where(['DATE(created_at)' => $today, 'payment_method' => 'gcash'])->get()->getRow()->total ?? 0;

    // 2. DATA FOR HISTORY TABLE
    $data['history'] = $db->table('pos_transactions as pt')
        ->select('pt.*, (SELECT COUNT(*) FROM pos_transaction_items WHERE txn_id = pt.txn_id) as item_count')
        ->where('DATE(pt.created_at)', $today)
        ->orderBy('pt.created_at', 'DESC')->get()->getResultArray();

    $data['title'] = "Point of Sale Intelligence";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "sales";
    return view('pages/admin/operations/sales/pos', $data);
}

// AJAX: Intelligent Search (Returns full details: Expiry, Batch, Stock)
public function get_product_pos($query)
{
    $db = \Config\Database::connect();
    $product = $db->table('products as p')
        ->select('p.product_id, p.name, p.sku, ib.batch_id, ib.batch_number, ib.expires_at, ib.quantity_avail, ib.sell_price, c.name as cat_name')
        ->join('inventory_batches as ib', 'ib.product_id = p.product_id')
        ->join('categories as c', 'c.category_id = p.category_id')
        ->where('ib.quantity_avail >', 0)
        ->groupStart()
            ->like('p.name', $query)
            ->orLike('p.barcode_value', $query)
        ->groupEnd()
        ->orderBy('ib.expires_at', 'ASC') // FEFO logic: show oldest first
        ->get()->getRow();

    return $this->response->setJSON($product);
}

public function process_pos()
{
    $db = \Config\Database::connect();
    $session = session();
    $items = $this->request->getPost('items'); // Array of batch_id, qty, price
    $total = $this->request->getPost('grand_total');

    $db->transStart();

    // 1. Generate POS Transaction
    $txnData = [
        'txn_number' => 'TXN-' . time(),
        'cashier_id' => $session->get('user_id') ?? 1,
        'subtotal'   => $total / 1.12,
        'vat_amount' => $total - ($total / 1.12),
        'total'      => $total,
        'payment_method' => $this->request->getPost('payment_method'),
        'amount_tendered' => $this->request->getPost('tendered'),
        'change_due' => $this->request->getPost('change'),
        'or_number' => 'OR-' . time() // BIR Compliant Sequence
    ];
    $db->table('pos_transactions')->insert($txnData);
    $txn_id = $db->insertID();

    // 2. Process Items & Deduct Stock
    foreach ($items as $item) {
        $db->table('pos_transaction_items')->insert([
            'txn_id' => $txn_id,
            'product_id' => $item['product_id'],
            'batch_id' => $item['batch_id'],
            'quantity' => $item['qty'],
            'unit_price' => $item['price'],
            'subtotal' => $item['qty'] * $item['price']
        ]);

        // Real-time deduction
        $db->table('inventory_batches')
           ->where('batch_id', $item['batch_id'])
           ->set('quantity_avail', "quantity_avail - {$item['qty']}", false)
           ->update();
    }

    $db->transComplete();
    return $this->response->setJSON(['status' => 'success', 'txn_id' => $txn_id]);
}






}