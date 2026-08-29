<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Browse Product</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i>Product Catalog</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Browse available medical supplies · Add to order</p>
            </div>

            <!-- Search and Horizontal Category Pills -->
            <div class="custom-table-container mb-4" style="border-radius: 15px;">
                <div class="row g-2 mb-3">
                    <div class="col-md-9">
                        <input type="text" id="productSearch" class="form-control form-control-sm rounded border" placeholder="Search products..." style="height:40px">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm rounded border" style="height:40px">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                </div>

                <!-- CATEGORY PILLS (Figma Match) -->
                <div class="d-flex gap-2 overflow-auto pb-2" style="scrollbar-width: none;">
                    <a href="?" class="btn btn-xs rounded border <?= !isset($_GET['cat']) ? 'btn-primary bg-primary text-white' : 'btn-light' ?> px-3">All</a>
                    <?php foreach($categories as $cat): ?>
                        <a href="?cat=<?= $cat['category_id'] ?>" class="btn btn-xs rounded border <?= (isset($_GET['cat']) && $_GET['cat'] == $cat['category_id']) ? 'btn-primary bg-primary text-white' : 'btn-light' ?> px-3">
                            <?= $cat['name'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PRODUCT GRID (Figma Match) -->
            <div class="row g-4">
                <?php foreach($products as $p): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="stat-card shadow-sm border-0 bg-white p-0 overflow-hidden text-center" style="border-radius: 20px;">
                        <!-- Product Image Area -->
                        <div class="p-4 bg-light position-relative">
                            <i class="fas fa-capsules fs-1 text-primary opacity-25"></i>
                        </div>
                        
                        <div class="p-3">
                            <h6 class="fw-bold mb-1 text-dark"><?= $p['name'] ?></h6>
                            <h5 class="fw-bold text-primary mb-3">₱ <?= number_format($p['sell_price'], 2) ?></h5>
                            
                            <?php if($p['quantity_avail'] <= 5): ?>
                                <span class="badge bg-soft-maroon text-maroon px-3 mb-2">Low Stock</span>
                            <?php else: ?>
                                <span class="badge bg-soft-success text-success px-3 mb-2">Available</span>
                            <?php endif; ?>

                            <div class="d-grid mt-3">
                                <button class="btn btn-sm btn-dark rounded-pill py-2 fw-bold" style="font-size: 10px;">Order Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                <nav><ul class="pagination pagination-sm custom-pager">
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                </ul></nav>
            </div>
        </div>
    </div>
</div>

<?= view('partials/client/footer') ?>