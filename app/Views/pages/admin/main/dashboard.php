<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1">Welcome back, <?= esc($fullname) ?></h6>
                <p class="mb-0 opacity-75 small"><?= date('l, F d, Y') ?> · Admin Command Center</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="inventory-kpi-card position-relative">
                        <i class="fas fa-coins position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                        <small class="text-muted fw-bold d-block mb-1">TODAY'S SALES</small>
                        <h3 class="fw-bold mb-0">₱<?= number_format($total_sales_today, 2) ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('admin/inventory/stock-management') ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative">
                            <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">ACTIVE PRODUCTS</small>
                            <h3 class="fw-bold mb-0 text-primary"><?= $active_products ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('admin/inventory/stock-management?status=low_stock') ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative">
                            <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">LOW STOCK ITEMS</small>
                            <h3 class="fw-bold mb-0 text-danger"><?= $low_stock_count ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('admin/sales/sales-orders?status=pending') ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative">
                            <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">PENDING ORDERS</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $pending_orders ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="custom-table-container h-100">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <h6 class="fw-bold mb-1" style="font-size:13px;"><i class="fas fa-chart-bar me-2 text-maroon"></i>7-Day Sales Trend</h6>
                    <p class="text-muted mb-0" style="font-size:10px;">Combined revenue from walk-in POS sales and institutional Sales Orders, updated daily</p>
                </div>
                <?php
                    $weekTotal = array_sum($weekly_trend['data']);
                    $todayIndex = count($weekly_trend['data']) - 1;
                    $yesterdayIndex = $todayIndex - 1;
                    $todayVal = $weekly_trend['data'][$todayIndex] ?? 0;
                    $yesterdayVal = $yesterdayIndex >= 0 ? $weekly_trend['data'][$yesterdayIndex] : 0;
                    $trendUp = $todayVal >= $yesterdayVal;
                ?>
                <span class="badge <?= $trendUp ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 <?= $trendUp ? 'text-success' : 'text-danger' ?> px-3 py-2" style="font-size:10px;">
                    <i class="fas fa-arrow-<?= $trendUp ? 'up' : 'down' ?> me-1"></i>vs. yesterday
                </span>
            </div>

            <div class="d-flex align-items-baseline gap-2 mb-3">
                <h4 class="fw-bold mb-0">₱<?= number_format($weekTotal, 2) ?></h4>
                <small class="text-muted">total this week</small>
            </div>

            <canvas id="salesChart" height="90"
                data-labels='<?= json_encode($weekly_trend['labels']) ?>'
                data-values='<?= json_encode($weekly_trend['data']) ?>'></canvas>

            <p class="text-muted mb-0 mt-3" style="font-size:9.5px;">
                <i class="fas fa-circle-info me-1"></i>Hover over a bar to see the exact amount for that day.
            </p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="custom-table-container h-100">
            <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-bell me-2 text-danger"></i>Active Alerts</h6>
            <?php if(empty($active_alerts)): ?>
                <p class="text-muted text-center py-4 mb-0">No active alerts.</p>
            <?php else: foreach($active_alerts as $a): ?>
                <div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                    <i class="fas fa-exclamation-circle me-2 mt-1 <?= $a['priority']=='high' ? 'text-danger' : 'text-warning' ?>"></i>
                    <div>
                        <p class="mb-0" style="font-size:11px;"><?= esc($a['message']) ?></p>
                        <small class="text-muted"><?= date('M d, h:i A', strtotime($a['created_at'])) ?></small>
                    </div>
                </div>
            <?php endforeach; endif; ?>
            <a href="<?= base_url('admin/management/alerts-tasks') ?>" class="btn btn-xs btn-outline-dark rounded-pill w-100 mt-2">View All Alerts</a>
        </div>
    </div>
</div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-crown me-2 text-warning"></i>Top Clients</h6>
                        <?php if(empty($top_clients)): ?>
                            <p class="text-muted text-center py-4 mb-0">No client orders yet.</p>
                        <?php else: foreach($top_clients as $c): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <p class="mb-0 fw-bold" style="font-size:11px;"><?= esc($c['organization']) ?></p>
                                    <small class="text-muted"><?= $c['total_orders'] ?> orders</small>
                                </div>
                                <span class="fw-bold text-maroon">₱<?= number_format($c['total_spent'], 0) ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-chart-pie me-2 text-primary"></i>Sales by Category</h6>
                        <?php if(empty($category_sales)): ?>
                            <p class="text-muted text-center py-4 mb-0">No sales data yet.</p>
                        <?php else: foreach($category_sales as $cs): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-bold"><?= esc($cs['category']) ?></small>
                                    <small class="text-muted">₱<?= number_format($cs['total'], 0) ?></small>
                                </div>
                                <div class="progress" style="height:6px;"><div class="progress-bar" style="width:<?= $cs['percent'] ?>%; background:#7b1113;"></div></div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="custom-table-container h-100">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-truck me-2 text-success"></i>Pending Deliveries</h6>
                        <?php if(empty($pending_deliveries)): ?>
                            <p class="text-muted text-center py-4 mb-0">No deliveries in progress.</p>
                        <?php else: foreach($pending_deliveries as $pd): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <p class="mb-0 fw-bold" style="font-size:11px;"><?= esc($pd['order_number']) ?></p>
                                    <small class="text-muted"><?= esc($pd['client_name']) ?></small>
                                </div>
                                <span class="badge bg-light text-dark border"><?= strtoupper($pd['status']) ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('public/js/admin/main/dashboard.js') ?>"></script>
<?= view('partials/admin/footer') ?>