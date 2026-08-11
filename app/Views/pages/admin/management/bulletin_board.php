<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content" style="background: #f8fafc;">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Bulletin Board</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>Bulletin Board</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Internal Announcements · Public Posts</p>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-4">
    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold" id="btnAddNewPost"
            data-bs-toggle="offcanvas"
            data-bs-target="#bulletinDrawer">
        + Create Announcement
    </button>
</div>

            <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius: 20px;">
                <h6 class="fw-bold mb-4 text-dark"><i class="fas fa-thumbtack me-2 text-danger"></i> Active Posts</h6>
                
                <div class="post-feed">
                    <?php if(empty($posts)): ?>
                        <p class="text-center text-muted py-5">No active announcements on the board.</p>
                    <?php endif; ?>

                    <?php foreach($posts as $p): ?>
                    <div class="post-card p-4 mb-3 border bg-white shadow-sm position-relative" style="border-radius: 15px;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-circle me-3 text-center" style="width: 35px; height: 35px;">
                                    <i class="fas <?= strpos(strtolower($p['title']), 'product') !== false ? 'fa-capsules text-warning' : 'fa-briefcase text-secondary' ?>"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;"><?= $p['title'] ?></h6>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?> • Target: <?= ucfirst($p['target_audience']) ?></small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if($p['is_pinned']): ?>
                                    <span class="badge bg-soft-success text-success px-3 border-0">Pinned</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-dark fw-normal mb-0" style="line-height: 1.6;"><?= $p['content'] ?></p>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <!-- EDIT BUTTON -->
                            <button class="btn btn-xs btn-outline-secondary rounded-pill px-3 btn-edit-post" data-id="<?= $p['post_id'] ?>">
                                <i class="fas fa-edit me-1"></i> Edit Post
                            </button>
                            <a href="<?= base_url('admin/management/bulletin/delete/'.$p['post_id']) ?>" class="btn btn-xs btn-outline-danger rounded-pill px-3" onclick="return confirm('Delete this record?')">
                                <i class="fas fa-trash me-1"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SINGLE FORM DRAWER (Used for both Add and Edit) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="bulletinDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Announcement Intelligence Form</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/bulletin/save') ?>" method="POST">
            <input type="hidden" name="post_id" id="post_id">
            
            <div class="mb-3"><label class="formal-label">Bulletin Title *</label>
                <input type="text" name="title" id="post_title" class="formal-input" required placeholder="Subject of the announcement">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="formal-label">Target Audience</label>
                    <select name="audience" id="post_audience" class="form-select formal-input">
                        <option value="all">All Channels (Public)</option>
                        <option value="staff">Internal Staff Portal Only</option>
                        <option value="clients">Institutional Client Portal</option>
                        <option value="customers">Customer Mobile App</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Configuration</label>
                    <div class="d-flex gap-3 pt-2">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_pinned" id="post_pinned"><label class="small fw-bold">Pin Post</label></div>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_published" id="post_published" checked><label class="small fw-bold">Go Live</label></div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6"><label class="formal-label">Display Starts (Optional)</label><input type="datetime-local" name="starts_at" id="post_start" class="formal-input"></div>
                <div class="col-6"><label class="formal-label">Display Ends (Optional)</label><input type="datetime-local" name="ends_at" id="post_end" class="formal-input"></div>
            </div>

            <div class="mb-4"><label class="formal-label">Message Content *</label>
                <textarea name="content" id="post_content" class="formal-input" rows="8" required placeholder="Type the message body..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow">SAVE & PUBLISH ANNOUNCEMENT</button>
        </form>
    </div>
</div>


<script src="<?= base_url('public/js/admin/management_bulletin.js') ?>"></script>
<?= view('partials/admin/footer') ?>