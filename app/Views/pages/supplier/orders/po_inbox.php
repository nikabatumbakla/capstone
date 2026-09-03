<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

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
                <p class="mb-0 opacity-75" style="font-size: 10px;">Review and acknowledge incoming orders from Robin Rose Trading</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <a href="?tab=pending" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $active_tab=='pending'?'border-bottom border-3 border-warning':'' ?>">
                            <small class="text-muted fw-bold d-block mb-1">AWAITING ACKNOWLEDGMENT</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $count_pending ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?tab=in_progress" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $active_tab=='in_progress'?'border-bottom border-3 border-primary':'' ?>">
                            <small class="text-muted fw-bold d-block mb-1">IN PROGRESS</small>
                            <h3 class="fw-bold mb-0 text-primary"><?= $count_in_progress ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?tab=history" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $active_tab=='history'?'border-bottom border-3 border-success':'' ?>">
                            <small class="text-muted fw-bold d-block mb-1">COMPLETED</small>
                            <h3 class="fw-bold mb-0 text-success"><?= $count_completed ?></h3>
                        </div>
                    </a>
                </div>
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
                        <thead class="table-dark"><tr><th class="ps-4">PO #</th><th>Items</th><th class="text-center">Total Qty</th><th>Expected</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($pos)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No orders in this category.</td></tr>
                            <?php else:
                                $statusMeta = ['sent' => 'bg-warning text-dark', 'acknowledged' => 'bg-primary', 'in_transit' => 'bg-info', 'received' => 'bg-success', 'partial' => 'bg-warning text-dark', 'cancelled' => 'bg-secondary'];
                                foreach($pos as $po): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= esc($po['po_number']) ?></td>
                                <td><?= $po['item_count'] ?></td>
                                <td class="text-center"><?= $po['total_qty'] ?></td>
                                <td><?= $po['expected_date'] ? date('M d, Y', strtotime($po['expected_date'])) : '—' ?></td>
                                <td><span class="badge <?= $statusMeta[$po['status']] ?? 'bg-secondary' ?> px-3"><?= strtoupper($po['status']) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-dark rounded-pill px-3 btn-view-po" data-id="<?= $po['po_id'] ?>">
                                        <?= $po['status'] === 'sent' ? 'Review & Acknowledge' : 'View' ?>
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

<script src="<?= base_url('public/js/supplier/po_inbox.js') ?>"></script>
<?= view('partials/supplier/footer') ?>