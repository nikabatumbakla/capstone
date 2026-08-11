<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">

        <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">BIR Compliance</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>BIR Compliance & Tax Reporting</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Automated Subsidiary Journals • VAT Computation • Form 2550M/Q Data</p>
            </div>


            <!-- 2. TAX TILES -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius: 20px;">
                        <small class="info-label">TOTAL OUTPUT VAT (SALES)</small>
                        <h4 class="fw-bold text-maroon mb-0">₱ <?= number_format($output_vat, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius: 20px;">
                        <small class="info-label">TOTAL INPUT VAT (PURCHASES)</small>
                        <h4 class="fw-bold text-primary mb-0">₱ <?= number_format($input_vat, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius: 20px; background: #fffdf0 !important; border: 1px solid #f1c40f !important;">
                        <small class="info-label text-warning">NET VAT PAYABLE</small>
                        <h4 class="fw-bold text-dark mb-0">₱ <?= number_format($net_vat_payable, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-dark text-white border-0" style="border-radius: 20px;">
                        <small class="info-label text-white-50">CURRENT OR SEQUENCE</small>
                        <h4 class="fw-bold mb-0"># <?= str_pad($or_control->current_number, 6, '0', STR_PAD_LEFT) ?></h4>
                    </div>
                </div>
            </div>

            <!-- 3. ACCOUNTING BOOKS GRID -->
            <div class="row g-3 mb-4">
                <?php 
                $books = [
                    ['fas fa-book', 'Subsidiary Sales Journal', 'Transaction-level sales tracking'],
                    ['fas fa-shopping-bag', 'Subsidiary Purchase Journal', 'Supplier invoice and input VAT log'],
                    ['fas fa-file-invoice', 'VAT Sales Book', 'Consolidated monthly VAT sales'],
                    ['fas fa-cash-register', 'Cash Receipts Journal', 'Daily cash and GCash flow audit']
                ];
                foreach($books as $b): ?>
                <div class="col-lg-3">
                    <div class="report-card p-3 bg-white border shadow-sm h-100 text-center" style="border-radius: 15px;">
                        <i class="<?= $b[0] ?> text-maroon fs-4 mb-2 opacity-50"></i>
                        <h6 class="fw-bold mb-1" style="font-size:11px"><?= $b[1] ?></h6>
                        <p class="text-muted extra-small mb-3"><?= $b[2] ?></p>
                        <button class="btn btn-xs btn-outline-dark w-100 rounded-pill">Open Journal</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 4. MONTHLY SALES SUMMARY (FOR FORM 2550M) -->
            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:12px"><i class="fas fa-calculator me-2 text-maroon"></i> Monthly VAT Summary Intelligence</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-dark rounded-pill px-4 shadow-sm"><i class="fas fa-file-pdf me-2"></i>Export 2550M Data</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Tax Period</th>
                                <th>Gross Vatable Sales</th>
                                <th>VAT Exempt Sales</th>
                                <th>Output VAT (12%)</th>
                                <th>Input VAT (Credit)</th>
                                <th class="text-end pe-4">Net VAT Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-bold"><?= date('F Y') ?></td>
                                <td class="fw-bold">₱ <?= number_format($total_gross_sales / 1.12, 2) ?></td>
                                <td class="text-muted">₱ 0.00</td>
                                <td class="text-maroon fw-bold">₱ <?= number_format($output_vat, 2) ?></td>
                                <td class="text-primary">₱ <?= number_format($input_vat, 2) ?></td>
                                <td class="text-end pe-4">
                                    <h6 class="fw-bold mb-0">₱ <?= number_format($net_vat_payable, 2) ?></h6>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-3 mt-3 bg-light rounded-4 border-start border-4 border-maroon">
                    <p class="mb-0 extra-small text-muted">
                        <i class="fas fa-info-circle me-1"></i> <b>Disclaimer:</b> These reports are system-generated internal records. Compliance with BIR Revenue Regulations No. 16-2005 and RMO No. 10-2005 requires a valid Permit to Use (PTU) for live tax filing.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('partials/admin/footer') ?>