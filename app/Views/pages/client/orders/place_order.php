<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Place Order</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i>Place a Sales Order</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Select products · Specify quantities · Submit for processing</p>
            </div>

            <div class="custom-table-container p-5" style="border-radius: 20px;">
                <h5 class="fw-bold mb-4" style="color: #0d2e4f;">NEW SALES ORDER FORM</h5>
                
                <form action="<?= base_url('client/orders/save-order') ?>" method="POST">
                    <div class="row g-4 border-bottom pb-4 mb-4">
                        <div class="col-md-6">
                            <label class="formal-label">Purchasing Entity</label>
                            <input type="text" class="formal-input read-only-input" value="<?= $fullname ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="formal-label">Billing Date (Auto-filled)</label>
                            <input type="text" class="formal-input read-only-input" value="<?= date('M d, Y') ?>" readonly>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Order Specification</h6>
                    <div id="item-rows">
                        <div class="row g-2 mb-2 item-row">
                            <div class="col-md-7">
                                <label class="info-label">Product & Current Price</label>
                                <select name="items[]" class="form-select formal-input product-select" required>
                                    <option value="" disabled selected>Select item..</option>
                                    <?php foreach($products as $p): ?>
                                        <option value="<?= $p['batch_id'] ?>" data-price="<?= $p['sell_price'] ?>"><?= $p['name'] ?> (₱<?= number_format($p['sell_price'], 2) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="info-label">Quantity</label>
                                <input type="number" name="qtys[]" class="formal-input qty-input" value="1" min="1">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger border-0 remove-row"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-xs btn-outline-dark mt-2" id="btnAddRow">+ Add Another Product</button>

                    <div class="mt-5 pt-4 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-0 text-muted small">Estimated Grand Total</p>
                                <h2 class="fw-bold mb-0 text-maroon" id="grandTotalDisplay">₱ 0.00</h2>
                                <input type="hidden" name="grand_total_hidden" id="grandTotalHidden">
                            </div>
                            <button type="submit" class="btn btn-dark px-5 py-3 fw-bold rounded-3 shadow">SUBMIT ORDER TO SYSTEM</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('public/js/client/orders.js') ?>"></script>
<?= view('partials/client/footer') ?>