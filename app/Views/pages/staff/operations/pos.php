<?= view('partials/staff/head') ?>
<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="row g-4">
                <!-- Transaction Cart -->
                <div class="col-lg-8">
                    <div class="custom-table-container border-0 shadow-sm" style="border-radius:20px; padding:25px;">
                        <h6 class="fw-bold mb-4"><i class="fas fa-shopping-basket me-2 text-maroon"></i>New Walk-in Transaction</h6>
                        <div class="input-group mb-4 rounded-pill border overflow-hidden bg-light shadow-none">
                            <input type="text" id="posSearch" class="form-control border-0 ps-4 bg-transparent" placeholder="Type product name or scan barcode..." style="height:45px">
                            <button class="btn btn-dark px-4 fw-bold">ADD TO CART</button>
                        </div>
                        <div class="table-responsive" style="min-height: 400px;">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr><th class="ps-3">Product Intelligence</th><th>Qty</th><th>Price</th><th>Total</th><th class="text-end pe-3">Action</th></tr>
                                </thead>
                                <tbody id="cartBody">
                                    <tr><td colspan="5" class="text-center py-5 text-muted">Awaiting product entry to calculate distribution totals.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Checkout Intelligence -->
                <div class="col-lg-4">
                    <div class="custom-table-container bg-dark text-white p-4 h-100 shadow-lg" style="border-radius:20px;">
                        <small class="text-white-50 d-block mb-1">TOTAL AMOUNT PAYABLE</small>
                        <h1 class="fw-bold text-warning mb-4" style="font-size:42px;">₱ 0.00</h1>
                        <div class="mb-3"><label class="info-label text-white-50">Cash Tendered</label><input type="number" class="form-control form-control-lg bg-transparent border-secondary text-white fw-bold"></div>
                        <div class="p-3 bg-secondary bg-opacity-25 rounded-4 mb-4"><small class="text-white-50 d-block">CHANGE DUE</small><h4 class="mb-0 text-primary fw-bold">₱ 0.00</h4></div>
                        <button class="btn btn-maroon w-100 py-3 fw-bold rounded-3 shadow">✓ FINALIZE TRANSACTION</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/staff/footer') ?>