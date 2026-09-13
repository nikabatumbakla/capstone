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

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small" id="flashError"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('warning')): ?>
                <div class="alert alert-warning py-2 small" id="flashWarning"><?= session()->getFlashdata('warning') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

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

            <div class="row g-2">
                <?php if(empty($products)): ?>
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fas fa-box-open fs-1 opacity-25 mb-3 d-block"></i>
                        No products currently available.
                    </div>
                <?php else: ?>
                    <?php foreach($products as $p):
                        $qtyInCart = $cart[$p['product_id']] ?? 0;
                    ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="border rounded-3 h-100 d-flex flex-column bg-white overflow-hidden position-relative">

                            <?php if($qtyInCart > 0): ?>
                                <span class="badge bg-dark position-absolute" style="top:6px; left:6px; z-index:2; font-size:8px;">
                                    In Cart: <?= $qtyInCart ?>
                                </span>
                            <?php endif; ?>

                            <div class="position-relative" style="height:110px; background:#ffffff; display:flex; align-items:center; justify-content:center;">
                                <?php if($p['image_path']): ?>
                                    <img src="<?= base_url($p['image_path']) ?>" alt="<?= esc($p['name']) ?>" style="max-width:100%; max-height:100%; object-fit:contain;">
                                <?php else: ?>
                                    <i class="fas fa-box-open" style="font-size:26px; color:#ddd;"></i>
                                <?php endif; ?>
                                <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm position-absolute btn-view-product" data-id="<?= $p['product_id'] ?>" style="top:6px; right:6px; width:26px; height:26px; padding:0; font-size:10px;" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <div class="p-2 d-flex flex-column flex-grow-1">
                                <small class="text-muted" style="font-size:9px;"><?= esc($p['category_name']) ?></small>
                                <p class="fw-bold mb-1" style="font-size:11px; line-height:1.25;"><?= esc($p['name']) ?></p>
                                <p class="fw-bold text-maroon mb-2" style="font-size:13px;">
                                    ₱<?= number_format($p['sell_price'] ?? 0, 2) ?>
                                    <small class="text-muted fw-normal" style="font-size:8.5px;">/ <?= esc($p['unit']) ?></small>
                                </p>
                                <form action="<?= base_url('client/orders/add-to-cart') ?>" method="POST" class="mt-auto d-flex gap-1">
                                    <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                    <input type="number" name="qty" value="1" min="1" max="<?= $p['total_stock'] ?>" class="form-control form-control-sm" style="width:42px; font-size:10px; padding:2px 4px;">
                                    <button type="submit" class="btn btn-dark btn-sm rounded-pill flex-grow-1" style="font-size:9.5px; padding:3px 6px;">
                                        <i class="fas fa-cart-plus"></i> Add
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

<script>
    setTimeout(function() {
        ['flashError', 'flashWarning', 'flashSuccess'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }
        });
    }, 5000);
</script>
<script src="<?= base_url('public/js/client/browse.js') ?>"></script>
<?= view('partials/client/footer') ?>