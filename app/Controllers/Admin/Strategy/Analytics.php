<?php
namespace App\Controllers\Admin\Strategy;
use App\Controllers\BaseController;

use App\Models\Admin\Strategy\AnalyticsModel;

class Analytics extends BaseController {

protected $analyticsModel;

    public function __construct()
    {
        $this->analyticsModel = new AnalyticsModel();
    }

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

    public function dss()
    {
        $search = $this->request->getGet('search');
        $data['products_list'] = $this->analyticsModel->searchProducts($search ?? '');
        $data['categories_list'] = $this->analyticsModel->getCategoriesList();
        $data = array_merge($data, $this->analyticsModel->getDssSummary());

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
    $from = $this->request->getGet('from') ?: date('Y-m', strtotime('-11 months'));
    $to   = $this->request->getGet('to') ?: date('Y-m');

    if ($pid === 'all') {
        return $this->response->setJSON($this->analyticsModel->getAllProductsForecastData($from, $to));
    }

    $result = $this->analyticsModel->getForecastData((int) $pid, $from, $to);
    if ($result === null) return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
    return $this->response->setJSON($result);
}


public function get_supplier_report()
    {
        return $this->response->setJSON($this->analyticsModel->getSupplierPerformance());
    }

    public function get_low_performing_report()
    {
        return $this->response->setJSON($this->analyticsModel->getLowPerformingProducts());
    }



public function reports()
    {
        $request = \Config\Services::request();
        $movementType = $request->getGet('movement') ?: '';
        $search = trim((string) ($request->getGet('search') ?? ''));
        $page = max(1, (int) ($request->getGet('page') ?? 1));
        $perPage = 10;

        $result = $this->analyticsModel->getMovementLogs($movementType, $search, $page, $perPage);
        $audit = $this->analyticsModel->getAuditTrailInfo();

        $data['total_revenue']    = $this->analyticsModel->getMonthRevenue();
        $data['inventory_value']  = $this->analyticsModel->getInventoryValuation();
        $data['expiry_waste']     = $this->analyticsModel->getExpiryWaste();
        $data['audit_log_count']  = $audit['count'];
        $data['last_audit_time']  = $audit['last_at'];

        $data['reports_data']   = $result['data'];
        $data['total_rows']     = $result['total'];
        $data['current_page']   = $page;
        $data['per_page']       = $perPage;
        $data['total_pages']    = $result['total_pages'];
        $data['movement_filter'] = $movementType;
        $data['search'] = $search;

        $data['title'] = "Reports & Analytics";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "reports";
        return view('pages/admin/strategy/analytics/reports', $data);
    }

public function get_movement_details($id)
    {
        $row = $this->analyticsModel->getMovementDetails((int) $id);
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Log not found']);
        return $this->response->setJSON($row);
    }

    public function get_products_by_category($catId = null)
    {
        return $this->response->setJSON($this->analyticsModel->getProductsByCategory($catId ? (int) $catId : null));
    }


public function get_overall_trend()
    {
        $from = $this->request->getGet('from') ?: date('Y-m', strtotime('-11 months'));
        $to   = $this->request->getGet('to') ?: date('Y-m');
        return $this->response->setJSON($this->analyticsModel->getOverallTrend($from, $to));
    }

    public function export($type, $format)
    {
        $reportData = $this->analyticsModel->getExportData($type);
        if ($reportData === null) {
            return redirect()->back()->with('error', 'Unknown report type.');
        }

        $filename = "RobinRose_" . ucfirst($type) . "_Report_" . date('Ymd');

        if ($format === 'excel') return $this->exportExcel($reportData, $filename);
        if ($format === 'pdf')   return $this->exportPdf($reportData, $filename, $type);

        return redirect()->back()->with('error', 'Unsupported export format.');
    }

    private function exportExcel(array $reportData, string $filename)
{
    set_time_limit(15);
    require_once 'C:/xampp/htdocs/PharMediSync/packages/manual_autoload.php';

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr($reportData['title'], 0, 31));

    $sheet->fromArray($reportData['columns'], null, 'A1');
    $lastCol = $sheet->getHighestColumn();
    $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
    $sheet->getStyle("A1:{$lastCol}1")->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('7B1113');
    $sheet->getStyle("A1:{$lastCol}1")->getFont()->getColor()->setRGB('FFFFFF');

    $rowNum = 2;
    foreach ($reportData['data'] as $row) {
        $sheet->fromArray(array_values((array) $row), null, 'A' . $rowNum);
        $rowNum++;
    }
    foreach (range('A', $lastCol) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}

    private function exportPdf(array $reportData, string $filename, string $type)
{
    require_once 'C:/xampp/htdocs/PharMediSync/packages/dompdf/vendor/autoload.php';

    $html = view('pages/admin/strategy/analytics/pdf_report', [
        'title'   => $reportData['title'],
        'columns' => $reportData['columns'],
        'data'    => $reportData['data'],
    ]);

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('chroot', 'C:/xampp/htdocs/PharMediSync/public');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->loadHtml($html);
    $dompdf->render();

    if (ob_get_length()) ob_end_clean();
    $dompdf->stream($filename . '.pdf', ['Attachment' => true]);
    exit;
}


}