<?= view('partials/staff/head') ?>
<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Goods Receipt</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Goods Receipt Recording (GRR)</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Scan delivery items · Verify against PO · Flag discrepancies</p>
            </div>

            <div class="custom-table-container border-0 shadow-sm" style="border-radius:20px; padding:25px;">
                <h6 class="fw-bold mb-3">Pending Inbound Shipments</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th class="ps-4">PO Number</th><th>Supplier</th><th>Expected Delivery</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if(empty($deliveries)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">No pending deliveries found for inspection.</td></tr>
                            <?php endif; ?>
                            <?php foreach($deliveries as $d): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= $d['po_number'] ?></td>
                                <td><?= $d['supplier'] ?></td>
                                <td><?= date('M d, Y', strtotime($d['expected_date'])) ?></td>
                                <td class="text-center"><button class="btn btn-sm btn-dark rounded-pill px-4">Begin Inspection</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/staff/footer') ?>