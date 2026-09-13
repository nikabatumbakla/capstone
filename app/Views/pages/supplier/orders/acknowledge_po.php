<?= view('partials/supplier/head') ?>

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>
        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm" style="background: linear-gradient(90deg, #27ae60, #1e8449);">
                <h5 class="fw-bold mb-1"><i class="fas fa-check-double me-2"></i> Acknowledge Purchase Order</h5>
                <p class="small mb-0 opacity-75">Confirm receipt · Set delivery date · Flag unavailable items</p>
            </div>

            <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 mb-4" onclick="history.back()"><i class="fas fa-arrow-left me-1"></i> Back to PO Inbox</button>

            <?php if(!empty($po->replacement_for_return_id)): ?>
                <div class="alert alert-info d-flex align-items-start gap-2 mb-4" style="font-size: 11px;">
                    <i class="fas fa-exchange-alt mt-1"></i>
                    <div><b>Replacement Delivery</b><br>This order replaces defective/incorrect items from Return #SRT-<?= str_pad($po->replacement_for_return_id, 4, '0', STR_PAD_LEFT) ?> — no payment is required, it's a straight exchange.</div>
                </div>
            <?php endif; ?>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius:20px;">
                <div class="row mb-4 bg-light p-3 rounded-4">
                    <div class="col-md-4"><small class="info-label">Order Reference</small><h6 class="fw-bold"><?= $po->po_number ?></h6></div>
                    <div class="col-md-4"><small class="info-label">Expected By Robin Rose</small><h6 class="fw-bold"><?= date('F d, Y', strtotime($po->expected_date)) ?></h6></div>
                    <div class="col-md-4"><small class="info-label text-end d-block">Total Value</small><h4 class="fw-bold text-maroon text-end">₱ <?= number_format($po->total_amount, 2) ?></h4></div>
                </div>

                <h6 class="fw-bold mb-3">Items — Can You Fulfill?</h6>
                <form action="<?= base_url('supplier/orders/process-acknowledge') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="po_id" value="<?= $po->po_id ?>">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" style="font-size:10px">
                            <thead class="table-dark">
                                <tr><th>Product</th><th>Qty Ordered</th><th>Confirm Qty You Can Supply *</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $i): ?>
                                <tr>
                                    <td class="fw-bold">
                                        <?= esc($i['name']) ?>
                                        <br><small class="text-muted"><?= esc($i['barcode_value'] ?: '—') ?></small>
                                        <input type="hidden" name="poi_id[]" value="<?= $i['poi_id'] ?>">
                                    </td>
                                    <td><?= $i['qty_ordered'] ?> <?= esc($i['unit']) ?></td>
                                    <td>
                                        <input type="number" name="item_qty[]" class="form-control form-control-sm border-0 bg-light rounded-pill text-center"
                                               value="<?= $i['qty_ordered'] ?>" min="0" max="<?= $i['qty_ordered'] ?>" style="width:110px" required>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="helper-text mb-0">Enter the actual quantity you can supply per item. If it's less than what was ordered, this order will still go through as acknowledged — Robin Rose Trading will see it as a partial commitment.</p>

                    <div class="row g-4 mt-2 border-top pt-4">
                        <div class="col-md-6">
                            <label class="formal-label">Confirmed Delivery Date *</label>
                            <input type="date" name="confirmed_date" class="formal-input border-success" required>
                        </div>
                        <div class="col-md-6">
                            <label class="formal-label">Notes to Robin Rose Trading</label>
                            <textarea name="notes" class="formal-input" rows="2" placeholder="Any notes or substitutions..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success px-5 py-3 fw-bold rounded-3 shadow">✓ CONFIRM ACKNOWLEDGEMENT</button>
                        <button type="button" class="btn btn-outline-secondary px-4 py-3 rounded-3" onclick="history.back()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= view('partials/supplier/footer') ?>