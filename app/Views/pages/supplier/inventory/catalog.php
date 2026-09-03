<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">My Product Catalog</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-box-open me-2"></i>Product Catalog</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Manage your pricing, SKU, and lead time for products you supply to Robin Rose Trading</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">TOTAL CATALOG ITEMS</small><h3 class="fw-bold mb-0"><?= $total_items ?></h3></div>
                </div>
                <div class="col-md-4">
                    <div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">PREFERRED ITEMS</small><h3 class="fw-bold mb-0 text-success"><?= $preferred_items ?></h3></div>
                </div>
                <div class="col-md-4">
                    <div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">ADDABLE PRODUCTS</small><h3 class="fw-bold mb-0 text-primary"><?= count($addable_products) ?></h3></div>
                </div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-list-ul me-2 text-maroon"></i>My Products</h6>
                    <div class="d-flex gap-2">
                        <form action="" method="GET">
                            <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search product or SKU..." style="width:200px;" value="<?= esc($search) ?>">
                        </form>
                        <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="offcanvas" data-bs-target="#addProductDrawer">
    <i class="fas fa-plus me-1"></i>Add Product
</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">Product</th><th>Category</th><th>My SKU</th><th>Unit Cost</th><th>Min. Order</th><th>Lead Time</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($catalog)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No products in your catalog yet. Click "Add Product" to get started.</td></tr>
                            <?php else: foreach($catalog as $c): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold d-block"><?= esc($c['product_name']) ?></span>
                                    <small class="text-muted">Robin Rose SKU: <?= esc($c['global_sku']) ?></small>
                                    <?php if($c['is_preferred']): ?><span class="badge bg-success ms-1" style="font-size:8px;">PREFERRED</span><?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= esc($c['category_name']) ?></span></td>
                                <td><?= esc($c['supplier_sku'] ?: '—') ?></td>
                                <td class="fw-bold">₱<?= number_format($c['unit_cost'], 2) ?></td>
                                <td><?= $c['minimum_order_qty'] ?> <?= esc($c['unit']) ?></td>
                                <td><?= $c['lead_time_days'] ?> days</td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-secondary rounded-circle btn-edit-catalog" data-id="<?= $c['catalog_id'] ?>" title="Edit" style="width:30px; height:30px;"><i class="fas fa-edit"></i></button>
                                    <a href="<?= base_url('supplier/inventory/catalog/delete/'.$c['catalog_id']) ?>" class="btn btn-xs btn-outline-danger rounded-circle" title="Remove" style="width:30px; height:30px;" onclick="return confirm('Remove this product from your catalog?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-4">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <?php for($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="addProductDrawer" style="width:480px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Add Product to Catalog</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <p class="text-muted mb-3" style="font-size:10px;">
            Showing products from your registered categories:
            <strong><?= !empty($registered_categories) ? implode(', ', array_column($registered_categories, 'name')) : 'None on file' ?></strong>
        </p>

        <?php if(empty($addable_products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fs-1 text-muted opacity-25 mb-3"></i>
                <p class="text-muted">
                    <?= empty($registered_categories)
                        ? 'No categories are on file for your account. Please contact Robin Rose Trading to update your supplier profile.'
                        : 'All products in your registered categories are already in your catalog.' ?>
                </p>
            </div>
        <?php else: ?>
        <form action="<?= base_url('supplier/inventory/catalog/add') ?>" method="POST">
            <div class="mb-3">
                <label class="formal-label">Select Product *</label>
                <select name="product_id" class="form-select formal-input" required>
                    <option value="" disabled selected>Choose a product...</option>
                    <?php
                        $grouped = [];
                        foreach($addable_products as $p) { $grouped[$p['category_name']][] = $p; }
                    ?>
                    <?php foreach($grouped as $catName => $prods): ?>
                        <optgroup label="<?= esc($catName) ?>">
                            <?php foreach($prods as $p): ?>
                                <option value="<?= $p['product_id'] ?>"><?= esc($p['name']) ?> (<?= esc($p['sku']) ?>)</option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="formal-label">Your SKU (optional)</label><input type="text" name="supplier_sku" class="formal-input" placeholder="Your internal product code"></div>
            <div class="row g-3 mb-3">
                <div class="col-6"><label class="formal-label">Unit Cost (₱) *</label><input type="number" step="0.01" name="unit_cost" class="formal-input" required></div>
                <div class="col-6"><label class="formal-label">Minimum Order Qty</label><input type="number" name="minimum_order_qty" class="formal-input" value="1"></div>
            </div>
            <div class="mb-4"><label class="formal-label">Lead Time (days)</label><input type="number" name="lead_time_days" class="formal-input" value="7"></div>
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-pill shadow">✓ ADD TO CATALOG</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="editCatalogDrawer" style="width:480px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Edit Catalog Entry</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="editCatalogContent">
        <div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>
    </div>
</div>

<script src="<?= base_url('public/js/supplier/catalog.js') ?>"></script>
<?= view('partials/supplier/footer') ?>