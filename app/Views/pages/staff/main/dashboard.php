<?= view('partials/staff/head') ?>
<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1">Good day, <?= esc(explode(' ', $fullname)[0]) ?>!</h6>
                <p class="mb-0 opacity-75 small"><?= date('l, F d, Y') ?> · Staff Terminal</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-3">
        <a href="<?= base_url('staff/operations/pos') ?>" target="_blank" rel="noopener" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">TODAY'S POS TXNS</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $pos_txns ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= base_url('staff/operations/goods-receipt') ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">PENDING GRR</small>
                <h3 class="fw-bold mb-0 text-danger"><?= $pending_grr ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= base_url('staff/operations/sales-orders?status=pending') ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">ORDERS TO PROCESS</small>
                <h3 class="fw-bold mb-0 text-success"><?= $orders_to_process ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= base_url('staff/info/alerts') ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">MY ALERTS</small>
                <h3 class="fw-bold mb-0 text-dark"><?= $assigned_alerts ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
</div>

            <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold" id="btnOpenQrDrawer">
        <i class="fas fa-qrcode me-2"></i>Mobile & Customer QR
    </button>
</div>

            <div class="row g-4">
    <div class="col-lg-6">
        <div class="custom-table-container h-100">
            <h6 class="fw-bold mb-4 text-dark" style="font-size:13px;"><i class="fas fa-tasks me-2 text-maroon"></i>Today's Tasks</h6>
            
                        <?php
                            $typeMeta = [
                                'low_stock'      => ['icon' => 'fa-exclamation-triangle', 'color' => '#e74c3c'],
                                'near_expiry'    => ['icon' => 'fa-hourglass-half', 'color' => '#f1c40f'],
                                'expired'        => ['icon' => 'fa-ban', 'color' => '#7b1113'],
                                'po_approval'    => ['icon' => 'fa-file-alt', 'color' => '#3498db'],
                                'assigned_task'  => ['icon' => 'fa-clipboard-check', 'color' => '#3498db'],
                            ];
                        ?>
                        <?php if(empty($tasks)): ?>
                            <div class="p-4 text-center text-muted">No open tasks right now — you're all caught up.</div>
                        <?php else: foreach($tasks as $t):
                            $meta = $typeMeta[$t['alert_type']] ?? ['icon' => 'fa-info-circle', 'color' => '#6c757d'];
                        ?>
                        <div class="p-2 px-3 mb-2 rounded-pill border d-flex align-items-center bg-light" style="border-left: 5px solid <?= $meta['color'] ?> !important;">
                            <i class="fas <?= $meta['icon'] ?> me-3" style="color:<?= $meta['color'] ?>;"></i>
                            <span class="fw-bold text-dark flex-grow-1"><?= esc($t['message']) ?></span>
                            <?php if($t['priority'] === 'high'): ?><span class="badge bg-danger">High</span><?php endif; ?>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
        <div class="custom-table-container h-100">
            <h6 class="fw-bold mb-4 text-dark" style="font-size:13px;"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low Stock to Restock</h6>
            <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-dark"><tr><th class="ps-3">Product</th><th>Stock</th><th>Reorder At</th><th class="text-center">Action</th></tr></thead>
                                <tbody>
                                    <?php if(empty($low_stock)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No products are currently low on stock.</td></tr>
                                    <?php else: foreach($low_stock as $ls): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold"><?= esc($ls['name']) ?></td>
                                        <td class="text-danger fw-bold"><?= $ls['quantity_avail'] ?></td>
                                        <td><?= $ls['reorder_level'] ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('staff/inventory/stock?highlight='.$ls['batch_id']) ?>" class="btn btn-xs btn-outline-dark rounded-pill px-3">Adjust</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="qrDrawer" style="width: 420px;">
            <div class="offcanvas-header border-bottom">
                <h6 class="fw-bold mb-0"><i class="fas fa-qrcode me-2"></i>QR Access Codes</h6>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-4">
                <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill border">
                    <li class="nav-item flex-grow-1"><button class="nav-link active rounded-pill w-100 small fw-bold" data-bs-toggle="pill" data-bs-target="#qr-staff">Staff Login</button></li>
                    <li class="nav-item flex-grow-1"><button class="nav-link rounded-pill w-100 small fw-bold" data-bs-toggle="pill" data-bs-target="#qr-customer">Customer Poster</button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active text-center" id="qr-staff">
                        <p class="text-muted mb-3" style="font-size:10px;">Scan with your phone to open the staff login on mobile. Log in with your own account as usual.</p>
                        <div id="staffQrCode" class="d-inline-block p-3 bg-white border rounded-3"></div>
                        <p class="text-muted mt-2 mb-3" style="font-size:9px; word-break:break-all;"><?= base_url('portal') ?></p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-dark rounded-pill flex-grow-1 btn-download-qr" data-target="staffQrCode" data-name="staff-login-qr"><i class="fas fa-download me-1"></i>Download</button>
                            <button class="btn btn-sm btn-dark rounded-pill flex-grow-1 btn-print-qr" data-target="staffQrCode" data-title="Staff Mobile Login"><i class="fas fa-print me-1"></i>Print</button>
                        </div>
                    </div>

                    <div class="tab-pane fade text-center" id="qr-customer">
                        <p class="text-muted mb-3" style="font-size:10px;">Printable poster QR — customers scan this to enter their name and contact details.</p>
                        <div id="customerQrCode" class="d-inline-block p-3 bg-white border rounded-3"></div>
                        <p class="text-muted mt-2 mb-3" style="font-size:9px; word-break:break-all;"><?= base_url('customer/info') ?></p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-dark rounded-pill flex-grow-1 btn-download-qr" data-target="customerQrCode" data-name="customer-info-qr"><i class="fas fa-download me-1"></i>Download</button>
                            <button class="btn btn-sm btn-dark rounded-pill flex-grow-1 btn-print-qr" data-target="customerQrCode" data-title="Customer Sign-In Poster"><i class="fas fa-print me-1"></i>Print</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const STAFF_LOGIN_URL = "<?= base_url('portal') ?>";
    const CUSTOMER_INFO_URL = "<?= base_url('customer/info') ?>";
</script>
<script src="<?= base_url('public/js/vendor/qrcode.min.js') ?>"></script>
<script src="<?= base_url('public/js/staff/dashboard_qr.js') ?>"></script>

<?= view('partials/staff/footer') ?>