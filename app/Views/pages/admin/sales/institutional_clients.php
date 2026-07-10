<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <!-- Back Button -->
            <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 mb-4" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>

            <!-- KPI Row (Figma Match) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="inventory-kpi-card p-3 shadow-sm bg-white d-flex justify-content-between align-items-center">
                        <div><small class="fw-bold text-muted" style="font-size:9px">SCHOOLS</small><h3 class="fw-bold mb-0"><?= $count_schools ?></h3></div>
                        <i class="fas fa-school fs-2 text-primary opacity-25"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card p-3 shadow-sm bg-white d-flex justify-content-between align-items-center">
                        <div><small class="fw-bold text-muted" style="font-size:9px">HOSPITALS</small><h3 class="fw-bold mb-0 text-danger"><?= $count_hospitals ?></h3></div>
                        <i class="fas fa-hospital fs-2 text-danger opacity-25"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card p-3 shadow-sm bg-white d-flex justify-content-between align-items-center">
                        <div><small class="fw-bold text-muted" style="font-size:9px">LGU / SK</small><h3 class="fw-bold mb-0 text-info"><?= $count_lgu ?></h3></div>
                        <i class="fas fa-landmark fs-2 text-info opacity-25"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card p-3 shadow-sm bg-white d-flex justify-content-between align-items-center">
                        <div><small class="fw-bold text-muted" style="font-size:9px">BARANGAYS</small><h3 class="fw-bold mb-0 text-success"><?= $count_brgy ?></h3></div>
                        <i class="fas fa-map-marker-alt fs-2 text-success opacity-25"></i>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="custom-table-container">
                <!-- Search & Filter Bar (Figma Match) -->
                <div class="row g-2 mb-4 align-items-center">
                    <div class="col-md-8 position-relative">
                        <input type="text" id="clientSearch" class="form-control form-control-sm rounded-pill ps-5 border" placeholder="Search client organization..." style="height:45px">
                        <i class="fas fa-search position-absolute text-muted" style="left:20px; top:15px"></i>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm rounded-pill border" style="height:45px">
                            <option value="">All Types</option>
                            <option value="school">School</option>
                            <option value="hospital">Hospital</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-dark w-100 rounded-pill fw-bold" style="height:45px" data-bs-toggle="offcanvas" data-bs-target="#addClientDrawer">+ Register</button>
                    </div>
                </div>

                <h6 class="fw-bold mb-3"><i class="fas fa-users me-2 text-maroon"></i>Client Directory</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:11px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Organization</th><th>Type</th><th>Contact</th><th>Credit Limit</th><th>Balance</th><th>Status</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="clientTableBody">
                            <?php foreach($clients as $c): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= $c['organization'] ?></td>
                                <td><?= ucfirst($c['client_type']) ?></td>
                                <td><?= $c['phone'] ?></td>
                                <td class="fw-bold">₱<?= number_format($c['credit_limit'], 2) ?></td>
                                <td class="text-danger fw-bold">₱<?= number_format($c['credit_used'], 2) ?></td>
                                <td><span class="badge rounded-pill bg-success px-3">Active</span></td>
                                <td class="text-center"><button class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-view-client" data-id="<?= $c['client_id'] ?>">View</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span class="text-muted small">Showing 1-<?= count($clients) ?> of <?= count($clients) ?> clients</span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: REGISTER CLIENT -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="addClientDrawer" style="width: 450px; border-left: 8px solid #1a2a6c;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">Register Institution</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/sales/save-client') ?>" method="POST">
            <div class="mb-3"><label class="formal-label">Organization Name *</label><input type="text" name="org" class="formal-input" required></div>
            <div class="mb-3">
                <label class="formal-label">Type</label>
                <select name="type" class="form-select formal-input">
                    <option value="school">School</option>
                    <option value="hospital">Hospital</option>
                    <option value="barangay">Barangay</option>
                    <option value="lgu">LGU / Government</option>
                </select>
            </div>
            <div class="row"><div class="col-6 mb-3"><label class="formal-label">Contact Person</label><input type="text" name="contact" class="formal-input"></div><div class="col-6 mb-3"><label class="formal-label">Phone</label><input type="text" name="phone" class="formal-input"></div></div>
            <div class="mb-3"><label class="formal-label">Credit Limit (₱)</label><input type="number" name="limit" class="formal-input" value="0"></div>
            <button type="submit" class="btn btn-save-adj w-100 py-3 mt-4" style="background:#1a2a6c">Confirm Registration</button>
        </form>
    </div>
</div>

<!-- SLIDING DRAWER: CLIENT INTELLIGENCE (View & History) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="clientDrawer" style="width: 500px; border-left: 8px solid #7b1113;">
    <div class="offcanvas-body" id="clientDrawerContent"></div>
</div>

<script src="<?= base_url('public/js/admin/sales_clients.js') ?>"></script>
<?= view('partials/admin/footer') ?>