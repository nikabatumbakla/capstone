<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1">Welcome back, <?= esc($fullname) ?></h6>
                <p class="mb-0 opacity-75 small"><?= date('l, F d, Y') ?> · Supplier Portal</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <a href="<?= base_url('supplier/orders/inbox?tab=open') ?>" class="text-decoration-none">
                        <div class="inventory-kpi-card border-bottom border-primary border-4">
                            <small class="text-muted fw-bold d-block mb-1">OPEN PURCHASE ORDERS</small>
                            <h3 class="fw-bold mb-0 text-primary"><?= $open_pos_count ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('supplier/orders/inbox?tab=pending') ?>" class="text-decoration-none">
                        <div class="inventory-kpi-card border-bottom border-warning border-4">
                            <small class="text-muted fw-bold d-block mb-1">AWAITING ACKNOWLEDGMENT</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $pending_ack ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card border-bottom border-success border-4">
                        <small class="text-muted fw-bold d-block mb-1">COMPLETED (YTD)</small>
                        <h3 class="fw-bold mb-0 text-success"><?= $completed_ytd ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card border-bottom border-maroon border-4">
                        <small class="text-muted fw-bold d-block mb-1">ON-TIME DELIVERY RATE</small>
                        <h3 class="fw-bold mb-0"><?= $scorecard->on_time_rate !== null ? number_format($scorecard->on_time_rate, 1) . '%' : 'No data yet' ?></h3>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="custom-table-container h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-file-invoice me-2 text-maroon"></i>Recent Purchase Orders</h6>
                            <a href="<?= base_url('supplier/orders/inbox') ?>" class="btn btn-xs btn-outline-dark rounded-pill px-3">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-dark"><tr><th>PO #</th><th>Items</th><th>Amount</th><th>Expected</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php if(empty($recent_pos)): ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No purchase orders yet.</td></tr>
                                    <?php else: foreach($recent_pos as $po): ?>
                                    <tr>
                                        <td class="fw-bold"><?= esc($po['po_number']) ?></td>
                                        <td><?= $po['items'] ?></td>
                                        <td>₱<?= number_format($po['total_amount'], 2) ?></td>
                                        <td><?= $po['expected_date'] ? date('M d, Y', strtotime($po['expected_date'])) : '—' ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= strtoupper($po['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="custom-table-container mb-4">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-chart-pie me-2 text-primary"></i>Performance Scorecard</h6>
                        <?php if($scorecard->total_orders == 0): ?>
                            <p class="text-muted text-center py-3 mb-0">Your scorecard will populate once you've completed your first delivery.</p>
                        <?php else: ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>On-Time Delivery</span>
                                <span class="fw-bold"><?= number_format($scorecard->on_time_rate, 1) ?>%</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>Order Accuracy</span>
                                <span class="fw-bold"><?= number_format($scorecard->accuracy_rate, 1) ?>%</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span>Total Orders Fulfilled</span>
                                <span class="fw-bold"><?= $scorecard->total_orders ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="custom-table-container">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-bullhorn me-2 text-warning"></i>Announcements</h6>
                        <?php if(empty($announcements)): ?>
                            <p class="text-muted text-center py-3 mb-0">No announcements right now.</p>
                        <?php else: foreach($announcements as $a): ?>
                            <div class="mb-3 pb-2 border-bottom">
                                <p class="fw-bold mb-1"><?= esc($a['title']) ?></p>
                                <small class="text-muted"><?= date('M d, Y', strtotime($a['created_at'])) ?></small>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/supplier/footer') ?>