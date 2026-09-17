<?php

namespace App\Models\Admin\Strategy;

use CodeIgniter\Model;

class BirComplianceModel extends Model
{
    protected $db;

    private const HIST_START_YEAR = 2025;
    private const HIST_START_MONTH = 1;
    private const HIST_END_YEAR = 2026;
    private const HIST_END_MONTH = 8;

    private const MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    private function isHistorical(int $year, int $month): bool
    {
        $key = $year * 12 + $month;
        $start = self::HIST_START_YEAR * 12 + self::HIST_START_MONTH;
        $end = self::HIST_END_YEAR * 12 + self::HIST_END_MONTH;
        return $key >= $start && $key <= $end;
    }

    private function monthName(int $month): string
    {
        return self::MONTH_NAMES[$month - 1] ?? '';
    }

    public function getMonthlySummary(int $year, int $month): array
    {
        if ($this->isHistorical($year, $month)) {
            return $this->getHistoricalSummary($year, $month);
        }
        return $this->getLiveSummary($year, $month);
    }

    private function getHistoricalSummary(int $year, int $month): array
    {
        $monthName = $this->monthName($month);

        $cash = $this->db->table('cash_sales_journal_monthly_totals')
            ->where('txn_year', $year)->where('txn_month', $monthName)
            ->get()->getRow();

        $client = $this->db->table('client_journal_monthly_totals')
            ->where('txn_year', $year)->where('txn_month', $monthName)
            ->get()->getRow();

        $purchase = $this->db->table('subsidiary_purchase_journal_monthly_totals')
            ->where('txn_year', $year)->where('txn_month', $monthName)
            ->get()->getRow();

        $cashGross = (float) ($cash->total_invoice_amount ?? 0);
        $cashVatable = (float) ($cash->total_taxable_amount ?? 0);
        $cashVat = (float) ($cash->total_vat_output_tax ?? 0);

        $clientGross = (float) ($client->total_computed_amount ?? 0);
        $clientVatable = $clientGross / 1.12;
        $clientVat = $clientGross - $clientVatable;

        $grossSales = $cashGross + $clientGross;
        $vatableSales = $cashVatable + $clientVatable;
        $outputVat = $cashVat + $clientVat;

        $grossPurchases = (float) ($purchase->total_invoice_amount ?? 0);
        $inputVat = (float) ($purchase->total_vat_input_tax ?? 0);

        return [
            'year' => $year, 'month' => $month,
            'gross_sales'     => $grossSales,
            'vatable_sales'   => $vatableSales,
            'output_vat'      => $outputVat,
            'gross_purchases' => $grossPurchases,
            'input_vat'       => $inputVat,
            'net_vat_payable' => $outputVat - $inputVat,
        ];
    }

    private function getLiveSummary(int $year, int $month): array
    {
        $posRow = $this->db->table('pos_transactions')
            ->selectSum('subtotal', 'subtotal_sum')->selectSum('vat_amount', 'vat_sum')->selectSum('total', 'total_sum')
            ->where('YEAR(created_at)', $year)->where('MONTH(created_at)', $month)
            ->where('status', 'completed')->get()->getRow();

        $soRow = $this->db->table('sales_orders')
            ->selectSum('subtotal', 'subtotal_sum')->selectSum('vat_amount', 'vat_sum')->selectSum('total', 'total_sum')
            ->where('YEAR(created_at)', $year)->where('MONTH(created_at)', $month)
            ->where('status !=', 'cancelled')->get()->getRow();

        $grossSales = (float) ($posRow->total_sum ?? 0) + (float) ($soRow->total_sum ?? 0);
        $vatableSales = (float) ($posRow->subtotal_sum ?? 0) + (float) ($soRow->subtotal_sum ?? 0);
        $outputVat = (float) ($posRow->vat_sum ?? 0) + (float) ($soRow->vat_sum ?? 0);

        $poRow = $this->db->table('purchase_orders')
            ->selectSum('total_amount', 'total_sum')
            ->whereIn('status', ['received', 'partial'])
            ->where('YEAR(received_date)', $year)->where('MONTH(received_date)', $month)
            ->get()->getRow();

        $grossPurchases = (float) ($poRow->total_sum ?? 0);
        $inputVat = $grossPurchases - ($grossPurchases / 1.12);

        return [
            'year' => $year, 'month' => $month,
            'gross_sales'     => $grossSales,
            'vatable_sales'   => $vatableSales,
            'output_vat'      => $outputVat,
            'gross_purchases' => $grossPurchases,
            'input_vat'       => $inputVat,
            'net_vat_payable' => $outputVat - $inputVat,
        ];
    }

    public function getRecentMonthlySummaries(int $count = 6): array
    {
        $summaries = [];
        for ($i = 0; $i < $count; $i++) {
            $ts = strtotime("-{$i} months");
            $summaries[] = $this->getMonthlySummary((int) date('Y', $ts), (int) date('n', $ts));
        }
        return $summaries;
    }

    public function getPaginatedMonthlySummaries(int $page = 1, int $perPage = 6, int $maxMonthsBack = 60): array
    {
        $offset = ($page - 1) * $perPage;
        $summaries = [];
        for ($i = $offset; $i < min($offset + $perPage, $maxMonthsBack); $i++) {
            $ts = strtotime("-{$i} months");
            $summaries[] = $this->getMonthlySummary((int) date('Y', $ts), (int) date('n', $ts));
        }
        return [
            'data' => $summaries,
            'total_pages' => (int) ceil($maxMonthsBack / $perPage),
            'current_page' => $page,
        ];
    }

    public function getVatSalesBook(int $page = 1, int $perPage = 12): array
    {
        return $this->getPaginatedMonthlySummaries($page, $perPage, 60);
    }

    public function getOrControl()
    {
        return $this->db->table('bir_or_control')->get()->getRow();
    }

    public function getSalesJournal(int $year, int $month, int $limit = 10000): array
{
    if ($this->isHistorical($year, $month)) {
        $monthName = $this->monthName($month);
        return $this->db->table('cash_sales_journal')
            ->select("CASE WHEN txn_day IS NOT NULL THEN CONCAT(txn_day,' {$monthName} {$year}') ELSE '{$monthName} {$year}' END as sale_date,
                COALESCE(description, 'Walk-in Customer') as buyer,
                taxable_amount, vat_output_tax, total_invoice_amount")
            ->where('txn_year', $year)->where('txn_month', $monthName)
            ->where('is_no_transaction', 0)
            ->orderBy('txn_day', 'ASC')->limit($limit)
            ->get()->getResultArray();
    }
    return $this->db->table('pos_transactions')
        ->select("DATE(created_at) as sale_date, COALESCE(customer_name, 'Walk-in Customer') as buyer,
            subtotal as taxable_amount, vat_amount as vat_output_tax, total as total_invoice_amount")
        ->where('YEAR(created_at)', $year)->where('MONTH(created_at)', $month)
        ->where('status', 'completed')
        ->orderBy('created_at', 'ASC')->limit($limit)
        ->get()->getResultArray();
}


    public function getPurchaseJournal(int $year, int $month, int $limit = 10000): array
{
    if ($this->isHistorical($year, $month)) {
        $monthName = $this->monthName($month);
        return $this->db->table('subsidiary_purchase_journal')
            ->select("CASE WHEN txn_day IS NOT NULL THEN CONCAT(txn_day,' {$monthName} {$year}') ELSE '{$monthName} {$year}' END as purchase_date,
                supplier_name, supplier_address, invoice_no, tin,
                vat_purchases_local, vat_input_tax, total_invoice_amount")
            ->where('txn_year', $year)->where('txn_month', $monthName)
            ->where('is_no_transaction', 0)
            ->orderBy('txn_day', 'ASC')->limit($limit)
            ->get()->getResultArray();
    }
    return $this->db->table('purchase_orders as po')
        ->select("po.received_date as purchase_date, COALESCE(s.name, gs.name) as supplier_name,
            COALESCE(s.address, gs.address) as supplier_address, po.po_number as invoice_no, s.tin,
            (po.total_amount / 1.12) as vat_purchases_local,
            (po.total_amount - (po.total_amount / 1.12)) as vat_input_tax, po.total_amount as total_invoice_amount")
        ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
        ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left')
        ->whereIn('po.status', ['received', 'partial'])
        ->where('YEAR(po.received_date)', $year)->where('MONTH(po.received_date)', $month)
        ->orderBy('po.received_date', 'ASC')->limit($limit)
        ->get()->getResultArray();
}

public function getClientJournal(int $year, int $month, int $limit = 10000): array
{
    if ($this->isHistorical($year, $month)) {
        $monthName = $this->monthName($month);
        return $this->db->table('client_journal_items as cji')
            ->select("CASE WHEN cj.txn_day IS NOT NULL THEN CONCAT(cj.txn_day,' {$monthName} {$year}') ELSE '{$monthName} {$year}' END as item_date,
                cj.client_name, cji.item_name, cji.qty,
                COALESCE(cji.price, 0) as price, COALESCE(cji.amount, 0) as amount,
                COALESCE(cji.exp_date, cji.exp_raw, '—') as expiry")
            ->join('client_journal as cj', 'cj.id = cji.journal_id')
            ->where('cj.txn_year', $year)
            ->where("(cj.txn_month = '{$monthName}' OR cj.txn_month IS NULL)", null, false)
            ->orderBy('cj.txn_day', 'ASC')->limit($limit)
            ->get()->getResultArray();
    }
    return $this->db->table('sales_order_items as soi')
        ->select("so.created_at as item_date, COALESCE(ic.organization, gc.name) as client_name,
            p.name as item_name, soi.quantity as qty, soi.unit_price as price, soi.subtotal as amount,
            (SELECT expires_at FROM inventory_batches WHERE batch_id = soi.batch_id) as expiry")
        ->join('sales_orders as so', 'so.order_id = soi.order_id')
        ->join('institutional_clients as ic', 'ic.client_id = so.client_id', 'left')
        ->join('guest_clients as gc', 'gc.guest_client_id = so.guest_client_id', 'left')
        ->join('products as p', 'p.product_id = soi.product_id')
        ->where('so.status !=', 'cancelled')
        ->where('YEAR(so.created_at)', $year)->where('MONTH(so.created_at)', $month)
        ->orderBy('so.created_at', 'ASC')->limit($limit)
        ->get()->getResultArray();
}

    private function getHistoricalPurchaseJournal(int $year, int $month, int $limit): array
{
    $monthName = $this->monthName($month);

    return $this->db->table('subsidiary_purchase_journal')
        ->select("invoice_no as ref_no, supplier_name,
            CASE WHEN txn_day IS NOT NULL THEN CONCAT(txn_day, ' {$monthName} {$year}') ELSE '{$monthName} {$year} (day unspecified)' END as received_date,
            total_invoice_amount as total_amount")
        ->where('txn_year', $year)->where('txn_month', $monthName)
        ->where('is_no_transaction', 0)
        ->orderBy('txn_day', 'ASC')
        ->limit($limit)
        ->get()->getResultArray();
}

    private function getLivePurchaseJournal(int $year, int $month, int $limit): array
    {
        return $this->db->table('purchase_orders as po')
            ->select("po.po_number as ref_no, COALESCE(s.name, gs.name) as supplier_name, po.received_date, po.total_amount")
            ->join('suppliers as s', 's.supplier_id = po.supplier_id', 'left')
            ->join('guest_suppliers as gs', 'gs.guest_supplier_id = po.guest_supplier_id', 'left')
            ->whereIn('po.status', ['received', 'partial'])
            ->where('YEAR(po.received_date)', $year)->where('MONTH(po.received_date)', $month)
            ->orderBy('po.received_date', 'DESC')->limit($limit)->get()->getResultArray();
    }


// Full VAT Sales Book history — every month from the historical start through today
public function getFullVatSalesBookHistory(): array
{
    $summaries = [];
    $startKey = self::HIST_START_YEAR * 12 + self::HIST_START_MONTH;
    $endKey = (int) date('Y') * 12 + (int) date('n');

    for ($key = $startKey; $key <= $endKey; $key++) {
        $year = intdiv($key - 1, 12);
        $month = $key - ($year * 12);
        $summaries[] = $this->getMonthlySummary($year, $month);
    }
    return array_reverse($summaries); // most recent first
}

public function getQuarterlySummary(int $year, int $quarter): array
{
    $months = match($quarter) {
        1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12],
        default => [1, 2, 3],
    };

    $totals = ['gross_sales' => 0, 'vatable_sales' => 0, 'output_vat' => 0, 'gross_purchases' => 0, 'input_vat' => 0];
    foreach ($months as $m) {
        $s = $this->getMonthlySummary($year, $m);
        $totals['gross_sales']     += $s['gross_sales'];
        $totals['vatable_sales']   += $s['vatable_sales'];
        $totals['output_vat']      += $s['output_vat'];
        $totals['gross_purchases'] += $s['gross_purchases'];
        $totals['input_vat']       += $s['input_vat'];
    }
    $totals['year'] = $year;
    $totals['quarter'] = $quarter;
    $totals['net_vat_payable'] = $totals['output_vat'] - $totals['input_vat'];
    return $totals;
}



}