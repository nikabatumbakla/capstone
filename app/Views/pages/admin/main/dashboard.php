<?= view('partials/admin/head') ?>
<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1">Admin Dashboard — Overview</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Today: <?= date('F d, Y') ?> - Iriga City, Camarines Sur</p>
            </div>

            <!-- 3. KPI STAT CARDS (WITH ICONS) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold" style="font-size: 8px;">TODAY'S SALE</small>
                                <h4 class="fw-bold mb-0">₱ <?= number_format($total_sales_today, 2) ?></h4>
                                <span class="text-success" style="font-size: 9px;">↑ 12% vs yesterday</span>
                            </div>
                            <div class="icon-circle bg-light p-2 rounded">
                                <i class="fas fa-money-bill-wave text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold" style="font-size: 8px;">ACTIVE PRODUCTS</small>
                                <h4 class="fw-bold mb-0"><?= $active_products ?></h4>
                                <span class="text-primary" style="font-size: 9px;">↑ 2 new this week</span>
                            </div>
                            <div class="icon-circle bg-light p-2 rounded">
                                <i class="fas fa-box text-secondary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold" style="font-size: 8px;">LOW STOCK ITEMS</small>
                                <h4 class="fw-bold mb-0 text-danger"><?= $low_stock_count ?></h4>
                                <span class="text-muted" style="font-size: 9px;">↑ 3 since yesterday</span>
                            </div>
                            <div class="icon-circle bg-light p-2 rounded">
                                <i class="fas fa-exclamation-circle text-danger fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-bold" style="font-size: 8px;">PENDING ORDERS</small>
                                <h4 class="fw-bold mb-0"><?= $pending_orders ?></h4>
                                <span class="text-info" style="font-size: 9px;">Institutional clients</span>
                            </div>
                            <div class="icon-circle bg-light p-2 rounded">
                                <i class="fas fa-file-alt text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. CHART & TOP CLIENTS -->
            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="content-card">
                        <h6 class="fw-bold mb-3 small"><i class="fas fa-chart-area me-2"></i>Weekly Sales Trend (<?= date('M Y') ?>)</h6>
                        <canvas id="salesChart" data-labels='<?= json_encode($weekly_trend['labels']) ?>' data-values='<?= json_encode($weekly_trend['data']) ?>' height="120"></canvas>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="content-card">
                        <h6 class="fw-bold mb-3 small"><i class="fas fa-users me-2"></i>Top Institutional Clients</h6>
                        <table class="table table-sm extra-small">
                            <thead><tr><th>Client</th><th>Type</th><th>Orders</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                <?php foreach($top_clients as $c): ?>
                                <tr>
                                    <td><?= $c['organization'] ?></td>
                                    <td><?= ucfirst($c['client_type']) ?></td>
                                    <td><?= $c['total_orders'] ?></td>
                                    <td class="text-end fw-bold">₱ <?= number_format($c['total_spent']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 5. BOTTOM ROW -->
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="content-card">
                        <h6 class="fw-bold mb-3 small"><i class="fas fa-bell me-2"></i>Active Alerts</h6>
                        <?php foreach($active_alerts as $alert): ?>
                            <div class="alert-item mb-2 p-2 border-start border-4 border-warning rounded bg-light">
                                <p class="mb-0 extra-small fw-bold text-maroon"><?= $alert['message'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-card">
                        <h6 class="fw-bold mb-3 small"><i class="fas fa-chart-pie me-2"></i>Sales by Category</h6>
                        <?php foreach($category_sales as $cat): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between extra-small mb-1">
                                <span><?= $cat['category'] ?></span>
                                <span class="fw-bold">₱ <?= number_format($cat['total']) ?></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: 60%; background-color: #4489b3;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-card">
                        <h6 class="fw-bold mb-3 small"><i class="fas fa-truck me-2"></i>Pending Deliveries</h6>
                        <table class="table table-sm extra-small">
                            <thead><tr><th>PO #</th><th>Supplier</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($pending_deliveries as $d): ?>
                                <tr>
                                    <td><?= $d['po_no'] ?></td>
                                    <td><?= $d['supplier'] ?></td>
                                    <td class="text-primary fw-bold"><?= $d['status'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/admin/footer') ?>