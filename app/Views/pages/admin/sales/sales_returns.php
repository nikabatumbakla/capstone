<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm" style="background: linear-gradient(90deg, #1a0505, #4a0000);">
                <h5 class="fw-bold mb-1"><i class="fas fa-undo me-2"></i> Sales Returns & Reversals</h5>
                <p class="small mb-0 opacity-75">Process returned medical items and restore inventory levels</p>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0">Return Logs</h6>
                    <button class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#returnDrawer">+ Process New Return</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" style="font-size:11px">
                        <thead class="table-dark">
                            <tr><th>Date</th><th>Order Ref</th><th>Handled By</th><th>Reason</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($returns as $r): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                <td class="fw-bold"><?= $r['order_number'] ?></td>
                                <td><?= $r['staff'] ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $r['reason'] ?></span></td>
                                <td><span class="badge bg-success">RESTORED</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: PROCESS RETURN (1 FORM 1 PROCESS) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="returnDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">Return Intelligence Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/sales/sales-returns/process') ?>" method="POST">
            <div class="mb-4">
                <label class="formal-label">Order / Transaction Number</label>
                <input type="text" name="ref_no" class="formal-input" placeholder="Enter SO- or TXN- number" required>
            </div>
            <div class="mb-4">
                <label class="formal-label">Reason for Return</label>
                <select name="reason" class="form-select formal-input">
                    <option value="Damaged Delivery">Damaged Delivery</option>
                    <option value="Wrong Item Sent">Wrong Item Sent</option>
                    <option value="Customer Cancellation">Customer Cancellation</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="formal-label">Return Notes</label>
                <textarea name="notes" class="formal-input" rows="4"></textarea>
            </div>
            <button type="submit" class="btn btn-maroon w-100 py-3 fw-bold">✓ PROCESS RETURN & RESTORE STOCK</button>
        </form>
    </div>
</div>

<?= view('partials/admin/footer') ?>