<?= view('partials/admin/head') ?>
<!-- Add the unique CSS link -->
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">

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
        <a href="?<?= $search ? 'search='.urlencode($search).'&' : '' ?>" class="text-decoration-none">
            <div class="inventory-kpi-card <?= !$status_filter ? 'border-bottom border-3 border-maroon' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">TOTAL PRODUCTS</small>
                <h3 class="fw-bold mb-0"><?= $total_products ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=low_stock<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none">
            <div class="inventory-kpi-card <?= $status_filter == 'low_stock' ? 'border-bottom border-3 border-danger' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">LOW STOCK</small>
                <h3 class="fw-bold mb-0 text-danger"><?= $low_stock ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=near_expiry<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none">
            <div class="inventory-kpi-card <?= $status_filter == 'near_expiry' ? 'border-bottom border-3 border-warning' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">NEAR EXPIRY</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $near_expiry ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="inventory-kpi-card">
            <small class="text-muted fw-bold d-block mb-1">CATEGORIES</small>
            <h3 class="fw-bold mb-0"><?= count($categories) ?></h3>
        </div>
    </div>
</div>

<?php if ($status_filter): ?>
<div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 12px;">
    <span>Filtering: <strong><?= $status_filter == 'low_stock' ? 'Low Stock Products' : 'Near Expiry Products' ?></strong></span>
    <a href="?" class="text-danger fw-bold text-decoration-none">Clear filter ×</a>
</div>
<?php endif; ?>

<div class="custom-table-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0" style="font-size: 14px;"><i class="fas fa-list me-2 text-maroon"></i>Current Stock Inventory</h6>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Category Filter -->
            <form action="" method="GET" class="filter-box bg-light rounded-pill px-3 py-1 shadow-none border">
    <input type="hidden" name="search" value="<?= esc($search ?? '') ?>">
    <select name="category" class="form-select border-0 bg-transparent" style="font-size: 11px; width: 150px;" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
        <?php endforeach; ?>
    </select>
</form>

            <!-- Search Bar -->
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
    <?php
        $isLowStock   = $item['batch_id'] && $item['quantity_avail'] <= $item['reorder_level'];
        $isNearExpiry = $item['batch_id'] && $item['expires_at']
                        && strtotime($item['expires_at']) >= strtotime('today')
                        && strtotime($item['expires_at']) <= strtotime('+6 months');
    ?>
    <?php if(!$item['batch_id']): ?>
        <span class="badge bg-secondary">No Stock</span>
    <?php elseif($isLowStock): ?>
        <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Low Stock</span>
    <?php elseif($isNearExpiry): ?>
        <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i> Near Expiry</span>
    <?php else: ?>
        <span class="badge bg-success px-4" style="border-radius: 20px;">Available</span>
    <?php endif; ?>
</td>

<td class="text-center">
    <?php if($item['batch_id']): ?>
        <button 
            class="btn btn-sm btn-dark rounded-pill px-2 btn-view" 
            data-id="<?= $item['batch_id'] ?>" 
            title="View">
            <i class="fas fa-eye" style="font-size: 11px;"></i>
        </button>
    <?php else: ?>
        <button 
            class="btn btn-sm btn-success rounded-pill px-2 btn-add-stock" 
            data-pid="<?= $item['pid'] ?>" 
            title="Add Stock">
            <i class="fas fa-plus" style="font-size: 11px;"></i>
        </button>
    <?php endif; ?>

    <button 
        class="btn btn-sm btn-outline-primary rounded-pill px-2 btn-edit" 
        data-id="<?= $item['pid'] ?>" 
        title="Edit">
        <i class="fas fa-edit" style="font-size: 11px;"></i>
    </button>

    <a href="<?= base_url('admin/inventory/delete-product/' . $item['pid']) ?>"
       class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-delete" 
       title="Delete"
       onclick="return confirm('Delete this product entirely? This also removes its stock batches. This cannot be undone.');">
        <i class="fas fa-trash" style="font-size: 11px;"></i>
    </a>
</td>

   
</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php
    $rangeStart = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
    $rangeEnd   = min($current_page * $per_page, $total_rows);

    $catQuery = $category_filter ? '&category=' . $category_filter : '';
    $searchQuery = $search ? '&search=' . urlencode($search) : '';
    $statusQuery = $status_filter ? '&status=' . $status_filter : '';
    $pageQuery = $catQuery . $searchQuery . $statusQuery;

    // Windowed pagination: show 3 page numbers at a time
    $windowSize = 3;
    $currentBlock = (int) ceil($current_page / $windowSize);
    $windowStart = (($currentBlock - 1) * $windowSize) + 1;
    $windowEnd = min($windowStart + $windowSize - 1, $total_pages);
?>
<div class="d-flex justify-content-between align-items-center mt-4">
    <span class="text-muted fw-bold" style="font-size: 10px;">
        Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> Entries
    </span>
    <nav>
        <ul class="pagination pagination-sm mb-0 custom-pager">
            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= max(1, $current_page - 1) . $pageQuery ?>"><i class="fas fa-chevron-left"></i></a>
            </li>

            <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i . $pageQuery ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $pageQuery ?>"><i class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
</div>

</div>

        </div>
    </div>
</div>

<!-- SLIDING DRAWER: ADD PRODUCT (Converted from Modal) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="addProductDrawer" style="width: 420px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold text-maroon">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/inventory/save-product') ?>" method="POST" enctype="multipart/form-data">

        <div class="mb-3">
            <label class="info-label">Product Image</label>
        <input type="file" name="product_image" class="form-control form-control-sm" accept="image/*">
    </div>

            <div class="mb-3">
    <label class="info-label">Product Category *</label>
    <select name="category_id" id="categorySelect" class="form-select form-select-sm" required>
        <option value="" selected disabled>Select category</option>
        <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>"><?= $cat['name'] ?></option>
        <?php endforeach; ?>
        <option value="__new__">+ Add New Category</option>
    </select>
    <div id="newCategoryWrap" class="mt-2" style="display:none;">
        <input type="text" name="new_category_name" id="newCategoryName" class="form-control form-control-sm" placeholder="e.g. Orthopedic Supports">
    </div>
</div>

            <div class="mb-3">
                <label class="info-label">Supplier (optional)</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">— None —</option>
                    <?php foreach($suppliers as $s): ?>
                        <option value="<?= $s['supplier_id'] ?>"><?= $s['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="info-label">Product Name *</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Digital Thermometer" required>
            </div>

            <div class="mb-3">
                <label class="info-label">Description</label>
                <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="e.g. Fast 10-second oral/underarm digital thermometer"></textarea>
            </div>

            <div class="mb-3">
                <label class="info-label">SKU (optional, leave blank to skip)</label>
                <input type="text" name="sku" class="form-control form-control-sm" placeholder="e.g. SKU-045">
            </div>

            <div class="mb-3">
                <label class="info-label">Barcode Value</label>
                <input type="text" name="barcode" class="form-control form-control-sm" placeholder="e.g. 4800067134504">
            </div>

            <div class="mb-3">
                <label class="info-label">Barcode Type</label>
                <select name="barcode_type" class="form-select form-select-sm">
                    <option value="">— Not set —</option>
                    <option value="EAN13">EAN13</option>
                    <option value="CODE128">CODE128</option>
                    <option value="QR">QR</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="info-label">Brand</label>
                <input type="text" name="brand" class="form-control form-control-sm" placeholder="e.g. Omron, Rossmax">
            </div>

            <div class="mb-3">
                <label class="info-label">Manufacturer</label>
                <input type="text" name="manufacturer" class="form-control form-control-sm" placeholder="e.g. Omron Healthcare Co. Ltd.">
            </div>

            <div class="mb-3">
                <label class="info-label">Unit of Measure</label>
                <input type="text" name="unit" class="form-control form-control-sm" placeholder="e.g. piece, box, roll, pack" value="piece">
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_vat_exempt" class="form-check-input" id="addVatExempt">
                <label class="form-check-label info-label mb-0" for="addVatExempt">VAT Exempt</label>
            </div>

            <div class="mb-3">
                <label class="info-label">Notes</label>
                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Fragile — handle with care"></textarea>
            </div>

        <hr class="my-4">
<h6 class="fw-bold text-maroon mb-3" style="font-size: 12px;"><i class="fas fa-boxes me-2"></i>Initial Stock (optional)</h6>
<p class="helper-text mb-3">Fill this in to add opening stock right away. Leave blank to add the product with no stock — you can use "Add Stock" later.</p>

<div class="mb-3">
    <label class="info-label">Batch Number</label>
    <input type="text" name="batch_number" class="form-control form-control-sm" placeholder="e.g. B2026-05">
</div>

<div class="row g-2 mb-3">
    <div class="col-6">
        <label class="info-label">Quantity</label>
        <input type="number" name="quantity" class="form-control form-control-sm" placeholder="e.g. 50" min="1">
    </div>
    <div class="col-6">
        <label class="info-label">Reorder Level</label>
        <input type="number" name="reorder_level" class="form-control form-control-sm" placeholder="e.g. 5" value="5">
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-6">
        <label class="info-label">Cost Price (per unit)</label>
        <input type="number" step="0.01" name="cost_price" class="form-control form-control-sm" placeholder="e.g. 600.00">
    </div>
    <div class="col-6">
        <label class="info-label">Sell Price (per unit)</label>
        <input type="number" step="0.01" name="sell_price" class="form-control form-control-sm" placeholder="e.g. 850.00">
    </div>
</div>

<div class="mb-3">
    <label class="info-label">Expiry Date</label>
    <input type="date" name="expires_at" class="form-control form-control-sm">
</div>

<hr class="my-4">
    <h6 class="fw-bold text-maroon mb-3" style="font-size: 12px;"><i class="fas fa-book-medical me-2"></i>Educational Content (optional)</h6>
    
   <div class="mb-3"><label class="formal-label">Video (Google Drive link)</label>
    <input type="text" name="video_url" class="formal-input" placeholder="e.g. https://drive.google.com/file/d/xxxxxxxxx/view?usp=sharing" value="">
    <p class="helper-text">Paste the normal Drive share link — it will convert automatically.</p>
</div>

    <div class="mb-3"><label class="info-label">Medical Description</label>
        <textarea name="medical_description" class="form-control form-control-sm" rows="2" placeholder="e.g. Non-invasive device used to measure blood oxygen saturation"></textarea></div>
    <div class="mb-3"><label class="info-label">Usage Guide</label>
        <textarea name="usage_guide" class="form-control form-control-sm" rows="2" placeholder="e.g. Clip onto fingertip, wait 10 seconds for reading"></textarea></div>
    <div class="mb-3"><label class="info-label">Warnings</label>
        <textarea name="warnings" class="form-control form-control-sm" rows="2" placeholder="e.g. Not for diagnostic use without physician review"></textarea></div>

    <button type="submit" class="btn btn-maroon w-100 py-2 mt-2 fw-bold rounded-3">SAVE PRODUCT</button>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="educationDrawer" style="width: 480px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold text-maroon"><i class="fas fa-book-medical me-2"></i>Product Education</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="educationContent">
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
<script src="<?= base_url('public/js/admin/operations/inventory/inventory.js') ?>"></script>
<?= view('partials/admin/footer') ?>