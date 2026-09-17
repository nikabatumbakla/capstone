<?php

namespace App\Models\Admin\Strategy;

use CodeIgniter\Model;

class AnalyticsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getSetting(string $key, $default)
    {
        $row = $this->db->table('store_settings')->where('setting_key', $key)->get()->getRow();
        return $row ? (float) $row->setting_value : $default;
    }

    public function getStoreInfo(): array
    {
        $rows = $this->db->table('store_settings')->get()->getResultArray();
        $info = [];
        foreach ($rows as $row) $info[$row['setting_key']] = $row['setting_value'];
        return $info;
    }

    public function getDailySales(int $productId, int $days = 30): array
    {
        $rows = $this->db->query("
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

    public function getDssSummary(): array
    {
        $activeCount = $this->db->table('products')->where('is_active', 1)->countAllResults();

        $lowStockAlerts = $this->db->table('inventory_batches as ib')
            ->join('products as p', 'p.product_id = ib.product_id')
            ->where('p.is_active', 1)
            ->where('ib.quantity_avail <= ib.reorder_level', null, false)
            ->countAllResults();

        $autoReorderSuggestions = $this->db->table('purchase_orders')
            ->where('is_auto_generated', 1)->where('status', 'pending_approval')->countAllResults();

        $forecastableRows = $this->db->query("
            SELECT product_id FROM (
                SELECT product_id, COUNT(DISTINCT DATE(moved_at)) as days
                FROM stock_movements
                WHERE movement_type IN ('pos_sale','outbound') AND moved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY product_id HAVING days >= 5
            ) x
        ")->getResultArray();

        $usageRows = $this->db->query("
            SELECT product_id, SUM(quantity)/30 as avg_daily
            FROM stock_movements
            WHERE movement_type IN ('pos_sale','outbound') AND moved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY product_id
        ")->getResultArray();
        $stockRows = $this->db->query("SELECT product_id, SUM(quantity_avail) as stock FROM inventory_batches GROUP BY product_id")->getResultArray();
        $stockMap = [];
        foreach ($stockRows as $r) $stockMap[$r['product_id']] = (float) $r['stock'];

        $predictedStockouts = 0;
        foreach ($usageRows as $r) {
            $avgDaily = (float) $r['avg_daily'];
            $stock = $stockMap[$r['product_id']] ?? 0;
            if ($avgDaily > 0 && ($stock / $avgDaily) <= 30) $predictedStockouts++;
        }

        $topProduct = $this->db->query("
            SELECT p.name, SUM(sm.quantity) as total_qty
            FROM stock_movements sm JOIN products p ON p.product_id = sm.product_id
            WHERE sm.movement_type IN ('pos_sale','outbound') AND sm.moved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY sm.product_id ORDER BY total_qty DESC LIMIT 1
        ")->getRow();

        $topClientType = $this->db->query("
            SELECT ic.client_type, SUM(so.total) as total_rev
            FROM sales_orders so JOIN institutional_clients ic ON ic.client_id = so.client_id
            WHERE YEAR(so.created_at)=YEAR(CURDATE()) AND MONTH(so.created_at)=MONTH(CURDATE()) AND so.status != 'cancelled'
            GROUP BY ic.client_type ORDER BY total_rev DESC LIMIT 1
        ")->getRow();

        return [
            'active_count'             => $activeCount,
            'low_stock_alerts'         => $lowStockAlerts,
            'auto_reorder_suggestions' => $autoReorderSuggestions,
            'forecastable_count'       => count($forecastableRows),
            'predicted_stockouts'      => $predictedStockouts,
            'low_performing'           => $this->getLowPerformingProducts(5),
            'supplier_performance'     => $this->getSupplierPerformance(5),
            'top_product'              => $topProduct->name ?? 'No sales yet',
            'top_client_type'          => $topClientType ? ucfirst($topClientType->client_type) : 'No orders yet',
            'top_sales_total'          => $this->getMonthRevenue(),
        ];
    }

    public function searchProducts(string $search = ''): array
    {
        $builder = $this->db->table('products as p')->select('p.product_id, p.name, p.barcode_value')->where('p.is_active', 1);
        if ($search) $builder->groupStart()->like('p.name', $search)->orLike('p.barcode_value', $search)->groupEnd();
        return $builder->orderBy('p.name', 'ASC')->get()->getResultArray();
    }

    public function getCategoriesList(): array
    {
        return $this->db->table('categories')->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    public function getProductsByCategory(?int $catId = null): array
    {
        $builder = $this->db->table('products')->select('product_id, name, barcode_value')->where('is_active', 1);
        if ($catId) $builder->where('category_id', $catId);
        return $builder->orderBy('name', 'ASC')->get()->getResultArray();
    }

    public function getSupplierPerformance(?int $limit = null): array
    {
        $suppliers = $this->db->table('suppliers')->where('is_active', 1)->get()->getResultArray();
        $rows = [];
        foreach ($suppliers as $s) {
            $stats = \App\Libraries\ScorecardCalculator::calculate($this->db, (int) $s['supplier_id']);
            if ($stats->total_orders == 0) continue;
            $rows[] = [
                'name'           => $s['name'],
                'lead_time_days' => $s['lead_time_days'],
                'on_time_rate'   => $stats->on_time_rate,
                'accuracy_rate'  => $stats->accuracy_rate,
                'total_orders'   => $stats->total_orders,
            ];
        }
        usort($rows, fn($a, $b) => $b['total_orders'] <=> $a['total_orders']);
        return $limit ? array_slice($rows, 0, $limit) : $rows;
    }

    public function getLowPerformingProducts(?int $limit = null): array
    {
        $sql = "
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
            ORDER BY last_moved ASC" . ($limit ? " LIMIT {$limit}" : "");
        return $this->db->query($sql)->getResultArray();
    }

    public function getForecastData(int $pid, string $from, string $to)
{
    $product = $this->db->table('products')->where('product_id', $pid)->get()->getRow();
    if (!$product) return null;

    $rows = $this->db->query("
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
        return ['error' => 'Not enough sales history in this date range for a reliable forecast (need at least 2 months with recorded sales).'];
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

    $mae = 0;
    foreach ($mValues as $i => $actual) $mae += abs($actual - $regressionLine[$i]);
    $mae = round($mae / $mCount, 2);

    // ============ FORWARD FORECAST — next 3 months beyond the training range ============
    $forecastMonths = [];
    $lastLabel = end($mLabels);
    $lastKey = (int) substr($lastLabel, 0, 4) * 12 + (int) substr($lastLabel, 5, 2);
    for ($step = 1; $step <= 3; $step++) {
        $futureKey = $lastKey + $step;
        $futureYear = intdiv($futureKey - 1, 12);
        $futureMonth = $futureKey - ($futureYear * 12);
        $predicted = round($intercept + $slope * ($mCount + $step), 1);
        $forecastMonths[] = ['label' => sprintf('%04d-%02d', $futureYear, $futureMonth), 'predicted' => max(0, $predicted)];
    }

    $daily = $this->getDailySales($pid, 30);
    $dValues = array_values($daily);
    $avgDailyUsage = array_sum($dValues) / count($dValues);

    $supplierLead = $this->db->table('supplier_product_catalog as spc')
        ->select('s.lead_time_days')
        ->join('suppliers as s', 's.supplier_id = spc.supplier_id')
        ->where('spc.product_id', $pid)
        ->orderBy('s.lead_time_days', 'ASC')
        ->limit(1)->get()->getRow();
    $leadTimeDays = $supplierLead ? (float) $supplierLead->lead_time_days : 7;

    $safetyDays = $this->getSetting('reorder_safety_days', 3);
    $safetyStock = $avgDailyUsage * $safetyDays;
    $rop = ($avgDailyUsage * $leadTimeDays) + $safetyStock;

    $costRow = $this->db->table('inventory_batches')->select('cost_price')->where('product_id', $pid)->orderBy('received_at','DESC')->get()->getRow();
    $unitCost = $costRow ? (float) $costRow->cost_price : 0;
    $orderCost = $this->getSetting('eoq_order_cost', 150);
    $holdingRate = $this->getSetting('eoq_holding_cost_rate', 0.15);
    $holdingCost = $unitCost > 0 ? ($unitCost * $holdingRate) : 10;
    $annualDemand = $avgDailyUsage * 365;
    $eoq = $holdingCost > 0 ? sqrt((2 * $annualDemand * $orderCost) / $holdingCost) : 0;

    $currentStock = (float) ($this->db->table('inventory_batches')->selectSum('quantity_avail')->where('product_id', $pid)->get()->getRow()->quantity_avail ?? 0);
    $daysUntilStockout = $avgDailyUsage > 0 ? round($currentStock / $avgDailyUsage) : null;
    $stockoutDate = $daysUntilStockout !== null ? date('M d, Y', strtotime("+{$daysUntilStockout} days")) : null;

    $pendingPO = $this->db->table('purchase_order_items as poi')
        ->select('po.po_id, po.po_number')
        ->join('purchase_orders as po', 'po.po_id = poi.po_id')
        ->where('poi.product_id', $pid)->where('po.is_auto_generated', 1)->where('po.status', 'pending_approval')
        ->get()->getRow();

    return [
        'product_name' => $product->name,
        'monthly_labels' => $mLabels, 'monthly_values' => $mValues, 'monthly_regression' => $regressionLine,
        'daily_labels' => array_keys($daily), 'daily_values' => $dValues,
        'slope' => round($slope, 2), 'intercept' => round($intercept, 2), 'r2' => round($r2, 3),
        'trend_direction' => $trendDirection,
        'avg_monthly_sales' => round($meanY, 1),
        'forecast_next_month' => $forecastNextMonth,
        'mae' => $mae,
        'forecast_months' => $forecastMonths,
        'avg_daily_usage' => round($avgDailyUsage, 2),
        'lead_time_days' => $leadTimeDays, 'safety_stock' => round($safetyStock, 1), 'rop' => round($rop),
        'eoq' => round($eoq), 'unit_cost' => $unitCost, 'order_cost' => $orderCost, 'holding_cost' => round($holdingCost, 2),
        'annual_demand' => round($annualDemand), 'current_stock' => $currentStock,
        'days_until_stockout' => $daysUntilStockout, 'stockout_date' => $stockoutDate,
        'pending_po' => $pendingPO ? ['po_id' => $pendingPO->po_id, 'po_number' => $pendingPO->po_number] : null,
    ];
}

public function getAllProductsForecastData(string $from, string $to): array
{
    $histStart = 2025 * 12 + 1;
    $histEnd = 2026 * 12 + 8; // matches BirComplianceModel's historical cutoff (Jan 2025 - Aug 2026)
    $monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    $start = new \DateTime($from . '-01');
    $end = new \DateTime($to . '-01');
    $mLabels = []; $mValues = [];

    while ($start <= $end) {
        $year = (int) $start->format('Y');
        $month = (int) $start->format('n');
        $key = $year * 12 + $month;
        $ym = $start->format('Y-m');
        $mLabels[] = $ym;

        if ($key >= $histStart && $key <= $histEnd) {
            $monthName = $monthNames[$month - 1];
            $cash = $this->db->table('cash_sales_journal_monthly_totals')
                ->where('txn_year', $year)->where('txn_month', $monthName)->get()->getRow();
            $client = $this->db->table('client_journal_monthly_totals')
                ->where('txn_year', $year)->where('txn_month', $monthName)->get()->getRow();
            $revenue = (float) ($cash->total_invoice_amount ?? 0) + (float) ($client->total_computed_amount ?? 0);
        } else {
            $pos = $this->db->table('pos_transactions')->selectSum('total')
                ->where('YEAR(created_at)', $year)->where('MONTH(created_at)', $month)
                ->where('status', 'completed')->get()->getRow();
            $so = $this->db->table('sales_orders')->selectSum('total')
                ->where('YEAR(created_at)', $year)->where('MONTH(created_at)', $month)
                ->where('status !=', 'cancelled')->get()->getRow();
            $revenue = (float) ($pos->total ?? 0) + (float) ($so->total ?? 0);
        }

        $mValues[] = round($revenue, 2);
        $start->modify('+1 month');
    }

    $mCount = count($mValues);
    $monthsWithSales = count(array_filter($mValues, fn($v) => $v > 0));

    if ($monthsWithSales < 2) {
        return ['error' => 'Not enough revenue history in this date range for a reliable forecast.'];
    }

    // ============ LINEAR REGRESSION on monthly revenue ============
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

    // ============ MAE — in-sample fit accuracy ============
    $mae = 0;
    foreach ($mValues as $i => $actual) $mae += abs($actual - $regressionLine[$i]);
    $mae = round($mae / $mCount, 2);

    // ============ FORWARD FORECAST — next 3 months beyond the training
    // range, genuinely unknown at prediction time (e.g. Sep/Oct/Nov 2026
    // when trained on Jan 2025 - Aug 2026) ============
    $forecastMonths = [];
    $lastLabel = end($mLabels);
    $lastKey = (int) substr($lastLabel, 0, 4) * 12 + (int) substr($lastLabel, 5, 2);
    for ($step = 1; $step <= 3; $step++) {
        $futureKey = $lastKey + $step;
        $futureYear = intdiv($futureKey - 1, 12);
        $futureMonth = $futureKey - ($futureYear * 12);
        $predicted = round($intercept + $slope * ($mCount + $step), 1);

        $forecastMonths[] = [
            'label' => sprintf('%04d-%02d', $futureYear, $futureMonth),
            'predicted' => max(0, $predicted),
        ];
    }

    // Daily series stays live-only (last 30 days) — matches the same convention as the per-product forecast
    $dailyRows = $this->db->query("
        SELECT DATE(created_at) as d, SUM(total) as amt FROM pos_transactions
        WHERE status='completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at)
    ")->getResultArray();
    $dailyMap = [];
    foreach ($dailyRows as $r) $dailyMap[$r['d']] = (float) $r['amt'];
    $dLabels = []; $dValues = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $dLabels[] = $date;
        $dValues[] = $dailyMap[$date] ?? 0;
    }

    return [
        'product_name' => 'All Products (Combined Revenue)',
        'monthly_labels' => $mLabels, 'monthly_values' => $mValues, 'monthly_regression' => $regressionLine,
        'daily_labels' => $dLabels, 'daily_values' => $dValues,
        'slope' => round($slope, 2), 'intercept' => round($intercept, 2), 'r2' => round($r2, 3),
        'trend_direction' => $trendDirection,
        'avg_monthly_sales' => round($meanY, 1),
        'forecast_next_month' => $forecastNextMonth,
        'mae' => $mae,
        'forecast_months' => $forecastMonths,
        'avg_daily_usage' => round(array_sum($dValues) / count($dValues), 2),
        'lead_time_days' => null, 'safety_stock' => null, 'rop' => null,
        'eoq' => null, 'unit_cost' => null, 'order_cost' => null, 'holding_cost' => null,
        'annual_demand' => null, 'current_stock' => null,
        'days_until_stockout' => null, 'stockout_date' => null, 'pending_po' => null,
        'is_aggregate' => true, 'is_revenue_based' => true,
    ];
}


    public function getMonthRevenue(): float
    {
        $pos = $this->db->table('pos_transactions')->selectSum('total')
            ->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)
            ->where('status', 'completed')->get()->getRow()->total ?? 0;
        $orders = $this->db->table('sales_orders')->selectSum('total')
            ->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)
            ->where('status !=', 'cancelled')->get()->getRow()->total ?? 0;
        return (float) $pos + (float) $orders;
    }

    public function getInventoryValuation(): float
    {
        return (float) ($this->db->query("SELECT COALESCE(SUM(quantity_avail * cost_price),0) as v FROM inventory_batches")->getRow()->v ?? 0);
    }

    public function getExpiryWaste(): float
    {
        return (float) ($this->db->query("SELECT COALESCE(SUM(quantity_avail * cost_price),0) as v FROM inventory_batches WHERE expires_at IS NOT NULL AND expires_at < CURDATE()")->getRow()->v ?? 0);
    }

    public function getAuditTrailInfo(): array
    {
        $count = $this->db->table('stock_movements')->countAllResults();
        $last = $this->db->table('stock_movements')->orderBy('moved_at', 'DESC')->get()->getRow();
        return ['count' => $count, 'last_at' => $last ? $last->moved_at : null];
    }

    public function getMovementLogs(string $movementType, string $search, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $apply = function ($b) use ($movementType, $search) {
            if ($movementType !== '') $b->where('sm.movement_type', $movementType);
            if ($search !== '') $b->groupStart()->like('p.name', $search)->orLike('p.barcode_value', $search)->groupEnd();
            return $b;
        };

        $countBuilder = $this->db->table('stock_movements as sm')->join('products as p', 'p.product_id = sm.product_id');
        $apply($countBuilder);
        $total = $countBuilder->countAllResults();

        $builder = $this->db->table('stock_movements as sm')
            ->select('sm.*, p.name as pname, p.barcode_value, u.full_name as staff')
            ->join('products as p', 'p.product_id = sm.product_id')
            ->join('users as u', 'u.user_id = sm.scanned_by', 'left');
        $apply($builder);
        $builder->orderBy('sm.moved_at', 'DESC')->limit($perPage, $offset);

        return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
    }

    public function getMovementDetails(int $id)
    {
        return $this->db->table('stock_movements as sm')
            ->select('sm.*, p.name as pname, p.barcode_value, ib.batch_number, u.full_name as staff')
            ->join('products as p', 'p.product_id = sm.product_id')
            ->join('inventory_batches as ib', 'ib.batch_id = sm.batch_id', 'left')
            ->join('users as u', 'u.user_id = sm.scanned_by', 'left')
            ->where('sm.movement_id', $id)->get()->getRow();
    }

    public function getOverallTrend(string $from, string $to): array
    {
        $rows = $this->db->query("
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
        return ['labels' => $labels, 'values' => $values];
    }

    public function getExportData(string $type): ?array
    {
        switch ($type) {
            case 'inventory':
                return [
                    'title' => 'Inventory Movement Report',
                    'columns' => ['Product', 'Batch', 'Available Qty', 'Expiry Date'],
                    'data' => $this->db->table('inventory_batches as ib')
                        ->select('p.name, ib.batch_number, ib.quantity_avail, ib.expires_at')
                        ->join('products as p', 'p.product_id = ib.product_id')
                        ->orderBy('ib.received_at', 'DESC')->get()->getResultArray(),
                ];
            case 'sales':
                return [
                    'title' => 'Sales Analytics Report',
                    'columns' => ['Order #', 'Client', 'Total', 'Status'],
                    'data' => $this->db->table('sales_orders as so')
                        ->select("so.order_number, COALESCE(ic.organization, gc.name) as organization, so.total, so.status")
                        ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
                        ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
                        ->orderBy('so.created_at', 'DESC')->get()->getResultArray(),
                ];
            case 'waste':
                return [
                    'title' => 'Expiry Waste Report',
                    'columns' => ['Product', 'Batch', 'Qty Remaining', 'Expired On', 'Value Lost (₱)'],
                    'data' => $this->db->query("
                        SELECT p.name, ib.batch_number, ib.quantity_avail, ib.expires_at, (ib.quantity_avail*ib.cost_price) as loss
                        FROM inventory_batches ib JOIN products p ON p.product_id = ib.product_id
                        WHERE ib.expires_at IS NOT NULL AND ib.expires_at < CURDATE() AND ib.quantity_avail > 0
                    ")->getResultArray(),
                ];
            case 'supplier':
                return [
                    'title' => 'Supplier Performance Report',
                    'columns' => ['Supplier', 'On-Time Rate (%)', 'Accuracy Rate (%)', 'Total Orders', 'Lead Time (days)'],
                    'data' => array_map(fn($r) => [
                        $r['name'],
                        $r['on_time_rate'] !== null ? round($r['on_time_rate'], 1) : 'N/A',
                        $r['accuracy_rate'] !== null ? round($r['accuracy_rate'], 1) : 'N/A',
                        $r['total_orders'],
                        $r['lead_time_days'],
                    ], $this->getSupplierPerformance()),
                ];
            case 'pos':
                return [
                    'title' => 'Walk-In POS Report',
                    'columns' => ['OR #', 'Cashier', 'Total', 'Payment Method', 'Date'],
                    'data' => $this->db->table('pos_transactions as pt')
                        ->select('pt.or_number, u.full_name as cashier, pt.total, pt.payment_method, pt.created_at')
                        ->join('users as u', 'u.user_id = pt.cashier_id', 'left')
                        ->where('pt.status', 'completed')->orderBy('pt.created_at', 'DESC')->get()->getResultArray(),
                ];
            case 'dss':
                return [
                    'title' => 'Predictive Analytics — Reorder Status',
                    'columns' => ['Product', 'Current Stock', 'Reorder Level', 'Status'],
                    'data' => $this->db->query("
                        SELECT p.name, COALESCE(SUM(ib.quantity_avail),0) as quantity_avail, MAX(ib.reorder_level) as reorder_level,
                            CASE WHEN COALESCE(SUM(ib.quantity_avail),0) <= MAX(ib.reorder_level) THEN 'Reorder Needed' ELSE 'Stable' END as status
                        FROM products p LEFT JOIN inventory_batches ib ON ib.product_id = p.product_id
                        WHERE p.is_active = 1
                        GROUP BY p.product_id
                    ")->getResultArray(),
                ];
            default:
                return null;
        }
    }
}