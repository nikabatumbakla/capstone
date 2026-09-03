<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Pending Applications</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-clock me-2"></i>New Supplier & Client Applications</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Review and approve before they appear in the main Identity Registry</p>
            </div>

            <div class="d-flex gap-2 mb-4">
                <a href="?role=" class="btn btn-sm rounded-pill px-3 <?= $role_filter=='' ? 'btn-dark' : 'btn-outline-dark' ?>">All</a>
                <a href="?role=institutional_client" class="btn btn-sm rounded-pill px-3 <?= $role_filter=='institutional_client' ? 'btn-dark' : 'btn-outline-dark' ?>">Clients</a>
                <a href="?role=supplier" class="btn btn-sm rounded-pill px-3 <?= $role_filter=='supplier' ? 'btn-dark' : 'btn-outline-dark' ?>">Suppliers</a>
            </div>

            <?php if(empty($applications)): ?>
                <div class="custom-table-container text-center py-5 text-muted">No pending applications.</div>
            <?php else: ?>
                <?php foreach($applications as $app): ?>
                <div class="custom-table-container mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-light text-dark border mb-2"><?= $app['role']=='supplier' ? 'SUPPLIER APPLICATION' : 'CLIENT APPLICATION' ?></span>
                            <h6 class="fw-bold mb-1"><?= esc($app['full_name']) ?></h6>
                            <p class="text-muted mb-0"><?= esc($app['email']) ?> &middot; <?= esc($app['phone']) ?></p>
                            <small class="text-muted">Applied <?= date('M d, Y', strtotime($app['created_at'])) ?></small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-dark rounded-circle btn-view-app" data-id="<?= $app['user_id'] ?>" title="View Details" style="width:34px; height:34px;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if($total_pages > 1): ?>
            <div class="d-flex justify-content-end mt-3">
                <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                    <?php for($i=1;$i<=$total_pages;$i++): ?>
                        <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&role=<?= $role_filter ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="appDetailsDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0" id="appDrawerTitle">Application Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="appDrawerContent">
        <div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>
    </div>
    <div class="p-3 border-top d-flex gap-2" id="appDrawerActions"></div>
</div>

<script src="<?= base_url('public/js/admin/management/pending_applications.js') ?>"></script>
<?= view('partials/admin/footer') ?>