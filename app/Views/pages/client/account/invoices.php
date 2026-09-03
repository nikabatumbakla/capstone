<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Invoices & Payments</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-file-invoice-dollar me-2"></i>My Invoices</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">View BIR-compliant invoices and submit payment references for outstanding orders</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <a href="?status=unpaid" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $status_filter=='unpaid' ? 'border-bottom border-3 border-danger' : '' ?>">
                            <small class="text-muted fw-bold d-block mb-1">OUTSTANDING BALANCE</small>
                            <h3 class="fw-bold mb-0 text-danger">₱<?= number_format($outstanding_amount, 2) ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?status=submitted" class="text-decoration-none">
    <div class="inventory-kpi-card <?= $status_filter=='submitted' ? 'border-bottom border-3 border-warning' : '' ?>">
        <small class="text-muted fw-bold d-block mb-1">PAYMENT SUBMITTED</small>
        <h3 class="fw-bold mb-0 text-warning"><?= $awaiting_clearance ?></h3>
    </div>
</a>
                </div>
                <div class="col-md-3">
                    <a href="?status=paid" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $status_filter=='paid' ? 'border-bottom border-3 border-success' : '' ?>">
                            <small class="text-muted fw-bold d-block mb-1">PAID (YTD)</small>
                            <h3 class="fw-bold mb-0 text-success"><?= $paid_ytd ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= !$status_filter ? 'border-bottom border-3 border-maroon' : '' ?>">
                            <small class="text-muted fw-bold d-block mb-1">UNPAID INVOICES</small>
                            <h3 class="fw-bold mb-0"><?= $unpaid_count ?></h3>
                        </div>
                    </a>
                </div>
            </div>

            <?php if ($status_filter): ?>
            <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
                <span><strong><?= ['unpaid'=>'Unpaid Orders','submitted'=>'Awaiting Clearance','paid'=>'Paid Orders'][$status_filter] ?? ucfirst($status_filter) ?></strong></span>
                <a href="?" class="text-danger fw-bold text-decoration-none">×</a>
            </div>
            <?php endif; ?>

            <div class="custom-table-container">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th class="ps-4">Invoice #</th><th>Order #</th><th>Total</th><th>VAT (12%)</th><th>Date</th><th>Status</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if(empty($invoices)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No invoices match this filter.</td></tr>
                            <?php else: foreach($invoices as $i):
                                $vatAmount = $i['total'] - ($i['total'] / 1.12);
                                $statusMeta = ['unpaid' => 'bg-danger', 'submitted' => 'bg-warning text-dark', 'paid' => 'bg-success'];
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= esc($i['invoice_number'] ?: 'N/A') ?></td>
                                <td><?= esc($i['order_number']) ?></td>
                                <td class="fw-bold">₱<?= number_format($i['total'], 2) ?></td>
                                <td class="text-muted">₱<?= number_format($i['vat_amount'], 2) ?></td>
                                <td><?= date('M d, Y', strtotime($i['created_at'])) ?></td>
                                <td><span class="badge rounded-pill <?= $statusMeta[$i['payment_status']] ?? 'bg-secondary' ?> px-3"><?= strtoupper($i['payment_status']) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-dark rounded-circle btn-view-invoice" data-id="<?= $i['order_id'] ?>" title="View Invoice" style="width:30px; height:30px;">
    <i class="fas fa-eye"></i>
</button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $q = '&status='.$status_filter;
                    $w=3; $cb=(int)ceil($current_page/$w); $ws=(($cb-1)*$w)+1; $we=min($ws+$w-1,$total_pages);
                ?>
                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-4">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i=$ws;$i<=$we;$i++): ?><li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li><?php endfor; ?>
                        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="invoiceDrawer" style="width:550px;"><div class="offcanvas-body p-0" id="invoiceDrawerContent"></div></div>

<script src="<?= base_url('public/js/client/invoices.js') ?>"></script>
<?= view('partials/client/footer') ?>