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

        if ($type === 'sales') {
            return $this->response->setJSON($this->birModel->getSalesJournal($year, $month));
        }
        if ($type === 'purchases') {
            return $this->response->setJSON($this->birModel->getPurchaseJournal($year, $month));
        }
        return $this->response->setStatusCode(404)->setJSON(['error' => 'Unknown journal type']);
    }

    public function export_2550m()
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));
        $summary = $this->birModel->getMonthlySummary($year, $month);

        return view('pages/admin/strategy/analytics/printable_report', [
            'data' => [$summary],
            'columns' => ['Gross Sales', 'Vatable Sales', 'Output VAT', 'Gross Purchases', 'Input VAT', 'Net VAT Payable'],
            'type' => '2550m_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT),
            'generated_at' => date('F j, Y g:i A'),
        ]);
    }

    public function get_vat_sales_book()
{
    $page = (int) ($this->request->getGet('page') ?? 1);
    return $this->response->setJSON($this->birModel->getVatSalesBook($page, 12));
}

public function get_cash_receipts_journal()
{
    $year = (int) ($this->request->getGet('year') ?? date('Y'));
    $month = (int) ($this->request->getGet('month') ?? date('n'));
    return $this->response->setJSON($this->birModel->getCashReceiptsJournal($year, $month));
}

}