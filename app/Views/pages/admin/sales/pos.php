<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="row g-4">
                <!-- LEFT: Item Selection -->
                <div class="col-lg-8">
                    <div class="custom-table-container h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0"><i class="fas fa-shopping-basket me-2 text-maroon"></i>Transaction Cart</h6>
                            <div class="position-relative">
                                <input type="text" id="posSearch" class="form-control form-control-sm rounded-pill ps-4" placeholder="Scan Barcode or Type Name..." style="width:300px">
                                <i class="fas fa-search position-absolute text-muted" style="left:15px; top:10px"></i>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-hover align-middle" style="font-size:11px">
                                <thead class="table-dark">
                                    <tr><th>Product Specification</th><th>Batch</th><th>Price</th><th width="100">Qty</th><th>Total</th><th></th></tr>
                                </thead>
                                <tbody id="cartBody">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Checkout -->
                <div class="col-lg-4">
                    <div class="custom-table-container bg-dark text-white shadow-lg">
                        <h6 class="fw-bold mb-4 border-bottom border-secondary pb-2">Checkout Intelligence</h6>
                        
                        <div class="mb-4">
                            <small class="text-muted d-block">GRAND TOTAL</small>
                            <h1 class="fw-bold mb-0 text-warning" id="displayTotal">₱ 0.00</h1>
                        </div>

                        <div class="mb-3">
                            <label class="info-label text-white-50">Payment Method</label>
                            <select id="paymentMethod" class="form-select bg-transparent text-white border-secondary formal-input">
                                <option value="cash" class="text-dark">Cash Payment</option>
                                <option value="gcash" class="text-dark">GCash Digital Transfer</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="info-label text-white-50">Amount Tendered</label>
                            <input type="number" id="amountTendered" class="form-control form-control-lg fw-bold bg-transparent text-white border-secondary formal-input">
                        </div>

                        <div class="p-3 bg-secondary bg-opacity-25 rounded-4 mb-4">
                            <small class="text-muted d-block">CHANGE DUE</small>
                            <h3 class="fw-bold mb-0" id="displayChange">₱ 0.00</h3>
                        </div>

                        <button class="btn btn-maroon w-100 py-3 fw-bold rounded-3 shadow" id="btnFinalize">
                            FINALIZE TRANSACTION
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('public/js/admin/sales_pos.js') ?>"></script>
<?= view('partials/admin/footer') ?>