<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Adjustment Logs</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-history me-2"></i>Stock Adjustment History</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">View · Create · Track Corrections — Batch-Level Audit Trail</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">MY TOTAL ADJUSTMENTS</small><h3 class="fw-bold mb-0"><?= $total_adjustments ?></h3></div></div>
                <div class="col-md-4"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">TODAY</small><h3 class="fw-bold mb-0 text-primary"><?= $today_adjustments ?></h3></div></div>
                <div class="col-md-4"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">DAMAGE / EXPIRED</small><h3 class="fw-bold mb-0 text-danger"><?= $damage_expired_count ?></h3></div></div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-list-ul me-2 text-maroon"></i>My Adjustment History</h6>
                    <div class="d-flex gap-2">
                        <form id="filterForm" action="" method="GET" class="d-flex gap-2">
                            <select name="reason" class="form-select form-select-sm" style="width:150px;">
                                <option value="">All Reasons</option>
                                <option value="Physical Count" <?= $reason_filter=='Physical Count'?'selected':'' ?>>Physical Count</option>
                                <option value="Damage" <?= $reason_filter=='Damage'?'selected':'' ?>>Damaged Goods</option>
                                <option value="Expired" <?= $reason_filter=='Expired'?'selected':'' ?>>Expired Stock</option>
                            </select>
                            <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill" placeholder="Search product..." style="width:180px;" value="<?= esc($search) ?>">
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th class="ps-4">Log #</th><th>Product</th><th class="text-center">Before</th><th class="text-center">After</th><th class="text-center">Diff</th><th>Reason</th><th class="text-end pe-4">Date</th></tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No adjustment history found.</td></tr>
                            <?php else: foreach($logs as $l):
                                $diff = $l['qty_after'] - $l['qty_before'];
                                $diffClass = ($diff > 0) ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted');
                            ?>
                            <tr>
                                <td class="ps-4 text-muted">ADJ-<?= str_pad($l['log_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="fw-bold text-dark"><?= esc($l['product_name']) ?></td>
                                <td class="text-center text-muted"><?= $l['qty_before'] ?></td>
                                <td class="text-center fw-bold"><?= $l['qty_after'] ?></td>
                                <td class="text-center fw-bold <?= $diffClass ?>"><?= ($diff > 0) ? '+'.$diff : $diff ?></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($l['reason']) ?></span></td>
                                <td class="text-end pe-4 text-muted"><?= date('M d, h:i A', strtotime($l['adjusted_at'])) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $q = '&reason='.urlencode($reason_filter).'&search='.urlencode($search);
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


<script src="<?= base_url('public/js/staff/inventory/inventory_logs.js') ?>"></script>
<?= view('partials/staff/footer') ?>