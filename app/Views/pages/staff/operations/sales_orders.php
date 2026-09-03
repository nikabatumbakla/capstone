<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Sales Orders</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-file-invoice me-2"></i>Sales Order Processing</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Distribution Queue — Track and Fulfill Institutional Orders</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-3">
        <a href="?status=pending" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter=='pending'?'border-bottom border-3 border-warning':'' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">PENDING</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $count_pending ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=processing" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter=='processing'?'border-bottom border-3 border-primary':'' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">PROCESSING</small>
                <h3 class="fw-bold mb-0 text-primary"><?= $count_processing ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=shipped" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter=='shipped'?'border-bottom border-3 border-info':'' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">SHIPPED</small>
                <h3 class="fw-bold mb-0 text-info"><?= $count_shipped ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=delivered" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter=='delivered'?'border-bottom border-3 border-success':'' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">DELIVERED</small>
                <h3 class="fw-bold mb-0 text-success"><?= $count_delivered ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
</div>

<?php if ($status_filter): ?>
<div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
    <span><strong>
        <?php
            $labels = ['pending' => 'Pending Orders', 'processing' => 'Orders Being Processed', 'shipped' => 'Shipped Orders', 'delivered' => 'Delivered Orders'];
            echo $labels[$status_filter] ?? strtoupper($status_filter);
        ?>
    </strong></span>
    <a href="?<?= $search ? 'search='.urlencode($search) : '' ?>" class="text-danger fw-bold text-decoration-none"> ×</a>
</div>
<?php endif; ?>

            <div class="custom-table-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-truck me-2 text-maroon"></i>Order Queue</h6>
        <form id="filterForm" action="" method="GET">
            <input type="hidden" name="status" value="<?= esc($status_filter) ?>">
            <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill" placeholder="Search order # or client..." style="width:220px;" value="<?= esc($search) ?>">
        </form>
    </div>
    <!-- table-responsive etc. unchanged -->

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">SO Number</th><th>Institution</th><th>Items</th><th>Total</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($orders)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No orders match this filter.</td></tr>
                            <?php else:
                                $statusMeta = [
                                    'pending'    => 'bg-warning text-dark',
                                    'confirmed'  => 'bg-info text-dark',
                                    'processing' => 'bg-primary',
                                    'shipped'    => 'bg-info',
                                    'delivered'  => 'bg-success',
                                    'cancelled'  => 'bg-secondary',
                                ];
                                foreach($orders as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= esc($o['order_number']) ?></td>
                                <td><?= esc($o['organization']) ?></td>
                                <td><?= $o['item_count'] ?> items</td>
                                <td class="fw-bold">₱<?= number_format($o['total'], 2) ?></td>
                                <td><span class="badge rounded-pill <?= $statusMeta[$o['status']] ?? 'bg-light text-dark border' ?> px-3"><?= strtoupper($o['status']) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-dark rounded-pill px-4 btn-manage-order" data-id="<?= $o['order_id'] ?>">Manage Order</button>
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
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted fw-bold" style="font-size:10px;">Page <?= $current_page ?> of <?= $total_pages ?></span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i=$ws;$i<=$we;$i++): ?><li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li><?php endfor; ?>
                        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="manageOrderDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0" id="orderDrawerTitle">Manage Order</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="orderDrawerContent"></div>
</div>

<script src="<?= base_url('public/js/staff/operations/sales_orders.js') ?>"></script>
<?= view('partials/staff/footer') ?>