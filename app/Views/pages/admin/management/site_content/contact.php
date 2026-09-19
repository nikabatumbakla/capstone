<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

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
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-address-book me-2 text-maroon"></i>Contact Information</h6>
                        <form action="<?= base_url('admin/management/site-content/contact/save') ?>" method="POST">
                            <div class="row g-3 mb-3">
                                <div class="col-6"><label class="formal-label">Phone / Viber Number *</label><input type="text" name="phone" class="formal-input" value="<?= esc($contact->phone ?? '') ?>" required></div>
                                <div class="col-6"><label class="formal-label">Email Address *</label><input type="email" name="email" class="formal-input" value="<?= esc($contact->email ?? '') ?>" required></div>
                            </div>

                            <div class="mb-3"><label class="formal-label">Short Address (shown on homepage)</label><input type="text" name="address" class="formal-input" value="<?= esc($contact->address ?? '') ?>"></div>
                            <div class="mb-3"><label class="formal-label">Full Address (shown under the map)</label><input type="text" name="full_address" class="formal-input" value="<?= esc($contact->full_address ?? '') ?>"></div>
                            <div class="mb-4"><label class="formal-label">Business Hours</label><input type="text" name="business_hours" class="formal-input" value="<?= esc($contact->business_hours ?? '') ?>"></div>

                            <p class="text-maroon fw-bold small border-bottom pb-1 mb-3">SOCIAL MEDIA</p>
                            <div class="row g-3 mb-4">
                                <div class="col-4"><label class="formal-label">Facebook URL</label><input type="url" name="facebook_url" class="formal-input" value="<?= esc($contact->facebook_url ?? '') ?>" placeholder="https://facebook.com/..."></div>
                                <div class="col-4"><label class="formal-label">Instagram URL</label><input type="url" name="instagram_url" class="formal-input" value="<?= esc($contact->instagram_url ?? '') ?>" placeholder="https://instagram.com/..."></div>
                                <div class="col-4"><label class="formal-label">Viber Number</label><input type="text" name="viber_number" class="formal-input" value="<?= esc($contact->viber_number ?? '') ?>"></div>
                            </div>

                            <div class="mb-4">
                                <label class="formal-label">Google Maps Embed URL</label>
                                <textarea name="map_embed_url" class="formal-input" rows="2"><?= esc($contact->map_embed_url ?? '') ?></textarea>
                                <p class="helper-text mb-0">Get this from Google Maps → Share → Embed a map → copy the "src" value only.</p>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3">✓ SAVE CONTACT INFO</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/admin/footer') ?>