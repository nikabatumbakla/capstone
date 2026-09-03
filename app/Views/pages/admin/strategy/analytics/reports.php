<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Reports & Analytics</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Sales · Inventory · Expiry Waste · Supplier Performance — PDF/CSV Export</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">REVENUE (THIS MONTH)</small>
                        <h4 class="fw-bold mb-0">₱<?= number_format($total_revenue, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">STOCK VALUATION</small>
                        <h4 class="fw-bold mb-0 text-primary">₱<?= number_format($inventory_value, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">EXPIRY LOSS (WASTE)</small>
                        <h4 class="fw-bold mb-0 text-danger">₱<?= number_format($expiry_waste, 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">AUDIT TRAIL</small>
                        <h4 class="fw-bold mb-0 text-success"><?= number_format($audit_log_count) ?></h4>
                        <small class="text-muted"><?= $last_audit_time ? 'Last logged: ' . date('M d, g:i A', strtotime($last_audit_time)) : 'No activity yet' ?></small>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-file-export me-2 text-maroon"></i>Official Document Exports</h6>
            <div class="row g-4 mb-4">
                <?php
                $reports = [
                    ['sales', 'Sales Analytics', 'By client, product, and period'],
                    ['inventory', 'Inventory Movement', 'Stock-in, stock-out logs'],
                    ['waste', 'Expiry Waste', 'Monetary value of expired items'],
                    ['supplier', 'Supplier Performance', 'Accuracy and lead time data'],
                    ['pos', 'Walk-In POS', 'Daily/Weekly retail summary'],
                    ['dss', 'Predictive Analytics', 'Reorder status by product']
                ];
                foreach($reports as $r): ?>
                <div class="col-lg-4">
                    <div class="custom-table-container">
                        <h6 class="fw-bold mb-1" style="font-size:12px;"><?= $r[1] ?></h6>
                        <p class="text-muted mb-3" style="font-size:10px;"><?= $r[2] ?></p>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('admin/strategy/analytics/export/'.$r[0].'/pdf') ?>" target="_blank" class="btn btn-dark flex-grow-1 py-1 rounded-pill"><i class="fas fa-file-pdf me-1"></i> PDF</a>
                            <a href="<?= base_url('admin/strategy/analytics/export/'.$r[0].'/csv') ?>" class="btn btn-outline-dark flex-grow-1 py-1 rounded-pill"><i class="fas fa-file-csv me-1"></i> CSV</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:12px;"><i class="fas fa-table me-2 text-maroon"></i>Stock Movement Log</h6>
                    <form id="filterForm" action="" method="GET" class="d-flex gap-2">
                        <select name="movement" id="movementFilter" class="form-select form-select-sm" style="width:150px;">
                            <option value="">All Movements</option>
                            <option value="inbound" <?= $movement_filter=='inbound'?'selected':'' ?>>Inbound</option>
                            <option value="outbound" <?= $movement_filter=='outbound'?'selected':'' ?>>Outbound</option>
                            <option value="pos_sale" <?= $movement_filter=='pos_sale'?'selected':'' ?>>POS Sale</option>
                            <option value="adjustment" <?= $movement_filter=='adjustment'?'selected':'' ?>>Adjustment</option>
                            <option value="return_inbound" <?= $movement_filter=='return_inbound'?'selected':'' ?>>Return (Inbound)</option>
                        </select>
                        <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill" placeholder="Search product/SKU..." style="width:180px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th class="ps-4">Timestamp</th><th>Product</th><th>Movement</th><th class="text-center">Qty</th><th>Handled By</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if(empty($reports_data)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No movement logs found.</td></tr>
                            <?php else: foreach($reports_data as $row): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= date('M d, h:i A', strtotime($row['moved_at'])) ?></td>
                                <td><span class="fw-bold"><?= esc($row['pname']) ?></span><br><small class="text-muted"><?= esc($row['sku']) ?></small></td>
                                <td>
                                    <?php $badgeMap = ['inbound'=>'bg-success','outbound'=>'bg-secondary','pos_sale'=>'bg-primary','adjustment'=>'bg-warning text-dark','return_inbound'=>'bg-info text-dark']; ?>
                                    <span class="badge <?= $badgeMap[$row['movement_type']] ?? 'bg-light text-dark border' ?> px-3"><?= strtoupper(str_replace('_',' ',$row['movement_type'])) ?></span>
                                </td>
                                <td class="text-center fw-bold"><?= abs($row['quantity']) ?></td>
                                <td class="text-muted"><?= esc($row['staff'] ?? 'System') ?></td>
                                <td class="text-center"><button class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-view-movement" data-id="<?= $row['movement_id'] ?>">View</button></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $rangeStart = $total_rows > 0 ? (($current_page-1)*$per_page)+1 : 0;
                    $rangeEnd = min($current_page*$per_page, $total_rows);
                    $movQuery = $movement_filter ? '&movement='.$movement_filter : '';
                    $searchQuery = $search ? '&search='.urlencode($search) : '';
                    $pageQuery = $movQuery . $searchQuery;
                    $windowSize=3; $currentBlock=(int)ceil($current_page/$windowSize);
                    $windowStart=(($currentBlock-1)*$windowSize)+1; $windowEnd=min($windowStart+$windowSize-1,$total_pages);
                ?>
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted fw-bold">Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> entries</span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$pageQuery ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i=$windowStart;$i<=$windowEnd;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$pageQuery ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$pageQuery ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="movementDrawer" style="width:450px;">
    <div class="offcanvas-header border-bottom bg-light"><h6 class="fw-bold mb-0">Movement Details</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body" id="movementContent"></div>
</div>

<script src="<?= base_url('public/js/admin/strategy/reports.js') ?>"></script>
<?= view('partials/admin/footer') ?>