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
                <h5 class="fw-bold mb-0">Public Site Content</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-globe me-2"></i>Manage Public Website</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Edit what visitors see on the Robin Rose Trading homepage.</p>
            </div>

            <?php if(session()->getFlashdata('success')): ?><div class="alert alert-success py-2 small"><?= session()->getFlashdata('success') ?></div><?php endif; ?>

            <div class="row g-4">
                <?= view('partials/admin/site_content_nav') ?>
                <div class="col-lg-9">

                    <!-- STORY / MISSION / VISION -->
                    <div class="custom-table-container mb-4">
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-book-open me-2 text-maroon"></i>Our Story, Mission & Vision</h6>
                        <form action="<?= base_url('admin/management/site-content/about/save') ?>" method="POST">
                            <div class="mb-3"><label class="formal-label">Story — Paragraph 1</label><textarea name="story_paragraph1" class="formal-input" rows="3"><?= esc($about->story_paragraph1 ?? '') ?></textarea></div>
                            <div class="mb-3"><label class="formal-label">Story — Paragraph 2</label><textarea name="story_paragraph2" class="formal-input" rows="3"><?= esc($about->story_paragraph2 ?? '') ?></textarea></div>
                            <div class="row g-3 mb-4">
                                <div class="col-6"><label class="formal-label">Our Mission</label><textarea name="mission_text" class="formal-input" rows="3"><?= esc($about->mission_text ?? '') ?></textarea></div>
                                <div class="col-6"><label class="formal-label">Our Vision</label><textarea name="vision_text" class="formal-input" rows="3"><?= esc($about->vision_text ?? '') ?></textarea></div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3">✓ SAVE ABOUT CONTENT</button>
                        </form>
                    </div>

                    <!-- TEAM MEMBERS -->
                    <div class="custom-table-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-users me-2 text-maroon"></i>Team Members</h6>
                            <button type="button" class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#teamDrawer" id="btnAddMember">
                                <i class="fas fa-plus me-1"></i> Add Member
                            </button>
                        </div>
                        <table class="table table-hover align-middle">
                            <thead class="table-dark"><tr><th>Name</th><th>Role</th><th>Order</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                            <tbody>
                                <?php if(empty($team)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No team members yet.</td></tr>
                                <?php else: foreach($team as $m): ?>
                                <tr>
    <td class="ps-4"><i class="fas fa-user-circle text-maroon me-2"></i><b><?= esc($m['name']) ?></b></td>
    <td><?= esc($m['role']) ?></td>
    <td><?= $m['sort_order'] ?></td>
    <td><span class="badge <?= $m['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $m['is_active'] ? 'Active' : 'Hidden' ?></span></td>
    <td class="text-center">
        <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle btn-edit-member" data-id="<?= $m['member_id'] ?>" style="width:30px; height:30px;"><i class="fas fa-edit"></i></button>
        <a href="<?= base_url('admin/management/site-content/about/team/delete/'.$m['member_id']) ?>" class="btn btn-xs btn-outline-danger rounded-circle" onclick="return confirm('Remove this team member?')" style="width:30px; height:30px;"><i class="fas fa-trash"></i></a>
    </td>
</tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="teamDrawer" style="width: 440px;">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0" id="teamDrawerTitle">Add Team Member</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
       <form action="<?= base_url('admin/management/site-content/about/team/save') ?>" method="POST">
    <input type="hidden" name="member_id" id="m_id">
    <div class="mb-3"><label class="formal-label">Name *</label><input type="text" name="name" id="m_name" class="formal-input" required></div>
    <div class="mb-3"><label class="formal-label">Role *</label><input type="text" name="role" id="m_role" class="formal-input" required></div>
    <div class="mb-3"><label class="formal-label">Display Order</label><input type="number" name="sort_order" id="m_order" class="formal-input" value="0"></div>
    <div class="mb-4 form-check"><input type="checkbox" name="is_active" id="m_active" class="form-check-input" checked><label class="form-check-label formal-label mb-0" for="m_active">Active</label></div>
    <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3">✓ SAVE MEMBER</button>
</form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('teamDrawer'));
    document.getElementById('btnAddMember').addEventListener('click', () => {
        document.getElementById('teamDrawerTitle').textContent = 'Add Team Member';
        document.querySelector('#teamDrawer form').reset();
        document.getElementById('m_id').value = '';
    });
    document.querySelectorAll('.btn-edit-member').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch(`${BASE_URL}/admin/management/site-content/about/team/edit/${this.getAttribute('data-id')}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('teamDrawerTitle').textContent = 'Edit Team Member';
                    document.getElementById('m_id').value = data.member_id;
                    document.getElementById('m_name').value = data.name;
                    document.getElementById('m_role').value = data.role;
                    document.getElementById('m_order').value = data.sort_order;
                    document.getElementById('m_active').checked = data.is_active == 1;
                    drawer.show();
                });
        });
    });
});
</script>
<?= view('partials/admin/footer') ?>