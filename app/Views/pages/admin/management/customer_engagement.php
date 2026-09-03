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
                <p class="mb-0 opacity-75" style="font-size: 10px;">Product Reviews · Suggestions · Store Feedback</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">AVG PRODUCT RATING</small>
                        <h3 class="fw-bold mb-0 text-warning"><?= $total_product_reviews > 0 ? number_format($avg_product_rating, 1) . ' / 5.0' : 'No ratings yet' ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">AVG STORE RATING</small>
                        <h3 class="fw-bold mb-0 text-primary"><?= $total_store_feedback > 0 ? number_format($avg_store_rating, 1) . ' / 5.0' : 'No ratings yet' ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">PENDING SUGGESTIONS</small>
                        <h3 class="fw-bold mb-0 text-info"><?= $pending_suggestions ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="inventory-kpi-card">
                        <small class="text-muted fw-bold d-block mb-1">TOTAL PRODUCT REVIEWS</small>
                        <h3 class="fw-bold mb-0"><?= $total_product_reviews ?></h3>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="eng-summary-card p-4">
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-star text-warning me-2"></i>Recent Reviews</h6>
                        <?php if(empty($recent_reviews)): ?>
                            <p class="text-muted text-center py-4">No approved reviews yet.</p>
                        <?php else: foreach($recent_reviews as $r): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold mb-0 text-dark" style="font-size:12px;"><?= esc($r['pname']) ?></h6>
                                <span class="text-warning"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></span>
                            </div>
                            <p class="mb-0 text-muted fst-italic">"<?= esc($r['comment']) ?>" — <?= esc($r['customer']) ?></p>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="eng-summary-card p-4">
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-chart-bar me-2 text-maroon"></i>Product Rating Breakdown</h6>
                        <?php if($total_product_reviews == 0): ?>
                            <p class="text-muted text-center py-4">No product reviews recorded yet.</p>
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

            <div class="custom-table-container">
                <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill border">
                    <li class="nav-item flex-grow-1"><button class="nav-link active rounded-pill w-100" data-bs-toggle="pill" data-bs-target="#m-reviews">Product Reviews</button></li>
                    <li class="nav-item flex-grow-1"><button class="nav-link rounded-pill w-100" data-bs-toggle="pill" data-bs-target="#m-suggestions">Product Suggestions</button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="m-reviews">
                        <form action="" method="GET" class="d-flex justify-content-end gap-2 mb-3">
                            <select name="review_status" class="form-select form-select-sm" style="width:140px;" onchange="this.form.submit()">
                                <option value="all" <?= $review_status=='all'?'selected':'' ?>>All</option>
                                <option value="pending" <?= $review_status=='pending'?'selected':'' ?>>Pending</option>
                                <option value="approved" <?= $review_status=='approved'?'selected':'' ?>>Live</option>
                            </select>
                            <input type="text" name="review_search" class="form-control form-control-sm rounded-pill" placeholder="Search product/customer..." style="width:200px;" value="<?= esc($review_search) ?>">
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark"><tr><th class="ps-4">Date</th><th>Customer</th><th>Product</th><th>Rating</th><th>Comment</th><th class="text-center">Action</th></tr></thead>
                                <tbody>
                                    <?php if(empty($all_reviews)): ?>
                                        <tr><td colspan="6" class="text-center py-5 text-muted">No reviews match this filter.</td></tr>
                                    <?php else: foreach($all_reviews as $r): ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                        <td class="fw-bold"><?= esc($r['customer']) ?></td>
                                        <td><?= esc($r['pname']) ?></td>
                                        <td class="text-warning"><?= str_repeat('★', $r['rating']) ?></td>
                                        <td><small class="text-muted"><?= esc($r['comment']) ?></small></td>
                                        <td class="text-center">
                                            <?php if(!$r['is_approved']): ?>
                                                <a href="<?= base_url('admin/management/reviews/approve/'.$r['review_id']) ?>" class="btn btn-xs btn-success rounded-pill px-3">Approve</a>
                                            <?php else: ?>
                                                <span class="badge bg-light text-success border border-success px-3">LIVE</span>
                                            <?php endif; ?>
                                            <a href="<?= base_url('admin/management/reviews/delete/'.$r['review_id']) ?>" class="btn btn-xs btn-outline-danger border-0 ms-2" onclick="return confirm('Remove this review?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                            $rq = '&review_status='.$review_status.'&review_search='.urlencode($review_search);
                            $rw=3; $rcb=(int)ceil($review_current_page/$rw); $rws=(($rcb-1)*$rw)+1; $rwe=min($rws+$rw-1,$review_total_pages);
                        ?>
                        <div class="d-flex justify-content-end mt-3">
                            <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                                <li class="page-item <?= $review_current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$review_current_page-1) ?><?= $rq ?>"><i class="fas fa-chevron-left"></i></a></li>
                                <?php for($i=$rws;$i<=$rwe;$i++): ?><li class="page-item <?= $i==$review_current_page?'active':'' ?>"><a class="page-link" href="?review_page=<?= $i.$rq ?>"><?= $i ?></a></li><?php endfor; ?>
                                <li class="page-item <?= $review_current_page>=$review_total_pages?'disabled':'' ?>"><a class="page-link" href="?review_page=<?= min($review_total_pages,$review_current_page+1).$rq ?>"><i class="fas fa-chevron-right"></i></a></li>
                            </ul></nav>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="m-suggestions">
                        <form action="" method="GET" class="d-flex justify-content-end gap-2 mb-3">
                            <select name="sug_status" class="form-select form-select-sm" style="width:140px;" onchange="this.form.submit()">
                                <option value="all" <?= $sug_status=='all'?'selected':'' ?>>All</option>
                                <option value="pending" <?= $sug_status=='pending'?'selected':'' ?>>Pending</option>
                                <option value="approved" <?= $sug_status=='approved'?'selected':'' ?>>Approved</option>
                                <option value="rejected" <?= $sug_status=='rejected'?'selected':'' ?>>Rejected</option>
                            </select>
                            <input type="text" name="sug_search" class="form-control form-control-sm rounded-pill" placeholder="Search product name..." style="width:200px;" value="<?= esc($sug_search) ?>">
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark"><tr><th class="ps-4">Product Name</th><th>Category</th><th>Requested By</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                                <tbody>
                                    <?php if(empty($all_suggestions)): ?>
                                        <tr><td colspan="5" class="text-center py-5 text-muted">No suggestions match this filter.</td></tr>
                                    <?php else: foreach($all_suggestions as $s): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= esc($s['product_name']) ?></td>
                                        <td><?= esc($s['category'] ?: '—') ?></td>
                                        <td><?= esc($s['requester'] ?: 'Guest') ?></td>
                                        <td><span class="badge rounded-pill bg-<?= $s['status']=='pending'?'warning text-dark':($s['status']=='approved'?'success':'secondary') ?> px-3"><?= strtoupper($s['status']) ?></span></td>
                                        <td class="text-center">
                                            <?php if($s['status']=='pending'): ?>
                                                <a href="<?= base_url('admin/management/suggestions/status/'.$s['suggestion_id'].'/approved') ?>" class="btn btn-xs btn-dark rounded-pill px-3">Approve</a>
                                                <a href="<?= base_url('admin/management/suggestions/status/'.$s['suggestion_id'].'/rejected') ?>" class="btn btn-xs btn-outline-secondary border-0 ms-1"><i class="fas fa-times"></i></a>
                                            <?php else: ?>
                                                <span class="text-muted">Reviewed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                            $sq = '&sug_status='.$sug_status.'&sug_search='.urlencode($sug_search);
                            $sw=3; $scb=(int)ceil($sug_current_page/$sw); $sws=(($scb-1)*$sw)+1; $swe=min($sws+$sw-1,$sug_total_pages);
                        ?>
                        <div class="d-flex justify-content-end mt-3">
                            <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                                <li class="page-item <?= $sug_current_page<=1?'disabled':'' ?>"><a class="page-link" href="?sug_page=<?= max(1,$sug_current_page-1).$sq ?>"><i class="fas fa-chevron-left"></i></a></li>
                                <?php for($i=$sws;$i<=$swe;$i++): ?><li class="page-item <?= $i==$sug_current_page?'active':'' ?>"><a class="page-link" href="?sug_page=<?= $i.$sq ?>"><?= $i ?></a></li><?php endfor; ?>
                                <li class="page-item <?= $sug_current_page>=$sug_total_pages?'disabled':'' ?>"><a class="page-link" href="?sug_page=<?= min($sug_total_pages,$sug_current_page+1).$sq ?>"><i class="fas fa-chevron-right"></i></a></li>
                            </ul></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('partials/admin/footer') ?>