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
    </div>
</div>
<?= view('partials/client/footer') ?>