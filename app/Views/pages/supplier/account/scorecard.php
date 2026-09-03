<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">My Scorecard</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-chart-pie me-2"></i>Performance Scorecard</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Delivery accuracy, order fulfillment, and lead time tracking</p>
            </div>

            <?php if($scorecard->total_orders == 0): ?>
                <div class="custom-table-container text-center py-5 mb-4">
                    <i class="fas fa-chart-line fs-1 text-muted opacity-25 mb-3"></i>
                    <p class="text-muted mb-0">Your scorecard will populate once you've completed your first delivery with Robin Rose Trading.</p>
                </div>
            <?php else: ?>
            <div class="row g-3 mb-4 text-center">
                <div class="col-md-3"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">TOTAL ORDERS</small><h3 class="fw-bold mb-0"><?= $scorecard->total_orders ?></h3></div></div>
                <div class="col-md-3"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">ON-TIME DELIVERY</small><h3 class="fw-bold mb-0 text-success"><?= number_format($scorecard->on_time_rate, 1) ?>%</h3></div></div>
                <div class="col-md-3"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">ORDER ACCURACY</small><h3 class="fw-bold mb-0 text-primary"><?= number_format($scorecard->accuracy_rate, 1) ?>%</h3></div></div>
                <div class="col-md-3"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">AVG LEAD TIME</small><h3 class="fw-bold mb-0"><?= $scorecard->avg_lead_time_actual ? round($scorecard->avg_lead_time_actual) . ' Days' : 'N/A' ?></h3></div></div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-4" style="font-size:13px;">Performance Breakdown</h6>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1"><small class="fw-bold">On-Time Delivery Rate</small><small class="text-success fw-bold"><?= number_format($scorecard->on_time_rate, 1) ?>%</small></div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-success" style="width: <?= $scorecard->on_time_rate ?>%"></div></div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1"><small class="fw-bold">Order Accuracy Rate</small><small class="text-primary fw-bold"><?= number_format($scorecard->accuracy_rate, 1) ?>%</small></div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-primary" style="width: <?= $scorecard->accuracy_rate ?>%"></div></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="custom-table-container">
                        <h6 class="fw-bold mb-3" style="font-size:13px;">Recent Order History</h6>
                        <table class="table table-sm" style="font-size:10px">
                            <thead class="table-dark"><tr><th>PO #</th><th>Expected</th><th>Status</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                <?php if(empty($po_history)): ?>
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No purchase orders yet.</td></tr>
                                <?php else: foreach($po_history as $po): ?>
                                    <tr>
                                        <td class="fw-bold"><?= esc($po['po_number']) ?></td>
                                        <td><?= $po['expected_date'] ? date('M d', strtotime($po['expected_date'])) : '—' ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= strtoupper($po['status']) ?></span></td>
                                        <td class="text-end">₱<?= number_format($po['total_amount'] ?? 0, 2) ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= view('partials/supplier/footer') ?>