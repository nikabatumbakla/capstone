<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <!-- Header & Back Button -->
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Reports & Analytics</h5>
            </div>
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Reports & Analytics</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Sales · Inventory · Expiry Waste · Supplier Performance — PDF/CSV Export</p>
            </div>


            <!-- 2. RESTORED ANALYTICAL TILES -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius: 20px;">
                        <small class="info-label">CUMULATIVE REVENUE</small>
                        <h4 class="fw-bold text-dark mb-0">₱ <?= number_format($total_revenue, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius: 20px;">
                        <small class="info-label">STOCK VALUATION</small>
                        <h4 class="fw-bold text-primary mb-0">₱ <?= number_format($inventory_value, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius: 20px;">
                        <small class="info-label text-danger">EXPIRY LOSS (WASTE)</small>
                        <h4 class="fw-bold text-danger mb-0">₱ <?= number_format($expiry_waste, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius: 20px;">
                        <small class="info-label text-success">SYSTEM INTEGRITY</small>
                        <h4 class="fw-bold text-success mb-0">AUDIT ACTIVE</h4>
                    </div>
                </div>
            </div>

            <!-- 3. FIGMA EXPORT GRID -->
            <h6 class="fw-bold mb-3"><i class="fas fa-file-export me-2 text-maroon"></i>Official Document Exports</h6>
            <div class="row g-3 mb-5">
                <?php 
                $reports = [
                    ['sales', '💰 Sales Analytics', 'By client, product, and period'],
                    ['inventory', '📦 Inventory Movement', 'Stock-in, stock-out logs'],
                    ['waste', '⏰ Expiry Waste', 'Monetary value of expired items'],
                    ['supplier', '🏭 Supplier Performance', 'Accuracy and lead time data'],
                    ['pos', '🛒 Walk-In POS', 'Daily/Weekly retail summary'],
                    ['dss', '🤖 Predictive Analytics', 'LR forecasts and EOQ results']
                ];
                foreach($reports as $r): ?>
                <div class="col-lg-4">
                    <div class="report-card p-3 border shadow-sm bg-white" style="border-radius: 15px;">
                        <h6 class="fw-bold mb-1" style="font-size:12px"><?= $r[1] ?></h6>
                        <p class="text-muted extra-small mb-3"><?= $r[2] ?></p>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('admin/analytics/export/'.$r[0].'/pdf') ?>" class="btn btn-export-pdf flex-grow-1 py-2">PDF</a>
                            <a href="<?= base_url('admin/analytics/export/'.$r[0].'/csv') ?>" class="btn btn-export-csv flex-grow-1 py-2">CSV</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 4. RESTORED INTELLIGENCE PREVIEW TABLE -->
            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:12px"><i class="fas fa-table me-2 text-maroon"></i> Intelligence Preview (Live Logs)</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm formal-input" style="width:150px">
                            <option>All Movements</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Timestamp</th>
                                <th>Product Specification</th>
                                <th>Movement Intelligence</th>
                                <th class="text-center">Qty</th>
                                <th>Handled By</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reports_data as $row): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= date('M d, h:i A', strtotime($row['moved_at'])) ?></td>
                                <td><span class="fw-bold"><?= $row['pname'] ?></span><br><small class="text-muted"><?= $row['sku'] ?></small></td>
                                <td>
                                    <?php if($row['movement_type'] == 'inbound'): ?>
                                        <span class="badge bg-soft-success text-success border-0 px-3">STOCK IN</span>
                                    <?php elseif($row['movement_type'] == 'pos_sale'): ?>
                                        <span class="badge bg-soft-primary text-primary border-0 px-3">POS SALE</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border px-3"><?= strtoupper($row['movement_type']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold"><?= abs($row['quantity']) ?></td>
                                <td><span class="text-muted"><?= $row['staff'] ?? 'System' ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-dark rounded-pill px-3">View Details</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 5. LOW PERFORMING ALERT -->
            <div class="mt-4 p-3 bg-white border rounded-4 d-flex justify-content-between align-items-center shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle text-warning fs-4 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold small">Low-Performing Products Detected</h6>
                        <p class="mb-0 extra-small text-muted">Analysis of items with no movement in 30+ days.</p>
                    </div>
                </div>
                <button class="btn btn-sm btn-dark rounded-pill px-4">Recalculate Analysis</button>
            </div>

        </div>
    </div>
</div>

<?= view('partials/admin/footer') ?>