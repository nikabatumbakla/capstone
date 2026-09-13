<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>
    const CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
    const CSRF_HASH = "<?= csrf_hash() ?>";
</script>

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Purchase Order Inbox</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-inbox me-2"></i>Purchase Orders</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Review, acknowledge, or decline incoming orders from Robin Rose Trading</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small" id="flashError"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <a href="?tab=pending<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $active_tab=='pending'?'border-bottom border-3 border-warning':'' ?>">
                            <i class="fas fa-hourglass-half position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">AWAITING ACKNOWLEDGMENT</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $count_pending ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?tab=in_progress<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $active_tab=='in_progress'?'border-bottom border-3 border-primary':'' ?>">
                            <i class="fas fa-truck position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">IN PROGRESS</small>
                            <h3 class="fw-bold mb-0 text-primary"><?= $count_in_progress ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?tab=history<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
                        <div class="inventory-kpi-card position-relative <?= $active_tab=='history'?'border-bottom border-3 border-success':'' ?>">
                            <i class="fas fa-check-double position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                            <small class="text-muted fw-bold d-block mb-1">COMPLETED / HISTORY</small>
                            <h3 class="fw-bold mb-0 text-success"><?= $count_completed ?></h3>
                            <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                        </div>
                    </a>
                </div>
            </div>

            <?php
                $tabLabels = ['pending' => 'Awaiting Acknowledgment', 'in_progress' => 'In Progress Orders', 'history' => 'Completed / History'];
            ?>
            <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 12px;">
                <span><strong><?= $tabLabels[$active_tab] ?? 'Awaiting Acknowledgment' ?></strong></span>
                <a href="?" class="text-danger fw-bold text-decoration-none">×</a>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;">Order Queue</h6>
                    <form action="" method="GET">
                        <input type="hidden" name="tab" value="<?= esc($active_tab) ?>">
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search PO #..." style="width:200px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
    <tr>
        <th class="ps-4">PO #</th>
        <th>Date Sent</th>
        <th>Items</th>
        <th class="text-center">Total Qty</th>
        <th>Expected</th>
        <th>Status</th>
        <th>Payment</th>
        <th class="text-center">Action</th>
    </tr>
</thead>
<tbody>
    <?php if(empty($pos)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted">No orders in this category.</td></tr>
    <?php else:
        $statusMeta = ['sent' => 'bg-warning text-dark', 'acknowledged' => 'bg-primary', 'in_transit' => 'bg-info', 'received' => 'bg-success', 'partial' => 'bg-warning text-dark', 'cancelled' => 'bg-secondary'];
        $today = date('Y-m-d');
        foreach($pos as $po):
            $isUnresolved = in_array($po['status'], ['sent', 'acknowledged', 'in_transit']);
            $daysUntilDue = $po['expected_date'] ? (strtotime($po['expected_date']) - strtotime($today)) / 86400 : null;
            $isOverdue = $isUnresolved && $daysUntilDue !== null && $daysUntilDue < 0;
            $isDueSoon = $isUnresolved && $daysUntilDue !== null && $daysUntilDue >= 0 && $daysUntilDue <= 2;
            $isPaid = ($po['payment_status'] ?? 'unpaid') === 'paid';
        ?>
    <tr>
        <td class="ps-4 fw-bold">
    <?= esc($po['po_number']) ?>
    <?php if(!empty($po->replacement_for_return_id)): ?>
    <div class="alert alert-info d-flex align-items-start gap-2 mb-4" style="font-size: 11px;">
        <i class="fas fa-exchange-alt mt-1"></i>
        <div><b>Replacement Delivery</b><br>This order replaces defective/incorrect items from Return #SRT-<?= str_pad($po->replacement_for_return_id, 4, '0', STR_PAD_LEFT) ?> — no payment is required, it's a straight exchange.</div>
    </div>
<?php endif; ?>
</td>
        <td class="text-muted"><?= date('M d, Y', strtotime($po['created_at'])) ?></td>
        <td><?= $po['item_count'] ?></td>
        <td class="text-center"><?= $po['total_qty'] ?></td>
        <td>
            <?= $po['expected_date'] ? date('M d, Y', strtotime($po['expected_date'])) : '—' ?>
            <?php if($isOverdue): ?>
                <span class="badge bg-danger ms-1" style="font-size:8px;">OVERDUE</span>
            <?php elseif($isDueSoon): ?>
                <span class="badge bg-warning text-dark ms-1" style="font-size:8px;">DUE SOON</span>
            <?php endif; ?>
        </td>
        <td><span class="badge <?= $statusMeta[$po['status']] ?? 'bg-secondary' ?> px-3"><?= strtoupper($po['status']) ?></span></td>
        <td>
            <?php if($po['status'] === 'acknowledged' || $po['status'] === 'in_transit'): ?>
                <?php if($isPaid): ?>
                    <span class="badge bg-success">PAID</span>
                <?php else: ?>
                    <span class="badge bg-secondary" title="Robin Rose Trading has not completed payment yet">AWAITING PAYMENT</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="text-muted">—</span>
            <?php endif; ?>
        </td>
        <td class="text-center">
            <button class="btn btn-xs btn-dark rounded-pill px-3 btn-view-po" data-id="<?= $po['po_id'] ?>">
                <?= $po['status'] === 'sent' ? 'Review & Respond' : 'View' ?>
            </button>
        </td>
    </tr>
    <?php endforeach; endif; ?>
</tbody>
                    </table>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-4">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <?php for($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&tab=<?= $active_tab ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="poDrawer" style="width:550px;"><div class="offcanvas-body p-0" id="poDrawerContent"></div></div>

<script>
    setTimeout(function() {
        ['flashError', 'flashSuccess'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }
        });
    }, 5000);
</script>
<script src="<?= base_url('public/js/supplier/po_inbox.js') ?>"></script>
<?= view('partials/supplier/footer') ?>