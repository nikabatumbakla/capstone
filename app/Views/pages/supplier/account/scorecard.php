<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">My Scorecard</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>My Performance Scorecard</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Delivery accuracy, order fulfillment, and lead time tracking</p>
            </div>

            <!-- Summary KPI Grid -->
            <div class="row g-3 mb-4 text-center">
                <div class="col-md-3"><div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4"><small class="info-label text-success">OVERALL SCORE</small><h2 class="fw-bold text-success mb-0"><?= $scorecard->on_time_rate ?? 0 ?>%</h2></div></div>
                <div class="col-md-3"><div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4"><small class="info-label">ON-TIME DELIVERIES</small><h2 class="fw-bold mb-0">96%</h2></div></div>
                <div class="col-md-3"><div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4"><small class="info-label">ORDER ACCURACY</small><h2 class="fw-bold text-primary mb-0">92%</h2></div></div>
                <div class="col-md-3"><div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4"><small class="info-label">AVG LEAD TIME</small><h2 class="fw-bold mb-0">5 Days</h2></div></div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="custom-table-container p-4 border-0 shadow-sm h-100" style="border-radius:20px;">
                        <h6 class="fw-bold mb-4">Performance Breakdown</h6>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1"><small class="fw-bold">On-Time Delivery Rate</small><small class="text-success fw-bold">96%</small></div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-success" style="width: 96%"></div></div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1"><small class="fw-bold">Order Accuracy Rate</small><small class="text-primary fw-bold">92%</small></div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-primary" style="width: 92%"></div></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius:20px;">
                        <h6 class="fw-bold mb-3">Recent Order History</h6>
                        <table class="table table-sm" style="font-size:9px">
                            <thead class="table-dark"><tr><th>PO #</th><th>On Time?</th><th>Accurate?</th><th>Score</th></tr></thead>
                            <tbody>
                                <tr><td>PO-041</td><td><span class="text-warning">Pending</span></td><td><i class="fas fa-check text-success"></i></td><td>—</td></tr>
                                <tr><td>PO-038</td><td><i class="fas fa-check text-success"></i></td><td><i class="fas fa-check text-success"></i></td><td>100%</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>