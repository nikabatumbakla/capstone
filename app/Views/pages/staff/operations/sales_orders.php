<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>
<script>
    const CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
    const CSRF_HASH = "<?= csrf_hash() ?>";
</script>

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Distribution Queue</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Sales Order Fulfillment</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Process pending orders, confirm payments, and dispatch deliveries</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small" id="flashError"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <a href="?status=pending<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $status_filter=='pending'?'border-bottom border-3 border-warning':'' ?>">
                            <i class="fas fa-hourglass-half position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">NEW ORDERS</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $count_pending ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?status=in_progress<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $status_filter=='in_progress'?'border-bottom border-3 border-primary':'' ?>">
                            <i class="fas fa-truck position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">IN PROGRESS</small>
                            <h3 class="fw-bold mb-0 text-primary"><?= $count_in_progress ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?status=returns<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $status_filter=='returns'?'border-bottom border-3 border-danger':'' ?>">
                            <i class="fas fa-undo-alt position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">RETURN UNDER REVIEW</small>
                            <h3 class="fw-bold mb-0 text-danger"><?= $count_returns ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?status=delivered<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $status_filter=='delivered'?'border-bottom border-3 border-success':'' ?>">
                            <i class="fas fa-check-circle position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">COMPLETED</small>
                            <h3 class="fw-bold mb-0 text-success"><?= $count_delivered ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;">Order Queue</h6>
                    <form action="" method="GET" id="filterForm">
                        <input type="hidden" name="status" value="<?= esc($status_filter) ?>">
                        <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill" placeholder="Search order #..." style="width:220px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <?php $displayStatusMap = ['pending'=>'Pending','ready_for_pickup'=>'Ready for Pickup','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered','return_pending'=>'Return Under Review','cancelled'=>'Cancelled']; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">Order #</th><th>Client</th><th class="text-center">Fulfillment</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($orders)): ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted">No orders match this filter.</td></tr>
                            <?php else: foreach($orders as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= esc($o['order_number']) ?></td>
                                <td>
                                    <?= esc($o['client_name']) ?>
                                    <?php if(!empty($o['guest_client_id'])): ?><br><span class="badge bg-secondary" style="font-size:8px;">WALK-IN</span><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if($o['fulfillment_type'] === 'pickup'): ?>
                                        <i class="fas fa-store text-info" title="Store Pickup"></i>
                                    <?php else: ?>
                                        <i class="fas fa-truck text-primary" title="Delivery"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= $o['item_count'] ?></td>
                                <td class="fw-bold">₱<?= number_format($o['total'], 2) ?></td>
                                <td><span class="fw-bold" style="color:<?= $o['payment_status']=='paid'?'#27ae60':'#e74c3c' ?>"><?= strtoupper($o['payment_status']) ?></span></td>
                                <td><span class="badge rounded-pill bg-light text-dark border px-3"><?= strtoupper($displayStatusMap[$o['status']] ?? $o['status']) ?></span></td>
                                <td class="text-center"><button class="btn btn-xs btn-dark rounded-pill px-3 btn-view-so" data-id="<?= $o['order_id'] ?>">View</button></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-4">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <?php for($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="soDrawer" style="width: 600px;"><div class="offcanvas-body p-0" id="soDrawerContent"></div></div>

<script>
    setTimeout(function() {
        ['flashError', 'flashSuccess'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) { el.style.transition = 'opacity 0.5s ease'; el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }
        });
    }, 5000);
</script>
<script src="<?= base_url('public/js/staff/operations/sales_orders.js') ?>"></script>
<?= view('partials/staff/footer') ?>