<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Inventory Stock</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i>Real-Time Stock Monitoring</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Track available medical units, batches, and shelf-life status</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-3">
        <a href="?status=has_stock<?= $search ? '&search='.urlencode($search) : '' ?><?= $category_filter ? '&category='.$category_filter : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter=='has_stock'?'border-bottom border-3 border-maroon':'' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">ITEMS IN CATALOG</small>
                <h3 class="fw-bold mb-0"><?= $has_stock ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=no_stock<?= $search ? '&search='.urlencode($search) : '' ?><?= $category_filter ? '&category='.$category_filter : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter=='no_stock'?'border-bottom border-3 border-secondary':'' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">NO STOCK YET</small>
                <h3 class="fw-bold mb-0 text-secondary"><?= $no_stock ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=low_stock<?= $search ? '&search='.urlencode($search) : '' ?><?= $category_filter ? '&category='.$category_filter : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter=='low_stock'?'border-bottom border-3 border-danger':'' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">CRITICAL LOW STOCK</small>
                <h3 class="fw-bold mb-0 text-danger"><?= $low_stock ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?status=near_expiry<?= $search ? '&search='.urlencode($search) : '' ?><?= $category_filter ? '&category='.$category_filter : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $status_filter=='near_expiry'?'border-bottom border-3 border-warning':'' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">NEAR EXPIRY (6 MOS)</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $near_expiry ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
</div>

<?php if ($status_filter): ?>
<div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
    <span><strong>
        <?php
            $labels = ['has_stock' => 'Items With Stock', 'no_stock' => 'No Stock Yet', 'low_stock' => 'Critical Low Stock', 'near_expiry' => 'Near Expiry (6 Months)'];
            echo $labels[$status_filter] ?? strtoupper($status_filter);
        ?>
    </strong></span>
    <a href="?<?= $search ? 'search='.urlencode($search).'&' : '' ?><?= $category_filter ? 'category='.$category_filter : '' ?>" class="text-danger fw-bold text-decoration-none"> ×</a>
</div>
<?php endif; ?>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:12px"><i class="fas fa-list-ul me-2 text-maroon"></i>Current Stock Registry</h6>
                    
                    <form action="" method="GET" class="d-flex gap-2">
    <input type="hidden" name="status" value="<?= esc($status_filter) ?>">
    <select name="category" class="form-select form-select-sm rounded-pill border" style="width: 150px;" onchange="this.form.submit()">
    <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>" <?= ($category_filter == $cat['category_id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="position-relative">
                            <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill ps-4" placeholder="Search item or SKU..." style="width: 200px;" value="<?= esc($search) ?>">
                            <i class="fas fa-search position-absolute text-muted" style="left: 12px; top: 10px; font-size: 10px;"></i>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead class="table-dark">
    <tr><th class="ps-4">Product</th><th>Category</th><th>Batch No.</th><th class="text-center">Available</th><th>Sell Price</th><th>Expiry</th><th class="text-center">Status</th></tr>
</thead>
<tbody>
    <?php if(empty($inventory)): ?>
        <tr><td colspan="7" class="text-center py-5 text-muted">No matching stock records.</td></tr>
    <?php else: foreach($inventory as $item): ?>
    <tr>
        <td class="ps-4"><span class="fw-bold d-block"><?= esc($item['product_name']) ?></span><small class="text-muted"><?= esc($item['sku']) ?></small></td>
        <td><span class="badge bg-light text-dark border"><?= esc($item['cat_name']) ?></span></td>
        <td><code><?= $item['batch_id'] ? esc($item['batch_number']) : '—' ?></code></td>
        <td class="text-center fw-bold text-maroon" style="font-size:13px"><?= $item['quantity_avail'] ?? 0 ?> <small class="fw-normal text-muted"><?= esc($item['unit']) ?></small></td>
        <td class="fw-bold">₱<?= number_format($item['sell_price'] ?? 0, 2) ?></td>
        <td>
            <?php if($item['expires_at']): ?>
                <span class="<?= (strtotime($item['expires_at']) < strtotime('+3 months')) ? 'text-danger fw-bold' : '' ?>"><?= date('M Y', strtotime($item['expires_at'])) ?></span>
            <?php else: ?><span class="text-muted">N/A</span><?php endif; ?>
        </td>
        <td class="text-center">
            <?php if(!$item['batch_id']): ?>
                <button class="btn btn-sm btn-success rounded-circle btn-add-batch" title="No Stock — Add Batch"
                    data-product-id="<?= $item['product_id'] ?>"
                    data-product-name="<?= esc($item['product_name']) ?>"
                    data-unit="<?= esc($item['unit']) ?>"
                    style="width:34px; height:34px;">
                    <i class="fas fa-plus"></i>
                </button>
            <?php elseif($item['quantity_avail'] <= $item['reorder_level']): ?>
                <button class="btn btn-sm btn-warning text-dark rounded-circle btn-view-details" title="Restock Required — View Details"
                    data-id="<?= $item['batch_id'] ?>" style="width:34px; height:34px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </button>
            <?php else: ?>
    <button class="btn btn-sm btn-success rounded-circle btn-view-details" title="Available — View Details"
        data-id="<?= $item['batch_id'] ?>" style="width:34px; height:34px;">
        <i class="fas fa-eye"></i>
    </button>
<?php endif; ?>
        </td>
    </tr>
    <?php endforeach; endif; ?>
</tbody>

                    </table>
                </div>

                <?php
                    $q = '&category='.$category_filter.'&search='.urlencode($search).'&status='.$status_filter;
                    $w=3; $cb=(int)ceil($current_page/$w); $ws=(($cb-1)*$w)+1; $we=min($ws+$w-1,$total_pages);
                ?>
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted fw-bold" style="font-size:10px;">Page <?= $current_page ?> of <?= $total_pages ?></span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i=$ws;$i<=$we;$i++): ?><li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li><?php endfor; ?>
                        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="detailsDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom bg-light"><h6 class="offcanvas-title fw-bold">Item Details</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body" id="drawerContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="adjustDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom bg-light"><h6 class="offcanvas-title fw-bold">Stock Adjustment Form</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4" id="adjustContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="newBatchDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom bg-light"><h6 class="offcanvas-title fw-bold">Record New Stock Batch</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4" id="newBatchContent"></div>
</div>

<script src="<?= base_url('public/js/staff/inventory/inventory.js') ?>"></script>
<?= view('partials/staff/footer') ?>