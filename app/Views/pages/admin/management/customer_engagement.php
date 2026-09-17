<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>
<style>
    .eng-summary-card { background: #fff; border-radius: 20px; height: 100%; border: 1px solid rgba(0,0,0,0.06); }
    .nav-pills .nav-link { color: #666; font-weight: 700; font-size: 11px; }
    .nav-pills .nav-link.active { background-color: #0d2e4f !important; color: #fff; }
</style>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Customer Engagement</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-heart me-2"></i>Customer Engagement Hub</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Store Ratings · Product Suggestions</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">AVG STORE RATING</small>
                        <h3 class="fw-bold mb-0 text-primary"><?= $total_store_ratings > 0 ? number_format($avg_store_rating, 1) . ' / 5.0' : 'No ratings yet' ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">TOTAL RATINGS</small>
                        <h3 class="fw-bold mb-0"><?= $total_store_ratings ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">PENDING SUGGESTIONS</small>
                        <h3 class="fw-bold mb-0 text-info"><?= $pending_suggestions ?></h3>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="eng-summary-card p-4">
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-star text-warning me-2"></i>Recent Store Ratings</h6>
                        <?php if(empty($recent_store_ratings)): ?>
                            <p class="text-muted text-center py-4">No ratings submitted yet.</p>
                        <?php else: foreach($recent_store_ratings as $r): ?>
                        <div class="mb-3 pb-3 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark" style="font-size:12px;"><?= esc($r['customer'] ?? 'Customer') ?></span>
                            <div>
                                <span class="text-warning"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></span>
                                <small class="text-muted ms-2"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="eng-summary-card p-4">
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-chart-bar me-2 text-maroon"></i>Store Rating Breakdown</h6>
                        <?php if($total_store_ratings == 0): ?>
                            <p class="text-muted text-center py-4">No ratings recorded yet.</p>
                        <?php else: foreach($star_breakdown as $star => $percent): ?>
                        <div class="d-flex align-items-center mb-2">
                            <div class="text-warning me-2" style="width:65px;">
                                <?php for($i=1; $i<=5; $i++): ?><i class="<?= ($i <= $star) ? 'fas' : 'far' ?> fa-star" style="font-size:9px"></i><?php endfor; ?>
                            </div>
                            <div class="progress flex-grow-1" style="height:6px; background:#f1f5f9;">
                                <div class="progress-bar" style="width:<?= $percent ?>%; background:#7b1113; border-radius:10px;"></div>
                            </div>
                            <span class="ms-2 fw-bold text-muted" style="width:35px;"><?= round($percent) ?>%</span>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('partials/admin/footer') ?>