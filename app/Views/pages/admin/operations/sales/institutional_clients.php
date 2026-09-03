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
                <h5 class="fw-bold mb-0">Institutional Clients</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Institutional Client Management — Outbound Distribution</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Schools · Hospitals · Barangays · SK · LGU</p>
            </div>

            <!-- KPI Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="inventory-kpi-card p-3 shadow-sm bg-white d-flex justify-content-between align-items-center">
                        <div><small class="fw-bold text-muted" style="font-size:9px">SCHOOLS</small><h3 class="fw-bold mb-0"><?= $count_schools ?></h3></div>
                        <i class="fas fa-school fs-2 text-primary opacity-25"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card p-3 shadow-sm bg-white d-flex justify-content-between align-items-center">
                        <div><small class="fw-bold text-muted" style="font-size:9px">HOSPITALS / CLINICS</small><h3 class="fw-bold mb-0 text-danger"><?= $count_hospitals ?></h3></div>
                        <i class="fas fa-hospital fs-2 text-danger opacity-25"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card p-3 shadow-sm bg-white d-flex justify-content-between align-items-center">
                        <div><small class="fw-bold text-muted" style="font-size:9px">LGU / SK</small><h3 class="fw-bold mb-0 text-info"><?= $count_lgu ?></h3></div>
                        <i class="fas fa-landmark fs-2 text-info opacity-25"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card p-3 shadow-sm bg-white d-flex justify-content-between align-items-center">
                        <div><small class="fw-bold text-muted" style="font-size:9px">BARANGAYS</small><h3 class="fw-bold mb-0 text-success"><?= $count_brgy ?></h3></div>
                        <i class="fas fa-map-marker-alt fs-2 text-success opacity-25"></i>
                    </div>
                </div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0" style="font-size:12px"><i class="fas fa-users me-2 text-maroon"></i>Client Directory</h6>

    <form id="searchForm" action="" method="GET" class="d-flex align-items-center gap-2">
        <select name="type" id="typeFilter" class="form-select form-select-sm rounded-pill border" style="height:38px; font-size:11px; width:170px;">
            <option value="">All Types</option>
            <option value="school" <?= ($type_filter == 'school') ? 'selected' : '' ?>>School</option>
            <option value="hospital_clinic" <?= ($type_filter == 'hospital_clinic') ? 'selected' : '' ?>>Hospital / Clinic</option>
            <option value="barangay" <?= ($type_filter == 'barangay') ? 'selected' : '' ?>>Barangay</option>
            <option value="lgu_sk" <?= ($type_filter == 'lgu_sk') ? 'selected' : '' ?>>LGU / SK</option>
            <option value="other" <?= ($type_filter == 'other') ? 'selected' : '' ?>>Other</option>
        </select>
        <div class="position-relative">
            <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill ps-4 border" placeholder="Search clients..." style="height:38px; font-size:11px; width:220px;" value="<?= esc($search) ?>">
            <i class="fas fa-search position-absolute text-muted" style="left:14px; top:11px; font-size:10px;"></i>
        </div>
    </form>
</div>

                <p class="text-muted mb-3" style="font-size:10px;">Showing verified partners only — accounts must be registered via the Partner Gateway with complete contact information.</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:11px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Organization</th><th>Type</th><th>Contact</th><th>Login Email</th><th>Credit Limit</th><th>Balance</th><th>Account</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="clientTableBody">
                            <?php if(empty($clients)): ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted">No verified clients match this filter.</td></tr>
                            <?php else: ?>
                                <?php foreach($clients as $c): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?= $c['organization'] ?></td>
                                    <td><?= ucfirst($c['client_type']) ?></td>
                                    <td><?= $c['phone'] ?></td>
                                    <td class="text-muted"><?= $c['login_email'] ?></td>
                                    <td class="fw-bold">₱<?= number_format($c['credit_limit'], 2) ?></td>
                                    <td class="text-danger fw-bold">₱<?= number_format($c['credit_used'], 2) ?></td>
                                    <td>
                                        <?= $c['is_verified']
                                            ? '<span class="badge rounded-pill bg-success px-3">Verified</span>'
                                            : '<span class="badge rounded-pill bg-warning text-dark px-3">Unverified</span>' ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-view-client" data-id="<?= $c['client_id'] ?>">View</button>
                                    </td>
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
                    <span class="text-muted small fw-bold">Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> clients</span>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="clientDrawer" style="width: 500px;"><div class="offcanvas-body" id="clientDrawerContent"></div></div>

<!-- NEW SALES ORDER DRAWER — launched only from a client's View panel -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="newOrderDrawer" style="width: 700px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="fw-bold mb-0" id="newOrderTitle"><i class="fas fa-plus me-2"></i>New Sales Order</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/sales/save-order') ?>" method="POST" id="newOrderForm">
            <input type="hidden" name="client_id" id="newOrderClientId">

            <div class="p-3 bg-light rounded-3 mb-3">
                <p class="info-label mb-1">Ordering for</p>
                <h6 class="fw-bold mb-0" id="newOrderClientDisplay">—</h6>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="formal-label">Delivery Address</label>
                    <input type="text" name="address" class="formal-input" placeholder="e.g. Iriga City, Camarines Sur">
                </div>
                <div class="col-6">
                    <label class="formal-label">Payment Method</label>
                    <select name="payment_method" class="form-select formal-input">
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="check">Check</option>
                        <option value="cod">Cash on Delivery</option>
                    </select>
                </div>
            </div>

            <p class="text-maroon fw-bold small border-bottom pb-1 mb-3">DISCOUNT</p>
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="formal-label">Discount Type</label>
                    <select name="discount_type" id="discountTypeSelect" class="form-select formal-input">
                        <option value="none">None</option>
                        <option value="pwd">PWD (20% + VAT Exempt)</option>
                        <option value="senior">Senior Citizen (20% + VAT Exempt)</option>
                        <option value="school">School / Institutional Discount</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="col-6" id="discountIdWrap" style="display:none;">
                    <label class="formal-label">ID Number</label>
                    <input type="text" name="discount_id_number" class="formal-input" placeholder="PWD / Senior ID No.">
                </div>
                <div class="col-6" id="discountHolderWrap" style="display:none;">
                    <label class="formal-label">Name on ID</label>
                    <input type="text" name="discount_holder_name" class="formal-input">
                </div>
                <div class="col-6" id="discountCustomWrap" style="display:none;">
                    <label class="formal-label">Custom Discount (%)</label>
                    <input type="number" name="discount_percent" class="formal-input" value="0" min="0" max="100">
                </div>
                <div class="col-12" id="discountSchoolWrap" style="display:none;">
                    <p class="helper-text mb-0">Applies the standard school discount rate: <b><span id="schoolRateDisplay"></span>%</b></p>
                </div>
            </div>

            <p class="text-maroon fw-bold small border-bottom pb-1 mb-3">ORDER ITEMS</p>
<div class="row g-2 mb-1 px-1" style="font-size:10px;">
    <div class="col-4 text-muted fw-bold">CATEGORY</div>
    <div class="col-3 text-muted fw-bold">PRODUCT</div>
    <div class="col-2 text-muted fw-bold">QTY</div>
    <div class="col-2 text-muted fw-bold text-end">SUBTOTAL</div>
</div>
<div id="orderRowsContainer"></div>
<button type="button" id="btnAddOrderRow" class="btn btn-xs btn-outline-dark mt-2">+ Add Product</button>

<div class="p-3 bg-light rounded-3 mt-4 border" style="font-size:12px;">
    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Gross Amount</span><span id="previewGross">₱0.00</span></div>
    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Discount<span id="previewDiscountLabel"></span></span><span id="previewDiscount" class="text-danger">-₱0.00</span></div>
    <div class="d-flex justify-content-between mb-1"><span class="text-muted">VAT-Exclusive Amount</span><span id="previewSubtotal">₱0.00</span></div>
    <div class="d-flex justify-content-between mb-2"><span class="text-muted">VAT (12%)</span><span id="previewVat">₱0.00</span></div>
    <hr class="my-1">
    <div class="d-flex justify-content-between">
        <span class="fw-bold">TOTAL AMOUNT DUE</span>
        <span class="fw-bold text-maroon fs-6" id="previewTotal">₱0.00</span>
    </div>
</div>

<button type="submit" class="btn w-100 py-3 mt-4 fw-bold text-white" style="background:#0d2e4f;">
    <i class="fas fa-file-invoice me-2"></i>CREATE SALES ORDER
</button>
 </form>
    </div>
</div>

<script>
  const PRODUCT_CATEGORIES = <?= json_encode($categories) ?>;
  const CLIENT_PRODUCTS = <?= json_encode($products) ?>;
  const SCHOOL_DISCOUNT_RATE = <?= (float) $school_discount_rate ?>;
</script>

<script src="<?= base_url('public/js/admin/operations/sales/sales_clients.js') ?>"></script>
<?= view('partials/admin/footer') ?>