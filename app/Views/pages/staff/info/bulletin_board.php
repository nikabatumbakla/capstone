<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 12px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Staff Bulletin Board</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-bullhorn me-2"></i>Announcements & Updates</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Internal announcements from management — view only</p>
            </div>

            <div class="custom-table-container">
                <h6 class="fw-bold mb-4 text-dark" style="font-size:13px;"><i class="fas fa-thumbtack me-2 text-danger"></i>Active Announcements</h6>

                <div class="post-feed">
                    <?php if(empty($posts)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fs-1 opacity-25 mb-2"></i>
                            <p>No active announcements right now.</p>
                        </div>
                    <?php else: foreach($posts as $p): ?>
                    <div class="p-4 mb-3 border bg-white shadow-sm rounded-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-start">
                                <?php if($p['image_path']): ?>
                                    <img src="<?= base_url($p['image_path']) ?>" class="rounded-3 me-3" style="width:45px; height:45px; object-fit:cover;">
                                <?php else: ?>
                                    <div class="bg-light p-2 rounded-circle me-3 text-center" style="width: 35px; height: 35px;">
                                        <i class="fas <?= $p['is_pinned'] ? 'fa-thumbtack text-danger' : 'fa-info-circle text-primary' ?>"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;"><?= esc($p['title']) ?></h6>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?> · Posted by <?= esc($p['author'] ?? 'Management') ?></small>
                                </div>
                            </div>
                            <?php if($p['is_pinned']): ?>
                                <span class="badge bg-danger px-3">IMPORTANT</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-dark mb-0 mt-3" style="line-height: 1.6;"><?= nl2br(esc($p['content'])) ?></p>
                    </div>
                    <?php endforeach; endif; ?>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-3">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1) ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <li class="page-item active"><span class="page-link"><?= $current_page ?></span></li>
                        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1) ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= view('partials/staff/footer') ?>