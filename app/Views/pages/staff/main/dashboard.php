<?= view('partials/staff/head') ?>
<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4">
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1">Good day, <?= explode(' ', $fullname)[0] ?>!</h6>
                <p class="mb-0 opacity-75 small"><?= date('l, F d, Y') ?> • Assigned: Inventory & POS</p>
            </div>

            <!-- KPI Row (Figma Themed) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4 border-bottom border-warning border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-muted fw-bold">TODAY'S POS TXNS</small><h2 class="fw-bold mb-0 text-warning">8</h2></div>
                            <i class="fas fa-shopping-cart opacity-10 fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4 border-bottom border-danger border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-muted fw-bold">PENDING GRR</small><h2 class="fw-bold mb-0 text-danger">2</h2></div>
                            <i class="fas fa-truck-loading opacity-10 fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4 border-bottom border-success border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-muted fw-bold">ORDERS TO PROCESS</small><h2 class="fw-bold mb-0 text-success">4</h2></div>
                            <i class="fas fa-file-invoice opacity-10 fs-2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4 border-bottom border-secondary border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><small class="text-muted fw-bold">ASSIGNED ALERTS</small><h2 class="fw-bold mb-0 text-dark">3</h2></div>
                            <i class="fas fa-bell opacity-10 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- LEFT: Today's Tasks -->
                <div class="col-lg-6">
                    <div class="custom-table-container p-4 h-100 border-0 shadow-sm" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-4 text-dark"><i class="fas fa-tasks me-2 text-maroon"></i>Today's Tasks</h6>
                        
                        <div class="p-2 px-3 mb-2 rounded-pill border d-flex align-items-center bg-light" style="border-left: 5px solid #e74c3c !important;">
                            <i class="fas fa-file-alt me-3 text-danger"></i>
                            <span class="fw-bold text-dark flex-grow-1">Process GRR for PO-2026-041 (Pentagon delivery)</span>
                        </div>
                        <div class="p-2 px-3 mb-2 rounded-pill border d-flex align-items-center bg-light" style="border-left: 5px solid #f1c40f !important;">
                            <i class="fas fa-exclamation-triangle me-3 text-warning"></i>
                            <span class="fw-bold text-dark flex-grow-1">Check Vinyl Gloves expiry — batch B2026-03</span>
                        </div>
                        <div class="p-2 px-3 mb-2 rounded-pill border d-flex align-items-center bg-light" style="border-left: 5px solid #3498db !important;">
                            <i class="fas fa-clipboard-check me-3 text-primary"></i>
                            <span class="fw-bold text-dark flex-grow-1">Prepare SO-0234 for Mediatrix delivery</span>
                        </div>
                        <div class="p-2 px-3 mb-2 rounded-pill border d-flex align-items-center bg-light" style="border-left: 5px solid #2ecc71 !important;">
                            <i class="fas fa-check-circle text-success me-3"></i>
                            <span class="text-muted flex-grow-1">POS shift starts at 8:00 AM</span>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Low Stock -->
                <div class="col-lg-6">
                    <div class="custom-table-container p-4 h-100 border-0 shadow-sm" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-4 text-dark"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low Stock to Restock</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle" style="font-size: 10px;">
                                <thead class="table-dark">
                                    <tr><th class="ps-3">Product</th><th>Stock</th><th>ROP</th><th class="text-center">Action</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td class="ps-3 fw-bold">Pulse Oximeter</td><td class="text-danger fw-bold">8</td><td>23</td><td class="text-center"><button class="btn btn-xs btn-outline-dark rounded-pill px-3">Adjust</button></td></tr>
                                    <tr><td class="ps-3 fw-bold">Alcohol 70%</td><td class="text-danger fw-bold">5</td><td>15</td><td class="text-center"><button class="btn btn-xs btn-outline-dark rounded-pill px-3">Adjust</button></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/staff/footer') ?>