<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
         
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Goods Receipt (GRR)</h5>
            </div>

            <div class="procurement-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i> Goods Receipt Recording</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Verify incoming stock against Purchase Orders to update inventory</p>
            </div>

            <div class="custom-table-container">
                <h6 class="fw-bold mb-4" style="font-size: 14px;"><i class="fas fa-clipboard-check me-2 text-maroon"></i>Awaiting Inspection</h6>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 11px;">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">PO #</th>
                                <th>Supplier</th>
                                <th>Expected Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($pending_receipts)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No pending deliveries to record.</td></tr>
                            <?php endif; ?>
                            <?php foreach($pending_receipts as $po): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= $po['po_number'] ?></td>
                                <td><?= $po['supplier_name'] ?></td>
                                <td><?= date('M d, Y', strtotime($po['expected_date'])) ?></td>
                                <td><span class="badge bg-info">In-Transit</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-dark rounded-pill px-4 btn-record-grr" data-id="<?= $po['po_id'] ?>">Process Items</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRR FORM DRAWER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="grrDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Record Incoming Goods</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="grrContent"></div>
</div>

<script src="<?= base_url('public/js/admin/procurement_grr.js') ?>"></script>
<?= view('partials/admin/footer') ?>