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
                <h5 class="fw-bold mb-0">Goods Receipt (GRR)</h5>
            </div>
     
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Goods Receipt Recording</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Verify incoming stock against Purchase Orders to update inventory</p>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
    <h6 class="fw-bold mb-0" style="font-size: 14px;"><i class="fas fa-clipboard-check me-2 text-maroon"></i>Awaiting Inspection</h6>

    <form action="" method="GET" class="filter-box bg-light rounded-pill px-3 py-1 shadow-none border">
        <select name="category" class="form-select border-0 bg-transparent" style="font-size: 11px; width: 180px;" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= ($category_filter == $cat['category_id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 11px;">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">PO #</th>
                                <th>Supplier</th>
                                <th>Expected Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($pending_receipts)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No pending deliveries to record.</td></tr>
                            <?php endif; ?>
                            <?php foreach($pending_receipts as $po): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= $po['po_number'] ?></td>
                                <td><?= $po['supplier_name'] ?></td>
                                <td><?= date('M d, Y', strtotime($po['expected_date'])) ?></td>
                                
                                <?php
    $grrStatusMeta = [
        'sent'         => ['label' => 'Sent — Awaiting Supplier', 'class' => 'bg-secondary'],
        'acknowledged' => ['label' => 'Acknowledged by Supplier', 'class' => 'bg-info'],
        'in_transit'   => ['label' => 'In Transit', 'class' => 'bg-primary'],
    ];
    $meta = $grrStatusMeta[$po['status']] ?? ['label' => ucwords($po['status']), 'class' => 'bg-secondary'];
?>
<td><span class="badge <?= $meta['class'] ?>"><?= $meta['label'] ?></span></td>
                                
                                <td class="text-center">
    <button class="btn btn-sm btn-dark rounded-pill px-3 btn-record-grr" data-id="<?= $po['po_id'] ?>">
        <i class="fas fa-box-open me-2"></i>Verify Delivery</button>
</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php
    $rangeStart = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
    $rangeEnd   = min($current_page * $per_page, $total_rows);
    $catQuery   = $category_filter ? '&category=' . $category_filter : '';

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
                <a class="page-link" href="?page=<?= max(1, $current_page - 1) . $catQuery ?>"><i class="fas fa-chevron-left"></i></a>
            </li>
            <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i . $catQuery ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $catQuery ?>"><i class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
</div>

            </div>
        </div>
    </div>
</div>

<!-- GRR FORM DRAWER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="grrDrawer" style="width: 700px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Record Incoming Goods</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="grrContent"></div>
</div>

<script>
  const BASE_URL = "<?= rtrim(base_url(), '/') ?>";
</script>
<script src="<?= base_url('public/js/admin/operations/procurement/procurement_grr.js') ?>"></script>
<?= view('partials/admin/footer') ?>