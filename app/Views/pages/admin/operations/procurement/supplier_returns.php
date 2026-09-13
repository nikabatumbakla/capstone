<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">

        <div class="d-flex align-items-center mb-4">
            <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
            <h5 class="fw-bold mb-0">Supplier Returns</h5>
        </div>

        <div class="procurement-banner mb-4 p-3 text-white shadow-sm">
            <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Supplier Return Management</h6>
            <p class="mb-0 opacity-75" style="font-size: 10px;">Auto-filed from Goods Receipt inspection → Approve → Return to Supplier → Replacement</p>
        </div>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-2 small" id="flashError"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <div class="bg-light p-1 rounded-pill d-inline-flex mb-4 border w-100 justify-content-between">
            <a href="?status=pending" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_status == 'pending') ? 'btn-white shadow-sm fw-bold text-warning' : 'text-muted' ?>"><i class="fas fa-hourglass-half me-1"></i>Pending</a>
            <a href="?status=approved" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_status == 'approved') ? 'btn-white shadow-sm fw-bold text-success' : 'text-muted' ?>"><i class="fas fa-check-circle me-1"></i>Approved</a>
            <a href="?status=rejected" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_status == 'rejected') ? 'btn-white shadow-sm fw-bold text-danger' : 'text-muted' ?>"><i class="fas fa-times-circle me-1"></i>Rejected</a>
            <a href="?status=all" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_status == 'all') ? 'btn-white shadow-sm' : 'text-muted' ?>"><i class="fas fa-list me-1"></i>All</a>
        </div>

        <div class="custom-table-container border-0 shadow-sm">
            <div class="d-flex justify-content-end mb-4">
                <form id="searchForm" action="" method="GET" class="position-relative">
                    <input type="hidden" name="status" value="<?= $active_status ?>">
                    <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill ps-5 border" placeholder="Search supplier or PO #..." style="height:40px; width:260px;" value="<?= esc($search) ?>">
                    <i class="fas fa-search position-absolute text-muted" style="left:18px; top:12px; font-size:11px;"></i>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" style="font-size:10.5px">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Return #</th><th>PO #</th><th>Supplier</th><th>Product</th><th class="text-center">Qty</th><th>Resolution</th><th>Return Progress</th><th>Requested</th><th>Status</th><th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $progressMeta = [
                                'not_sent'          => ['label' => '—', 'class' => 'text-muted'],
                                'sent_to_supplier'  => ['label' => 'Sent — Awaiting Supplier', 'class' => 'bg-secondary'],
                                'received_by_supplier' => ['label' => 'Received by Supplier', 'class' => 'bg-info text-dark'],
                            ];
                        ?>
                        <?php if(empty($returns)): ?>
                            <tr><td colspan="10" class="text-center py-5 text-muted">No returns found.</td></tr>
                        <?php else: foreach($returns as $r):
                            $pm = $progressMeta[$r['supplier_return_status'] ?? 'not_sent'] ?? $progressMeta['not_sent'];
                        ?>
                        <tr>
                            <td class="ps-4 text-muted">SRT-<?= str_pad($r['return_id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td class="fw-bold"><?= $r['po_number'] ?></td>
                            <td><?= $r['supplier_name'] ?></td>
                            <td><?= $r['product_name'] ?></td>
                            <td class="text-center fw-bold"><?= $r['quantity'] ?></td>
                            <td>
                                <?php if(($r['resolution_type'] ?? 'exchange') === 'exchange'): ?>
                                    <span class="badge bg-info text-dark">Exchange</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">Refund</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($r['status'] === 'approved'): ?>
                                    <span class="badge <?= $pm['class'] ?>"><?= $pm['label'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <?php $b = ($r['status'] == 'approved') ? 'bg-success' : (($r['status'] == 'rejected') ? 'bg-danger' : 'bg-warning text-dark'); ?>
                                <span class="badge rounded-pill <?= $b ?> px-3"><?= strtoupper($r['status']) ?></span>
                            </td>
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

            <?php
                $rangeStart  = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
                $rangeEnd    = min($current_page * $per_page, $total_rows);
                $searchQuery = $search !== '' ? '&search=' . urlencode($search) : '';
                $pageQuery   = '&status=' . $active_status . $searchQuery;

                $windowSize   = 3;
                $currentBlock = (int) ceil($current_page / $windowSize);
                $windowStart  = (($currentBlock - 1) * $windowSize) + 1;
                $windowEnd    = min($windowStart + $windowSize - 1, $total_pages);
            ?>
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <span class="text-muted small fw-bold">Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> entries</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= max(1, $current_page - 1) . $pageQuery ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i . $pageQuery ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $pageQuery ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul>
                </nav>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- VIEW RETURN DETAILS — all actions (approve/reject/mark-sent/replacement) live here now -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="viewReturnDrawer" style="width: 500px;">
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
<script src="<?= base_url('public/js/admin/operations/procurement/procurement_return.js') ?>"></script>
<?= view('partials/admin/footer') ?>