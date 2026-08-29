<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Add Adjusment</h5>

            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i>Adjustment Logs</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">View · Update Stock · Stock Adjustment · Batch Tracking</p>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-4">
    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold"
            data-bs-toggle="offcanvas"
            data-bs-target="#newAdjustDrawer">
        + New Adjustment
    </button>
</div>


            <div class="custom-table-container border-0 shadow-sm" style="border-radius:25px; padding:30px;">
                <h6 class="fw-bold mb-4" style="font-size: 14px;"><i class="fas fa-history me-2 text-maroon"></i> My Adjustment History</h6>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:11px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Log #</th>
                                <th>Product</th>
                                <th class="text-center">Before</th>
                                <th class="text-center">After</th>
                                <th class="text-center">Diff</th>
                                <th>Reason</th>
                                <th class="text-end pe-4">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No history found in database.</td></tr>
                            <?php endif; ?>

                            <?php foreach($logs as $l): 
                                $diff = $l['qty_after'] - $l['qty_before'];
                                $diffClass = ($diff > 0) ? 'text-success' : 'text-danger';
                            ?>
                            <tr>
                                <td class="ps-4 text-muted">ADJ-0<?= $l['log_id'] ?></td>
                                <td class="fw-bold text-dark"><?= $l['product_name'] ?></td>
                                <td class="text-center text-muted"><?= $l['qty_before'] ?></td>
                                <td class="text-center fw-bold"><?= $l['qty_after'] ?></td>
                                <td class="text-center fw-bold <?= $diffClass ?>"><?= ($diff > 0) ? '+'.$diff : $diff ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $l['reason'] ?></span></td>
                                <td class="text-end pe-4 text-muted"><?= date('M d, H:i', strtotime($l['adjusted_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: NEW ADJUSTMENT FORM -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="newAdjustDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Create New Stock Adjustment</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <h5 class="fw-bold mb-4" style="color: #b30000; font-size: 14px;">STOCK ADJUSTMENT FORM</h5>
        <form action="<?= base_url('staff/inventory/adjust_stock') ?>" method="POST">
            <div class="row g-3">
                <div class="col-12">
                    <label class="formal-label">Select Product & Batch *</label>
                    <select name="batch_id" id="selectProductToAdjust" class="form-select formal-input" required>
                        <option value="" disabled selected>Choose item to adjust...</option>
                        <?php foreach($available_stocks as $s): ?>
                            <option value="<?= $s['batch_id'] ?>" data-qty="<?= $s['quantity_avail'] ?>"><?= $s['name'] ?> (Batch: <?= $s['batch_number'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Current Quantity</label>
                    <input type="text" id="displayQtyBefore" class="formal-input read-only-input" readonly placeholder="0">
                    <input type="hidden" name="qty_before" id="hiddenQtyBefore">
                </div>
                <div class="col-6">
                    <label class="formal-label">New Corrected Quantity *</label>
                    <input type="number" name="qty_after" class="formal-input" required>
                </div>
                <div class="col-6">
                    <label class="formal-label">Reason *</label>
                    <select name="reason" class="form-select formal-input" required>
                        <option value="Physical Count">Physical Count</option>
                        <option value="Damage">Damaged Goods</option>
                        <option value="Expired">Expired Stock</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Adjusted By</label>
                    <input type="text" class="formal-input read-only-input" value="<?= $fullname ?>" readonly>
                </div>
                <div class="col-12">
                    <label class="formal-label">Audit Notes</label>
                    <textarea name="notes" class="formal-input" rows="4" placeholder="Describe why this correction is needed"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-save-adj w-100 py-3 mt-4">✓ Confirm & Log Adjustment</button>
        </form>
    </div>
</div>

<script src="<?= base_url('public/js/staff/inventory_logs.js') ?>"></script>
<?= view('partials/staff/footer') ?>