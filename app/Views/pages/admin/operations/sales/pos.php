<?= $this->extend('layouts/pos_layout') ?>=

<?= $this->section('content') ?>

<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="container-fluid p-4 pos-page">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <h5 class="fw-bold mb-0"><i class="fas fa-cash-register me-2 text-maroon"></i>Point of Sale Terminal</h5>
            <span class="badge rounded-pill ms-3" style="background:#eafaf1; color:#1e8449; font-size:9.5px; padding:5px 10px;">
                <span class="live-dot"></span>LIVE
            </span>
        </div>
        <div class="text-end">
            <p class="mb-0 fw-bold" style="font-size:11px;"><?= esc($fullname) ?></p>
            <small class="text-muted" id="liveClock" style="font-size:10px;"></small>
            <br><small class="text-muted" id="lastSynced" style="font-size:9px;">Syncing…</small>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT: PRODUCT BROWSER -->
        <div class="col-lg-5">
    <div class="custom-table-container border-0 shadow-sm" style="padding:16px;">
        <div class="input-group mb-3 border rounded-pill overflow-hidden bg-light">
            <input type="text" id="posSearch" class="form-control border-0 ps-4 bg-transparent" placeholder="Search product name or scan barcode..." style="height:40px; font-size:11px;">
            <i class="fas fa-search text-muted mx-2 align-self-center"></i>
        </div>

        <div class="mb-3">
            <select id="categorySelect" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= esc($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="productGrid" class="row g-2" style="max-height: 400px; overflow-y:auto;"></div>
<div id="productGridEmpty" class="empty-state" style="display:none;">
    <i class="fas fa-box-open mb-2" style="font-size:20px;"></i>
    <div>No matching products.</div>
</div>
    </div>
</div>

        <!-- MIDDLE: CART -->
        <div class="col-lg-4">
            <div class="custom-table-container border-0 shadow-sm" style="padding:16px;">
                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center" style="font-size:12px;">
                    <span><i class="fas fa-shopping-basket me-2 text-maroon"></i>Current Sale</span>
                    <span class="badge bg-dark" id="cartCountBadge" style="font-size:9px;">0 item(s)</span>
                </h6>
                <div style="max-height: 320px; overflow-y:auto;">
                    <table class="table table-sm align-middle" style="font-size:10.5px">
                        <thead class="table-dark"><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th><th></th></tr></thead>
                        <tbody id="cartTableBody"></tbody>
                    </table>
                    <div id="cartEmptyState" class="empty-state" style="display:none;">
                        <i class="fas fa-basket-shopping mb-2" style="font-size:20px;"></i>
                        <div>Cart is empty — tap a product to add it.</div>
                    </div>
                </div>

                <hr>
                <div class="mb-2">
                    <label class="info-label">Walk-in Customer Name (optional)</label>
                    <input type="text" id="customerName" class="form-control form-control-sm" placeholder="e.g. Juan Dela Cruz">
                </div>
                <div class="mb-2">
                    <label class="info-label">Discount</label>
                    <select id="discountType" class="form-select form-select-sm">
                        <option value="none">None</option>
                        <option value="pwd">PWD (20% + VAT Exempt)</option>
                        <option value="senior">Senior Citizen (20% + VAT Exempt)</option>
                    </select>
                </div>
                <div class="row g-2 mb-2" id="discountIdRow" style="display:none;">
                    <div class="col-6"><input type="text" id="discountIdNumber" class="form-control form-control-sm" placeholder="ID Number"></div>
                    <div class="col-6"><input type="text" id="discountHolderName" class="form-control form-control-sm" placeholder="Name on ID"></div>
                </div>
            </div>
        </div>

        <!-- RIGHT: TOTALS, PAYMENT, DAILY SUMMARY -->
        <div class="col-lg-3">
            <div class="custom-table-container border-0 shadow-sm mb-3" style="padding:16px;">
                <div class="mb-2 d-flex justify-content-between" style="font-size:11px;"><span>Gross Amount</span><span id="calcGross">₱0.00</span></div>
                <div class="mb-2 d-flex justify-content-between text-danger" style="font-size:11px;"><span>Discount</span><span id="calcDiscount">-₱0.00</span></div>
                <div class="mb-2 d-flex justify-content-between" style="font-size:11px;"><span>VAT-Exclusive</span><span id="calcSubtotal">₱0.00</span></div>
                <div class="mb-2 d-flex justify-content-between" style="font-size:11px;"><span>VAT (<?= (float) $vat_rate ?>%)</span><span id="calcVat">₱0.00</span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">TOTAL DUE</span>
                    <span class="fw-bold text-maroon fs-5" id="calcTotal">₱0.00</span>
                </div>

                <div class="row g-2 mt-3">
                    <div class="col-6"><input type="radio" class="btn-check" name="payType" id="pG" value="gcash"><label class="btn btn-outline-primary w-100 py-2 fw-bold" style="font-size:11px" for="pG"><i class="fas fa-mobile-screen-button me-1"></i>GCash</label></div>
                    <div class="col-6"><input type="radio" class="btn-check" name="payType" id="pC" value="cash" checked><label class="btn btn-outline-dark w-100 py-2 fw-bold" style="font-size:11px" for="pC"><i class="fas fa-money-bill-wave me-1"></i>Cash</label></div>
                </div>

                <div id="cashFields" class="row g-2 mt-2">
                    <div class="col-6"><label class="info-label">Tendered</label><input type="number" id="tendered" class="form-control formal-input fw-bold" min="0" step="0.01"></div>
                    <div class="col-6"><label class="info-label">Change</label><input type="text" id="change" class="form-control formal-input bg-light border-0 fw-bold text-primary" readonly value="₱0.00"></div>
                </div>

                <div id="gcashFields" class="mt-2" style="display:none;">
                    <label class="info-label">GCash Reference Number *</label>
                    <input type="text" id="gcashRef" class="form-control formal-input" placeholder="e.g. 0123456789012">
                    <p class="mb-0 mt-1" style="color:#888;">Enter the reference number once the customer shows their completed payment.</p>
                </div>

                <div id="posAlert" class="alert py-2 px-2 mt-2 mb-0" style="font-size:10.5px; display:none;"></div>

                <button class="btn btn-maroon w-100 py-3 fw-bold rounded-3 shadow mt-3" id="btnComplete">
                    <i class="fas fa-check-circle me-2"></i>COMPLETE TRANSACTION
                </button>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="kpi-card shadow-sm" style="background:linear-gradient(135deg,#f1c40f,#d4a90a);">
                        <i class="fas fa-receipt kpi-icon"></i>
                        <small class="fw-bold d-block">Txns Today</small>
                        <h5 class="fw-bold mb-0" id="kpiTxns"><?= (int) $total_txns ?></h5>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card shadow-sm" style="background:linear-gradient(135deg,#2ecc71,#25a35a);">
                        <i class="fas fa-sack-dollar kpi-icon"></i>
                        <small class="fw-bold d-block">Gross Sales</small>
                        <h5 class="fw-bold mb-0" id="kpiGross">₱<?= number_format((float) $gross_sales, 2) ?></h5>
                    </div>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="kpi-card shadow-sm" style="background:linear-gradient(135deg,#34495e,#2c3e50);">
                        <i class="fas fa-money-bill kpi-icon"></i>
                        <small class="fw-bold d-block">Cash</small>
                        <h6 class="fw-bold mb-0" id="kpiCash">₱<?= number_format((float) $cash_sales, 2) ?></h6>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card shadow-sm" style="background:linear-gradient(135deg,#2980b9,#1f618d);">
                        <i class="fas fa-mobile-screen-button kpi-icon"></i>
                        <small class="fw-bold d-block">GCash</small>
                        <h6 class="fw-bold mb-0" id="kpiGcash">₱<?= number_format((float) $gcash_sales, 2) ?></h6>
                    </div>
                </div>
            </div>

            <div class="custom-table-container border-0 shadow-sm" style="padding:16px; max-height:260px; overflow-y:auto;">
                <h6 class="fw-bold mb-2" style="font-size:11px;"><i class="fas fa-history me-1 text-muted"></i>Today's Transactions</h6>
                <div id="txnHistoryList">
                    <?php if (empty($history)): ?>
                        <div class="empty-state" id="txnEmptyState"><i class="fas fa-clipboard-list mb-2" style="font-size:18px;"></i><div>No transactions yet today.</div></div>
                    <?php else: foreach ($history as $h): ?>
                        <div class="d-flex justify-content-between border-bottom py-1 txn-row" style="font-size:10px;" data-txn="<?= (int) $h['txn_id'] ?>">
                            <span><?= esc($h['or_number']) ?> (<?= (int) $h['item_count'] ?>)</span>
                            <span class="fw-bold">₱<?= number_format((float) $h['total'], 2) ?></span>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  const ALL_PRODUCTS = <?= json_encode($products) ?>;
  const STORE_INFO = <?= json_encode($store_info) ?>;
  const VAT_RATE = <?= (float) $vat_rate ?>;
</script>
<script src="<?= base_url('public/js/admin/operations/sales/sales_pos.js') ?>"></script>
<?= $this->endSection() ?>