<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">My Orders</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-list-ul me-2"></i>My Orders</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Monitor distribution status and download invoices</p>
            </div>

            <div class="custom-table-container">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th class="ps-4">Order #</th><th>Status</th><th>Total Bill</th><th>Payment</th><th>Created At</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= $o['order_number'] ?></td>
                                <td><span class="badge rounded-pill bg-light text-dark border px-3"><?= strtoupper($o['status']) ?></span></td>
                                <td class="fw-bold">₱ <?= number_format($o['total'], 2) ?></td>
                                <td><span class="text-<?= $o['payment_status']=='paid'?'success':'danger' ?> fw-bold"><?= strtoupper($o['payment_status']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                <td class="text-center"><button class="btn btn-xs btn-dark rounded-pill px-3">View Details</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>