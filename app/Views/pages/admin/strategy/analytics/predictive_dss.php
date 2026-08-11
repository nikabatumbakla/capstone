<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content" style="background:#f4f7fa">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <!-- Header & Back Button -->
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Predictive/DSS</h5>
            </div>
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Predictive Analysis & Decision Support System (DSS)</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Linear Regression · Moving Average · ROP · EOQ — Data-Driven Decisions</p>
            </div>


            <!-- ROW 1: TOP KPIs -->
            <div class="row g-3 mb-4">
                <div class="col"><div class="stat-card shadow-sm p-3 bg-white border-0 rounded-4"><small class="info-label">TOTAL PRODUCTS</small><h3 class="fw-bold mb-0">₱ 1,234</h3><small class="text-muted">Active Products</small></div></div>
                <div class="col"><div class="stat-card shadow-sm p-3 bg-white border-0 rounded-4"><small class="info-label text-danger">LOW STOCK ALERTS</small><h3 class="fw-bold mb-0">30</h3><small class="text-muted">Products</small></div></div>
                <div class="col"><div class="stat-card shadow-sm p-3 bg-white border-0 rounded-4"><small class="info-label text-primary">PREDICTED STOCKOUTS</small><h3 class="fw-bold mb-0">16</h3><small class="text-muted">Within 30 days</small></div></div>
                <div class="col"><div class="stat-card shadow-sm p-3 bg-white border-0 rounded-4"><small class="info-label">AUTO REORDER SUGGESTIONS</small><h3 class="fw-bold mb-0">19</h3><small class="text-muted">Products</small></div></div>
                <div class="col"><div class="stat-card shadow-sm p-3 bg-white border-0 rounded-4"><small class="info-label text-success">MODEL ACCURACY</small><h3 class="fw-bold mb-0">0.87</h3><small class="text-muted">Linear Regression</small></div></div>
            </div>

            <!-- ROW 2: LINEAR REGRESSION & SUMMARY -->
            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="custom-table-container h-100 p-4 rounded-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0">Sales Forecasting (Linear Regression)</h6>
                            <div class="d-flex gap-2">
                                <select id="productSearch" class="form-select form-select-sm formal-input" style="width:200px">
                                    <?php foreach($products_list as $p): ?><option value="<?= $p['product_id'] ?>"><?= $p['name'] ?></option><?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-dark px-3" id="btnRunForecast">Run Forecast</button>
                            </div>
                        </div>
                        <canvas id="lrChart" height="150"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="custom-table-container h-100 p-4 rounded-5">
                        <h6 class="fw-bold mb-4 border-bottom pb-2">Forecast Summary</h6>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2"><span>Average Daily Sales (Past 30 Days)</span><b id="avg_sales">86.67 units</b></div>
                            <div class="d-flex justify-content-between mb-2"><span>Predicted Daily Sales (Next 15 Days)</span><b id="pred_sales">92.54 units</b></div>
                            <div class="p-3 bg-soft-maroon rounded-4 border border-danger mt-3">
                                <p class="mb-0 text-danger fw-bold">Predicted Stockout Date</p>
                                <h5 class="fw-bold text-danger mb-0">May 28, 2024 (In 27 days)</h5>
                            </div>
                        </div>
                        <button class="btn btn-outline-dark w-100 py-2 btn-view-intel" data-type="forecast">View Full Forecast Report</button>
                    </div>
                </div>
            </div>

            <!-- ROW 3: EOQ, DEMAND PATTERN, TRENDS -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="custom-table-container p-4 rounded-5 h-100">
                        <h6 class="fw-bold mb-4">Auto-Reorder Recommendation</h6>
                        <div class="mb-2 d-flex justify-content-between"><span>Trigger</span><span class="text-danger fw-bold">Stock ≤ ROP</span></div>
                        <div class="mb-2 d-flex justify-content-between"><span>Action</span><span class="fw-bold">Generate Purchase Order</span></div>
                        <div class="mb-4 d-flex justify-content-between"><span>Suggested Quantity</span><span class="text-primary fw-bold" id="eoq_val">450 units (EOQ)</span></div>
                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-outline-dark btn-sm flex-grow-1">Review PO</button>
                            <button class="btn btn-success btn-sm flex-grow-1">✓ Approve & Send</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-table-container p-4 rounded-5 h-100">
                        <h6 class="fw-bold mb-4">Demand Pattern Analysis (Seasonality)</h6>
                        <canvas id="maChart" height="180"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-table-container p-4 rounded-5 h-100">
                        <h6 class="fw-bold mb-4">Sales Trend Analytics</h6>
                        <div class="mb-3 d-flex justify-content-between"><span>Top Selling Product</span><b>Paracetamol</b></div>
                        <div class="mb-3 d-flex justify-content-between"><span>Top Client Type</span><b>Hospital</b></div>
                        <div class="mb-3 d-flex justify-content-between"><span>Top Sales</span><b>245,500.00</b></div>
                        <button class="btn btn-outline-dark w-100 mt-4 py-2">Recalculate Recommendations</button>
                    </div>
                </div>
            </div>

            <!-- ROW 4: SUPPLIERS, LOW PERFORMING, INFO, ACTIONS -->
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="custom-table-container p-4 rounded-5 h-100">
                        <h6 class="fw-bold mb-3">Performance Analytics (Suppliers)</h6>
                        <table class="table table-sm extra-small">
                            <thead class="table-light"><tr><th>Supplier</th><th>On-Time</th><th>Acc</th></tr></thead>
                            <tbody><tr><td>MedSurge</td><td>58%</td><td>96%</td></tr></tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-table-container p-4 rounded-5 h-100">
                        <h6 class="fw-bold mb-3">Low Performing Products</h6>
                        <table class="table table-sm extra-small">
                            <tbody><tr><td>Vitamin C</td><td>Slow Moving</td></tr></tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-table-container p-4 rounded-5 h-100">
                        <h6 class="fw-bold mb-3">Model Information</h6>
                        <p class="mb-1 text-muted">Model Used: <b>Linear Regression</b></p>
                        <p class="mb-1 text-muted">Training data: <b>Last 180 days</b></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-table-container p-4 rounded-5 h-100 bg-light">
                        <h6 class="fw-bold mb-3">Quick Actions</h6>
                        <button class="btn btn-link text-dark p-0 d-block small mb-2 text-decoration-none">Export DSS Report (PDF)</button>
                        <button class="btn btn-link text-dark p-0 d-block small text-decoration-none">Export Data (Excel)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- INTELLIGENCE DRAWER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="intelDrawer" style="width: 700px; border-left: 8px solid #000;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold">Decision Intelligence Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="intelContent">
        <!-- Dynamic Content: EOQ Cost Trade-Off Graph + Detailed Math -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= base_url('public/js/admin/strategy/analytics.js') ?>"></script>
<?= view('partials/admin/footer') ?>