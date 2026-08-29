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
                <h5 class="fw-bold mb-0">Payment</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-credit-card me-2"></i>Payment Portal</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Settle outstanding balance · Upload proof of payment</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="custom-table-container p-4 h-100 border-0 shadow-sm">
                        <h6 class="fw-bold mb-3"><i class="fas fa-file-invoice me-2"></i> Outstanding Invoices</h6>
                        <table class="table table-sm" style="font-size:10px">
                            <thead><tr class="table-dark"><th>Invoice #</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($unpaid_invoices as $i): ?>
                                <tr>
                                    <td>SI-<?= str_pad($i['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td class="fw-bold">₱<?= number_format($i['total'], 2) ?></td>
                                    <td><span class="badge bg-soft-maroon text-maroon"><?= strtoupper($i['payment_status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="p-3 bg-soft-maroon rounded-4 mt-4 d-flex justify-content-between">
                            <span class="fw-bold">Total Outstanding:</span>
                            <h5 class="fw-bold text-maroon mb-0">₱<?= number_format($total_outstanding, 2) ?></h5>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="custom-table-container p-4 border-0 shadow-sm">
                        <h6 class="fw-bold mb-4"><i class="fas fa-money-bill-wave me-2"></i> Make a Payment</h6>
                        <form action="<?= base_url('client/account/process-payment') ?>" method="POST" enctype="multipart/form-data">
                            <div class="mb-3"><label class="formal-label">Invoice to Pay</label>
                                <select name="order_id" class="form-select formal-input">
                                    <?php foreach($unpaid_invoices as $i): ?><option value="<?= $i['order_id'] ?>">SI-<?= str_pad($i['order_id'], 4, '0', STR_PAD_LEFT) ?> - ₱<?= number_format($i['total'], 2) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3"><label class="formal-label">Payment Method</label>
                                <select name="method" class="form-select formal-input"><option>Cash</option><option>GCash</option><option>Bank Transfer</option></select>
                            </div>
                            <div class="mb-3"><label class="formal-label">Amount (₱)</label><input type="number" name="amount" class="formal-input" required></div>
                            <div class="mb-4"><label class="formal-label">Upload Proof of Payment</label><input type="file" name="proof" class="form-control formal-input"></div>
                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-pill">Submit Payment Information</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>