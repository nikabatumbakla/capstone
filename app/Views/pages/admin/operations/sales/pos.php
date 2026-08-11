<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="row g-4">
                
                <!-- LEFT: TRANSACTION CART -->
                <div class="col-lg-7">
                    <div class="custom-table-container border-0 shadow-sm" style="padding:20px;">
                        <h6 class="fw-bold mb-3" style="font-size:13px"><i class="fas fa-shopping-basket me-2 text-maroon"></i>New POS Transaction</h6>
                        
                        <!-- Intelligent Search -->
                        <div class="input-group mb-3 border rounded-pill overflow-hidden bg-light shadow-none">
                            <input type="text" id="posSearch" class="form-control border-0 ps-4 bg-transparent" placeholder="Type product name or SKU..." style="height:40px; font-size:11px;">
                            <button class="btn btn-dark px-4 fw-bold" style="font-size:10px" id="btnSearch">+ ADD PRODUCT</button>
                        </div>

                        <div class="table-responsive" style="min-height: 250px; max-height:350px; overflow-y:auto">
                            <table class="table table-hover align-middle" style="font-size:10.5px">
                                <thead class="table-dark">
                                    <tr><th class="ps-3">Product</th><th class="text-center">Qty</th><th>Price</th><th>VAT</th><th>Subtotal</th><th></th></tr>
                                </thead>
                                <tbody id="cartTableBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Formal Calculation Block -->
                        <div class="p-3 mt-3 rounded-4" style="background:#f8f9fa; border: 1px dashed #ccc; font-size:11px">
                            <div class="d-flex justify-content-between mb-1"><span>Subtotal:</span><span id="subtotal">₱ 0.00</span></div>
                            <div class="d-flex justify-content-between mb-1"><span>VAT (12%):</span><span id="vat">₱ 0.00</span></div>
                            <div class="d-flex justify-content-between mb-2"><span>Discount:</span><span>₱ 0.00</span></div>
                            <div class="d-flex justify-content-between border-top pt-2"><h5 class="fw-bold mb-0">GRAND TOTAL:</h5><h5 class="fw-bold text-maroon mb-0" id="grandTotal">₱ 0.00</h5></div>
                        </div>

                        <div class="row g-2 mt-3">
                            <div class="col-6"><input type="radio" class="btn-check" name="payType" id="pG" value="gcash"><label class="btn btn-outline-primary w-100 py-2 fw-bold" style="font-size:11px" for="pG">GCash</label></div>
                            <div class="col-6"><input type="radio" class="btn-check" name="payType" id="pC" value="cash" checked><label class="btn btn-outline-dark w-100 py-2 fw-bold" style="font-size:11px" for="pC">Cash</label></div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-6"><label class="info-label">Cash Tendered</label><input type="number" id="tendered" class="form-control formal-input fw-bold fs-6"></div>
                            <div class="col-6"><label class="info-label">Change Due</label><input type="text" id="change" class="form-control formal-input bg-light border-0 fw-bold fs-6 text-primary" readonly value="₱ 0.00"></div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-maroon flex-grow-1 py-3 fw-bold rounded-3 shadow" id="btnComplete">✓ COMPLETE TRANSACTION</button>
                            <button class="btn btn-outline-dark px-4 py-3 rounded-3" onclick="window.print()"><i class="fas fa-print"></i></button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: SUMMARY & HISTORY -->
                <div class="col-lg-5">
                    <!-- Daily Summary Tiles (Figma Match) -->
                    <div class="row g-3 mb-4">
                        <div class="col-6"><div class="p-3 rounded-4 shadow-sm text-white" style="background:#f1c40f">
                            <small class="fw-bold opacity-75 d-block">TOTAL TXNS</small>
                            <h2 class="fw-bold mb-0"><?= $total_txns ?></h2>
                        </div></div>
                        <div class="col-6"><div class="p-3 rounded-4 shadow-sm text-white" style="background:#2ecc71">
                            <small class="fw-bold opacity-75 d-block">GROSS SALES</small>
                            <h4 class="fw-bold mb-0">₱<?= number_format($gross_sales, 2) ?></h4>
                        </div></div>
                        <div class="col-6"><div class="p-3 rounded-4 shadow-sm text-white" style="background:#2980b9">
                            <small class="fw-bold opacity-75 d-block">CASH SALES</small>
                            <h5 class="fw-bold mb-0">₱<?= number_format($cash_sales, 2) ?></h5>
                        </div></div>
                        <div class="col-6"><div class="p-3 rounded-4 shadow-sm text-white" style="background:#e74c3c">
                            <small class="fw-bold opacity-75 d-block">GCASH SALES</small>
                            <h5 class="fw-bold mb-0">₱<?= number_format($gcash_sales, 2) ?></h5>
                        </div></div>
                    </div>

                    <!-- History -->
                    <div class="custom-table-container border-0 shadow-sm" style="padding:20px">
                        <h6 class="fw-bold mb-3" style="font-size:12px"><i class="fas fa-history me-2 text-muted"></i>Transaction History (Today)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" style="font-size:10px">
                                <thead class="table-dark">
                                    <tr><th>OR #</th><th>Items</th><th>Total</th><th>Payment</th><th class="text-end">Time</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($history as $h): ?>
                                    <tr>
                                        <td class="fw-bold"><?= $h['or_number'] ?></td>
                                        <td><?= $h['item_count'] ?></td>
                                        <td class="fw-bold text-maroon">₱<?= number_format($h['total'], 2) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= strtoupper($h['payment_method']) ?></span></td>
                                        <td class="text-end text-muted"><?= date('H:i', strtotime($h['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('public/js/admin/operations/sales/sales_pos.js') ?>"></script>
<?= view('partials/admin/footer') ?>