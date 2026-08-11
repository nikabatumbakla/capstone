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
                <h5 class="fw-bold mb-0">Sales Returns</h5>
            </div>

            <div class="procurement-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Sales Returns</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Invoice Generation · Payment Tracking · Delivery Status · Returns</p>
            </div>

        
            <!-- 1. TOP BUTTONS -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <!-- FIXED: Targets must match Drawer IDs -->
                    <button class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold" data-bs-toggle="offcanvas" data-bs-target="#supplierReturnDrawer">Return to Supplier</button>
                    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="offcanvas" data-bs-target="#returnDrawer">+ New Client Return</button>
                </div>
            </div>

            <!-- 2. TABS -->
            <div class="bg-light p-1 rounded-pill d-inline-flex mb-4 border w-100 justify-content-between">
                <a href="?status=pending&type=<?= $active_type ?>" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_status == 'pending') ? 'btn-white shadow-sm fw-bold text-danger' : 'text-muted' ?>">⏳ Pending (<?= $total_rows ?>)</a>
                <a href="?status=approved&type=<?= $active_type ?>" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_status == 'approved') ? 'btn-white shadow-sm fw-bold text-success' : 'text-muted' ?>">✅ Approved</a>
                <a href="?status=rejected&type=<?= $active_type ?>" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_status == 'rejected') ? 'btn-white shadow-sm fw-bold text-danger' : 'text-muted' ?>">❌ Rejected</a>
                <a href="?status=all&type=<?= $active_type ?>" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_status == 'all') ? 'btn-white shadow-sm' : 'text-muted' ?>">All Returns</a>
            </div>

            <div class="custom-table-container border-0 shadow-sm">
                <!-- 3. SEARCH & TYPE TOGGLE -->
                <div class="row g-2 mb-4 align-items-center">
                    <div class="col-md-9 position-relative">
                        <form id="searchForm" action="" method="GET">
                            <input type="hidden" name="type" value="<?= $active_type ?>">
                            <input type="hidden" name="status" value="<?= $active_status ?>">
                            <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill ps-5 border" placeholder="Search return intelligence..." style="height:45px" value="<?= esc($search) ?>">
                            <i class="fas fa-search position-absolute text-muted" style="left:20px; top:15px"></i>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100 p-1 bg-light rounded-pill border">
                            <a href="?type=outbound" class="btn btn-xs rounded-pill <?= $active_type == 'outbound' ? 'btn-dark' : 'text-muted' ?>">Client List</a>
                            <a href="?type=inbound" class="btn btn-xs rounded-pill <?= $active_type == 'inbound' ? 'btn-dark' : 'text-muted' ?>">Supplier List</a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:10.5px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Return #</th><th>Order #</th><th>Entity</th><th>Product</th><th class="text-center">Qty</th><th>Reason</th><th>Requested</th><th>Status</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="returnTableBody">
                            <?php foreach($returns as $r): ?>
                            <tr>
                                <td class="ps-4 text-muted">RTN-0<?= $r['return_id'] ?></td>
                                <td class="fw-bold"><?= $r['order_number'] ?></td>
                                <td><?= $r['client_name'] ?></td>
                                <td><?= $r['product_name'] ?></td>
                                <td class="text-center fw-bold"><?= $r['quantity'] ?></td>
                                <td><span class="text-muted"><?= substr($r['reason'], 0, 15) ?>...</span></td>
                                <td><?= date('M d', strtotime($r['created_at'])) ?></td>
                                <td>
                                    <?php $b = ($r['status'] == 'approved') ? 'bg-success' : (($r['status'] == 'rejected') ? 'bg-danger' : 'bg-warning text-dark'); ?>
                                    <span class="badge rounded-pill <?= $b ?> px-3"><?= strtoupper($r['status']) ?></span>
                                </td>
                                <td class="text-center">
    <div class="d-flex gap-1 justify-content-center">
        <?php if($r['status'] == 'pending'): ?>
            <!-- UPDATED ACTION BUTTONS -->
            <a href="<?= base_url('admin/sales/approve-return/'.$r['return_id']) ?>" class="btn btn-xs btn-success rounded-2">✓ Approve</a>
            <a href="<?= base_url('admin/sales/reject-return/'.$r['return_id']) ?>" class="btn btn-xs btn-danger rounded-2 text-white">Reject</a>
        <?php endif; ?>
        <button class="btn btn-xs btn-outline-dark rounded-2 btn-view-return" data-id="<?= $r['return_id'] ?>">View</button>
    </div>
</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 4. PAGINATION -->
                <?php 
                    $startRange = ($total_rows > 0) ? (($current_page - 1) * $per_page) + 1 : 0;
                    $endRange   = min($current_page * $per_page, $total_rows);
                    $queryStr   = "&search=".urlencode($search)."&status=".$active_status."&type=".$active_type;
                ?>
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted small fw-bold">Showing <?= $startRange ?>-<?= $endRange ?> of <?= $total_rows ?> entries</span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $current_page-1 ?><?= $queryStr ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i=1; $i<=$total_pages; $i++): ?>
                            <li class="page-item <?= ($i==$current_page) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?><?= $queryStr ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $current_page+1 ?><?= $queryStr ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DRAWER: CLIENT RETURN REQUEST (OUTBOUND) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="returnDrawer" style="width: 700px;">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0">CLIENT RETURN REQUEST</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
        <h5 class="fw-bold mb-4" style="color: #b30000; font-size: 14px;">STOCK ADJUSTMENT FORM</h5>
        <form action="<?= base_url('admin/sales/process-return') ?>" method="POST">
            <div class="row g-3">
                <div class="col-6"><label class="formal-label">Original Sales Order *</label>
                    <select name="order_id" id="returnOrderSelect" class="form-select formal-input" required>
                        <option value="" disabled selected>Select Order</option>
                        <?php foreach($delivered_orders as $do): ?><option value="<?= $do['order_id'] ?>"><?= $do['order_number'] ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6"><label class="formal-label">Client (auto-filled)</label><input type="text" id="returnClientAuto" class="formal-input read-only-input" readonly></div>
                <div class="col-6"><label class="formal-label">Product to Return *</label><select name="product_id" id="returnProductSelect" class="form-select formal-input" required><option value="">Select product..</option></select></div>
                <div class="col-6"><label class="formal-label">Return Quantity *</label><input type="number" name="qty" class="formal-input" required></div>
                <div class="col-12"><label class="formal-label">Reason for Return *</label><select name="reason_cat" class="form-select formal-input"><option value="Damaged">Damaged Goods</option><option value="Expired">Expired</option></select></div>
                <div class="col-12"><label class="formal-label">Description / Details</label><textarea name="notes" class="formal-input" rows="4"></textarea></div>
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-save-adj w-100 py-3">✓ Save Adjustment</button></div>
        </form>
    </div>
</div>

<!-- DRAWER: SUPPLIER RETURN (INBOUND) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="supplierReturnDrawer" style="width: 700px; ">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0">SUPPLIER RETURN REQUEST</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
        <h5 class="fw-bold mb-4" style="color: #1a0505; font-size: 14px;">PROCUREMENT RETURN FORM</h5>
        <form action="<?= base_url('admin/procurement/save-return') ?>" method="POST">
             <div class="mb-3"><label class="formal-label">Reference PO #</label>
                <select name="po_id" class="form-select formal-input" required>
                    <?php foreach($received_pos as $po): ?><option value="<?= $po['po_id'] ?>"><?= $po['po_number'] ?> (<?= $po['sname'] ?>)</option><?php endforeach; ?>
                </select>
             </div>
             <div class="mb-3"><label class="formal-label">Defective Qty</label><input type="number" name="qty" class="formal-input" required></div>
             <div class="mb-3"><label class="formal-label">Reason</label><textarea name="notes" class="formal-input" rows="3" required></textarea></div>
             <button type="submit" class="btn btn-dark w-100 py-3 fw-bold">✓ PROCESS SUPPLIER RETURN</button>
        </form>
    </div>
</div>

<!-- DRAWER: VIEW RETURN INTELLIGENCE (NEWLY ADDED) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="viewReturnDrawer" style="width: 500px; ">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Return Intelligence Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="viewReturnContent"></div>
</div>

<script src="<?= base_url('public/js/admin/operations/sales/sales_returns.js') ?>"></script>
<?= view('partials/admin/footer') ?>