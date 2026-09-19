<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Announcements</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-bullhorn me-2"></i>Announcements</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Updates from Robin Rose Trading for institutional clients</p>
            </div>

            <?php if(session()->getFlashdata('success')): ?><div class="alert alert-success py-2 small"><?= session()->getFlashdata('success') ?></div><?php endif; ?>
            <?php if(session()->getFlashdata('error')): ?><div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div><?php endif; ?>

            <div class="row g-4">
                <!-- LEFT: ANNOUNCEMENTS -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-bold"><?= count($posts) ?> active announcement<?= count($posts) != 1 ? 's' : '' ?></span>
                        <form action="" method="GET">
                            <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search announcements..." style="width:220px;" value="<?= esc($search ?? '') ?>">
                        </form>
                    </div>

                    <div class="custom-table-container">
                        <?php if(empty($posts)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fs-1 opacity-25 mb-2"></i>
                                <p>No announcements right now.</p>
                            </div>
                        <?php else: foreach($posts as $p): ?>
                        <div class="p-4 mb-3 border rounded-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold text-maroon mb-1"><?= esc($p['title']) ?></h6>
                                <small class="text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?></small>
                            </div>
                            <?php if($p['image_path']): ?>
                                <img src="<?= base_url($p['image_path']) ?>" class="rounded-3 my-2" style="max-width:100%; max-height:200px; object-fit:cover;">
                            <?php endif; ?>
                            <p class="mb-0 mt-2 text-muted" style="font-size:11.5px; line-height:1.7;"><?= nl2br(esc($p['content'])) ?></p>
                            <?php if($p['is_pinned']): ?>
                                <span class="badge bg-danger mt-3">IMPORTANT</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; endif; ?>

                        <?php if($total_pages > 1): ?>
                        <div class="d-flex justify-content-end mt-3">
                            <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                                <?php for($i=1;$i<=$total_pages;$i++): ?>
                                    <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                                <?php endfor; ?>
                            </ul></nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RIGHT: CLIENT TESTIMONIALS -->
                <div class="col-lg-4">
                    <div class="custom-table-container">
                        <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-quote-right me-2 text-maroon"></i>Client Testimonials</h6>

                        <div class="p-3 mb-4 bg-light rounded-4">
                            <p class="fw-bold mb-2" style="font-size:11px;">Share your experience with Robin Rose Trading</p>
                            <form action="<?= base_url('client/support/announcements/submit-testimonial') ?>" method="POST">
                                <div class="mb-2">
                                    <?php for($i=1;$i<=5;$i++): ?>
                                        <i class="far fa-star testimonial-star" data-val="<?= $i ?>" style="color:#ccc; font-size:18px; cursor:pointer; margin-right:3px;"></i>
                                    <?php endfor; ?>
                                    <input type="hidden" name="rating" id="testimonialRating" value="0">
                                </div>
                                <textarea name="quote" class="form-control form-control-sm mb-2" rows="2" placeholder="Tell us about your experience..." required></textarea>
                                <button type="submit" class="btn btn-sm w-100 text-white" style="background:#7b1113;">Submit Feedback</button>
                            </form>
                            <p class="text-muted mb-0 mt-2" style="font-size:9px;">Your feedback will be reviewed before appearing on our public website.</p>
                        </div>

                        <?php if(empty($testimonials)): ?>
                            <p class="text-muted text-center py-3">No testimonials published yet.</p>
                        <?php else: foreach($testimonials as $t): ?>
                        <div class="p-3 mb-2 border rounded-4">
                            <div class="text-warning mb-1"><?= str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']) ?></div>
                            <p class="mb-1" style="font-size:11px; font-style:italic;">"<?= esc($t['quote']) ?>"</p>
                            <small class="text-muted">— <?= esc($t['client_name']) ?><?= $t['client_role'] ? ', ' . esc($t['client_role']) : '' ?></small>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.testimonial-star').forEach(star => {
    star.addEventListener('click', function() {
        const val = parseInt(this.dataset.val);
        document.getElementById('testimonialRating').value = val;
        document.querySelectorAll('.testimonial-star').forEach((s, i) => {
            s.classList.toggle('fas', i < val);
            s.classList.toggle('far', i >= val);
            s.style.color = i < val ? '#f1c40f' : '#ccc';
        });
    });
});
</script>

<?= view('partials/client/footer') ?>