<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </button>
                    <h5 class="fw-bold mb-0">Supplier Management</h5>
                </div>
            </div>

            <!-- Banner -->
            <div class="procurement-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i> Supplier Management — Inbound Procurement</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Monitor reliability, lead times, and order accuracy for decision support</p>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size: 14px;"><i class="fas fa-id-badge me-2 text-maroon"></i>Active Suppliers</h6>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Corrected Filter Form -->
                        <div class="position-relative filter-box bg-light rounded-pill px-2 border">
                            <form action="" method="GET">
                                <input type="text" name="search" class="form-control form-control-sm border-0 bg-transparent ps-4" placeholder="Search supplier..." style="font-size: 11px; width: 200px; height: 35px;" value="<?= $_GET['search'] ?? '' ?>">
                                <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 11px; font-size: 11px;"></i>
                            </form>
                        </div>
                        <button class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#addSupplierDrawer" style="font-size: 11px; height: 38px;">
                            <i class="fas fa-plus me-2"></i> REGISTER SUPPLIER
                        </button>
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
                                <a href="#" class="btn btn-xs btn-outline-maroon rounded-2">+ Create PO</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted fw-bold" style="font-size: 10px;">Showing <?= count($suppliers) ?> Active Accounts</span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager"><li class="page-item active"><a class="page-link" href="#">1</a></li></ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: ADD SUPPLIER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="addSupplierDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">Register New Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/procurement/save-supplier') ?>" method="POST">
             <div class="mb-3"><label class="formal-label">Company Name *</label><input type="text" name="name" class="formal-input" required></div>
             <div class="row"><div class="col-6 mb-3"><label class="formal-label">Contact Person</label><input type="text" name="contact" class="formal-input"></div><div class="col-6 mb-3"><label class="formal-label">Phone</label><input type="text" name="phone" class="formal-input"></div></div>
             <div class="mb-3"><label class="formal-label">Email Address</label><input type="email" name="email" class="formal-input"></div>
             <div class="mb-3"><label class="formal-label">Address</label><textarea name="address" class="formal-input" rows="2"></textarea></div>
             <button type="submit" class="btn btn-maroon w-100 py-2 mt-1">Save Supplier</button>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="supplierDrawer" style="width: 450px;"><div class="offcanvas-body" id="supplierDrawerContent"></div></div>

<script src="<?= base_url('public/js/admin/procurement.js') ?>"></script>
<?= view('partials/admin/footer') ?>