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
                        <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-image me-2 text-maroon"></i>Homepage Hero Section</h6>
                        <form action="<?= base_url('admin/management/site-content/hero/save') ?>" method="POST">
                            <div class="mb-3">
                                <label class="formal-label">Badge Text</label>
                                <input type="text" name="badge_text" class="formal-input" value="<?= esc($hero->badge_text ?? '') ?>" placeholder="e.g. FDA Certified & BIR Compliant">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-4">
                                    <label class="formal-label">Headline Line 1</label>
                                    <input type="text" name="headline_line1" class="formal-input" value="<?= esc($hero->headline_line1 ?? '') ?>">
                                </div>
                                <div class="col-4">
                                    <label class="formal-label">Accent Word</label>
                                    <input type="text" name="headline_accent" class="formal-input" value="<?= esc($hero->headline_accent ?? '') ?>">
                                </div>
                                <div class="col-4">
                                    <label class="formal-label">Headline Line 3</label>
                                    <input type="text" name="headline_line3" class="formal-input" value="<?= esc($hero->headline_line3 ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="formal-label">Subtext</label>
                                <textarea name="subtext" class="formal-input" rows="3"><?= esc($hero->subtext ?? '') ?></textarea>
                            </div>

                            <p class="text-maroon fw-bold small border-bottom pb-1 mb-3">TRUST STATISTICS</p>
                            <div class="row g-3 mb-4">
                                <?php for($i=1;$i<=4;$i++): ?>
                                <div class="col-3">
                                    <label class="formal-label">Stat <?= $i ?> Number</label>
                                    <input type="text" name="stat<?= $i ?>_number" class="formal-input mb-2" value="<?= esc($hero->{"stat{$i}_number"} ?? '') ?>">
                                    <label class="formal-label">Stat <?= $i ?> Label</label>
                                    <input type="text" name="stat<?= $i ?>_label" class="formal-input" value="<?= esc($hero->{"stat{$i}_label"} ?? '') ?>">
                                </div>
                                <?php endfor; ?>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3">✓ SAVE HERO SECTION</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/admin/footer') ?>