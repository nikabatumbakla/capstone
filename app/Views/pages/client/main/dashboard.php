<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1">Welcome back, <?= esc($client->organization ?? $fullname) ?></h6>
                <p class="mb-0 opacity-75 small"><?= date('l, F d, Y') ?> · Institutional Client Portal</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <a href="<?= base_url('client/orders/my-orders?status=active') ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative border-bottom border-3 border-primary">
                            <i class="fas fa-clipboard-list position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">ACTIVE ORDERS</small>
                            <h3 class="fw-bold mb-0 text-primary"><?= $active_orders ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('client/orders/my-orders') ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative border-bottom border-3 border-success">
                            <i class="fas fa-receipt position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">TOTAL ORDERS (YTD)</small>
                            <h3 class="fw-bold mb-0 text-success"><?= $total_orders_ytd ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('client/orders/my-orders?status=unpaid') ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative border-bottom border-3 border-warning">
                            <i class="fas fa-hourglass-half position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">AWAITING PAYMENT</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $pending_payment ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card position-relative border-bottom border-3 border-maroon">
                        <i class="fas fa-coins position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                        <small class="text-muted fw-bold d-block mb-1">TOTAL SPEND (YTD)</small>
                        <h3 class="fw-bold mb-0">₱<?= number_format($total_spend_ytd, 2) ?></h3>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="custom-table-container mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-receipt me-2 text-maroon"></i>Recent Orders</h6>
                            <a href="<?= base_url('client/orders/my-orders') ?>" class="btn btn-xs btn-outline-dark rounded-pill px-3">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-dark"><tr><th>Order #</th><th>Items</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php if(empty($recent_orders)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No orders yet.</td></tr>
                                    <?php else: foreach($recent_orders as $o): ?>
                                    <tr>
                                        <td class="fw-bold"><?= esc($o['order_number']) ?></td>
                                        <td><?= $o['item_count'] ?></td>
                                        <td>₱<?= number_format($o['total'], 2) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= strtoupper($o['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="col-lg-5">
                    <div class="custom-table-container mb-4">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-money-check-alt me-2 text-warning"></i>Awaiting Payment Confirmation</h6>
                        <?php if(empty($awaiting_confirmation)): ?>
                            <p class="text-muted text-center py-3 mb-0">Nothing awaiting confirmation.</p>
                        <?php else: foreach($awaiting_confirmation as $pc): ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span><?= esc($pc['order_number']) ?></span>
                                <span class="fw-bold">₱<?= number_format($pc['total'], 2) ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>

                    <div class="custom-table-container">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-bullhorn me-2 text-primary"></i>Announcements</h6>
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
<?= view('partials/client/footer') ?>