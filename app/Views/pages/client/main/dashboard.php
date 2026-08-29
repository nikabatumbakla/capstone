<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1">Welcome, <?= $fullname ?>!</h6>
                <p class="mb-0 opacity-75 small">Your procurement dashboard — Robin Rose Trading</p>
            </div>

            <!-- KPI Tiles (Figma Match) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4">
                        <small class="info-label">ACTIVE ORDERS</small>
                        <h2 class="fw-bold mb-0">3</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4">
                        <small class="info-label text-danger">OUTSTANDING BALANCE</small>
                        <h2 class="fw-bold mb-0 text-danger">₱ 12,400</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4">
                        <small class="info-label">TOTAL ORDERS (YTD)</small>
                        <h2 class="fw-bold mb-0">28</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-4">
                        <small class="info-label">CREDIT LIMIT</small>
                        <h2 class="fw-bold mb-0">₱ 50,000</h2>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Recent Orders (Left Column) -->
                <div class="col-lg-7">
                    <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-4">📋 Recent Orders</h6>
                        <table class="table table-sm align-middle" style="font-size:10px">
                            <thead class="table-dark">
                                <tr><th>Order #</th><th>Total</th><th>Status</th><th class="text-end">Delivery</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="fw-bold">SO-0234</td><td>₱ 7,364</td><td><span class="text-primary fw-bold">PROCESSING</span></td><td class="text-end">Apr 21</td></tr>
                                <tr><td class="fw-bold">SO-0228</td><td>₱ 12,500</td><td><span class="text-success fw-bold">DELIVERED</span></td><td class="text-end">Apr 10</td></tr>
                            </tbody>
                        </table>
                        <button class="btn btn-xs btn-outline-dark rounded-pill px-4 mt-3 shadow-none">View All Orders →</button>
                    </div>
                </div>

                <!-- Account Summary (Right Column) -->
                <div class="col-lg-5">
                    <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-4">💳 Account Summary</h6>
                        <div class="mb-2 d-flex justify-content-between"><span>Credit Limit</span><span class="fw-bold">₱ 50,000</span></div>
                        <div class="mb-2 d-flex justify-content-between p-2 bg-light rounded text-success fw-bold"><span>Available Credit</span><span>₱ 37,600</span></div>
                        <div class="mb-3 d-flex justify-content-between p-2 bg-soft-maroon rounded text-danger fw-bold"><span>Outstanding Balance</span><span>₱ 12,400</span></div>
                        <div class="mb-4 d-flex justify-content-between"><span>Payment Terms</span><span class="fw-bold">Net 30</span></div>
                        <button class="btn btn-dark w-100 py-2 fw-bold rounded-pill shadow">Pay Now</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>