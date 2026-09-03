<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                    <h5 class="fw-bold mb-0">Browse Medical Supplies</h5>
                </div>
                <a href="<?= base_url('client/orders/place-order') ?>" class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold">
                    <i class="fas fa-shopping-cart me-2"></i>Cart (<?= $cart_count ?>)
                </a>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-shopping-bag me-2"></i>Product Catalog</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Add items to your cart, then proceed to Place Order to submit your request</p>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <form action="" method="GET" class="d-flex gap-2">
                    <select name="category" class="form-select form-select-sm" style="width:190px;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= $category_filter == $cat['category_id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="position-relative">
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill ps-4" placeholder="Search products..." style="width:230px;" value="<?= esc($search) ?>">
                        <i class="fas fa-search position-absolute text-muted" style="left:14px; top:9px; font-size:10px;"></i>
                    </div>
                </form>
            </div>

            <div class="row g-3">
                <?php if(empty($products)): ?>
                    <p class="text-center text-muted py-5">No products found.</p>
                <?php else: ?>
                    <?php foreach($products as $p):
                        $isLowStock = $p['total_stock'] > 0 && $p['reorder_level'] && $p['total_stock'] <= $p['reorder_level'];
                        $isOutOfStock = $p['total_stock'] <= 0;
                    ?>
                    <div class="col-md-3">
                        <div class="custom-table-container h-100 d-flex flex-column p-0 overflow-hidden">

                            <div class="position-relative" style="height:140px; background:#f4f4f4; overflow:hidden;" onmouseover="this.querySelector('.hover-view-btn').style.opacity=1" onmouseout="this.querySelector('.hover-view-btn').style.opacity=0">
                                <?php if($p['image_path']): ?>
                                    <img src="<?= base_url($p['image_path']) ?>" alt="<?= esc($p['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-box-open" style="font-size:32px; color:#ccc;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background:rgba(0,0,0,0.35);">
                                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-bold btn-view-product hover-view-btn" data-id="<?= $p['product_id'] ?>" style="opacity:0; transition:opacity 0.2s;">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                </div>
                            </div>

                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <small class="text-muted"><?= esc($p['category_name']) ?></small>
                                <h6 class="fw-bold mb-1"><?= esc($p['name']) ?></h6>
                                <div class="mb-2" style="font-size:9.5px; color:#888;">
                                    <?php if($p['brand'] || $p['manufacturer']): ?>
                                        <?= esc($p['brand'] ?: $p['manufacturer']) ?> · <?php endif; ?>Per <?= esc($p['unit']) ?>
                                </div>
                                <p class="fw-bold text-maroon mb-2" style="font-size:15px;">₱<?= number_format($p['sell_price'] ?? 0, 2) ?></p>
                                <div class="mb-3">
                                    <?php if($isOutOfStock): ?>
                                        <span class="badge rounded-pill" style="background:#fde2e2; color:#c0392b; font-weight:600;">Out of Stock</span>
                                    <?php elseif($isLowStock): ?>
                                        <span class="badge rounded-pill" style="background:#fdecd2; color:#b9770e; font-weight:600;">Limited Stock</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill" style="background:#d9f2e3; color:#1e8449; font-weight:600;">Available</span>
                                    <?php endif; ?>
                                </div>
                                <form action="<?= base_url('client/orders/add-to-cart') ?>" method="POST" class="mt-auto d-flex gap-2">
                                    <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                    <input type="number" name="qty" value="1" min="1" max="<?= $p['total_stock'] ?>" class="form-control form-control-sm" style="width:65px;" <?= $isOutOfStock ? 'disabled' : '' ?>>
                                    <button type="submit" class="btn btn-sm btn-dark rounded-pill flex-grow-1" <?= $isOutOfStock ? 'disabled' : '' ?>>
                                        <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php
                $q = '&category='.$category_filter.'&search='.urlencode($search);
                $w=3; $cb=(int)ceil($current_page/$w); $ws=(($cb-1)*$w)+1; $we=min($ws+$w-1,$total_pages);
            ?>
            <?php if($total_pages > 1): ?>
            <div class="d-flex justify-content-end mt-4">
                <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                    <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
                    <?php for($i=$ws;$i<=$we;$i++): ?><li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li><?php endfor; ?>
                    <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
                </ul></nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="productDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Product Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0" id="productDrawerContent">
        <div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>
    </div>
</div>

<script src="<?= base_url('public/js/client/browse.js') ?>"></script>
<?= view('partials/client/footer') ?>