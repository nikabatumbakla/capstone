<?= view('partials/admin/head') ?>
<!-- Add the unique CSS link -->
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4 stock-wrapper">
            <!-- 1. Header & Back Button -->
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Stock Management</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Inventory Management — Dual Direction</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Inbound • Outbound • POS — Real-Time Stock Tracking</p>
            </div>
           

            <!-- 3. Inventory KPIs -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">TOTAL PRODUCTS</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold mb-0"><?= $total_products ?></h3>
                            <i class="fas fa-cubes text-muted opacity-45"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">LOW STOCK</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold mb-0 text-danger"><?= $low_stock ?></h3>
                            <i class="fas fa-exclamation-triangle text-danger opacity-45"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">NEAR EXPIRY</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold mb-0 text-warning"><?= $near_expiry ?></h3>
                            <i class="fas fa-hourglass-half text-warning opacity-45"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">CATEGORIES</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold mb-0"><?= count($categories) ?></h3>
                            <i class="fas fa-tags text-muted opacity-45"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Table and Filter Section -->
            <!-- THE SPECIFIC SECTION TO RESIZE -->
<div class="custom-table-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0" style="font-size: 14px;"><i class="fas fa-list me-2 text-maroon"></i>Current Stock Inventory</h6>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Category Filter -->
            <form action="" method="GET" class="filter-box bg-light rounded-pill px-3 py-1 shadow-none border">
                <select name="category" class="form-select border-0 bg-transparent" style="font-size: 11px; width: 150px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Search Bar -->
            <div class="position-relative filter-box bg-light rounded-pill px-2 border shadow-none">
                <input type="text" id="inventorySearch" class="form-control form-control-sm border-0 bg-transparent ps-4" placeholder="Search product intelligence..." style="font-size: 11px; width: 220px; height: 35px;">
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
                <tr>
                    <th class="ps-4">Product</th><th>SKU</th><th>Barcode</th><th>Category</th><th>Batch</th><th>Stock</th><th>Status</th><th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody">
                <?php foreach($inventory as $item): ?>
                <tr>
                    <td class="ps-4 fw-bold text-dark" style="font-size: 12px;"><?= $item['product_name'] ?></td>
                    <td><span class="text-muted"><?= $item['sku'] ?></span></td>
                    <td><?= $item['barcode_value'] ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $item['category_name'] ?></span></td>
                    <td>
    <?php if($item['batch_id']): ?>
        <small class="fw-bold"><?= $item['batch_number'] ?></small>
    <?php else: ?>
        <small class="text-muted">No batch yet</small>
    <?php endif; ?>
</td>
<td class="fw-bold text-maroon" style="font-size: 14px;"><?= $item['quantity_avail'] ?></td>
<td>
    <?php if(!$item['batch_id']): ?>
        <span class="badge bg-secondary">No Stock</span>
    <?php elseif($item['quantity_avail'] <= $item['reorder_level']): ?>
        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Low Stock</span>
    <?php else: ?>
        <span class="badge bg-success px-4" style="border-radius: 20px;">Available</span>
    <?php endif; ?>
</td>
<td class="text-center">
    <?php if($item['batch_id']): ?>
        <button class="btn btn-sm btn-dark rounded-pill px-3 btn-view" data-id="<?= $item['batch_id'] ?>" style="font-size: 10px;">View</button>
    <?php else: ?>
        <button class="btn btn-sm btn-success rounded-pill px-3 btn-add-stock" data-pid="<?= $item['pid'] ?>" style="font-size: 10px;">Add Stock</button>
    <?php endif; ?>
    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-edit" data-id="<?= $item['pid'] ?>" style="font-size: 10px;">Edit</button>
    <a href="<?= base_url('admin/inventory/delete-product/' . $item['pid']) ?>"
       class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-delete"
       onclick="return confirm('Delete this product entirely? This also removes its stock batches. This cannot be undone.');"
       style="font-size: 10px;">Delete</a>
</td>

   
</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- FIGMA MATCH PAGINATION -->
    <?php
    $rangeStart = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
    $rangeEnd   = min($current_page * $per_page, $total_rows);
    
    $catQuery = $category_filter ? '&category=' . $category_filter : '';
    $searchQuery = $search ? '&search=' . urlencode($search) : '';
    $pageQuery = $catQuery . $searchQuery;
?>
<div class="d-flex justify-content-between align-items-center mt-4">
    <span class="text-muted fw-bold" style="font-size: 10px;">
        Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> Entries
    </span>
    <nav>
        <ul class="pagination pagination-sm mb-0 custom-pager">
            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= max(1, $current_page - 1) . $pageQuery  ?>"><i class="fas fa-chevron-left"></i></a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i . $pageQuery  ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $pageQuery  ?>"><i class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
</div>
</div>

        </div>
    </div>
</div>

<!-- SLIDING DRAWER: ADD PRODUCT (Converted from Modal) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="addProductDrawer">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-maroon">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/inventory/save-product') ?>" method="POST">
            <div class="mb-3"><label class="info-label">Product Category</label>
                <select name="category_id" class="form-select form-select-sm" required>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>"><?= $cat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="info-label">Product Name</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="Full item name" required>
            </div>
            <div class="mb-3"><label class="info-label">Barcode (Internal/UPC)</label>
                <input type="text" name="barcode" class="form-control form-control-sm">
            </div>
            <div class="mb-3"><label class="info-label">Unit of Measure</label>
                <input type="text" name="unit" class="form-control form-control-sm" placeholder="e.g. piece, box, roll">
            </div>
            <button type="submit" class="btn btn-maroon w-100 py-2 mt-4 fw-bold rounded-3">SAVE PRODUCT</button>
        </form>
    </div>
</div>


<!-- SLIDING DRAWER: VIEW PRODUCT (Enhanced with all DB Info) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="productDrawer">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-maroon">Product Intelligence</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="drawerContent">
        <!-- Content Loaded via AJAX -->
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="editProductDrawer" style="width: 400px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-maroon">Edit Product Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="editProductContent">
        <!-- Loaded via JS -->
    </div>
</div>

<!-- SLIDING DRAWER: STOCK ADJUSTMENT -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="adjustDrawer" style="width: 400px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-dark"><i class="fas fa-adjust me-2"></i>Stock Adjustment Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="adjustDrawerContent">
        <!-- Loaded via AJAX -->
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="addStockDrawer" style="width: 400px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-maroon">Add Stock (New Batch)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="addStockContent"></div>
</div>

<script>
  const BASE_URL = "<?= rtrim(base_url(), '/') ?>";
</script>
<script src="<?= base_url('public/js/admin/inventory.js') ?>"></script>
<?= view('partials/admin/footer') ?>