<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Product Catalog</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>My Product Catalog</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Manage your items listed for Robin Rose Trading</p>
            </div>


            <div class="custom-table-container border-0 shadow-sm" style="border-radius:20px; padding:25px;">
                <div class="d-flex justify-content-between mb-4">
                    <h6 class="fw-bold mb-0">Product Listings</h6>
                    <button class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#addCatalogDrawer">+ Add Product</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:10px">
                        <thead class="table-dark">
                            <tr><th>Products</th><th>Supplier SKU</th><th>Unit Cost</th><th>Lead Time</th><th>Available?</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($catalog as $c): ?>
                            <tr>
                                <td class="fw-bold"><?= $c['product_name'] ?></td>
                                <td><code><?= $c['supplier_sku'] ?></code></td>
                                <td class="fw-bold text-maroon">₱ <?= number_format($c['unit_cost'], 2) ?></td>
                                <td><?= $c['lead_time_days'] ?> days</td>
                                <td><span class="badge bg-soft-success text-success px-3">Available</span></td>
                                <td class="text-center"><button class="btn btn-xs btn-outline-dark rounded-pill px-3">Edit</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: ADD CATALOG ITEM -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="addCatalogDrawer" style="width: 500px; border-left: 8px solid #0d2e4f;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">List Product to RRT Catalog</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('supplier/account/catalog/save') ?>" method="POST">
            <div class="mb-3"><label class="formal-label">Select Global Product</label>
                <select name="product_id" class="form-select formal-input">
                    <?php foreach($all_products as $p): ?><option value="<?= $p['product_id'] ?>"><?= $p['name'] ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="formal-label">Your Company SKU</label><input type="text" name="supplier_sku" class="formal-input"></div>
            <div class="mb-3"><label class="formal-label">Unit Cost (₱)</label><input type="number" step="0.01" name="unit_cost" class="formal-input" required></div>
            <div class="mb-3"><label class="formal-label">Your Lead Time (Days)</label><input type="number" name="lead_time" class="formal-input" value="5"></div>
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3">✓ SAVE LISTING</button>
        </form>
    </div>
</div>
<?= view('partials/client/footer') ?>