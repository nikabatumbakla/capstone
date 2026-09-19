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
                <h6 class="fw-bold mb-1"><i class="fas fa-quote-right me-2"></i>Client-Submitted Testimonials</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Clients submit these from their portal. Review and publish the ones you'd like shown on the public homepage.</p>
            </div>

            <?php if(session()->getFlashdata('success')): ?><div class="alert alert-success py-2 small"><?= session()->getFlashdata('success') ?></div><?php endif; ?>

            <div class="row g-4">
                <?= view('partials/admin/site_content_nav') ?>
                <div class="col-lg-9">
                    <div class="custom-table-container">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark"><tr><th class="ps-4">Client</th><th>Quote</th><th>Rating</th><th>Submitted</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                            <tbody>
                                <?php if(empty($testimonials)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No testimonials submitted yet.</td></tr>
                                <?php else: foreach($testimonials as $t): ?>
                                <tr>
                                    <td class="ps-4"><b><?= esc($t['client_name']) ?></b><br><small class="text-muted"><?= esc($t['client_role']) ?></small></td>
                                    <td><small class="text-muted"><?= esc(mb_strimwidth($t['quote'], 0, 60, '...')) ?></small></td>
                                    <td class="text-warning"><?= str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']) ?></td>
                                    <td><small class="text-muted"><?= date('M d, Y', strtotime($t['created_at'])) ?></small></td>
                                    <td><span class="badge <?= $t['is_published'] ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $t['is_published'] ? 'Published' : 'Pending Review' ?></span></td>
                                    <td class="text-center">
                                        <?php if($t['is_published']): ?>
                                            <a href="<?= base_url('admin/management/site-content/testimonials/toggle/'.$t['testimonial_id'].'?publish=0') ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-3">Hide</a>
                                        <?php else: ?>
                                            <a href="<?= base_url('admin/management/site-content/testimonials/toggle/'.$t['testimonial_id'].'?publish=1') ?>" class="btn btn-xs btn-success rounded-pill px-3">Publish</a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('admin/management/site-content/testimonials/delete/'.$t['testimonial_id']) ?>" class="btn btn-xs btn-outline-danger rounded-circle" onclick="return confirm('Delete this testimonial?')" style="width:26px; height:26px;"><i class="fas fa-trash"></i></a>
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
<?= view('partials/admin/footer') ?>