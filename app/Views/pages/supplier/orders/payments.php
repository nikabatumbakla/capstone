<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Payment History</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-money-check-alt me-2"></i>Payments Received</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Completed payments from Robin Rose Trading</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">TOTAL RECEIVED (ALL TIME)</small><h3 class="fw-bold mb-0 text-success">₱<?= number_format($total_paid, 2) ?></h3></div>
                </div>
                <div class="col-md-4">
                    <div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">RECEIVED THIS MONTH</small><h3 class="fw-bold mb-0 text-primary">₱<?= number_format($paid_this_month, 2) ?></h3></div>
                </div>
                <div class="col-md-4">
                    <div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">PAID ORDERS</small><h3 class="fw-bold mb-0"><?= $count_paid ?></h3></div>
                </div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;">Payment Records</h6>
                    <form action="" method="GET">
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search PO #..." style="width:200px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">PO #</th><th>Amount</th><th>Method</th><th>Reference</th><th>Date Paid</th></tr></thead>
                        <tbody>
                            <?php if(empty($payments)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No payments recorded yet.</td></tr>
                            <?php else: foreach($payments as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= esc($p['po_number']) ?></td>
                                <td class="fw-bold text-success">₱<?= number_format($p['total_amount'], 2) ?></td>
                                <td><?= $p['payment_method'] ? strtoupper(str_replace('_',' ', $p['payment_method'])) : '—' ?></td>
                                <td><code><?= esc($p['payment_reference'] ?: '—') ?></code></td>
                                <td><?= $p['paid_at'] ? date('M d, Y', strtotime($p['paid_at'])) : '—' ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-4">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <?php for($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= view('partials/supplier/footer') ?>