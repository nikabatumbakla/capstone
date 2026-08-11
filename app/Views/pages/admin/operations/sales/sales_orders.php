<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">

            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Sales Order</h5>
            </div>

            <div class="procurement-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Sales Order Management — Outbound</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Invoice Generation · Payment Tracking · Delivery Status · Returns</p>
            </div>

            <div class="custom-table-container">
                <!-- AUTOMATIC SEARCH & FILTER FORM -->
                <form id="filterForm" action="" method="GET" class="row g-2 mb-4 align-items-center">
                    <div class="col-md-8 position-relative">
                        <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill ps-5 border" placeholder="Search orders..." style="height:45px" value="<?= esc($search) ?>">
                        <i class="fas fa-search position-absolute text-muted" style="left:20px; top:15px"></i>
                    </div>
                    <div class="col-md-2">
                        <select name="type" id="typeFilter" class="form-select form-select-sm rounded-pill border" style="height:45px">
                            <option value="">All Categories</option>
                            <option value="school" <?= ($type_filter == 'school') ? 'selected' : '' ?>>Schools</option>
                            <option value="hospital" <?= ($type_filter == 'hospital') ? 'selected' : '' ?>>Hospitals</option>
                            <option value="barangay" <?= ($type_filter == 'barangay') ? 'selected' : '' ?>>Barangays</option>
                            <option value="lgu" <?= ($type_filter == 'lgu') ? 'selected' : '' ?>>LGU Units</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-dark w-100 rounded-pill fw-bold" style="height:45px" data-bs-toggle="offcanvas" data-bs-target="#newOrderDrawer">+ New Sales Order</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:11px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Order #</th><th>Client</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Delivery</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($orders)): ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted">No records found.</td></tr>
                            <?php else: ?>
                                <?php foreach($orders as $o): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= $o['order_number'] ?></td>
                                    <td><?= $o['client_name'] ?></td>
                                    <td><?= $o['item_count'] ?> items</td>
                                    <td class="fw-bold">₱<?= number_format($o['total'], 2) ?></td>
                                    <td><span class="fw-bold" style="color:<?= $o['payment_status']=='paid'?'#27ae60':'#e74c3c' ?>"><?= strtoupper($o['payment_status']) ?></span></td>
                                    <td><span class="badge rounded-pill bg-soft-maroon text-maroon px-3"><?= ucwords($o['status']) ?></span></td>
                                    <td><?= date('M d', strtotime($o['created_at'])) ?></td>
                                    <td class="text-center"><button class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-view-so" data-id="<?= $o['order_id'] ?>">View</button></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ACCURATE PAGINATION RANGE -->
                <?php 
                    $startRange = ($total_rows > 0) ? (($current_page - 1) * $per_page) + 1 : 0;
                    $endRange   = min($current_page * $per_page, $total_rows);
                    $queryStr   = "&search=".urlencode($search)."&type=".urlencode($type_filter);
                ?>
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted small fw-bold">Showing <?= $startRange ?>-<?= $endRange ?> of <?= $total_rows ?> orders</span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $current_page - 1 ?><?= $queryStr ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?><?= $queryStr ?>"><?= $i ?></a></li><?php endfor; ?>
                        <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $current_page + 1 ?><?= $queryStr ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: CREATE INSTITUTIONAL SALES ORDER (FIGMA MATCH) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="newOrderDrawer" style="width: 700px;">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold"><i class="fas fa-plus me-2"></i>New Sales Order</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/sales/save-order') ?>" method="POST">
            <h6 class="text-maroon fw-bold mb-4">STOCK ADJUSTMENT FORM</h6> <!-- Matching Figma Header Style -->
            <div class="row g-3">
                <div class="col-6">
                    <label class="formal-label">Institutional Client *</label>
                    <select name="client_id" class="form-select formal-input" required>
                        <option value="">Select Institution</option>
                        <?php foreach($clients as $c): ?><option value="<?= $c['client_id'] ?>"><?= $c['organization'] ?> (<?= ucfirst($c['client_type']) ?>)</option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Delivery Address</label>
                    <input type="text" name="address" class="formal-input" placeholder="Iriga City, Cam Sur">
                </div>
                <div class="col-6">
                    <label class="formal-label">Product</label>
                    <select name="items[]" class="form-select formal-input">
                        <?php foreach($products as $p): ?><option value="<?= $p['product_id'] ?>"><?= $p['name'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Quantity</label>
                    <input type="number" name="qtys[]" class="formal-input" value="10">
                </div>
                <div class="col-6">
                    <label class="formal-label">Payment Method</label>
                    <select name="payment_method" class="form-select formal-input">
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="check">Check</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Discount (%)</label>
                    <input type="number" name="discount" class="formal-input" value="0">
                </div>
            </div>
            
            <div class="mt-4"><p class="text-maroon fw-bold small border-bottom pb-1">ORDER ITEMS</p></div>
            <button type="submit" class="btn btn-dark py-2 px-4 mt-4 fw-bold" style="background:#0d2e4f">Create Sales Order + Generate Invoice</button>
        </form>
    </div>
</div>

<!-- VIEW DRAWER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="soDrawer" style="width: 600px;"><div class="offcanvas-body p-0" id="soDrawerContent"></div></div>

<script src="<?= base_url('public/js/admin/operations/sales/sales_orders.js') ?>"></script>
<?= view('partials/admin/footer') ?>