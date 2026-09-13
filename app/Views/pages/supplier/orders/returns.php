<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Supplier Returns</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-undo-alt me-2"></i>Returns Sent to You</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Items Robin Rose Trading is returning — reasons, status, and exchange progress</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small" id="flashError"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <a href="?status=pending" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $status_filter=='pending'?'border-bottom border-3 border-warning':'' ?>">
                            <small class="text-muted fw-bold d-block mb-1">PENDING REVIEW</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $count_pending ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?status=approved" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $status_filter=='approved'?'border-bottom border-3 border-success':'' ?>">
                            <small class="text-muted fw-bold d-block mb-1">APPROVED</small>
                            <h3 class="fw-bold mb-0 text-success"><?= $count_approved ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="?status=rejected" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $status_filter=='rejected'?'border-bottom border-3 border-secondary':'' ?>">
                            <small class="text-muted fw-bold d-block mb-1">REJECTED</small>
                            <h3 class="fw-bold mb-0 text-secondary"><?= $count_rejected ?></h3>
                        </div>
                    </a>
                </div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;">Return Records</h6>
                    <form action="" method="GET">
                        <input type="hidden" name="status" value="<?= esc($status_filter) ?>">
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search PO #..." style="width:200px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">PO #</th><th>Product</th><th class="text-center">Qty</th><th>Origin</th><th>Progress</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($returns)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No return records.</td></tr>
                            <?php else:
                                $statusMeta = ['pending' => 'bg-warning text-dark', 'approved' => 'bg-success', 'rejected' => 'bg-secondary'];
                                foreach($returns as $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= esc($r['po_number']) ?></td>
                                <td><?= esc($r['product_name']) ?><br><small class="text-muted"><?= esc($r['barcode_value'] ?: '—') ?></small></td>
                                <td class="text-center"><?= $r['quantity'] ?></td>
                                <td>
                                    <?php if(($r['source'] ?? 'manual') === 'grr_discrepancy'): ?>
                                        <span class="badge bg-info text-dark">Flagged at Receiving</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Manual Request</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($r['status'] === 'approved' && $r['supplier_return_status'] === 'sent_to_supplier'): ?>
                                        <span class="badge bg-warning text-dark">Awaiting Your Confirmation</span>
                                    <?php elseif($r['status'] === 'approved' && $r['supplier_return_status'] === 'received_by_supplier'): ?>
                                        <span class="badge bg-success">Confirmed Received</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $statusMeta[$r['status']] ?? 'bg-secondary' ?> px-3"><?= strtoupper($r['status']) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-dark rounded-2 btn-view-return" data-id="<?= $r['return_id'] ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
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
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="viewReturnDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Return Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="viewReturnContent"></div>
</div>

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
<script src="<?= base_url('public/js/supplier/returns.js') ?>"></script>
<?= view('partials/supplier/footer') ?>