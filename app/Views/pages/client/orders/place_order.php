<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Place New Sales Order</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-file-invoice me-2"></i>Review Your Order</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Confirm items and fulfillment details before submitting — final pricing is verified upon submission</p>
            </div>

            <?php if(empty($cart_items)): ?>
                <div class="custom-table-container text-center py-5">
                    <p class="text-muted mb-3">Your cart is empty.</p>
                    <a href="<?= base_url('client/orders/browse') ?>" class="btn btn-maroon rounded-pill px-4">Browse Products</a>
                </div>
            <?php else: ?>
            <form action="<?= base_url('client/orders/save-order') ?>" method="POST" id="orderForm">
                <div class="custom-table-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="font-size:13px;">Order Items</h6>
                        <a href="<?= base_url('client/orders/browse') ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                            <i class="fas fa-plus me-1"></i> Add More Items
                        </a>
                    </div>
                    <table class="table table-sm align-middle" id="orderItemsTable">
                        <thead class="table-dark"><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach($cart_items as $item): ?>
                            <tr data-price="<?= $item['sell_price'] ?>">
                                <td><?= esc($item['name']) ?><input type="hidden" name="product_ids[]" value="<?= $item['product_id'] ?>"></td>
                                <td class="text-center"><input type="number" name="qtys[]" class="form-control form-control-sm qty-input text-center" value="<?= $item['qty'] ?>" min="1" max="<?= $item['total_stock'] ?>" style="width:80px; margin:auto;"></td>
                                <td class="text-end">₱<?= number_format($item['sell_price'], 2) ?></td>
                                <td class="text-end fw-bold row-subtotal">₱<?= number_format($item['sell_price'] * $item['qty'], 2) ?></td>
                                <td class="text-center"><a href="<?= base_url('client/orders/remove-from-cart/'.$item['product_id']) ?>" class="text-danger"><i class="fas fa-times-circle"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="text-end mt-3">
                        <p class="mb-1">Estimated Total: <span class="fw-bold text-maroon fs-6" id="estimatedTotal">₱0.00</span></p>
                        <small class="text-muted">Final amount (with VAT breakdown) is confirmed once your order is submitted.</small>
                    </div>
                </div>

                <div class="custom-table-container mb-4">
                    <h6 class="fw-bold mb-3" style="font-size:13px;">Fulfillment Method</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="d-flex align-items-center gap-2 p-3 border rounded-3" style="cursor:pointer;">
                                <input type="radio" name="fulfillment_type" value="delivery" id="fulfillDelivery" checked>
                                <span><i class="fas fa-truck me-2 text-primary"></i><strong>Delivery</strong><br><small class="text-muted">Deliver to your address</small></span>
                            </label>
                        </div>
                        <div class="col-6">
                            <label class="d-flex align-items-center gap-2 p-3 border rounded-3" style="cursor:pointer;">
                                <input type="radio" name="fulfillment_type" value="pickup" id="fulfillPickup">
                                <span><i class="fas fa-store me-2 text-success"></i><strong>Pick Up</strong><br><small class="text-muted">Claim at Robin Rose Trading</small></span>
                            </label>
                        </div>
                    </div>

                    <div class="row g-3" id="deliveryAddressGroup">
                        <div class="col-12">
                            <label class="formal-label">Delivery Address *</label>
                            <textarea name="delivery_address" class="formal-input" rows="2" id="deliveryAddressInput" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="custom-table-container mb-4">
                    <h6 class="fw-bold mb-3" style="font-size:13px;">Payment Method</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="formal-label">Payment Method *</label>
                            <select name="payment_method" class="form-select formal-input" id="paymentMethodSelect" required>
                                <option value="check">Check</option>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                            </select>
                        </div>
                        <div class="col-6" id="chequeNote" style="display:none;">
                            <div class="p-2 rounded-3 bg-light border" style="font-size: 10px; color: #555;">
                                <i class="fas fa-info-circle me-1"></i>
                                Cheque payments are subject to bank clearance before the order is processed. This is not a credit facility — full payment is required upon clearance.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="custom-table-container mb-4">
                    <h6 class="fw-bold mb-3" style="font-size:13px;">Order Notes (optional)</h6>
                    <textarea name="order_notes" class="formal-input" rows="2" placeholder="e.g. Preferred delivery time, special handling instructions"></textarea>
                </div>

                <button type="submit" class="btn btn-maroon w-100 py-3 fw-bold rounded-3 shadow">✓ SUBMIT ORDER</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="<?= base_url('public/js/client/place_order.js') ?>"></script>
<?= view('partials/client/footer') ?>