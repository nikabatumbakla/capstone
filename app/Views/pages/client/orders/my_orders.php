<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>
<script>
    const CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
    const CSRF_HASH = "<?= csrf_hash() ?>";
</script>

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">My Orders</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-list-ul me-2"></i>Order History</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Monitor distribution status and view invoices</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-3">
        <a href="?status=active<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none">
            <div class="inventory-kpi-card <?= $status_filter=='active' ? 'border-bottom border-3 border-primary' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">ACTIVE ORDERS</small>
                <h3 class="fw-bold mb-0 text-primary"><?= $count_active ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=unpaid<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none">
            <div class="inventory-kpi-card <?= $status_filter=='unpaid' ? 'border-bottom border-3 border-warning' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">AWAITING PAYMENT</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $count_unpaid ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=ytd<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none">
            <div class="inventory-kpi-card <?= $status_filter=='ytd' ? 'border-bottom border-3 border-success' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">TOTAL ORDERS (YTD)</small>
                <h3 class="fw-bold mb-0 text-success"><?= $count_ytd ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=completed<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none">
            <div class="inventory-kpi-card <?= $status_filter=='completed' ? 'border-bottom border-3 border-info' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">COMPLETED</small>
                <h3 class="fw-bold mb-0 text-info"><?= $count_completed ?></h3>
            </div>
        </a>
    </div>
</div>

            <?php if ($status_filter): ?>
            <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
                <span><strong><?= ['active'=>'Active Orders','unpaid'=>'Awaiting Payment','ytd'=>'This Year\'s Orders','completed'=>'Completed Orders'][$status_filter] ?? ucfirst($status_filter) ?></strong></span>
                <a href="?<?= $search ? 'search='.urlencode($search) : '' ?>" class="text-danger fw-bold text-decoration-none"> ×</a>
            </div>
            <?php endif; ?>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;">Order Records</h6>
                    <form action="" method="GET">
                        <input type="hidden" name="status" value="<?= esc($status_filter) ?>">
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search order #..." style="width:220px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <?php
                    $statusLabels = ['pending' => 'Pending', 'ready_for_pickup' => 'Ready for Pickup', 'out_for_delivery' => 'Out for Delivery', 'cancelled' => 'Cancelled'];
                ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">Order #</th><th>Items</th><th>Status</th><th>Total</th><th>Payment</th><th>Created At</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($orders)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No orders match this filter.</td></tr>
                            <?php else: foreach($orders as $o):
                                $displayStatus = ($o['status'] === 'delivered')
                                    ? ($o['fulfillment_type'] === 'pickup' ? 'Order Completed' : 'Delivered')
                                    : ($statusLabels[$o['status']] ?? ucfirst($o['status']));
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= esc($o['order_number']) ?></td>
                                <td><?= $o['item_count'] ?></td>
                                <td><span class="badge rounded-pill bg-light text-dark border px-3"><?= strtoupper($displayStatus) ?></span></td>
                                <td class="fw-bold">₱<?= number_format($o['total'], 2) ?></td>
                                <td><span class="text-<?= $o['payment_status']=='paid'?'success':'danger' ?> fw-bold"><?= strtoupper($o['payment_status']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                
                                <td class="text-center">
    <?php if($o['fulfillment_type'] === 'delivery' && in_array($o['payment_method'], ['cheque','bank_transfer']) && $o['payment_status'] === 'unpaid'): ?>
        <?php if(!empty($o['client_payment_submitted_at'])): ?>
            <span class="badge bg-secondary d-block mb-1" style="font-size:8px;">AWAITING CONFIRMATION</span>
        <?php else: ?>
            <button type="button" class="btn btn-xs btn-warning rounded-circle me-1 btn-submit-payment" data-id="<?= $o['order_id'] ?>" title="Submit Payment" style="width:28px;height:28px;"><i class="fas fa-money-check-alt"></i></button>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($o['fulfillment_type'] === 'delivery' && $o['status'] === 'out_for_delivery'): ?>
        <button type="button" class="btn btn-xs btn-success rounded-circle me-1 btn-confirm-receipt" data-id="<?= $o['order_id'] ?>" title="Confirm Receipt" style="width:28px;height:28px;"><i class="fas fa-check-double"></i></button>
    <?php elseif($o['fulfillment_type'] === 'pickup' && $o['status'] === 'ready_for_pickup'): ?>
        <span class="badge bg-info text-dark d-block mb-1" style="font-size:9px;">READY — VISIT STORE</span>
    <?php endif; ?>

    <button class="btn btn-xs btn-dark rounded-circle btn-view-order" data-id="<?= $o['order_id'] ?>" title="View Details" style="width:28px;height:28px;"><i class="fas fa-eye"></i></button>
</td>

                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $q = '&status='.$status_filter.'&search='.urlencode($search);
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="orderDrawer" style="width:600px;"><div class="offcanvas-body p-0" id="orderDrawerContent"></div></div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="paymentDrawer" style="width:450px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0"><i class="fas fa-money-check-alt me-2"></i>Complete Your Payment</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="paymentDrawerContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="receiptDrawer" style="width:550px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0"><i class="fas fa-check-double me-2"></i>Confirm Delivery</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="receiptDrawerContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="issueDrawer" style="width:550px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Report an Issue</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="issueDrawerContent"></div>
</div>

<script src="<?= base_url('public/js/client/my_orders.js') ?>"></script>
<?= view('partials/client/footer') ?>