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
                <h5 class="fw-bold mb-0">Invoices</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-credit-card me-2"></i>My Invoices / Sales Invoice</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">View and download BIR-compliant invoices</p>
            </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Invoice #</th><th>Order #</th><th>Total</th><th>VAT (12%)</th><th>Date</th><th>Status</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($invoices as $i): ?>
                            <tr>
                                <td class="ps-4 fw-bold">SI-<?= str_pad($i['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= $i['order_number'] ?></td>
                                <td class="fw-bold">₱ <?= number_format($i['total'], 2) ?></td>
                                <td class="text-muted">₱ <?= number_format($i['total'] * 0.12, 2) ?></td>
                                <td><?= date('M d, Y', strtotime($i['created_at'])) ?></td>
                                <td><span class="badge rounded-pill <?= $i['payment_status']=='paid'?'bg-success':'bg-warning text-dark' ?> px-3"><?= strtoupper($i['payment_status']) ?></span></td>
                                <td class="text-center"><button class="btn btn-xs btn-outline-danger px-3"><i class="fas fa-file-pdf me-1"></i> PDF</button></td>
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