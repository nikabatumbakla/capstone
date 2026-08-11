<?= view('partials/admin/head') ?>
<style>
    .eng-summary-card { background: #fff; border-radius: 25px; height: 100%; border: 1px solid rgba(0,0,0,0.05); transition: 0.3s; }
    .eng-summary-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .nav-pills .nav-link { color: #666; font-weight: 700; font-size: 11px; }
    .nav-pills .nav-link.active { background-color: #0d2e4f !important; color: #fff; }
</style>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content" style="background: #f8fafc;">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Customer Engagement</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-heart me-2"></i> Customer Engagement Hub</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Product Reviews · Suggestions · Store Feedbackhh</p>
            </div>

            <!-- 2. KPI Tiles -->
            <div class="row g-3 mb-4 text-center">
                <div class="col-md-3"><div class="stat-card shadow-sm p-3 bg-white border-0 rounded-4"><small class="info-label">AVG PRODUCT RATING</small><h4 class="fw-bold text-warning"><?= number_format($avg_rating, 1) ?> / 5.0</h4></div></div>
                <div class="col-md-3"><div class="stat-card shadow-sm p-3 bg-white border-0 rounded-4"><small class="info-label">NEW PRODUCT REQUESTS</small><h4 class="fw-bold text-primary"><?= $pending_suggestions ?></h4></div></div>
                <div class="col-md-3"><div class="stat-card shadow-sm p-3 bg-white border-0 rounded-4"><small class="info-label">TOTAL REVIEWS</small><h4 class="fw-bold"><?= count($all_reviews) ?></h4></div></div>
                <div class="col-md-3"><div class="stat-card shadow-sm p-3 bg-dark text-white border-0" style="border-radius:20px"><small class="info-label text-white-50">SYSTEM STATUS</small><h4 class="fw-bold text-success">LIVE</h4></div></div>
            </div>

            <!-- 3. INTELLIGENCE SNAPSHOT (Middle Row - Figma Match) -->
            <div class="row g-4 mb-5">
                <!-- SNAP 1: Reviews -->
                <div class="col-lg-4">
                    <div class="eng-summary-card p-4">
                        <h6 class="fw-bold mb-4"><i class="fas fa-star text-warning me-2"></i> Recent Reviews</h6>
                        <?php foreach($recent_reviews as $r): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold mb-0 text-dark"><?= $r['pname'] ?></h6>
                                <span class="text-warning small"><?= str_repeat('★', $r['rating']) ?></span>
                            </div>
                            <p class="mb-0 text-muted small" style="font-style:italic">"<?= $r['comment'] ?>"</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- SNAP 2: Suggestions -->
                <div class="col-lg-4">
                    <div class="eng-summary-card p-4 text-center">
                        <h6 class="fw-bold mb-4 text-start"><i class="fas fa-lightbulb text-info me-2"></i> Product Suggestions</h6>
                        <table class="table table-sm text-start">
                            <thead><tr style="font-size:9px"><th>Product</th><th>By</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($recent_suggestions as $s): ?>
                                <tr>
                                    <td class="fw-bold"><?= $s['product_name'] ?></td>
                                    <td class="text-muted small"><?= ucfirst($s['user_role'] ?: 'Guest') ?></td>
                                    <td><span class="badge <?= $s['status']=='pending'?'text-warning':'text-success' ?> p-0"><?= ucfirst($s['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- SNAP 3: Store Performance Bars -->
                <div class="col-lg-4">
                    <div class="eng-card p-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-pen-nib text-muted me-2"></i> Store Feedback</h6>
                        
                        <div class="text-center py-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <h1 class="fw-bold mb-0 me-2" style="font-size: 42px;"><?= number_format($avg_rating, 1) ?></h1>
                                <i class="fas fa-star text-warning fs-3"></i>
                            </div>
                            <p class="text-muted small">Based on <?= $total_feedback ?> reviews</p>
                        </div>

                        <!-- STAR BARS -->
                        <div class="mt-2">
                            <?php 
                                $bar_colors = [5 => '#27ae60', 4 => '#f1c40f', 3 => '#e67e22'];
                                foreach($star_breakdown as $star => $percent): 
                            ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="text-warning me-2" style="width: 70px;">
                                    <?php for($i=1; $i<=5; $i++): ?><i class="<?= ($i <= $star) ? 'fas' : 'far' ?> fa-star" style="font-size:8px"></i><?php endfor; ?>
                                </div>
                                <div class="progress flex-grow-1" style="height: 6px; background:#f1f5f9;">
                                    <div class="progress-bar" style="width: <?= $percent ?>%; background: <?= $bar_colors[$star] ?>; border-radius:10px;"></div>
                                </div>
                                <span class="ms-2 fw-bold text-muted" style="font-size:10px;"><?= round($percent) ?>%</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. MANAGEMENT TABLES (Bottom Row - Your previous logic) -->
            <div class="custom-table-container">
                <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill border" role="tablist">
                    <li class="nav-item flex-grow-1"><button class="nav-link active rounded-pill w-100" data-bs-toggle="pill" data-bs-target="#m-reviews">Product Reviews Management</button></li>
                    <li class="nav-item flex-grow-1"><button class="nav-link rounded-pill w-100" data-bs-toggle="pill" data-bs-target="#m-suggestions">Product Suggestions Management</button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="m-reviews">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr><th class="ps-4">Timestamp</th><th>Customer</th><th>Product</th><th>Rating</th><th>Comment</th><th class="text-center">Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($all_reviews as $r): ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                        <td class="fw-bold"><?= $r['customer'] ?></td>
                                        <td><?= $r['pname'] ?></td>
                                        <td class="text-warning"><?= str_repeat('★', $r['rating']) ?></td>
                                        <td><small class="text-muted"><?= $r['comment'] ?></small></td>
                                        <td class="text-center">
                                            <?php if(!$r['is_approved']): ?>
                                                <a href="<?= base_url('admin/management/reviews/approve/'.$r['review_id']) ?>" class="btn btn-xs btn-success rounded-pill px-3">Approve</a>
                                            <?php else: ?>
                                                <span class="badge bg-light text-success border border-success px-3">LIVE</span>
                                            <?php endif; ?>
                                            <a href="<?= base_url('admin/management/reviews/delete/'.$r['review_id']) ?>" class="btn btn-xs btn-outline-danger border-0 ms-2"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="m-suggestions">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr><th class="ps-4">Product Name</th><th>Category</th><th>Requester</th><th>Status</th><th class="text-center">Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($all_suggestions as $s): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= $s['product_name'] ?></td>
                                        <td><?= $s['category'] ?></td>
                                        <td><?= $s['requester'] ?: 'Walk-in' ?></td>
                                        <td><span class="badge rounded-pill bg-<?= $s['status']=='pending'?'warning text-dark':'success' ?> px-3"><?= strtoupper($s['status']) ?></span></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('admin/management/suggestions/status/'.$s['suggestion_id'].'/approved') ?>" class="btn btn-xs btn-dark rounded-pill px-3">Consider</a>
                                            <a href="<?= base_url('admin/management/suggestions/status/'.$s['suggestion_id'].'/rejected') ?>" class="btn btn-xs btn-outline-secondary border-0 ms-1"><i class="fas fa-times"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?= view('partials/admin/footer') ?>