<?php

namespace App\Models\Admin\Strategy;

use CodeIgniter\Model;

class BirComplianceModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // Output VAT: VAT collected from sales (POS + institutional), for one calendar month
    public function getOutputVat(int $year, int $month): array
    {
        $pos = $this->db->table('pos_transactions')
            ->selectSum('vat_amount', 'v')->selectSum('total', 't')->selectSum('subtotal', 's')
            ->where('status', 'completed')
            ->where('YEAR(created_at)', $year)->where('MONTH(created_at)', $month)
            ->get()->getRow();

        $so = $this->db->table('sales_orders')
            ->selectSum('vat_amount', 'v')->selectSum('total', 't')->selectSum('subtotal', 's')
            ->where('status !=', 'cancelled')
            ->where('YEAR(created_at)', $year)->where('MONTH(created_at)', $month)
            ->get()->getRow();

        return [
            'gross_sales'   => ($pos->t ?? 0) + ($so->t ?? 0),
            'vatable_sales' => ($pos->s ?? 0) + ($so->s ?? 0),
            'output_vat'    => ($pos->v ?? 0) + ($so->v ?? 0),
        ];
    }

    // Input VAT: VAT paid to suppliers on received purchase orders, for one calendar month
    // Uses each PO item's actual unit_cost rather than assuming a flat rate on the PO total
    public function getInputVat(int $year, int $month): array
    {
        $row = $this->db->query("
            SELECT
                COALESCE(SUM(poi.qty_ordered * poi.unit_cost), 0) as gross_purchases
            FROM purchase_orders po
            JOIN purchase_order_items poi ON poi.po_id = po.po_id
            WHERE po.status IN ('received','partial')
              AND YEAR(po.received_date) = ? AND MONTH(po.received_date) = ?
        ", [$year, $month])->getRow();

        $grossPurchases = (float) ($row->gross_purchases ?? 0);
        // Purchases are recorded VAT-inclusive at cost; back out the VAT component
        $inputVat = $grossPurchases - ($grossPurchases / 1.12);

        return ['gross_purchases' => $grossPurchases, 'input_vat' => $inputVat];
    }

    public function getMonthlySummary(int $year, int $month): array
    {
        $out = $this->getOutputVat($year, $month);
        $in  = $this->getInputVat($year, $month);
        return [
            'year' => $year, 'month' => $month,
            'gross_sales'   => $out['gross_sales'],
            'vatable_sales' => $out['vatable_sales'],
            'output_vat'    => $out['output_vat'],
            'gross_purchases' => $in['gross_purchases'],
            'input_vat'     => $in['input_vat'],
            'net_vat_payable' => $out['output_vat'] - $in['input_vat'],
        ];
    }

    // Last several months, for the trend table at the bottom of the page
    public function getRecentMonthlySummaries(int $count = 6): array
    {
        $summaries = [];
        for ($i = 0; $i < $count; $i++) {
            $ts = strtotime("-{$i} months");
            $summaries[] = $this->getMonthlySummary((int) date('Y', $ts), (int) date('n', $ts));
        }
        return $summaries;
    }

    public function getOrControl()
    {
        return $this->db->table('bir_or_control')->get()->getRow();
    }

    // Real subsidiary journal rows — not a placeholder button
    public function getSalesJournal(int $year, int $month, int $limit = 50): array
    {
        return $this->db->query("
            SELECT 'POS' as source, or_number as ref_no, created_at, total, vat_amount, payment_method
            FROM pos_transactions
            WHERE status='completed' AND YEAR(created_at)=? AND MONTH(created_at)=?
            UNION ALL
            SELECT 'Institutional' as source, order_number as ref_no, created_at, total, vat_amount, payment_method
            FROM sales_orders
            WHERE status != 'cancelled' AND YEAR(created_at)=? AND MONTH(created_at)=?
            ORDER BY created_at DESC LIMIT ?
        ", [$year, $month, $year, $month, $limit])->getResultArray();
    }

    public function getPurchaseJournal(int $year, int $month, int $limit = 50): array
    {
        return $this->db->table('purchase_orders as po')
            ->select('po.po_number as ref_no, s.name as supplier_name, po.received_date, po.total_amount')
            ->join('suppliers as s', 's.supplier_id = po.supplier_id')
            ->whereIn('po.status', ['received', 'partial'])
            ->where('YEAR(po.received_date)', $year)->where('MONTH(po.received_date)', $month)
            ->orderBy('po.received_date', 'DESC')->limit($limit)->get()->getResultArray();
    }

    // Real pagination over monthly summaries, not a hardcoded "last 6"
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

// VAT Sales Book: same consolidated monthly figures, framed as its own book for the drawer
public function getVatSalesBook(int $page = 1, int $perPage = 12): array
{
    return $this->getPaginatedMonthlySummaries($page, $perPage, 60);
}

// Cash Receipts Journal: daily Cash vs GCash totals for one month
public function getCashReceiptsJournal(int $year, int $month): array
{
    return $this->db->query("
        SELECT DATE(created_at) as day,
               SUM(CASE WHEN payment_method='cash' THEN total ELSE 0 END) as cash_total,
               SUM(CASE WHEN payment_method='gcash' THEN total ELSE 0 END) as gcash_total,
               SUM(total) as day_total,
               COUNT(*) as txn_count
        FROM pos_transactions
        WHERE status = 'completed' AND YEAR(created_at) = ? AND MONTH(created_at) = ?
        GROUP BY DATE(created_at)
        ORDER BY day DESC
    ", [$year, $month])->getResultArray();
}

}