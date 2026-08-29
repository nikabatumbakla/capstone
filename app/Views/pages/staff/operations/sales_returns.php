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
                <h5 class="fw-bold mb-0">Sales Returns</h5>

            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i>Sales Returns</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Approving a return automatically restores inventory and updates the sales order.</p>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-4">
    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold"
            data-bs-toggle="offcanvas"
            data-bs-target="#returnDrawer">
        + Process Return
    </button>
</div>

            <div class="custom-table-container border-0 shadow-sm" style="border-radius:20px; padding:25px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th class="ps-4">Timestamp</th><th>Reference SO</th><th>Institution</th><th>Return Reason</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($returns as $r): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                <td class="fw-bold"><?= $r['order_number'] ?></td>
                                <td><?= $r['organization'] ?></td>
                                <td><span class="text-muted"><?= $r['reason'] ?></span></td>
                                <td><span class="badge bg-success px-3">RESTORED</span></td>
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