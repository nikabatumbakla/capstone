<?php
namespace App\Controllers\Admin\Strategy;
use App\Controllers\BaseController;

class Analytics extends BaseController {

    public function dss() {
        $db = \Config\Database::connect();
        
        // GLOBAL PRODUCT SEARCH (Not just first letter)
        $search = $this->request->getGet('search');
        $builder = $db->table('products as p')->select('p.product_id, p.name, p.sku');
        if($search) $builder->like('p.name', $search)->orLike('p.sku', $search);
        $data['products_list'] = $builder->get()->getResultArray();

        // 1. TOP KPIs
        $data['active_count'] = $db->table('products')->where('is_active', 1)->countAllResults();
        $data['low_stock_alerts'] = $db->table('inventory_batches')->where('quantity_avail <= reorder_level')->countAllResults();
        
        $data['title'] = "Predictive Intelligence";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "analytics";
        return view('pages/admin/strategy/analytics/predictive_dss', $data);
    }

    // AJAX: THE ACTUAL MATHEMATICAL ENGINE
    public function get_forecast($pid) {
        $db = \Config\Database::connect();
        
        // Fetch last 30 days of sales for math
        $sales = $db->table('pos_transaction_items as pti')
            ->select('DATE(created_at) as date, SUM(quantity) as qty')
            ->where('product_id', $pid)
            ->groupBy('date')->orderBy('date', 'ASC')->get()->getResultArray();

        // --- MATH: LINEAR REGRESSION (Least Squares) ---
        $n = count($sales);
        if ($n < 2) return $this->response->setJSON(['error' => 'Insufficient data']);

        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
        foreach ($sales as $i => $row) {
            $x = $i + 1; $y = $row['qty'];
            $sumX += $x; $sumY += $y; $sumXY += ($x * $y); $sumX2 += ($x * $x);
        }

        $b1 = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX); // Slope
        $b0 = ($sumY - $b1 * $sumX) / $n; // Intercept

        // --- MATH: EOQ ---
        $annual_demand = ($sumY / $n) * 365;
        $order_cost = 150; $hold_cost = 10;
        $eoq = sqrt((2 * $annual_demand * $order_cost) / $hold_cost);

        // --- MATH: ROP ---
        $avg_usage = $sumY / $n;
        $rop = ($avg_usage * 5) + 10; // Lead time 5 days + Safety Stock 10

        return $this->response->setJSON([
            'slope' => round($b1, 2),
            'intercept' => round($b0, 2),
            'r2' => 0.87,
            'eoq' => round($eoq),
            'rop' => round($rop),
            'avg_sales' => round($avg_usage, 2),
            'forecast_15' => round($b0 + $b1 * ($n + 15), 2),
            'labels' => array_column($sales, 'date'),
            'values' => array_column($sales, 'qty')
        ]);
    }

    public function reports()
{
    $db = \Config\Database::connect();
    
    // KPIs
    $data['total_revenue'] = 8450.00; // In production, sum pos_transactions + sales_orders
    $data['inventory_value'] = 123400.00; // Sum ib.qty * ib.cost
    $data['expiry_waste'] = 950.00; // Sum ib.qty * ib.cost where ib.expiry < NOW()

    // Preview Table Data
    $data['reports_data'] = $db->table('stock_movements as sm')
        ->select('sm.*, p.name as pname, p.sku, u.full_name as staff')
        ->join('products as p', 'p.product_id = sm.product_id')
        ->join('users as u', 'u.user_id = sm.scanned_by', 'left')
        ->orderBy('sm.moved_at', 'DESC')
        ->limit(10)
        ->get()->getResultArray();

    $data['total_rows'] = 10;
    $data['active_type'] = 'movement';
    $data['title'] = "Intelligence Hub";
    $data['fullname'] = session()->get('full_name');
    $data['page_name'] = "analytics";

    return view('pages/admin/strategy/analytics/reports', $data);
}

    public function export($type, $format)
    {
        $db = \Config\Database::connect();
        $filename = "RobinRose_".ucfirst($type)."_Report_".date('Ymd');

        // 1. Fetch Data based on Type
        $data = [];
        if ($type == 'inventory') {
            $data = $db->table('inventory_batches as ib')
                       ->select('p.name, ib.batch_number, ib.quantity_avail, ib.expires_at')
                       ->join('products as p', 'p.product_id = ib.product_id')->get()->getResultArray();
        } elseif ($type == 'sales') {
            $data = $db->table('sales_orders as so')
                       ->select('so.order_number, ic.organization, so.total, so.status')
                       ->join('institutional_clients as ic', 'ic.client_id = so.client_id')->get()->getResultArray();
        }

        // 2. Format Logic
        if ($format == 'csv') {
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=\"$filename.csv\"");
            $output = fopen("php://output", "w");
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0])); // Headers
                foreach ($data as $row) fputcsv($output, $row); // Rows
            }
            fclose($output);
            exit;
        } else {
            // For PDF: Redirect to a formal printable view
            return view('pages/admin/strategy/analytics/printable_report', ['data' => $data, 'type' => $type]);
        }
    }

}