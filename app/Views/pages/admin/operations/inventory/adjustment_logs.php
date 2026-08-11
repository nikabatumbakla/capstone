<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <!-- Header & Back Button -->
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Stock Audit Trail</h5>
            </div>
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Inventory Management — Dual Direction</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Inbound • Outbound • POS — Real-Time Stock Tracking</p>
            </div>

            <!-- Unique Container -->
            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size: 14px;"><i class="fas fa-fingerprint me-2 text-maroon"></i>Modification History</h6>
                    
                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative filter-box bg-light rounded-pill px-2 border">
                            <input type="text" id="logSearch" class="form-control form-control-sm border-0 bg-transparent ps-4" placeholder="Search product or staff..." style="font-size: 11px; width: 250px; height: 35px;">
                            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 11px; font-size: 11px;"></i>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Timestamp</th>
                                <th>Product / SKU</th>
                                <th>Adjusted By</th>
                                <th class="text-center">Before</th>
                                <th class="text-center">After</th>
                                <th class="text-center">Difference</th>
                                <th>Reason</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="logTableBody">
                            <?php foreach($logs as $l): 
                                $diff = $l['qty_after'] - $l['qty_before'];
                                $diffClass = ($diff > 0) ? 'text-success' : 'text-danger';
                                $diffIcon = ($diff > 0) ? 'fa-caret-up' : 'fa-caret-down';
                            ?>
                            <tr>
                                <td class="ps-4 text-muted" style="font-size: 10px;"><?= date('M d, Y • h:i A', strtotime($l['adjusted_at'])) ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= $l['product_name'] ?></div>
                                    <small class="text-muted"><?= $l['sku'] ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= $l['staff_name'] ?></span></td>
                                <td class="text-center fw-bold text-muted"><?= $l['qty_before'] ?></td>
                                <td class="text-center fw-bold text-dark"><?= $l['qty_after'] ?></td>
                                <td class="text-center fw-bold <?= $diffClass ?>">
                                    <i class="fas <?= $diffIcon ?> me-1"></i><?= ($diff > 0) ? '+'.$diff : $diff ?>
                                </td>
                                <td><span class="badge rounded-pill bg-soft-maroon text-maroon"><?= $l['reason'] ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-dark rounded-pill px-3 btn-view-log" data-id="<?= $l['log_id'] ?>" style="font-size: 10px;">Details</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted fw-bold" style="font-size: 10px;">Audit Integrity Active</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 custom-pager">
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RIGHT SIDE LOG INTELLIGENCE DRAWER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="logDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-info-circle me-2"></i>Log Intelligence</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="logDrawerContent">
        <!-- Content loaded via AJAX -->
    </div>
</div>

<script src="<?= base_url('public/js/admin/operations/inventory/inventory_logs.js') ?>"></script>
<?= view('partials/admin/footer') ?>