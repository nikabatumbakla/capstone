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
                <h5 class="fw-bold mb-0">Purchase Order History</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-history me-2"></i>Received Deliveries — Read Only</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Look back at what was received, from whom, and when. No actions can be taken from this page.</p>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-boxes me-2 text-maroon"></i>All Closed Purchase Orders</h6>
                    <form id="filterForm" action="" method="GET">
                        <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill" placeholder="Search PO # or supplier..." style="width:220px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">PO Number</th><th>Supplier</th><th>Items</th><th>Total</th><th>Received</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($history)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No closed purchase orders found.</td></tr>
                            <?php else:
                                $statusMeta = ['received' => 'bg-success', 'partial' => 'bg-warning text-dark', 'cancelled' => 'bg-secondary'];
                                foreach($history as $h): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= esc($h['po_number']) ?></td>
                                <td><?= esc($h['supplier_name'] ?? '—') ?></td>
                                <td><?= $h['item_count'] ?> items</td>
                                <td class="fw-bold">₱<?= number_format($h['total_amount'], 2) ?></td>
                                <td><?= $h['received_date'] ? date('M d, Y', strtotime($h['received_date'])) : '—' ?></td>
                                <td><span class="badge <?= $statusMeta[$h['status']] ?? 'bg-light text-dark border' ?> px-3"><?= strtoupper($h['status']) ?></span></td>
                                <td class="text-center"><button type="button" class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-view-po" data-id="<?= $h['po_id'] ?>">View</button></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-4">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <?php for($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="poDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0" id="poDrawerTitle">PO Details</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4" id="poDrawerContent"></div>
</div>

<script src="<?= base_url('public/js/staff/operations/po_history.js') ?>"></script>
<?= view('partials/staff/footer') ?>