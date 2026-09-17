<?php

namespace App\Controllers\Admin\Strategy;
use App\Controllers\BaseController;
use App\Models\Admin\Strategy\BirComplianceModel;

class Compliance extends BaseController
{
    protected $birModel;

    public function __construct()
    {
        $this->birModel = new BirComplianceModel();
    }

    public function bir()
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));

        $summary = $this->birModel->getMonthlySummary($year, $month);

        $data['selected_year'] = $year;
        $data['selected_month'] = $month;
        $data['output_vat'] = $summary['output_vat'];
        $data['input_vat'] = $summary['input_vat'];
        $data['net_vat_payable'] = $summary['net_vat_payable'];
        $data['total_gross_sales'] = $summary['gross_sales'];
        $data['vatable_sales'] = $summary['vatable_sales'];

        $summaryPage = (int) ($this->request->getGet('summary_page') ?? 1);
        $summaryResult = $this->birModel->getPaginatedMonthlySummaries($summaryPage, 6, 60);
        $data['recent_summaries'] = $summaryResult['data'];
        $data['summary_current_page'] = $summaryResult['current_page'];
        $data['summary_total_pages'] = $summaryResult['total_pages'];

        $data['or_control'] = $this->birModel->getOrControl();

        $data['title'] = "BIR Compliance & Tax";
        $data['fullname'] = session()->get('full_name');
        $data['page_name'] = "compliance";
        return view('pages/admin/strategy/bir_hub', $data);
    }

    public function get_journal($type)
{
    $year = (int) ($this->request->getGet('year') ?? date('Y'));
    $month = (int) ($this->request->getGet('month') ?? date('n'));

    try {
        if ($type === 'sales') return $this->response->setJSON($this->birModel->getSalesJournal($year, $month, 500));
        if ($type === 'purchases') return $this->response->setJSON($this->birModel->getPurchaseJournal($year, $month, 500));
        if ($type === 'clients') return $this->response->setJSON($this->birModel->getClientJournal($year, $month, 500));
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Unknown journal type']);
    } catch (\Throwable $e) {
        log_message('error', 'get_journal failed: ' . $e->getMessage());
        return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
    }
}

    public function export_2550m()
{
    $year = (int) ($this->request->getGet('year') ?? date('Y'));
    $month = (int) ($this->request->getGet('month') ?? date('n'));
    $s = $this->birModel->getMonthlySummary($year, $month);

    $data = [[
        number_format($s['gross_sales'], 2), number_format($s['vatable_sales'], 2), number_format($s['output_vat'], 2),
        number_format($s['gross_purchases'], 2), number_format($s['input_vat'], 2),
        $s['net_vat_payable'] < 0 ? 'Credit ' . number_format(abs($s['net_vat_payable']), 2) : number_format($s['net_vat_payable'], 2),
    ]];

    return $this->exportJournalPdf(
        "BIR Form 2550M Data — " . date('F Y', mktime(0,0,0,$month,1,$year)),
        ['Gross Sales', 'Vatable Sales', 'Output VAT', 'Gross Purchases', 'Input VAT', 'Net VAT Payable'],
        $data,
        '2550m_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT)
    );
}

    public function get_vat_sales_book()
{
    try {
        $page = (int) ($this->request->getGet('page') ?? 1);
        return $this->response->setJSON($this->birModel->getVatSalesBook($page, 12));
    } catch (\Throwable $e) {
        return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    }
}

    public function get_cash_receipts_journal()
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));
        return $this->response->setJSON($this->birModel->getCashReceiptsJournal($year, $month));
    }

public function export_sales_journal_pdf()
{
    $year = (int) ($this->request->getGet('year') ?? date('Y'));
    $month = (int) ($this->request->getGet('month') ?? date('n'));
    $rows = $this->birModel->getSalesJournal($year, $month);

    $taxTotal = array_sum(array_column($rows, 'taxable_amount'));
    $vatTotal = array_sum(array_column($rows, 'vat_output_tax'));
    $grandTotal = array_sum(array_column($rows, 'total_invoice_amount'));

    $data = array_map(fn($r) => [$r['sale_date'], $r['buyer'], number_format((float)$r['taxable_amount'],2), number_format((float)$r['vat_output_tax'],2), number_format((float)$r['total_invoice_amount'],2)], $rows);
    $data[] = ['', '', 'TOTAL: ' . number_format($taxTotal,2), number_format($vatTotal,2), number_format($grandTotal,2)];

    return $this->exportJournalPdf(
        "Subsidiary Sales Journal — " . date('F Y', mktime(0,0,0,$month,1,$year)),
        ['Date', 'Buyer', 'Taxable Sales (12%)', 'VAT Output Tax', 'Total Invoice Amount'],
        $data, 'sales_journal_' . $year . '_' . str_pad($month,2,'0',STR_PAD_LEFT)
    );
}

public function export_purchase_journal_pdf()
{
    $year = (int) ($this->request->getGet('year') ?? date('Y'));
    $month = (int) ($this->request->getGet('month') ?? date('n'));
    $rows = $this->birModel->getPurchaseJournal($year, $month);

    $vatPurchTotal = array_sum(array_column($rows, 'vat_purchases_local'));
    $inputVatTotal = array_sum(array_column($rows, 'vat_input_tax'));
    $grandTotal = array_sum(array_column($rows, 'total_invoice_amount'));

    $data = array_map(fn($r) => [
        $r['purchase_date'], trim(($r['supplier_name']??'').' — '.($r['supplier_address']??'')), $r['invoice_no'] ?? '—', $r['tin'] ?? '—',
        number_format((float)$r['vat_purchases_local'],2), number_format((float)$r['vat_input_tax'],2), number_format((float)$r['total_invoice_amount'],2)
    ], $rows);
    $data[] = ['', '', '', 'TOTAL:', number_format($vatPurchTotal,2), number_format($inputVatTotal,2), number_format($grandTotal,2)];

    return $this->exportJournalPdf(
        "Subsidiary Purchase Journal — " . date('F Y', mktime(0,0,0,$month,1,$year)),
        ['Date', 'Name and Address of Supplier', 'Inv. No.', 'TIN / VAT Reg. No.', 'VAT Purchases — Local (Goods)', 'VAT Input Tax', 'Total Invoice Amount'],
        $data, 'purchase_journal_' . $year . '_' . str_pad($month,2,'0',STR_PAD_LEFT)
    );
}

public function export_client_journal_pdf()
{
    $year = (int) ($this->request->getGet('year') ?? date('Y'));
    $month = (int) ($this->request->getGet('month') ?? date('n'));
    $rows = $this->birModel->getClientJournal($year, $month);

    $amountTotal = array_sum(array_column($rows, 'amount'));

    $data = array_map(fn($r) => [
        $r['item_date'], $r['client_name'], $r['item_name'], $r['qty'] ?? '—',
        number_format((float)$r['price'],2), number_format((float)$r['amount'],2), $r['expiry'] ?? '—'
    ], $rows);
    $data[] = ['', '', '', '', 'TOTAL:', number_format($amountTotal,2), ''];

    return $this->exportJournalPdf(
        "Subsidiary Client Journal — " . date('F Y', mktime(0,0,0,$month,1,$year)),
        ['Date', 'Client', 'Item', 'Qty', 'Price', 'Amount', 'Expiry'],
        $data, 'client_journal_' . $year . '_' . str_pad($month,2,'0',STR_PAD_LEFT)
    );
}

public function export_vat_sales_book_pdf()
{
    $rows = $this->birModel->getFullVatSalesBookHistory();

    $data = array_map(fn($s) => [
        date('F Y', mktime(0,0,0,$s['month'],1,$s['year'])),
        number_format($s['vatable_sales'], 2),
        number_format($s['output_vat'], 2),
        $s['net_vat_payable'] < 0 ? 'Credit ' . number_format(abs($s['net_vat_payable']), 2) : number_format($s['net_vat_payable'], 2),
    ], $rows);

    return $this->exportJournalPdf(
        "VAT Sales Book — Full History",
        ['Period', 'Vatable Sales', 'Output VAT', 'Net Payable'],
        $data,
        'vat_sales_book_full_history'
    );
}


// Shared PDF renderer — same letterhead/style as the Reports & Analytics export
private function exportJournalPdf(string $title, array $columns, array $data, string $filename)
{
    require_once 'C:/xampp/htdocs/PharMediSync/packages/dompdf/vendor/autoload.php';

    $html = view('pages/admin/strategy/analytics/pdf_report', [
        'title' => $title, 'columns' => $columns, 'data' => $data,
    ]);

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('chroot', 'C:/xampp/htdocs/PharMediSync/public');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->loadHtml($html);
    $dompdf->render();

    if (ob_get_length()) ob_end_clean(); // <-- discard anything already buffered
    $dompdf->stream($filename . '.pdf', ['Attachment' => true]);
    exit;
}
public function export_2550q()
{
    $year = (int) ($this->request->getGet('year') ?? date('Y'));
    $quarter = (int) ($this->request->getGet('quarter') ?? ceil(date('n') / 3));
    $s = $this->birModel->getQuarterlySummary($year, $quarter);

    require_once 'C:/xampp/htdocs/PharMediSync/packages/dompdf/vendor/autoload.php';

    $db = \Config\Database::connect();
    $storeRows = $db->table('store_settings')->get()->getResultArray();
    $store = [];
    foreach ($storeRows as $row) $store[$row['setting_key']] = $row['setting_value'];

    $html = view('pages/admin/strategy/analytics/form_2550q', [
        'store' => $store, 'year' => $year, 'quarter' => $quarter, 's' => $s,
    ]);

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('chroot', 'C:/xampp/htdocs/PharMediSync/public');

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->setPaper('legal', 'portrait');
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("2550Q_{$year}_Q{$quarter}.pdf", ['Attachment' => true]);
    exit;
}

    
}