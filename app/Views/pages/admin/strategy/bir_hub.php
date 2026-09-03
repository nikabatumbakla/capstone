<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </button>
                    <h5 class="fw-bold mb-0">BIR Compliance</h5>
                </div>
                <form action="" method="GET" class="d-flex align-items-end gap-2">
    <div>
        <label class="info-label d-block mb-1">Month</label>
        <select name="month" class="form-select form-select-sm" style="width:140px; height:36px; font-size:11px;">
            <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m==$selected_month?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div>
        <label class="info-label d-block mb-1">Year</label>
        <select name="year" class="form-select form-select-sm" style="width:100px; height:36px; font-size:11px;">
            <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
                <option value="<?= $y ?>" <?= $y==$selected_year?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3" style="height:36px;">View</button>
</form>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>BIR Compliance & Tax Reporting</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Automated Subsidiary Journals • VAT Computation • Form 2550M/Q Data — <?= date('F Y', mktime(0,0,0,$selected_month,1,$selected_year)) ?></p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">OUTPUT VAT (SALES)</small>
                        <h4 class="fw-bold mb-0">₱<?= number_format($output_vat, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">INPUT VAT (PURCHASES)</small>
                        <h4 class="fw-bold mb-0 text-primary">₱<?= number_format($input_vat, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card" style="background:#fffdf0; border-color:#f1c40f;">
                        <small class="text-muted fw-bold d-block mb-1">NET VAT PAYABLE</small>
                        <h4 class="fw-bold mb-0"><?= $net_vat_payable < 0 ? 'Credit: ' : '' ?>₱<?= number_format(abs($net_vat_payable), 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card" style="background:#212529; color:#fff;">
                        <small class="d-block mb-1" style="color:#aaa;">CURRENT OR SEQUENCE</small>
                        <h4 class="fw-bold mb-0"># <?= $or_control ? str_pad($or_control->current_number, 6, '0', STR_PAD_LEFT) : '000000' ?></h4>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="custom-table-container h-100 d-flex flex-column">
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1" style="font-size:12px;"><i class="fas fa-book me-2 text-maroon"></i>Subsidiary Sales Journal</h6>
                <p class="text-muted mb-0" style="font-size:10px;">Transaction-level sales tracking for the selected month.</p>
            </div>
            <button class="btn btn-xs btn-outline-dark rounded-pill mt-3 btn-open-journal" data-type="sales">Open Journal</button>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="custom-table-container h-100 d-flex flex-column">
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1" style="font-size:12px;"><i class="fas fa-shopping-bag me-2 text-maroon"></i>Subsidiary Purchase Journal</h6>
                <p class="text-muted mb-0" style="font-size:10px;">Supplier invoice and input VAT log for the selected month.</p>
            </div>
            <button class="btn btn-xs btn-outline-dark rounded-pill mt-3 btn-open-journal" data-type="purchases">Open Journal</button>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="custom-table-container h-100 d-flex flex-column">
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1" style="font-size:12px;"><i class="fas fa-file-invoice me-2 text-maroon"></i>VAT Sales Book</h6>
                <p class="text-muted mb-0" style="font-size:10px;">Consolidated monthly VAT sales, full history.</p>
            </div>
            <button class="btn btn-xs btn-outline-dark rounded-pill mt-3 btn-open-vat-book">Open Journal</button>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="custom-table-container h-100 d-flex flex-column">
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1" style="font-size:12px;"><i class="fas fa-cash-register me-2 text-maroon"></i>Cash Receipts Journal</h6>
                <p class="text-muted mb-0" style="font-size:10px;">Daily Cash and GCash flow for the selected month.</p>
            </div>
            <button class="btn btn-xs btn-outline-dark rounded-pill mt-3 btn-open-cash-journal">Open Journal</button>
        </div>
    </div>
</div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:12px;"><i class="fas fa-calculator me-2 text-maroon"></i>Monthly VAT Summary (Last 6 Months)</h6>
                    <a href="<?= base_url('admin/strategy/compliance/export-2550m?year='.$selected_year.'&month='.$selected_month) ?>" target="_blank" class="btn btn-sm btn-dark rounded-pill px-4">
                        <i class="fas fa-file-pdf me-2"></i>Export 2550M Data
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th class="ps-4">Tax Period</th><th>Gross Vatable Sales</th><th>Output VAT (12%)</th><th>Gross Purchases</th><th>Input VAT (Credit)</th><th class="text-end pe-4">Net VAT Payable</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_summaries as $s): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= date('F Y', mktime(0,0,0,$s['month'],1,$s['year'])) ?></td>
                                <td>₱<?= number_format($s['vatable_sales'], 2) ?></td>
                                <td class="text-maroon fw-bold">₱<?= number_format($s['output_vat'], 2) ?></td>
                                <td>₱<?= number_format($s['gross_purchases'], 2) ?></td>
                                <td class="text-primary">₱<?= number_format($s['input_vat'], 2) ?></td>
                                <td class="text-end pe-4 fw-bold"><?= $s['net_vat_payable'] < 0 ? 'Credit ' : '' ?>₱<?= number_format(abs($s['net_vat_payable']), 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php
    $monthQuery = '&month=' . $selected_month . '&year=' . $selected_year;
    $windowSize = 3;
    $currentBlock = (int) ceil($summary_current_page / $windowSize);
    $windowStart = (($currentBlock - 1) * $windowSize) + 1;
    $windowEnd = min($windowStart + $windowSize - 1, $summary_total_pages);
?>
<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
    <span class="text-muted fw-bold">Showing page <?= $summary_current_page ?> of <?= $summary_total_pages ?> (6 months per page)</span>
    <nav>
        <ul class="pagination pagination-sm mb-0 custom-pager">
            <li class="page-item <?= $summary_current_page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?summary_page=<?= max(1, $summary_current_page - 1) . $monthQuery ?>"><i class="fas fa-chevron-left"></i></a>
            </li>
            <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                <li class="page-item <?= $i == $summary_current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?summary_page=<?= $i . $monthQuery ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $summary_current_page >= $summary_total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?summary_page=<?= min($summary_total_pages, $summary_current_page + 1) . $monthQuery ?>"><i class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
</div>

                <div class="p-3 mt-3 bg-light rounded-4 border-start border-4 border-maroon">
                    <p class="mb-0 text-muted" style="font-size:10px;">
                        <i class="fas fa-info-circle me-1"></i> <b>Disclaimer:</b> These reports are system-generated internal records. Compliance with BIR Revenue Regulations No. 16-2005 and RMO No. 10-2005 requires a valid Permit to Use (PTU) for live tax filing.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="journalDrawer" style="width:600px;">
    <div class="offcanvas-header border-bottom bg-light"><h6 class="fw-bold mb-0" id="journalTitle">Journal</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body" id="journalContent"></div>
</div>

<script src="<?= base_url('public/js/admin/strategy/bir.js') ?>"></script>
<?= view('partials/admin/footer') ?>