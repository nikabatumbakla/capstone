<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">

        <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Purchase Orders</h5>
            </div>
     
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Purchase Order Management</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Auto-Reorder Recommendations • Approval Workflow • Inbound Tracking</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="inventory-kpi-card">
            <small class="text-muted fw-bold d-block mb-1">POs THIS MONTH</small>
            <h3 class="fw-bold mb-0"><?= $po_this_month ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <a href="?status=pending_approval" class="text-decoration-none">
            <div class="inventory-kpi-card <?= $status_filter == 'pending_approval' ? 'border-bottom border-3 border-warning' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">PENDING APPROVAL</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $count_pending ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="inventory-kpi-card">
            <small class="text-muted fw-bold d-block mb-1">AUTO-REORDERS THIS MONTH</small>
            <h3 class="fw-bold mb-0 text-primary"><?= $auto_reorders_this_month ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="inventory-kpi-card">
            <small class="text-muted fw-bold d-block mb-1">SPEND THIS MONTH</small>
            <h3 class="fw-bold mb-0 text-maroon">₱<?= number_format($spend_this_month, 2) ?></h3>
        </div>
    </div>
</div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="bg-light p-1 rounded-pill d-inline-flex border">
                        <a href="<?= base_url('admin/procurement/purchase-orders') ?>" class="btn btn-xs rounded-pill px-3 <?= !$status_filter ? 'btn-dark' : 'text-muted' ?>">All POs</a>
                        <a href="?status=pending_approval" class="btn btn-xs rounded-pill px-3 <?= $status_filter == 'pending_approval' ? 'btn-dark' : 'text-muted' ?>">Pending (<?= $count_pending ?>)</a>
                        <a href="?status=sent" class="btn btn-xs rounded-pill px-3 <?= $status_filter == 'sent' ? 'btn-dark' : 'text-muted' ?>">Sent to Supplier</a>
                        <a href="?status=received" class="btn btn-xs rounded-pill px-3 <?= $status_filter == 'received' ? 'btn-dark' : 'text-muted' ?>">Received</a>
                    </div>
                    <a href="<?= base_url('admin/procurement/suppliers') ?>" class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus me-2"></i>Create PO
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 10px;">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">PO #</th><th>Supplier</th><th>Items</th><th>Total</th><th>Status</th><th>Origin</th><th>Expected</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $statusLabels = [
                                    'draft'            => 'Draft',
                                    'pending_approval' => 'Pending Approval',
                                    'approved'         => 'Approved',
                                    'sent'             => 'Sent to Supplier',
                                    'partial'          => 'Partially Received',
                                    'received'         => 'Received',
                                    'cancelled'        => 'Cancelled',
                                ];
                            ?>
                            <?php foreach($pos as $po): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= $po['po_number'] ?></td>
                                <td><?= $po['supplier_name'] ?></td>
                                <td><?= $po['item_count'] ?> items</td>
                                <td class="fw-bold text-maroon">₱<?= number_format($po['total_amount'], 2) ?></td>

                                <?php
    $statusIcons = [
        'draft' => 'fa-file', 'pending_approval' => 'fa-hourglass-half', 'approved' => 'fa-thumbs-up',
        'sent' => 'fa-paper-plane', 'partial' => 'fa-truck-loading', 'received' => 'fa-check-circle', 'cancelled' => 'fa-ban',
    ];
?>
<td><span class="badge rounded-pill bg-light text-dark border"><i class="fas <?= $statusIcons[$po['status']] ?? 'fa-circle' ?> me-1"></i><?= $statusLabels[$po['status']] ?? ucwords($po['status']) ?></span></td>
                                <td class="text-center">
    <?= $po['is_auto_generated']
        ? '<i class="fas fa-robot text-primary" title="System-Generated (Auto-Reorder)"></i>'
        : '<i class="fas fa-user-edit text-muted" title="Manual Entry"></i>' ?>
</td>
                                <td><?= $po['expected_date'] ? date('M d, Y', strtotime($po['expected_date'])) : '—' ?></td>
                               
                                <td class="text-center">
    <?php if($po['status'] == 'pending_approval'): ?>
        <a href="<?= base_url('admin/procurement/approve-po/'.$po['po_id']) ?>" class="btn btn-xs btn-success px-2 py-1" title="Approve" onclick="return confirm('Approve this purchase order?');">
            <i class="fas fa-check"></i>
        </a>
        <a href="<?= base_url('admin/procurement/reject-po/'.$po['po_id']) ?>" class="btn btn-xs btn-danger px-2 py-1" title="Reject" onclick="return confirm('Reject and cancel this purchase order?');">
            <i class="fas fa-times"></i>
        </a>
    <?php endif; ?>
    <button class="btn btn-xs btn-dark px-2 py-1 btn-view-po" data-id="<?= $po['po_id'] ?>" title="View Details">
        <i class="fas fa-eye"></i>
    </button>
</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($pos)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No purchase orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $rangeStart = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
                    $rangeEnd   = min($current_page * $per_page, $total_rows);
                    $statusQuery = $status_filter ? '&status=' . $status_filter : '';

                    $windowSize   = 3;
                    $currentBlock = (int) ceil($current_page / $windowSize);
                    $windowStart  = (($currentBlock - 1) * $windowSize) + 1;
                    $windowEnd    = min($windowStart + $windowSize - 1, $total_pages);
                ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span class="text-muted fw-bold" style="font-size: 10px;">
                        Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> Entries
                    </span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 custom-pager">
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= max(1, $current_page - 1) . $statusQuery ?>"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i . $statusQuery ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $statusQuery ?>"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="poViewDrawer" style="width: 600px;"><div class="offcanvas-body" id="poViewContent"></div></div>

<script>
  const BASE_URL = "<?= rtrim(base_url(), '/') ?>";
</script>
<script src="<?= base_url('public/js/admin/operations/procurement/procurement_po.js') ?>"></script>
<?= view('partials/admin/footer') ?>