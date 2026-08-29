<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 12px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Staff Bulletin Board</h5>

            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i> Predictive Analysis — View Only</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">View only for staff</p>
            </div>

            <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius: 20px;">
                <h6 class="fw-bold mb-4 text-dark"><i class="fas fa-thumbtack me-2 text-danger"></i> Active Announcements</h6>
                
                <div class="post-feed">
                    <?php foreach($posts as $p): ?>
                    <div class="post-card p-4 mb-3 border bg-white shadow-sm position-relative" style="border-radius: 15px;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-circle me-3 text-center" style="width: 35px; height: 35px;">
                                    <i class="fas <?= $p['is_pinned'] ? 'fa-thumbtack text-danger' : 'fa-info-circle text-primary' ?>"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;"><?= $p['title'] ?></h6>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?> • Posted by: <?= $p['author'] ?></small>
                                </div>
                            </div>
                            <?php if($p['is_pinned']): ?>
                                <span class="badge bg-soft-success text-success px-3 border-0">IMPORTANT</span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-3">
                            <p class="text-dark fw-normal mb-0" style="line-height: 1.6;"><?= $p['content'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/staff/footer') ?>