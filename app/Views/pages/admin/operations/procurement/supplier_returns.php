<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Supplier Returns</h5>
            </div>
            <button class="btn btn-sm btn-dark rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="offcanvas" data-bs-target="#supplierReturnDrawer">
                <i class="fas fa-plus me-2"></i>New Return Request
            </button>
        </div>

        <div class="procurement-banner mb-4 p-3 text-white shadow-sm">
            <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Supplier Return Management</h6>
            <p class="mb-0 opacity-75" style="font-size: 10px;">Submit → Approve/Reject → Auto-Deduct Inventory</p>
        </div>

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
                            <th class="ps-4">Return #</th><th>PO #</th><th>Supplier</th><th>Product</th><th class="text-center">Qty</th><th>Reason</th><th>Requested</th><th>Status</th><th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($returns)): ?>
                            <tr><td colspan="9" class="text-center py-5 text-muted">No returns found.</td></tr>
                        <?php else: foreach($returns as $r): ?>
                        <tr>
                            <td class="ps-4 text-muted">SRT-<?= str_pad($r['return_id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td class="fw-bold"><?= $r['po_number'] ?></td>
                            <td><?= $r['supplier_name'] ?></td>
                            <td><?= $r['product_name'] ?></td>
                            <td class="text-center fw-bold"><?= $r['quantity'] ?></td>
                            <td><span class="text-muted"><?= esc(substr($r['reason'], 0, 25)) ?>...</span></td>
                            <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <?php $b = ($r['status'] == 'approved') ? 'bg-success' : (($r['status'] == 'rejected') ? 'bg-danger' : 'bg-warning text-dark'); ?>
                                <span class="badge rounded-pill <?= $b ?> px-3"><?= strtoupper($r['status']) ?></span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <?php if($r['status'] == 'pending'): ?>
                                        <a href="<?= base_url('admin/procurement/approve-supplier-return/'.$r['return_id']) ?>" class="btn btn-xs btn-success rounded-2" title="Approve" onclick="return confirm('Approve — this will deduct stock and mark it for return to supplier.');"><i class="fas fa-check"></i></a>
                                        <a href="<?= base_url('admin/procurement/reject-supplier-return/'.$r['return_id']) ?>" class="btn btn-xs btn-danger rounded-2 text-white" title="Reject" onclick="return confirm('Reject this return request?');"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                    <button class="btn btn-xs btn-outline-dark rounded-2 btn-view-return" data-id="<?= $r['return_id'] ?>" title="View"><i class="fas fa-eye"></i></button>
                                </div>
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

<!-- ONE FORM, ONE PROCESS: file a supplier return request -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="supplierReturnDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="fw-bold mb-0"><i class="fas fa-undo-alt me-2"></i>New Supplier Return</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/procurement/save-supplier-return') ?>" method="POST">
            
        <div class="mb-3">
    <label class="formal-label">Reference PO # *</label>
    <select name="po_id" id="returnPoSelect" class="form-select formal-input" required>
        <option value="" disabled selected>Select PO</option>
        <?php foreach($received_pos as $po): ?>
            <option value="<?= $po['po_id'] ?>"><?= $po['po_number'] ?> (<?= $po['sname'] ?>)</option>
        <?php endforeach; ?>
    </select>
</div>
<div class="mb-3">
    <label class="formal-label">Product *</label>
    <select name="product_id" id="returnPoProductSelect" class="form-select formal-input" required>
        <option value="">Select PO first</option>
    </select>
    <input type="hidden" name="batch_id" id="returnPoBatchId">
</div>
<div class="mb-3">
    <label class="formal-label">Defective / Return Qty *</label>
    <input type="number" name="qty" id="returnPoQty" class="formal-input" min="1" required>
</div>
<div class="mb-3">
    <label class="formal-label">Expected Refund / Credit (₱, optional)</label>
    <input type="number" step="0.01" name="refund_amount" id="returnPoRefund" class="formal-input" placeholder="Auto-suggested from unit cost">
</div>
<div class="mb-3">
    <label class="formal-label">Supplier Credit Note # (once issued)</label>
    <input type="text" name="credit_note_number" class="formal-input" placeholder="e.g. CN-2026-0091">
</div>
<div class="mb-3">
    <label class="formal-label">Reason *</label>
    <textarea name="notes" class="formal-input" rows="3" required placeholder="e.g. 5 units arrived damaged, packaging compromised"></textarea>
</div>

            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold">
                <i class="fas fa-paper-plane me-2"></i>Submit for Approval
            </button>
        </form>
    </div>
</div>

<!-- VIEW RETURN DETAILS -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="viewReturnDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Return Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="viewReturnContent"></div>
</div>

<script src="<?= base_url('public/js/admin/operations/procurement/procurement_returns.js') ?>"></script>
<?= view('partials/admin/footer') ?>