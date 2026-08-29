<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Announcements</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-credit-card me-2"></i>Announcements</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Posts from Robin Rose Trading for institutional clients</p>
            </div>
            
            <div class="row g-3">
                <?php foreach($posts as $p): ?>
                <div class="col-12">
                    <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius:20px;">
                        <div class="d-flex justify-content-between">
                            <h6 class="fw-bold text-maroon"><?= $p['title'] ?></h6>
                            <small class="text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?></small>
                        </div>
                        <p class="mb-0 mt-2 text-muted" style="font-size:12px; line-height:1.6;"><?= $p['content'] ?></p>
                        <?php if($p['is_pinned']): ?>
                            <span class="badge bg-soft-maroon text-maroon mt-3">IMPORTANT</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>