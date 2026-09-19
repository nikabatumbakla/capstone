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
                    <div class="custom-table-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-handshake me-2 text-maroon"></i>Services Cards</h6>
                            <button type="button" class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#svcDrawer" id="btnAddService">
                                <i class="fas fa-plus me-1"></i> Add Service
                            </button>
                        </div>
                        <table class="table table-hover align-middle">
                            <thead class="table-dark"><tr><th class="ps-4">Icon</th><th>Title</th><th>Order</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                            <tbody>
                                <?php if(empty($services)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No services yet.</td></tr>
                                <?php else: foreach($services as $s): ?>
                                <tr>
                                    <td class="ps-4"><i class="fas <?= esc($s['icon']) ?> text-maroon"></i></td>
                                    <td><b><?= esc($s['title']) ?></b></td>
                                    <td><?= $s['sort_order'] ?></td>
                                    <td><span class="badge <?= $s['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $s['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle btn-edit-svc" data-id="<?= $s['service_id'] ?>" style="width:30px; height:30px;"><i class="fas fa-edit"></i></button>
                                        <a href="<?= base_url('admin/management/site-content/services/delete/'.$s['service_id']) ?>" class="btn btn-xs btn-outline-danger rounded-circle" onclick="return confirm('Delete this service?')" style="width:30px; height:30px;"><i class="fas fa-trash"></i></a>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="svcDrawer" style="width: 460px;">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0" id="svcDrawerTitle">Add Service</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/site-content/services/save') ?>" method="POST">
            <input type="hidden" name="service_id" id="s_id">
            <div class="mb-3"><label class="formal-label">Icon (Font Awesome class) *</label><input type="text" name="icon" id="s_icon" class="formal-input" required></div>
            <div class="mb-3"><label class="formal-label">Title *</label><input type="text" name="title" id="s_title" class="formal-input" required></div>
            <div class="mb-3"><label class="formal-label">Description *</label><textarea name="description" id="s_desc" class="formal-input" rows="3" required></textarea></div>
            <div class="mb-3"><label class="formal-label">Feature List (one per line)</label><textarea name="feature_list" id="s_features" class="formal-input" rows="4" placeholder="Hospitals & Clinics&#10;Schools & Universities"></textarea></div>
            <div class="mb-3"><label class="formal-label">Display Order</label><input type="number" name="sort_order" id="s_order" class="formal-input" value="0"></div>
            <div class="mb-4 form-check"><input type="checkbox" name="is_active" id="s_active" class="form-check-input" checked><label class="form-check-label formal-label mb-0" for="s_active">Active</label></div>
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3">✓ SAVE SERVICE</button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('svcDrawer'));
    document.getElementById('btnAddService').addEventListener('click', () => {
        document.getElementById('svcDrawerTitle').textContent = 'Add Service';
        document.querySelector('#svcDrawer form').reset();
        document.getElementById('s_id').value = '';
    });
    document.querySelectorAll('.btn-edit-svc').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch(`${BASE_URL}/admin/management/site-content/services/edit/${this.getAttribute('data-id')}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('svcDrawerTitle').textContent = 'Edit Service';
                    document.getElementById('s_id').value = data.service_id;
                    document.getElementById('s_icon').value = data.icon;
                    document.getElementById('s_title').value = data.title;
                    document.getElementById('s_desc').value = data.description;
                    document.getElementById('s_features').value = data.feature_list;
                    document.getElementById('s_order').value = data.sort_order;
                    document.getElementById('s_active').checked = data.is_active == 1;
                    drawer.show();
                });
        });
    });
});
</script>
<?= view('partials/admin/footer') ?>