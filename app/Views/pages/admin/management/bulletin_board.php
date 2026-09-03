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
                <h5 class="fw-bold mb-0">Announcement</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-bullhorn me-2"></i>Announcement</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Internal Announcements · Public Posts</p>
            </div>


            <div class="row g-4 mb-4">
                <div class="col-md-3"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">TOTAL POSTS</small><h3 class="fw-bold mb-0"><?= $counts['total'] ?></h3></div></div>
                <div class="col-md-3"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">PINNED</small><h3 class="fw-bold mb-0 text-danger"><?= $counts['pinned'] ?></h3></div></div>
                <div class="col-md-3"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">PUBLISHED</small><h3 class="fw-bold mb-0 text-success"><?= $counts['published'] ?></h3></div></div>
                <div class="col-md-3"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">DRAFTS</small><h3 class="fw-bold mb-0 text-muted"><?= $counts['drafts'] ?></h3></div></div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-thumbtack me-2 text-maroon"></i>Active Posts</h6>
                    <div class="d-flex gap-2">
    <form id="filterForm" action="" method="GET" class="d-flex gap-2">
        <select name="audience" class="form-select form-select-sm" style="width:170px;">
            <option value="">All Audiences</option>
            <option value="all" <?= $audience_filter=='all'?'selected':'' ?>>All Channels (Public)</option>
            <option value="staff" <?= $audience_filter=='staff'?'selected':'' ?>>Staff Portal</option>
            <option value="clients" <?= $audience_filter=='clients'?'selected':'' ?>>Client Portal</option>
            <option value="customers" <?= $audience_filter=='customers'?'selected':'' ?>>Customer App</option>
        </select>
        <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill" placeholder="Search posts..." style="width:180px;" value="<?= esc($search) ?>">
    </form>

    <button class="btn btn-sm btn-outline-dark rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#archiveDrawer" id="btnOpenArchive">
        <i class="fas fa-archive me-1"></i>Archive
    </button>
    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold" id="btnAddNewPost" data-bs-toggle="offcanvas" data-bs-target="#bulletinDrawer">
        <i class="fas fa-plus me-1"></i>Create Announcement
    </button>
</div>
                </div>

                <div class="post-feed">
                    <?php if(empty($posts)): ?>
                        <p class="text-center text-muted py-5">No announcements match this filter.</p>
                    <?php endif; ?>

                    <?php
                        $statusMeta = [
                            'live'      => ['label' => 'Live', 'class' => 'bg-success'],
                            'scheduled' => ['label' => 'Scheduled', 'class' => 'bg-info'],
                            'expired'   => ['label' => 'Expired', 'class' => 'bg-secondary'],
                            'draft'     => ['label' => 'Draft', 'class' => 'bg-warning text-dark'],
                        ];
                    ?>
                    <?php foreach($posts as $p): $sm = $statusMeta[$p['status']]; ?>
                    <div class="p-4 mb-3 border bg-white shadow-sm rounded-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-start">
                                <?php if($p['image_path']): ?>
                                    <img src="<?= base_url($p['image_path']) ?>" class="rounded-3 me-3" style="width:50px; height:50px; object-fit:cover;">
                                <?php else: ?>
                                    <div class="bg-light p-2 rounded-circle me-3 text-center" style="width:35px; height:35px;">
                                        <i class="fas fa-bullhorn text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size:13px;"><?= esc($p['title']) ?></h6>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?> · Target: <?= ucfirst($p['target_audience']) ?> · By <?= esc($p['author'] ?? 'System') ?></small>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <?php if($p['is_pinned']): ?><span class="badge bg-dark px-2">Pinned</span><?php endif; ?>
                                <span class="badge <?= $sm['class'] ?> px-2"><?= $sm['label'] ?></span>
                            </div>
                        </div>

                        <p class="text-dark mb-0 mt-3" style="line-height:1.6;"><?= nl2br(esc($p['content'])) ?></p>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end gap-2">
    <button class="btn btn-xs btn-outline-secondary rounded-pill px-3 btn-edit-post" data-id="<?= $p['post_id'] ?>"><i class="fas fa-edit me-1"></i>Edit</button>
    <a href="<?= base_url('admin/management/bulletin/delete/'.$p['post_id']) ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-3" onclick="return confirm('Move this announcement to the archive?')"><i class="fas fa-archive me-1"></i>Archive</a>
</div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php
                    $q = '&audience='.$audience_filter.'&search='.urlencode($search);
                    $windowSize=3; $currentBlock=(int)ceil($current_page/$windowSize);
                    $windowStart=(($currentBlock-1)*$windowSize)+1; $windowEnd=min($windowStart+$windowSize-1,$total_pages);
                ?>
                <div class="d-flex justify-content-end mt-3">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i=$windowStart;$i<=$windowEnd;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="bulletinDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0" id="drawerTitle">Create New Announcement</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/bulletin/save') ?>" method="POST" id="bulletinForm" enctype="multipart/form-data">
            <input type="hidden" name="post_id" id="form_post_id">

            <div class="mb-3"><label class="formal-label">Bulletin Title *</label>
                <input type="text" name="title" id="form_title" class="formal-input" required placeholder="Subject of the announcement">
            </div>

            <div class="mb-3"><label class="formal-label">Image (optional)</label>
                <input type="file" name="image" id="form_image" class="form-control formal-input" accept="image/*">
                <div id="form_image_preview" class="mt-2"></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="formal-label">Target Audience</label>
                    <select name="audience" id="form_audience" class="form-select formal-input">
                        <option value="all">All Channels (Public)</option>
                        <option value="staff">Internal Staff Portal Only</option>
                        <option value="clients">Institutional Client Portal</option>
                        <option value="customers">Customer Mobile App</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Configuration</label>
                    <div class="d-flex gap-3 pt-2">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_pinned" id="form_pinned"><label class="small fw-bold">Pin Post</label></div>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_published" id="form_published" checked><label class="small fw-bold">Go Live</label></div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
    <div class="col-6"><label class="formal-label">Display Starts *</label><input type="datetime-local" name="starts_at" id="form_start" class="formal-input" required></div>
    <div class="col-6"><label class="formal-label">Display Ends *</label><input type="datetime-local" name="ends_at" id="form_end" class="formal-input" required></div>
</div>
<p class="helper-text mb-3"><i class="fas fa-info-circle me-1"></i>Once the end time passes, this announcement is automatically archived and removed from active view.</p>

            <div class="mb-4"><label class="formal-label">Message Content *</label>
                <textarea name="content" id="form_content" class="formal-input" rows="8" required placeholder="Type the message body..."></textarea>
            </div>

            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow" id="btnSubmit">SAVE & PUBLISH ANNOUNCEMENT</button>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="archiveDrawer" style="width: 480px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0"><i class="fas fa-archive me-2"></i>Archived Announcements</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="archiveListContent"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="repostDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Repost Announcement</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/bulletin/repost') ?>" method="POST">
            <input type="hidden" name="archive_id" id="repost_archive_id">

            <div class="mb-3"><label class="formal-label">Title *</label>
                <input type="text" name="title" id="repost_title_input" class="formal-input" required>
            </div>

            <div class="mb-3"><label class="formal-label">Content *</label>
                <textarea name="content" id="repost_content_input" class="formal-input" rows="5" required></textarea>
            </div>

            <div class="mb-3"><label class="formal-label">New Display Starts *</label>
                <input type="datetime-local" name="starts_at" id="repost_starts_at" class="formal-input" required>
            </div>
            <div class="mb-4"><label class="formal-label">New Display Ends *</label>
                <input type="datetime-local" name="ends_at" id="repost_ends_at" class="formal-input" required>
            </div>

            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow">✓ REPOST ANNOUNCEMENT</button>
        </form>
    </div>
</div>

<script src="<?= base_url('public/js/admin/management/bulletin.js') ?>"></script>
<?= view('partials/admin/footer') ?>