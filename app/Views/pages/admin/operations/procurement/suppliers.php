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
                <h5 class="fw-bold mb-0">Supplier Management</h5>
            </div>
     
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Supplier Management — Inbound Procurement</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Monitor reliability, lead times, and order accuracy for decision support</p>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
    <h6 class="fw-bold mb-0" style="font-size: 14px;"><i class="fas fa-id-badge me-2 text-maroon"></i>Active Suppliers</h6>

    <div class="d-flex align-items-center gap-3">
        <!-- Category Filter (products this supplier carries) -->
        <form action="" method="GET" class="filter-box bg-light rounded-pill px-3 py-1 shadow-none border">
            <input type="hidden" name="search" value="<?= esc($search ?? '') ?>">
            <select name="category" class="form-select border-0 bg-transparent" style="font-size: 11px; width: 150px;" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= ($category_filter == $cat['category_id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Search Bar -->
        <div class="position-relative filter-box bg-light rounded-pill px-2 border shadow-none">
            <input type="text" id="supplierSearch" class="form-control form-control-sm border-0 bg-transparent ps-4"
                   placeholder="Search supplier..." style="font-size: 11px; width: 200px; height: 35px;"
                   value="<?= esc($search ?? '') ?>">
            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 11px; font-size: 11px;"></i>
        </div>
    </div>
</div>

                <div class="row g-3">
                    <?php foreach($suppliers as $s): 
                        $perf = ($s['on_time_rate'] + $s['accuracy_rate']) / 2;
                        $color = ($perf >= 90) ? '#22c55e' : (($perf >= 75) ? '#f59e0b' : '#ef4444');
                    ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="compact-supplier-card p-3 h-100 border">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div style="width: 70%;"><h6 class="fw-bold mb-0 text-dark text-truncate"><?= $s['name'] ?></h6><small class="text-muted" style="font-size: 9px;"><?= $s['contact_person'] ?: 'Distributor' ?></small></div>
                                <div class="mini-gauge" style="--perf: <?= $perf ?>%; --color: <?= $color ?>;"><span class="fw-bold" style="font-size: 9px;"><?= round($perf) ?>%</span></div>
                            </div>
                            <div class="row g-1 mb-3 pt-2 border-top">
                                <div class="col-4 border-end text-center"><small class="mini-label">Lead Time</small><p class="mini-val"><?= $s['lead_time_days'] ?>d</p></div>
                                <div class="col-4 border-end text-center"><small class="mini-label">Accuracy</small><p class="mini-val"><?= round($s['accuracy_rate']) ?>%</p></div>
                                <div class="col-4 text-center"><small class="mini-label">Orders</small><p class="mini-val"><?= $s['total_orders'] ?: 0 ?></p></div>
                            </div>
                            <div class="d-grid gap-1">
    <button class="btn btn-xs btn-dark rounded-2 btn-view-supplier" data-id="<?= $s['supplier_id'] ?>">View</button>
    <button type="button" class="btn btn-xs btn-outline-maroon rounded-2 btn-create-po"
            data-id="<?= $s['supplier_id'] ?>" data-name="<?= esc($s['name']) ?>">+ Create PO</button>
</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php
    $rangeStart = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
    $rangeEnd   = min($current_page * $per_page, $total_rows);

    $catQuery    = $category_filter ? '&category=' . $category_filter : '';
    $searchQuery = $search ? '&search=' . urlencode($search) : '';
    $pageQuery   = $catQuery . $searchQuery;

    $windowSize   = 3;
    $currentBlock = (int) ceil($current_page / $windowSize);
    $windowStart  = (($currentBlock - 1) * $windowSize) + 1;
    $windowEnd    = min($windowStart + $windowSize - 1, $total_pages);
?>
<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
    <span class="text-muted fw-bold" style="font-size: 10px;">
        Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> Entries
    </span>
    <nav>
        <ul class="pagination pagination-sm mb-0 custom-pager">
            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= max(1, $current_page - 1) . $pageQuery ?>"><i class="fas fa-chevron-left"></i></a>
            </li>
            <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i . $pageQuery ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $pageQuery ?>"><i class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
</div>

            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="supplierDrawer" style="width: 450px;"><div class="offcanvas-body" id="supplierDrawerContent"></div></div>

<!-- CREATE PO DRAWER — scoped per supplier -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="createPODrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="fw-bold mb-0" id="createPOTitle">Create Purchase Order</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" style="font-size: 11px;" id="createPOContent"></div>
</div>

<script>
  const BASE_URL = "<?= rtrim(base_url(), '/') ?>";
</script>
<script src="<?= base_url('public/js/admin/operations/procurement/procurement.js') ?>"></script>
<?= view('partials/admin/footer') ?>