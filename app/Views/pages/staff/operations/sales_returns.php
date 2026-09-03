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
                <h5 class="fw-bold mb-0">Sales Returns</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-undo-alt me-2"></i>Sales Returns</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Submit a return request — an admin will review and approve before inventory is restored.</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <a href="?status=pending<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $status_filter=='pending'?'border-bottom border-3 border-warning':'' ?>">
                            <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">PENDING APPROVAL</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $count_pending ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?status=approved<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $status_filter=='approved'?'border-bottom border-3 border-success':'' ?>">
                            <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">APPROVED</small>
                            <h3 class="fw-bold mb-0 text-success"><?= $count_approved ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?status=rejected<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $status_filter=='rejected'?'border-bottom border-3 border-danger':'' ?>">
                            <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">REJECTED</small>
                            <h3 class="fw-bold mb-0 text-danger"><?= $count_rejected ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
            </div>

            <?php if ($status_filter): ?>
            <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
                <span><strong>
                    <?php
                        $labels = ['pending' => 'Pending Approval', 'approved' => 'Approved Returns', 'rejected' => 'Rejected Returns'];
                        echo $labels[$status_filter] ?? strtoupper($status_filter);
                    ?>
                </strong></span>
                <a href="?<?= $search ? 'search='.urlencode($search) : '' ?>" class="text-danger fw-bold text-decoration-none"> ×</a>
            </div>
            <?php endif; ?>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-undo-alt me-2 text-maroon"></i>My Submitted Returns</h6>
                    <div class="d-flex gap-2">
                        <form id="filterForm" action="" method="GET">
                            <input type="hidden" name="status" value="<?= esc($status_filter) ?>">
                            <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill" placeholder="Search order # or client..." style="width:200px;" value="<?= esc($search) ?>">
                        </form>
                        <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="offcanvas" data-bs-target="#returnDrawer">
                            <i class="fas fa-plus me-1"></i>Process Return
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">Date</th><th>Reference SO</th><th>Institution</th><th>Product</th><th class="text-center">Qty</th><th>Reason</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if(empty($returns)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No returns found.</td></tr>
                            <?php else:
                                $statusMeta = ['pending' => 'bg-warning text-dark', 'approved' => 'bg-success', 'rejected' => 'bg-danger'];
                                foreach($returns as $r): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                <td class="fw-bold"><?= esc($r['order_number']) ?></td>
                                <td><?= esc($r['organization']) ?></td>
                                <td><?= esc($r['product_name'] ?? '—') ?></td>
                                <td class="text-center fw-bold"><?= $r['quantity'] ?></td>
                                <td><span class="text-muted"><?= esc(substr($r['reason'], 0, 30)) ?>...</span></td>
                                <td><span class="badge <?= $statusMeta[$r['status']] ?? 'bg-secondary' ?> px-3"><?= strtoupper($r['status']) ?></span></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $q = '&status='.$status_filter.'&search='.urlencode($search);
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="returnDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="fw-bold mb-0"><i class="fas fa-undo-alt me-2"></i>Process Client Return</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('staff/operations/process-return') ?>" method="POST">
            <div class="row g-3">
                <div class="col-6">
                    <label class="formal-label">Original Sales Order *</label>
                    <select name="order_id" id="returnOrderSelect" class="form-select formal-input" required>
                        <option value="" disabled selected>Select Order</option>
                        <?php foreach($eligible_orders as $eo): ?>
                            <option value="<?= $eo['order_id'] ?>"><?= esc($eo['order_number']) ?> — <?= esc($eo['organization']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Client (auto-filled)</label>
                    <input type="text" id="returnClientAuto" class="formal-input read-only-input" readonly>
                </div>
                <div class="col-6">
                    <label class="formal-label">Product to Return *</label>
                    <select name="product_id" id="returnProductSelect" class="form-select formal-input" required>
                        <option value="">Select order first</option>
                    </select>
                    <input type="hidden" name="batch_id" id="returnBatchId">
                </div>
                <div class="col-6">
                    <label class="formal-label">Return Quantity *</label>
                    <input type="number" name="qty" class="formal-input" min="1" required>
                </div>
                <div class="col-6">
                    <label class="formal-label">Item Condition *</label>
                    <select name="restock_condition" class="form-select formal-input" required>
                        <option value="resellable">Resellable</option>
                        <option value="damaged">Damaged</option>
                        <option value="expired">Expired</option>
                        <option value="disposed">Disposed / Write-off</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="formal-label">Reason for Return *</label>
                    <select name="reason_cat" class="form-select formal-input">
                        <option value="Damaged">Damaged Goods</option>
                        <option value="Expired">Expired</option>
                        <option value="Wrong Item">Wrong Item Delivered</option>
                        <option value="Excess">Excess Quantity</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="formal-label">Description / Details *</label>
                    <textarea name="notes" class="formal-input" rows="4" required></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-save-adj w-100 py-3"><i class="fas fa-paper-plane me-2"></i>Submit for Approval</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('public/js/staff/operations/sales_returns.js') ?>"></script>
<?= view('partials/staff/footer') ?>