<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">

            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Predictive / DSS</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-brain me-2"></i>Predictive Analysis & Decision Support System</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Monthly Sales Forecasting · Reorder Point (ROP) · Economic Order Quantity (EOQ)</p>
            </div>

            <div class="custom-table-container mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-chart-area me-2 text-maroon"></i>Overall Monthly Revenue (All Products)</h6>
        <span class="text-muted" style="font-size:10px;">Business-wide view — for per-product reorder planning, use the forecast tool below.</span>
    </div>
    <canvas id="overallTrendChart" height="70"></canvas>
</div>

            <!-- KPI ROW -->
            <div class="row g-4 mb-4">
                <div class="col-md">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">ACTIVE PRODUCTS</small>
                        <h3 class="fw-bold mb-0"><?= $active_count ?></h3>
                    </div>
                </div>
                <div class="col-md">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">LOW STOCK ALERTS</small>
                        <h3 class="fw-bold mb-0 text-danger"><?= $low_stock_alerts ?></h3>
                    </div>
                </div>
                <div class="col-md">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">PREDICTED STOCKOUTS</small>
                        <h3 class="fw-bold mb-0 text-warning"><?= $predicted_stockouts ?></h3>
                        <small class="text-muted">Within 30 days</small>
                    </div>
                </div>
                <div class="col-md">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">AUTO REORDERS PENDING</small>
                        <h3 class="fw-bold mb-0 text-primary"><?= $auto_reorder_suggestions ?></h3>
                    </div>
                </div>
                <div class="col-md">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">FORECASTABLE PRODUCTS</small>
                        <h3 class="fw-bold mb-0 text-success"><?= $forecastable_count ?></h3>
                        <small class="text-muted">Enough history to model</small>
                    </div>
                </div>
            </div>

            <!-- LINEAR REGRESSION: MONTHLY SALES FORECAST -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="custom-table-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0" style="font-size:14px;"><i class="fas fa-chart-line me-2 text-maroon"></i>Monthly Sales Forecast (Linear Regression)</h6>
                            <div class="d-flex gap-2 flex-wrap">
    <select id="categorySelect" class="form-select form-select-sm" style="font-size:11px; width:150px;">
        <option value="">All Categories</option>
        <?php foreach($categories_list as $c): ?><option value="<?= $c['category_id'] ?>"><?= esc($c['name']) ?></option><?php endforeach; ?>
    </select>
    <select id="productSearch" class="form-select form-select-sm" style="font-size:11px; width:170px;" disabled>
        <option value="">Select category first</option>
    </select>
    <input type="month" id="fromMonth" class="form-control form-control-sm" style="font-size:11px; width:120px;">
    <input type="month" id="toMonth" class="form-control form-control-sm" style="font-size:11px; width:120px;">
    <button class="btn btn-sm btn-maroon rounded-pill px-4" id="btnRunForecast">Run Forecast</button>
</div>
                        </div>
                        <p class="text-muted mb-3" style="font-size:10px;">Trend is modeled over the last 12 months. Reorder math (ROP/EOQ) still uses a daily usage rate for precision — shown on the right.</p>
                        <canvas id="lrChart" height="130"></canvas>
                        <div class="row g-2 mt-3" id="regressionEquationBox" style="display:none; font-size:10px;">
                            <div class="col-12 p-2 bg-light rounded-3 text-muted">
                                Model: <b>y = <span id="eqIntercept"></span> + <span id="eqSlope"></span>x</b> &nbsp;|&nbsp;
                                Trend: <b id="eqTrend"></b> &nbsp;|&nbsp; R²: <b id="eqR2"></b>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-4 border-bottom pb-2" style="font-size:13px;">Forecast Summary</h6>
                        <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Avg. Monthly Sales</span><b id="avg_monthly">—</b></div>
                        <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Forecast — Next Month</span><b id="forecast_month">—</b></div>
                        <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Avg. Daily Usage</span><b id="avg_daily">—</b></div>
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted">Model Fit (R²)</span><b id="r2_val">—</b></div>
                        <div class="p-3 rounded-4 border" id="stockoutBox" style="background:#fdf1f1; border-color:#f1c0c0 !important;">
                            <p class="mb-0 text-danger fw-bold" style="font-size:10px;">Predicted Stockout Date</p>
                            <h6 class="fw-bold text-danger mb-0" id="stockout_val">Select a product and run a forecast</h6>
                        </div>
                        <button class="btn btn-outline-dark w-100 py-2 mt-3 btn-view-intel" data-type="forecast" disabled>
                            <i class="fas fa-search-plus me-1"></i> View Full Forecast Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- ROP/EOQ, MOVING AVERAGE, TRENDS -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-boxes me-2 text-maroon"></i>Reorder Point & EOQ</h6>
                        <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Reorder Point (ROP)</span><span class="text-danger fw-bold" id="rop_val">—</span></div>
                        <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Supplier Lead Time</span><span class="fw-bold" id="lead_val">—</span></div>
                        <div class="mb-2 d-flex justify-content-between"><span class="text-muted">Safety Stock</span><span class="fw-bold" id="safety_val">—</span></div>
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted">EOQ (Suggested Order Qty)</span><span class="text-primary fw-bold" id="eoq_val">—</span></div>
                        <div id="pendingPoBox"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-wave-square me-2 text-maroon"></i>Demand Pattern (7-Day Moving Avg)</h6>
                        <p class="text-muted mb-3" style="font-size:10px;">Based on the last 30 days of daily movement for this product.</p>
                        <canvas id="maChart" height="160"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-chart-pie me-2 text-maroon"></i>Sales Trend Analytics</h6>
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted">Top Selling Product (30d)</span><b><?= esc($top_product) ?></b></div>
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted">Top Client Type (This Month)</span><b><?= esc($top_client_type) ?></b></div>
                        <div class="mb-3 d-flex justify-content-between"><span class="text-muted">Total Sales (This Month)</span><b>₱<?= number_format($top_sales_total, 2) ?></b></div>
                    </div>
                </div>
            </div>

            <!-- SUPPLIERS, LOW PERFORMING, MODEL INFO -->
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="custom-table-container h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0" style="font-size:12px;"><i class="fas fa-truck me-2 text-maroon"></i>Supplier Performance</h6>
                            <button class="btn btn-xs btn-outline-dark rounded-pill px-2 btn-view-intel" data-type="suppliers"><i class="fas fa-eye"></i></button>
                        </div>
                        <table class="table table-sm" style="font-size:10px;">
                            <thead class="table-light"><tr><th>Supplier</th><th class="text-end">On-Time</th><th class="text-end">Acc.</th></tr></thead>
                            <tbody>
                                <?php if(empty($supplier_performance)): ?>
                                    <tr><td colspan="3" class="text-muted text-center py-3">No scorecard data yet.</td></tr>
                                <?php else: foreach(array_slice($supplier_performance, 0, 3) as $sp): ?>
                                    <tr><td><?= esc($sp['name']) ?></td><td class="text-end"><?= $sp['on_time_rate'] ?>%</td><td class="text-end"><?= $sp['accuracy_rate'] ?>%</td></tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-table-container h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0" style="font-size:12px;"><i class="fas fa-battery-quarter me-2 text-maroon"></i>Low Performing Products</h6>
                            <button class="btn btn-xs btn-outline-dark rounded-pill px-2 btn-view-intel" data-type="lowperforming"><i class="fas fa-eye"></i></button>
                        </div>
                        <table class="table table-sm" style="font-size:10px;">
                            <tbody>
                                <?php if(empty($low_performing)): ?>
                                    <tr><td class="text-muted text-center py-3">No slow-moving stock detected.</td></tr>
                                <?php else: foreach(array_slice($low_performing, 0, 3) as $lp): ?>
                                    <tr><td><?= esc($lp['name']) ?></td><td class="text-end text-muted"><?= $lp['last_moved'] ? 'No movement 30+ days' : 'Never sold' ?></td></tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-3" style="font-size:12px;"><i class="fas fa-info-circle me-2 text-maroon"></i>Model Information</h6>
                        <p class="mb-2 text-muted">Trend Model: <b class="text-dark">Linear Regression (Least Squares)</b></p>
                        <p class="mb-2 text-muted">Trend Window: <b class="text-dark">Last 12 months</b></p>
                        <p class="mb-2 text-muted">Reorder Math Window: <b class="text-dark">Last 30 days (daily)</b></p>
                        <p class="mb-0 text-muted">Min. Data Required: <b class="text-dark">2 months with sales</b></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- INTELLIGENCE DRAWER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="intelDrawer" style="width: 650px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold" id="intelDrawerTitle">Decision Intelligence Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="intelContent"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= base_url('public/js/admin/strategy/analytics.js') ?>"></script>
<?= view('partials/admin/footer') ?>