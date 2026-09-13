<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Stock Management</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Inventory Management — Dual Direction</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Inbound • Outbound • POS — Real-Time Stock Tracking</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-3">
        <a href="?<?= $search ? 'search='.urlencode($search).'&' : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= !$status_filter ? 'border-bottom border-3 border-maroon' : '' ?>">
                <i class="fas fa-boxes-stacked position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">TOTAL PRODUCTS</small>
                <h3 class="fw-bold mb-0"><?= $total_products ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=low_stock<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter == 'low_stock' ? 'border-bottom border-3 border-danger' : '' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">LOW STOCK</small>
                <h3 class="fw-bold mb-0 text-danger"><?= $low_stock ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=near_expiry<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter == 'near_expiry' ? 'border-bottom border-3 border-warning' : '' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">NEAR EXPIRY</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $near_expiry ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="inventory-kpi-card position-relative">
            <i class="fas fa-layer-group position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
            <small class="text-muted fw-bold d-block mb-1">CATEGORIES</small>
            <h3 class="fw-bold mb-0"><?= count($categories) ?></h3>
        </div>
    </div>
</div>

            <?php if ($status_filter): ?>
<div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 12px;">
    <span><strong><?= $status_filter == 'low_stock' ? 'Low Stock Products' : 'Near Expiry Products' ?></strong></span>
    <a href="?" class="text-danger fw-bold text-decoration-none">×</a>
</div>
<?php endif; ?>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size: 14px;"><i class="fas fa-list me-2 text-maroon"></i>Current Stock Inventory</h6>

                    <div class="d-flex align-items-center gap-3">
                        <form action="" method="GET" class="filter-box bg-light rounded-pill px-3 py-1 shadow-none border">
                            <input type="hidden" name="search" value="<?= esc($search ?? '') ?>">
                            <select name="category" class="form-select border-0 bg-transparent" style="font-size: 11px; width: 150px;" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <div class="position-relative filter-box bg-light rounded-pill px-2 border shadow-none">
                            <input type="text" id="inventorySearch" class="form-control form-control-sm border-0 bg-transparent ps-4"
                                   placeholder="Search product intelligence..." style="font-size: 11px; width: 220px; height: 35px;"
                                   value="<?= esc($search ?? '') ?>">
                            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 11px; font-size: 11px;"></i>
                        </div>

                        <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-lg btn-add-product" data-bs-toggle="offcanvas" data-bs-target="#addProductDrawer" style="font-size: 11px; height: 38px;">
                            <i class="fas fa-plus me-2"></i> ADD PRODUCT
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th class="ps-4">Product</th><th>Barcode</th><th>Category</th><th>Batch</th><th>Stock</th><th>Status</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody id="inventoryTableBody" data-auto-refresh="true">
                            <?= view('pages/admin/operations/inventory/_stock_rows', ['inventory' => $inventory]) ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $rangeStart = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
                    $rangeEnd = min($current_page * $per_page, $total_rows);
                    $catQuery = $category_filter ? '&category=' . $category_filter : '';
                    $searchQuery = $search ? '&search=' . urlencode($search) : '';
                    $statusQuery = $status_filter ? '&status=' . $status_filter : '';
                    $pageQuery = $catQuery . $searchQuery . $statusQuery;
                    $windowSize = 3;
                    $currentBlock = (int) ceil($current_page / $windowSize);
                    $windowStart = (($currentBlock - 1) * $windowSize) + 1;
                    $windowEnd = min($windowStart + $windowSize - 1, $total_pages);
                ?>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span class="text-muted fw-bold" style="font-size: 10px;" id="rangeInfo">Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> Entries</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 custom-pager">
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= max(1, $current_page - 1) . $pageQuery ?>"><i class="fas fa-chevron-left"></i></a></li>
                            <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i . $pageQuery ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $pageQuery ?>"><i class="fas fa-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="addProductDrawer" style="width: 460px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-maroon">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav nav-pills p-3 bg-light border-bottom" id="addProductTabs">
            <li class="nav-item flex-fill"><button class="nav-link active w-100 rounded-pill" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-add-info">1. Product Info</button></li>
            <li class="nav-item flex-fill"><button class="nav-link w-100 rounded-pill" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-add-stock">2. Initial Stock</button></li>
            <li class="nav-item flex-fill"><button class="nav-link w-100 rounded-pill" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-add-edu">3. Education</button></li>
        </ul>

        <form action="<?= base_url('admin/inventory/save-product') ?>" method="POST" enctype="multipart/form-data" class="p-4">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-add-info">
                    <div class="mb-3"><label class="info-label">Product Image</label><input type="file" name="product_image" class="form-control form-control-sm" accept="image/*"></div>
                    <div class="mb-3">
                        <label class="info-label">Product Category *</label>
                        <select name="category_id" id="categorySelect" class="form-select form-select-sm" required>
                            <option value="" selected disabled>Select category</option>
                            <?php foreach($categories as $cat): ?><option value="<?= $cat['category_id'] ?>"><?= $cat['name'] ?></option><?php endforeach; ?>
                            <option value="__new__">+ Add New Category</option>
                        </select>
                        <div id="newCategoryWrap" class="mt-2" style="display:none;"><input type="text" name="new_category_name" id="newCategoryName" class="form-control form-control-sm" placeholder="e.g. Orthopedic Supports"></div>
                    </div>
                    <div class="mb-3"><label class="info-label">Supplier (optional)</label>
                        <select name="supplier_id" class="form-select form-select-sm"><option value="">— None —</option><?php foreach($suppliers as $s): ?><option value="<?= $s['supplier_id'] ?>"><?= $s['name'] ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="info-label">Product Name *</label><input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Digital Thermometer" required></div>
                    <div class="mb-3"><label class="info-label">Description</label><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="e.g. Fast 10-second oral/underarm digital thermometer"></textarea></div>
                    
                    
                    <div class="mb-3">
    <label class="info-label">Barcode Value (leave blank to auto-generate)</label>
    <input type="text" name="barcode" class="form-control form-control-sm" placeholder="Leave blank for a system-generated barcode">
</div>
<div class="mb-3">
    <label class="info-label">Barcode Type</label>
    <select name="barcode_type" class="form-select form-select-sm">
        <option value="CODE128" selected>Internal / Auto-Generated (CODE128)</option>
        <option value="EAN13">Manufacturer's Retail Barcode (EAN13)</option>
    </select>
    <p class="helper-text mb-0" style="font-size:9px;">Choose "Manufacturer's Retail Barcode" only if you're entering a real barcode already printed on the product itself.</p>
</div>
                    
                    <div class="mb-3"><label class="info-label">Brand</label><input type="text" name="brand" class="form-control form-control-sm" placeholder="e.g. Omron, Rossmax"></div>
                    <div class="mb-3"><label class="info-label">Manufacturer</label><input type="text" name="manufacturer" class="form-control form-control-sm" placeholder="e.g. Omron Healthcare Co. Ltd."></div>
                    <div class="mb-3"><label class="info-label">Unit of Measure</label><input type="text" name="unit" class="form-control form-control-sm" placeholder="e.g. piece, box, roll, pack" value="piece"></div>
                    <div class="mb-3 form-check"><input type="checkbox" name="is_vat_exempt" class="form-check-input" id="addVatExempt"><label class="form-check-label info-label mb-0" for="addVatExempt">VAT Exempt</label></div>
                    <div class="mb-3"><label class="info-label">Notes</label><textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Fragile — handle with care"></textarea></div>
                    <button type="button" class="btn btn-dark w-100 btn-next-tab" data-next="#tab-add-stock" style="border-radius: 8px; font-weight: 700; font-size: 11px; padding: 10px;">Next: Initial Stock →</button>
                </div>

                <div class="tab-pane fade" id="tab-add-stock">
                    <p class="helper-text mb-3">Fill this in to add opening stock right away. Leave blank to add the product with no stock — use "Add Stock" later.</p>
                    <div class="mb-3"><label class="info-label">Batch Number</label><input type="text" name="batch_number" class="form-control form-control-sm" placeholder="e.g. B2026-05"></div>
                    <div class="mb-3">
    <label class="info-label">Lot Number (optional)</label>
    <input type="text" name="lot_number" class="form-control form-control-sm" placeholder="Leave blank if not printed on the product">
</div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="info-label">Quantity</label><input type="number" name="quantity" class="form-control form-control-sm" placeholder="e.g. 50" min="1"></div>
                        <div class="col-6"><label class="info-label">Reorder Level</label><input type="number" name="reorder_level" class="form-control form-control-sm" placeholder="e.g. 5" value="5"></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="info-label">Cost Price (per unit)</label><input type="number" step="0.01" name="cost_price" class="form-control form-control-sm" placeholder="e.g. 600.00"></div>
                        <div class="col-6"><label class="info-label">Sell Price (per unit)</label><input type="number" step="0.01" name="sell_price" class="form-control form-control-sm" placeholder="e.g. 850.00"></div>
                    </div>
                    <div class="mb-3"><label class="info-label">Expiry Date</label><input type="date" name="expires_at" class="form-control form-control-sm"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill btn-prev-tab" data-prev="#tab-add-info">← Back</button>
                        <button type="button" class="btn btn-dark flex-fill btn-next-tab" data-next="#tab-add-edu">Next: Education →</button>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-add-edu">
    <div class="mb-3"><label class="formal-label">Video (Google Drive link)</label>
        <input type="text" name="video_url" class="formal-input" placeholder="e.g. https://drive.google.com/file/d/xxxxxxxxx/view?usp=sharing">
        <p class="helper-text">Paste the normal Drive share link — it will convert automatically.</p>
    </div>
    <div class="mb-3"><label class="info-label">Medical Description</label><textarea name="medical_description" class="form-control form-control-sm" rows="2" placeholder="e.g. Non-invasive device used to measure blood oxygen saturation"></textarea></div>
    <div class="mb-3"><label class="info-label">Usage Purpose</label><textarea name="usage_purpose" class="form-control form-control-sm" rows="2" placeholder="e.g. Monitoring oxygen levels in patients with respiratory conditions"></textarea></div>
    <div class="mb-3"><label class="info-label">Usage Guide</label><textarea name="usage_guide" class="form-control form-control-sm" rows="2" placeholder="e.g. Clip onto fingertip, wait 10 seconds for reading"></textarea></div>
    <div class="mb-3"><label class="info-label">Warnings</label><textarea name="warnings" class="form-control form-control-sm" rows="2" placeholder="e.g. Not for diagnostic use without physician review"></textarea></div>
    <div class="mb-3"><label class="info-label">Storage Information</label><textarea name="storage_info" class="form-control form-control-sm" rows="2" placeholder="e.g. Store at room temperature, away from direct sunlight"></textarea></div>
    <div class="mb-3"><label class="info-label">Healthcare Tips</label><textarea name="healthcare_tips" class="form-control form-control-sm" rows="2" placeholder="e.g. Check regularly if you have asthma or COPD"></textarea></div>
    <div class="mb-3"><label class="info-label">Warranty Information</label><input type="text" name="warranty_info" class="form-control form-control-sm" placeholder="e.g. 1 year manufacturer warranty"></div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary flex-fill btn-prev-tab" data-prev="#tab-add-stock">← Back</button>
        <button type="submit" class="btn btn-maroon flex-fill fw-bold">✓ SAVE PRODUCT</button>
    </div>
</div>

            </div>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="productDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-maroon">Product Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="drawerContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="editProductDrawer" style="width: 460px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-maroon">Edit Product Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0" id="editProductContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="adjustDrawer" style="width: 400px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-dark"><i class="fas fa-adjust me-2"></i>Stock Adjustment Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="adjustDrawerContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="educationDrawer" style="width: 480px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-maroon"><i class="fas fa-book-medical me-2"></i>Product Education</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="educationContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="addStockDrawer" style="width: 400px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-maroon">Add Stock (New Batch)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="addStockContent"></div>
</div>

<script>const BASE_URL = "<?= base_url() ?>";</script>
<script src="<?= base_url('public/js/admin/operations/inventory/inventory.js') ?>"></script>
<?= view('partials/admin/footer') ?>