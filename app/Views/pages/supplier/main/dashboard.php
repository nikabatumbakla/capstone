<?= view('partials/client/head') ?> <!-- Reuse head for CSS consistency -->
<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1">Welcome, <?= $fullname ?>!</h6>
                <p class="mb-0 opacity-75 small">Supplier Dashboard — Robin Rose Trading Partner</p>
            </div>

            <!-- KPI Tiles (Figma Style) -->
            <div class="row g-3 mb-4 text-center">
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4">
                        <small class="info-label">OPEN POs</small>
                        <h2 class="fw-bold mb-0">2</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4">
                        <small class="info-label text-success">ON-TIME RATE</small>
                        <h2 class="fw-bold mb-0 text-success"><?= $scorecard->on_time_rate ?? 96 ?>%</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4">
                        <small class="info-label">ACCURACY RATE</small>
                        <h2 class="fw-bold mb-0 text-primary"><?= $scorecard->accuracy_rate ?? 92 ?>%</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4">
                        <small class="info-label">TOTAL ORDERS (YTD)</small>
                        <h2 class="fw-bold mb-0"><?= $scorecard->total_orders ?? 28 ?></h2>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Open Purchase Orders -->
                <div class="col-lg-7">
                    <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-3"><i class="fas fa-file-invoice me-2"></i> Open Purchase Orders</h6>
                        
                        <div class="alert alert-warning p-2 small mb-3 border-0 rounded-3">
                            <i class="fas fa-exclamation-triangle me-2"></i> PO-2026-041 requires your acknowledgement
                        </div>

                        <table class="table table-sm align-middle" style="font-size:10px">
                            <thead class="table-dark">
                                <tr><th>PO #</th><th>Order Items</th><th>Amount</th><th>Due Date</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($open_pos as $po): ?>
                                <tr>
                                    <td class="fw-bold"><?= $po['po_number'] ?></td>
                                    <td><?= $po['items'] ?> types</td>
                                    <td class="fw-bold">₱ <?= number_format($po['total_amount'], 2) ?></td>
                                    <td><?= date('M d', strtotime($po['expected_date'])) ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?= ($po['status'] == 'sent') ? 'warning text-dark' : 'success' ?> px-3">
                                            <?= ($po['status'] == 'sent') ? 'NEED ACK' : 'RECEIVED' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Performance Snapshot -->
                <div class="col-lg-5">
                    <div class="custom-table-container p-4 border-0 shadow-sm h-100" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-4">📊 Performance Snapshot</h6>
                        <div class="d-flex align-items-center justify-content-center py-4">
                            <!-- Circular Progress Simulation -->
                            <div class="rounded-circle border border-5 border-success d-flex align-items-center justify-content-center" style="width:100px; height:100px;">
                                <h3 class="mb-0 fw-bold text-success">89%</h3>
                            </div>
                            <div class="ms-4">
                                <div class="mb-2"><small class="text-muted d-block" style="font-size:9px">ON-TIME DELIVERY</small>
                                    <div class="progress" style="height:5px; width:150px;"><div class="progress-bar bg-success" style="width: 96%"></div></div>
                                </div>
                                <div><small class="text-muted d-block" style="font-size:9px">ORDER ACCURACY</small>
                                    <div class="progress" style="height:5px; width:150px;"><div class="progress-bar bg-primary" style="width: 92%"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>