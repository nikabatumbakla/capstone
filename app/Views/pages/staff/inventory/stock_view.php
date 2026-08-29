<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Inventory Intelligence</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i>Real-Time Stock Monitoring</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Track available medical units, batches, and shelf-life status</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="stat-card p-3 bg-white border-0 shadow-sm rounded-4"><small class="info-label">ITEMS IN CATALOG</small><h4 class="fw-bold mb-0"><?= $total_items ?></h4></div></div>
                <div class="col-md-4"><div class="stat-card p-3 bg-white border-0 shadow-sm rounded-4"><small class="info-label text-danger">CRITICAL LOW STOCK</small><h4 class="fw-bold mb-0 text-danger"><?= $low_stock ?></h4></div></div>
                <div class="col-md-4"><div class="stat-card p-3 bg-white border-0 shadow-sm rounded-4"><small class="info-label text-warning">NEAR EXPIRY (6 MOS)</small><h4 class="fw-bold mb-0 text-warning"><?= $near_expiry ?></h4></div></div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:12px"><i class="fas fa-list-ul me-2 text-maroon"></i>Current Stock Registry</h6>
                    
                    <form action="" method="GET" class="d-flex gap-2">
                        <select name="category" class="form-select form-select-sm rounded-pill border" style="width: 150px;" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control form-control-sm rounded-pill ps-4" placeholder="Search item or SKU..." style="width: 200px;" value="<?= $_GET['search'] ?? '' ?>">
                            <i class="fas fa-search position-absolute text-muted" style="left: 12px; top: 10px; font-size: 10px;"></i>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Product Specification</th>
                                <th>Category</th>
                                <th>Batch No.</th>
                                <th class="text-center">Available</th>
                                <th>Sell Price</th>
                                <th>Expiry</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($inventory as $item): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold d-block"><?= $item['product_name'] ?></span>
                                    <small class="text-muted"><?= $item['sku'] ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= $item['cat_name'] ?></span></td>
                                <td><code><?= $item['batch_number'] ?></code></td>
                                <td class="text-center fw-bold text-maroon" style="font-size:13px"><?= $item['quantity_avail'] ?> <small class="fw-normal text-muted"><?= $item['unit'] ?></small></td>
                                <td class="fw-bold">₱ <?= number_format($item['sell_price'], 2) ?></td>
                                <td>
                                    <?php if($item['expires_at']): ?>
                                        <span class="<?= (strtotime($item['expires_at']) < strtotime('+3 months')) ? 'text-danger fw-bold' : '' ?>">
                                            <?= date('M Y', strtotime($item['expires_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($item['quantity_avail'] <= $item['reorder_level']): ?>
                                        <span class="badge bg-warning text-dark px-3">Restock Required</span>
                                    <?php else: ?>
                                        <span class="badge bg-success px-3">Available</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-view-details" data-id="<?= $item['batch_id'] ?>">View Details</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DETAILS DRAWER (Staff Mode) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="detailsDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold">Item Specification Intelligence</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="drawerContent"></div>
</div>

<!-- NEW DRAWER: STOCK ADJUSTMENT FORM (FIGMA STYLE) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="adjustDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold">Stock Adjustment Form</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="adjustContent"></div>
</div>

<script src="<?= base_url('public/js/staff/inventory.js') ?>"></script>
<?= view('partials/staff/footer') ?>