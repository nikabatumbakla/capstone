<?php
namespace App\Controllers\Admin\Strategy;
use App\Controllers\BaseController;

class Analytics extends BaseController {

    private function getSetting($db, $key, $default) {
        $row = $db->table('store_settings')->where('setting_key', $key)->get()->getRow();
        return $row ? (float) $row->setting_value : $default;
    }

    // Daily units moved out (POS + Sales Orders) for one product, last $days days, zero-filled
    private function getDailySales($db, $productId, $days = 30) {
        $rows = $db->query("
            SELECT DATE(moved_at) as d, SUM(quantity) as qty
            FROM stock_movements
            WHERE product_id = ?
              AND movement_type IN ('pos_sale','outbound')
              AND moved_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(moved_at)
        ", [$productId, $days])->getResultArray();

        $map = [];
        foreach ($rows as $r) $map[$r['d']] = (float) $r['qty'];

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $series[$date] = $map[$date] ?? 0;
        }
        return $series;
    }

    public function dss() {
        $db = \Config\Database::connect();

        $search = $this->request->getGet('search');
        $builder = $db->table('products as p')->select('p.product_id, p.name, p.sku')->where('p.is_active', 1);
        if ($search) $builder->groupStart()->like('p.name', $search)->orLike('p.sku', $search)->groupEnd();
        $data['products_list'] = $builder->orderBy('p.name', 'ASC')->get()->getResultArray();

        $data['categories_list'] = $db->table('categories')->orderBy('sort_order','ASC')->get()->getResultArray();

        $data['active_count'] = $db->table('products')->where('is_active', 1)->countAllResults();

        $data['low_stock_alerts'] = $db->table('inventory_batches as ib')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('p.is_active', 1)
            ->where('ib.quantity_avail <= ib.reorder_level', null, false)
            ->countAllResults();

        // Auto-reorder suggestions ties directly to AutoReorder::check() output
        $data['auto_reorder_suggestions'] = $db->table('purchase_orders')
            ->where('is_auto_generated', 1)->where('status', 'pending_approval')->countAllResults();

        // Products with enough recent sales history (>=5 distinct sale days in last 30) to forecast meaningfully
        $forecastableRows = $db->query("
            SELECT product_id FROM (
                SELECT product_id, COUNT(DISTINCT DATE(moved_at)) as days
                FROM stock_movements
                WHERE movement_type IN ('pos_sale','outbound') AND moved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY product_id HAVING days >= 5
            ) x
        ")->getResultArray();
        $data['forecastable_count'] = count($forecastableRows);

        // Predicted stockouts within 30 days: avg daily usage vs current stock, computed per product
        $usageRows = $db->query("
            SELECT product_id, SUM(quantity)/30 as avg_daily
            FROM stock_movements
            WHERE movement_type IN ('pos_sale','outbound') AND moved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY product_id
        ")->getResultArray();
        $stockRows = $db->query("SELECT product_id, SUM(quantity_avail) as stock FROM inventory_batches GROUP BY product_id")->getResultArray();
        $stockMap = [];
        foreach ($stockRows as $r) $stockMap[$r['product_id']] = (float) $r['stock'];

        $predictedStockouts = 0;
        foreach ($usageRows as $r) {
            $avgDaily = (float) $r['avg_daily'];
            $stock = $stockMap[$r['product_id']] ?? 0;
            if ($avgDaily > 0 && ($stock / $avgDaily) <= 30) $predictedStockouts++;
        }
        $data['predicted_stockouts'] = $predictedStockouts;

        // Low performing products: no outbound movement recently despite having stock
        $data['low_performing'] = $db->query("
            SELECT p.product_id, p.name,
                   COALESCE(SUM(ib.quantity_avail), 0) as stock,
                   MAX(sm.moved_at) as last_moved
            FROM products p
            LEFT JOIN inventory_batches ib ON ib.product_id = p.product_id
            LEFT JOIN stock_movements sm ON sm.product_id = p.product_id AND sm.movement_type IN ('pos_sale','outbound')
            WHERE p.is_active = 1
            GROUP BY p.product_id
            HAVING stock > 0 AND (last_moved IS NULL OR last_moved < DATE_SUB(NOW(), INTERVAL 30 DAY))
            ORDER BY last_moved ASC
            LIMIT 5
        ")->getResultArray();

        // Real supplier scorecard rows (values only as accurate as what's been computed into that table)
        $data['supplier_performance'] = $db->table('supplier_scorecards as sc')
            ->select('s.name, sc.on_time_rate, sc.accuracy_rate')
            ->join('suppliers as s', 's.supplier_id = sc.supplier_id')
            ->orderBy('sc.total_orders', 'DESC')->limit(5)->get()->getResultArray();

        // Sales trend: top product by units moved (last 30 days)
        $topProduct = $db->query("
            SELECT p.name, SUM(sm.quantity) as total_qty
            FROM stock_movements sm JOIN products p ON p.product_id = sm.product_id
            WHERE sm.movement_type IN ('pos_sale','outbound') AND sm.moved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY sm.product_id ORDER BY total_qty DESC LIMIT 1
        ")->getRow();
        $data['top_product'] = $topProduct->name ?? 'No sales yet';

        // Top client type by revenue (this month, institutional orders only)
        $topClientType = $db->query("
            SELECT ic.client_type, SUM(so.total) as total_rev
            FROM sales_orders so JOIN institutional_clients ic ON ic.client_id = so.client_id
            WHERE YEAR(so.created_at)=YEAR(CURDATE()) AND MONTH(so.created_at)=MONTH(CURDATE()) AND so.status != 'cancelled'
            GROUP BY ic.client_type ORDER BY total_rev DESC LIMIT 1
        ")->getRow();
        $data['top_client_type'] = $topClientType ? ucfirst($topClientType->client_type) : 'No orders yet';

        $monthTotal = $db->table('pos_transactions')->selectSum('total')
            ->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)
            ->where('status', 'completed')->get()->getRow()->total ?? 0;
        $monthTotal += $db->table('sales_orders')->selectSum('total')
            ->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)
            ->where('status !=', 'cancelled')->get()->getRow()->total ?? 0;
        $data['top_sales_total'] = $monthTotal;

        $data['title'] = "Predictive Intelligence";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "analytics";
        return view('pages/admin/strategy/analytics/predictive_dss', $data);
    }

    private function getMonthlySales($db, $productId, $months = 12) {
    $rows = $db->query("
        SELECT DATE_FORMAT(moved_at, '%Y-%m') as ym, SUM(quantity) as qty
        FROM stock_movements
        WHERE product_id = ?
          AND movement_type IN ('pos_sale','outbound')
          AND moved_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
        GROUP BY ym
    ", [$productId, $months])->getResultArray();

    $map = [];
    foreach ($rows as $r) $map[$r['ym']] = (float) $r['qty'];

    $series = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $ym = date('Y-m', strtotime("-{$i} months"));
        $series[$ym] = $map[$ym] ?? 0;
    }
    return $series;
}

public function get_forecast($pid) 
{
    $db = \Config\Database::connect();

    $product = $db->table('products')->where('product_id', $pid)->get()->getRow();
    if (!$product) return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);

    // ---- MONTHLY: drives the forecast chart and narrative ----
    $from = $this->request->getGet('from') ?: date('Y-m', strtotime('-11 months'));
    $to   = $this->request->getGet('to') ?: date('Y-m');

    $rows = $db->query("
        SELECT DATE_FORMAT(moved_at, '%Y-%m') as ym, SUM(quantity) as qty
        FROM stock_movements
        WHERE product_id = ? AND movement_type IN ('pos_sale','outbound')
          AND DATE_FORMAT(moved_at, '%Y-%m') BETWEEN ? AND ?
        GROUP BY ym
    ", [$pid, $from, $to])->getResultArray();

    $map = [];
    foreach ($rows as $r) $map[$r['ym']] = (float) $r['qty'];

    $start = new \DateTime($from . '-01');
    $end = new \DateTime($to . '-01');
    $mLabels = []; $mValues = [];
    while ($start <= $end) {
        $ym = $start->format('Y-m');
        $mLabels[] = $ym;
        $mValues[] = $map[$ym] ?? 0;
        $start->modify('+1 month');
    }
    $mCount = count($mValues);
    $monthsWithSales = count(array_filter($mValues, fn($v) => $v > 0));

    if ($monthsWithSales < 2) {
        return $this->response->setJSON(['error' => 'Not enough sales history in this date range for a reliable forecast (need at least 2 months with recorded sales).']);
    }

    $sumX=0; $sumY=0; $sumXY=0; $sumX2=0;
    foreach ($mValues as $i => $y) { $x=$i+1; $sumX+=$x; $sumY+=$y; $sumXY+=($x*$y); $sumX2+=($x*$x); }
    $den = ($mCount * $sumX2 - $sumX * $sumX);
    $slope = $den != 0 ? ($mCount*$sumXY - $sumX*$sumY) / $den : 0;
    $intercept = ($sumY - $slope*$sumX) / $mCount;

    $regressionLine = [];
    foreach ($mValues as $i => $y) $regressionLine[] = round($intercept + $slope*($i+1), 1);

    $meanY = $sumY / $mCount;
    $ssTot=0; $ssRes=0;
    foreach ($mValues as $i => $y) { $p = $intercept + $slope*($i+1); $ssTot += pow($y-$meanY,2); $ssRes += pow($y-$p,2); }
    $r2 = $ssTot > 0 ? max(0, 1 - ($ssRes/$ssTot)) : 0;

    $forecastNextMonth = round($intercept + $slope * ($mCount + 1), 1);
    $trendDirection = $slope > 0.5 ? 'upward' : ($slope < -0.5 ? 'downward' : 'stable');

    // ---- DAILY: drives ROP / EOQ (needs a per-day rate, not per-month) ----
    $daily = $this->getDailySales($db, $pid, 30);
    $dValues = array_values($daily);
    $avgDailyUsage = array_sum($dValues) / count($dValues);

    $supplierLead = $db->query("SELECT s.lead_time_days FROM products p JOIN suppliers s ON s.supplier_id = p.supplier_id WHERE p.product_id = ?", [$pid])->getRow();
    $leadTimeDays = $supplierLead ? (float) $supplierLead->lead_time_days : 7;
    $safetyDays = $this->getSetting($db, 'reorder_safety_days', 3);
    $safetyStock = $avgDailyUsage * $safetyDays;
    $rop = ($avgDailyUsage * $leadTimeDays) + $safetyStock;

    $costRow = $db->table('inventory_batches')->select('cost_price')->where('product_id', $pid)->orderBy('received_at','DESC')->get()->getRow();
    $unitCost = $costRow ? (float) $costRow->cost_price : 0;
    $orderCost = $this->getSetting($db, 'eoq_order_cost', 150);
    $holdingRate = $this->getSetting($db, 'eoq_holding_cost_rate', 0.15);
    $holdingCost = $unitCost > 0 ? ($unitCost * $holdingRate) : 10;
    $annualDemand = $avgDailyUsage * 365;
    $eoq = $holdingCost > 0 ? sqrt((2 * $annualDemand * $orderCost) / $holdingCost) : 0;

    $currentStock = (float) ($db->table('inventory_batches')->selectSum('quantity_avail')->where('product_id', $pid)->get()->getRow()->quantity_avail ?? 0);
    $daysUntilStockout = $avgDailyUsage > 0 ? round($currentStock / $avgDailyUsage) : null;
    $stockoutDate = $daysUntilStockout !== null ? date('M d, Y', strtotime("+{$daysUntilStockout} days")) : null;

    $pendingPO = $db->table('purchase_order_items as poi')
        ->select('po.po_id, po.po_number')
        ->join('purchase_orders as po', 'po.po_id = poi.po_id')
        ->where('poi.product_id', $pid)->where('po.is_auto_generated', 1)->where('po.status', 'pending_approval')
        ->get()->getRow();

    return $this->response->setJSON([
        'product_name' => $product->name,
        'monthly_labels' => $mLabels, 'monthly_values' => $mValues, 'monthly_regression' => $regressionLine,
        'slope' => round($slope, 2), 'intercept' => round($intercept, 2), 'r2' => round($r2, 3),
        'trend_direction' => $trendDirection,
        'avg_monthly_sales' => round($meanY, 1),
        'forecast_next_month' => $forecastNextMonth,
        'avg_daily_usage' => round($avgDailyUsage, 2),
        'lead_time_days' => $leadTimeDays, 'safety_stock' => round($safetyStock, 1), 'rop' => round($rop),
        'eoq' => round($eoq), 'unit_cost' => $unitCost, 'order_cost' => $orderCost, 'holding_cost' => round($holdingCost, 2),
        'annual_demand' => round($annualDemand), 'current_stock' => $currentStock,
        'days_until_stockout' => $daysUntilStockout, 'stockout_date' => $stockoutDate,
        'pending_po' => $pendingPO ? ['po_id' => $pendingPO->po_id, 'po_number' => $pendingPO->po_number] : null,
    ]);
}

// AJAX: full supplier scorecard list for the drawer
public function get_supplier_report() {
    $db = \Config\Database::connect();
    $data = $db->table('supplier_scorecards as sc')
        ->select('s.name, s.lead_time_days, sc.on_time_rate, sc.accuracy_rate, sc.total_orders, sc.on_time_deliveries, sc.accurate_orders')
        ->join('suppliers as s', 's.supplier_id = sc.supplier_id')
        ->orderBy('sc.total_orders', 'DESC')->get()->getResultArray();
    return $this->response->setJSON($data);
}

// AJAX: full low-performing / dead stock list for the drawer
public function get_low_performing_report() {
    $db = \Config\Database::connect();
    $data = $db->query("
        SELECT p.product_id, p.name, c.name as cat_name,
               COALESCE(SUM(ib.quantity_avail), 0) as stock,
               MAX(sm.moved_at) as last_moved
        FROM products p
        JOIN categories c ON c.category_id = p.category_id
        LEFT JOIN inventory_batches ib ON ib.product_id = p.product_id
        LEFT JOIN stock_movements sm ON sm.product_id = p.product_id AND sm.movement_type IN ('pos_sale','outbound')
        WHERE p.is_active = 1
        GROUP BY p.product_id
        HAVING stock > 0 AND (last_moved IS NULL OR last_moved < DATE_SUB(NOW(), INTERVAL 30 DAY))
        ORDER BY last_moved ASC
    ")->getResultArray();
    return $this->response->setJSON($data);
}


public function reports()
{
    $db = \Config\Database::connect();
    $request = \Config\Services::request();

    $movementType = $request->getGet('movement') ?: '';
    $search = trim((string) ($request->getGet('search') ?? ''));
    $page = (int) ($request->getGet('page') ?? 1);
    if ($page < 1) $page = 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $monthTotal = $db->table('pos_transactions')->selectSum('total')
        ->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)
        ->where('status', 'completed')->get()->getRow()->total ?? 0;
    $monthTotal += $db->table('sales_orders')->selectSum('total')
        ->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)
        ->where('status !=', 'cancelled')->get()->getRow()->total ?? 0;
    $data['total_revenue'] = $monthTotal;

    $data['inventory_value'] = $db->query("SELECT COALESCE(SUM(quantity_avail * cost_price),0) as v FROM inventory_batches")->getRow()->v;
    $data['expiry_waste'] = $db->query("SELECT COALESCE(SUM(quantity_avail * cost_price),0) as v FROM inventory_batches WHERE expires_at IS NOT NULL AND expires_at < CURDATE()")->getRow()->v;

    // Real "system integrity" figure: audit trail volume + how current it is
    $data['audit_log_count'] = $db->table('stock_movements')->countAllResults();
    $lastMovement = $db->table('stock_movements')->orderBy('moved_at', 'DESC')->get()->getRow();
    $data['last_audit_time'] = $lastMovement ? $lastMovement->moved_at : null;

    $applyFilters = function($builder) use ($movementType, $search) {
        if ($movementType !== '') $builder->where('sm.movement_type', $movementType);
        if ($search !== '') {
            $builder->groupStart()->like('p.name', $search)->orLike('p.sku', $search)->groupEnd();
        }
        return $builder;
    };

    $countBuilder = $db->table('stock_movements as sm')->join('products as p', 'p.product_id = sm.product_id');
    $applyFilters($countBuilder);
    $totalRows = $countBuilder->countAllResults();

    $builder = $db->table('stock_movements as sm')
        ->select('sm.*, p.name as pname, p.sku, u.full_name as staff')
        ->join('products as p', 'p.product_id = sm.product_id')
        ->join('users as u', 'u.user_id = sm.scanned_by', 'left');
    $applyFilters($builder);
    $builder->orderBy('sm.moved_at', 'DESC')->limit($perPage, $offset);
    $data['reports_data'] = $builder->get()->getResultArray();

    $data['total_rows']   = $totalRows;
    $data['current_page'] = $page;
    $data['per_page']     = $perPage;
    $data['total_pages']  = max(1, (int) ceil($totalRows / $perPage));
    $data['movement_filter'] = $movementType;
    $data['search'] = $search;

    $data['title'] = "Reports & Analytics";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "reports";
    return view('pages/admin/strategy/analytics/reports', $data);
}

public function get_movement_details($id)
{
    $db = \Config\Database::connect();
    $row = $db->table('stock_movements as sm')
        ->select('sm.*, p.name as pname, p.sku, p.barcode_value, ib.batch_number, u.full_name as staff')
        ->join('products as p', 'p.product_id = sm.product_id')
        ->join('inventory_batches as ib', 'ib.batch_id = sm.batch_id', 'left')
        ->join('users as u', 'u.user_id = sm.scanned_by', 'left')
        ->where('sm.movement_id', $id)->get()->getRow();

    if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Log not found']);
    return $this->response->setJSON($row);
}

public function export($type, $format)
{
    $db = \Config\Database::connect();
    $filename = "RobinRose_" . ucfirst($type) . "_Report_" . date('Ymd');

    $columns = [];
    $data = [];

    switch ($type) {
        case 'inventory':
            $columns = ['Product', 'Batch', 'Available Qty', 'Expiry Date'];
            $data = $db->table('inventory_batches as ib')->select('p.name, ib.batch_number, ib.quantity_avail, ib.expires_at')
                ->join('products as p', 'p.product_id = ib.product_id')->get()->getResultArray();
            break;
        case 'sales':
            $columns = ['Order #', 'Client', 'Total', 'Status'];
            $data = $db->table('sales_orders as so')->select('so.order_number, ic.organization, so.total, so.status')
                ->join('institutional_clients as ic', 'ic.client_id = so.client_id')->get()->getResultArray();
            break;
        case 'waste':
            $columns = ['Product', 'Batch', 'Qty Remaining', 'Expired On', 'Value Lost (₱)'];
            $rows = $db->query("SELECT p.name, ib.batch_number, ib.quantity_avail, ib.expires_at, (ib.quantity_avail*ib.cost_price) as loss
                FROM inventory_batches ib JOIN products p ON p.product_id = ib.product_id
                WHERE ib.expires_at IS NOT NULL AND ib.expires_at < CURDATE() AND ib.quantity_avail > 0")->getResultArray();
            $data = $rows;
            break;
        case 'supplier':
            $columns = ['Supplier', 'On-Time Rate', 'Accuracy Rate', 'Total Orders', 'Lead Time (days)'];
            $data = $db->table('supplier_scorecards as sc')->select('s.name, sc.on_time_rate, sc.accuracy_rate, sc.total_orders, s.lead_time_days')
                ->join('suppliers as s', 's.supplier_id = sc.supplier_id')->get()->getResultArray();
            break;
        case 'pos':
            $columns = ['OR #', 'Cashier', 'Total', 'Payment Method', 'Date'];
            $data = $db->table('pos_transactions as pt')->select('pt.or_number, u.full_name as cashier, pt.total, pt.payment_method, pt.created_at')
                ->join('users as u', 'u.user_id = pt.cashier_id', 'left')
                ->where('pt.status', 'completed')->orderBy('pt.created_at', 'DESC')->get()->getResultArray();
            break;
        case 'dss':
            $columns = ['Product', 'Current Stock', 'Reorder Level', 'Status'];
            $data = $db->table('inventory_batches as ib')->select('p.name, ib.quantity_avail, ib.reorder_level,
                    "CASE WHEN ib.quantity_avail <= ib.reorder_level THEN \'Reorder Needed\' ELSE \'Stable\' END as status', false)
                ->join('products as p', 'p.product_id = ib.product_id')->get()->getResultArray();
            break;
        default:
            return redirect()->back()->with('error', 'Unknown report type.');
    }

    if ($format === 'csv') {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename.csv\"");
        $output = fopen("php://output", "w");
        fputcsv($output, $columns);
        foreach ($data as $row) fputcsv($output, $row);
        fclose($output);
        exit;
    }

    // PDF and XLSX both render through one formal printable view for now;
    // once the Robin Rose letterhead template arrives, this is the single place to restyle.
    return view('pages/admin/strategy/analytics/printable_report', [
        'data' => $data, 'columns' => $columns, 'type' => $type, 'format' => $format,
        'generated_at' => date('F j, Y g:i A'),
    ]);
}

    public function get_products_by_category($catId = null)
{
    $db = \Config\Database::connect();
    $builder = $db->table('products')->select('product_id, name, sku')->where('is_active', 1);
    if ($catId) $builder->where('category_id', $catId);
    return $this->response->setJSON($builder->orderBy('name', 'ASC')->get()->getResultArray());
}

public function get_overall_trend()
{
    $db = \Config\Database::connect();
    $from = $this->request->getGet('from') ?: date('Y-m', strtotime('-11 months'));
    $to   = $this->request->getGet('to') ?: date('Y-m');

    $rows = $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(total) as revenue
        FROM (
            SELECT created_at, total FROM pos_transactions WHERE status = 'completed'
            UNION ALL
            SELECT created_at, total FROM sales_orders WHERE status != 'cancelled'
        ) combined
        WHERE DATE_FORMAT(created_at, '%Y-%m') BETWEEN ? AND ?
        GROUP BY ym ORDER BY ym ASC
    ", [$from, $to])->getResultArray();

    $map = [];
    foreach ($rows as $r) $map[$r['ym']] = (float) $r['revenue'];

    $start = new \DateTime($from . '-01');
    $end = new \DateTime($to . '-01');
    $labels = []; $values = [];
    while ($start <= $end) {
        $ym = $start->format('Y-m');
        $labels[] = $ym;
        $values[] = $map[$ym] ?? 0;
        $start->modify('+1 month');
    }

    return $this->response->setJSON(['labels' => $labels, 'values' => $values]);
}

}