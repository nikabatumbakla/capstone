<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>
    const CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
    const CSRF_HASH = "<?= csrf_hash() ?>";
    const PRODUCTS_DATA = <?= json_encode($products) ?>;
    const CATEGORIES_DATA = <?= json_encode($categories) ?>;
    const SCHOOL_DISCOUNT_RATE = <?= (float) $school_discount_rate ?>;
</script>

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

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small" id="flashError"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

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
            <option value="walkin" <?= ($type_filter == 'walkin') ? 'selected' : '' ?>>Walk-in (No Account)</option>
        </select>
        <div class="position-relative">
            <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill ps-4 border" placeholder="Search order # or client..." style="height:38px; font-size:11px; width:220px;" value="<?= esc($search) ?>">
            <i class="fas fa-search position-absolute text-muted" style="left:14px; top:11px; font-size:10px;"></i>
        </div>
    </form>
    <button type="button" class="btn btn-sm btn-dark rounded-pill px-4 shadow-sm" id="btnWalkinSale">
    <i class="fas fa-user-plus me-2"></i>Walk-in Sale (No Account)
</button>
</div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:11px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Order #</th><th>Client</th><th class="text-center">Fulfillment</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($orders)): ?>
                                <tr><td colspan="9" class="text-center py-5 text-muted">No records found.</td></tr>
                            <?php else: ?>
                                <?php foreach($orders as $o): ?>
                                <tr>
                                    <td class="ps-4 fw-bold">
    <?= $o['order_number'] ?>
    <?php if(!empty($o['replacement_for_return_id'])): ?>
        <span class="badge bg-info text-dark ms-1" style="font-size:8px;">REPLACEMENT</span>
    <?php endif; ?>
</td>
                                    <td>
                                        <?= $o['client_name'] ?>
                                        <?php if(!empty($o['guest_client_id'])): ?>
                                            <br><span class="badge bg-secondary" style="font-size:8px;">WALK-IN — NO ACCOUNT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if(($o['fulfillment_type'] ?? 'delivery') === 'pickup'): ?>
                                            <i class="fas fa-store text-info" title="Store Pickup"></i>
                                        <?php else: ?>
                                            <i class="fas fa-truck text-primary" title="Delivery"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $o['item_count'] ?> items</td>
                                    <td class="fw-bold">₱<?= number_format($o['total'], 2) ?></td>
                                    <td><span class="fw-bold" style="color:<?= $o['payment_status']=='paid'?'#27ae60':'#e74c3c' ?>"><?= strtoupper($o['payment_status']) ?></span></td>
                                    <td><span class="badge rounded-pill bg-soft-maroon text-dark px-3"><?= ucwords(str_replace('_',' ',$o['status'])) ?></span></td>
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

<!-- NEW SALES ORDER DRAWER — launched only from a client's View panel -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="newOrderDrawer" style="width: 700px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="fw-bold mb-0" id="newOrderTitle"><i class="fas fa-plus me-2"></i>New Sales Order</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/sales/save-order') ?>" method="POST" id="newOrderForm">
            <?= csrf_field() ?>

            <input type="hidden" name="order_mode" id="orderModeField" value="registered">

<div id="registeredClientBlock" class="p-3 bg-light rounded-3 mb-3">
    <p class="info-label mb-1">Ordering for</p>
    <h6 class="fw-bold mb-0" id="newOrderClientDisplay">—</h6>
    <input type="hidden" name="client_id" id="newOrderClientId">
</div>

<div id="walkinClientBlock" class="mb-3" style="display:none;">
    <p class="text-maroon fw-bold small border-bottom pb-1 mb-3">CUSTOMER INFORMATION (WALK-IN)</p>
    <div class="row g-3">
        <div class="col-6"><label class="formal-label">Customer / Organization Name *</label><input type="text" name="guest_name" id="guestNameInput" class="formal-input"></div>
        <div class="col-6"><label class="formal-label">Contact Person</label><input type="text" name="guest_contact" class="formal-input"></div>
        <div class="col-6"><label class="formal-label">Phone</label><input type="text" name="guest_phone" class="formal-input"></div>
        <div class="col-6"><label class="formal-label">Email</label><input type="email" name="guest_email" class="formal-input"></div>
        <div class="col-6"><label class="formal-label">Address</label><input type="text" name="guest_address" class="formal-input"></div>
        <div class="col-6"><label class="formal-label">TIN</label><input type="text" name="guest_tin" class="formal-input"></div>
    </div>
    <p class="helper-text mt-2">If this customer has ordered before under the same name, their record is reused automatically.</p>
</div>

            <div class="row g-3 mb-1">
    <div class="col-6">
        <label class="formal-label">Fulfillment Type *</label>
        <select name="fulfillment_type" id="fulfillmentTypeSelect" class="form-select formal-input" required>
            <option value="delivery" selected>Delivery</option>
            <option value="pickup">Store Pickup</option>
        </select>
    </div>
    <div class="col-6" id="deliveryAddressWrap">
        <label class="formal-label">Delivery Address</label>
        <input type="text" name="address" id="deliveryAddressInput" class="formal-input" placeholder="e.g. Iriga City, Camarines Sur">
    </div>
</div>
<div class="mb-1">
    <label class="formal-label">Payment Method</label>
    <select name="payment_method" id="paymentMethodSelect" class="form-select formal-input">
        <option value="cash">Cash</option>
        <option value="gcash">GCash</option>
        <option value="bank_transfer">Bank Transfer</option>
        <option value="cheque">Cheque</option>
    </select>
</div>
<p class="helper-text mb-3" id="paymentNote"></p>

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
    setTimeout(function() {
        ['flashError', 'flashSuccess'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }
        });
    }, 5000);
</script>
<script src="<?= base_url('public/js/admin/operations/sales/sales_orders.js') ?>"></script>
<?= view('partials/admin/footer') ?>