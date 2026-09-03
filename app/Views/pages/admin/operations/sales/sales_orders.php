<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">

        <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Sales Orders</h5>
            </div>
     
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Sales Order Management — Outbound</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Invoice Generation · Payment Tracking · Delivery Status · Returns</p>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
    <h6 class="fw-bold mb-0" style="font-size:14px;"><i class="fas fa-receipt me-2 text-maroon"></i>Order Records</h6>

    <form id="filterForm" action="" method="GET" class="d-flex align-items-center gap-2">
        <select name="type" id="typeFilter" class="form-select form-select-sm rounded-pill border" style="height:38px; font-size:11px; width:170px;">
            <option value="">All Categories</option>
            <option value="school" <?= ($type_filter == 'school') ? 'selected' : '' ?>>Schools</option>
            <option value="hospital_clinic" <?= ($type_filter == 'hospital_clinic') ? 'selected' : '' ?>>Hospitals / Clinics</option>
            <option value="barangay" <?= ($type_filter == 'barangay') ? 'selected' : '' ?>>Barangays</option>
            <option value="lgu_sk" <?= ($type_filter == 'lgu_sk') ? 'selected' : '' ?>>LGU / SK</option>
        </select>
        <div class="position-relative">
            <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill ps-4 border" placeholder="Search order # or client..." style="height:38px; font-size:11px; width:220px;" value="<?= esc($search) ?>">
            <i class="fas fa-search position-absolute text-muted" style="left:14px; top:11px; font-size:10px;"></i>
        </div>
    </form>
</div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:11px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Order #</th><th>Client</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($orders)): ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted">No records found.</td></tr>
                            <?php else: ?>
                                <?php foreach($orders as $o): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= $o['order_number'] ?></td>
                                    <td><?= $o['client_name'] ?></td>
                                    <td><?= $o['item_count'] ?> items</td>
                                    <td class="fw-bold">₱<?= number_format($o['total'], 2) ?></td>
                                    <td><span class="fw-bold" style="color:<?= $o['payment_status']=='paid'?'#27ae60':'#e74c3c' ?>"><?= strtoupper($o['payment_status']) ?></span></td>
                                    <td><span class="badge rounded-pill bg-soft-maroon text-dark px-3"><?= ucwords($o['status']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                    <td class="text-center"><button class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-view-so" data-id="<?= $o['order_id'] ?>">View</button></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $rangeStart  = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
                    $rangeEnd    = min($current_page * $per_page, $total_rows);
                    $searchQuery = $search !== '' ? '&search=' . urlencode($search) : '';
                    $typeQuery   = $type_filter !== '' ? '&type=' . urlencode($type_filter) : '';
                    $pageQuery   = $searchQuery . $typeQuery;

                    $windowSize   = 3;
                    $currentBlock = (int) ceil($current_page / $windowSize);
                    $windowStart  = (($currentBlock - 1) * $windowSize) + 1;
                    $windowEnd    = min($windowStart + $windowSize - 1, $total_pages);
                ?>
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted small fw-bold">Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> orders</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 custom-pager">
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= max(1, $current_page - 1) . $pageQuery ?>"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i . $pageQuery ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $pageQuery ?>"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="soDrawer" style="width: 600px;"><div class="offcanvas-body p-0" id="soDrawerContent"></div></div>

<script src="<?= base_url('public/js/admin/operations/sales/sales_orders.js') ?>"></script>
<?= view('partials/admin/footer') ?>