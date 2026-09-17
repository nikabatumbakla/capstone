<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-3">
    <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Product Info</h5>
</div>

<div class="text-center mb-2">
    <div style="height:180px; background:#f4f4f4; border-radius:12px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
        <?php if($p->image_path): ?><img src="<?= base_url($p->image_path) ?>" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?><i class="fas fa-box-open fs-1 text-muted"></i><?php endif; ?>
    </div>
</div>

<h5 class="fw-bold mb-0"><?= esc($p->name) ?></h5>
<h4 class="fw-bold mb-2" style="color:#7b1113;">₱<?= number_format($p->sell_price ?? 0, 2) ?></h4>

<div class="mb-3">
    <?php
        $isOut = $p->total_stock <= 0;
        $isLow = !$isOut && $p->reorder_level && $p->total_stock <= $p->reorder_level;
    ?>
    <span class="badge <?= $isOut ? 'bg-secondary' : ($isLow ? 'bg-warning text-dark' : 'bg-success') ?>"><?= $isOut ? 'Out of Stock' : ($isLow ? 'Low Stock ('.$p->total_stock.' Units)' : 'In Stock ('.$p->total_stock.' Units)') ?></span>
    <span class="badge bg-light text-dark border ms-1"><?= esc($p->category_name) ?></span>
</div>

<ul class="nav nav-pills mb-3 gap-1" id="prodTabs">
    <li class="nav-item flex-fill"><button type="button" class="btn btn-sm w-100 tab-btn active" data-tab="info">Info</button></li>
    <li class="nav-item flex-fill"><button type="button" class="btn btn-sm w-100 tab-btn" data-tab="details">Details</button></li>
    <li class="nav-item flex-fill"><button type="button" class="btn btn-sm w-100 tab-btn" data-tab="education">Education</button></li>
    <li class="nav-item flex-fill"><button type="button" class="btn btn-sm w-100 tab-btn" data-tab="warnings">Warnings</button></li>
</ul>

<div id="tab-info" class="tab-pane">
    <div class="m-card">
        <p class="fw-bold mb-1" style="font-size:11px;">🏷 BRAND & MANUFACTURER</p>
        <small class="text-muted d-block mb-3"><?= esc($p->brand ?: $p->manufacturer ?: 'N/A') ?></small>
        <p class="fw-bold mb-1" style="font-size:11px;">📦 AVAILABILITY</p>
        <small class="text-muted d-block mb-3"><?= $isOut ? 'Currently unavailable' : ($isLow ? '⚠ Low Stock — Only '.$p->total_stock.' units remaining' : 'Available') ?></small>
        <?php if($p->batch_number): ?>
        <p class="fw-bold mb-1" style="font-size:11px;">🏷 BATCH NUMBER</p>
        <small class="text-muted d-block mb-3"><?= esc($p->batch_number) ?></small>
        <?php endif; ?>
        <?php if($p->expires_at): ?>
        <p class="fw-bold mb-1" style="font-size:11px;">📅 EXPIRY DATE</p>
        <small class="text-muted d-block mb-3"><?= date('F Y', strtotime($p->expires_at)) ?></small>
        <?php endif; ?>
        <p class="fw-bold mb-1" style="font-size:11px;">🏬 STORE AVAILABILITY</p>
        <small class="text-muted d-block">Available at Robin Rose Trading, Iriga City</small>
    </div>
</div>

<div id="tab-details" class="tab-pane" style="display:none;">
    <div class="m-card">
        <?php if($p->medical_description): ?>
        <p class="fw-bold mb-1" style="font-size:11px;">MEDICAL DESCRIPTION</p>
        <small class="text-muted d-block mb-3"><?= esc($p->medical_description) ?></small>
        <?php endif; ?>
        <?php if($p->usage_purpose): ?>
        <p class="fw-bold mb-1" style="font-size:11px;">USAGE PURPOSE</p>
        <small class="text-muted d-block mb-3"><?= esc($p->usage_purpose) ?></small>
        <?php endif; ?>
        <?php if($p->usage_guide): ?>
        <p class="fw-bold mb-1" style="font-size:11px;">INSTRUCTIONS FOR USE</p>
        <small class="text-muted d-block mb-3"><?= nl2br(esc($p->usage_guide)) ?></small>
        <?php endif; ?>
        <?php if($p->warranty_info): ?>
        <p class="fw-bold mb-1" style="font-size:11px;">WARRANTY</p>
        <small class="text-muted d-block"><?= esc($p->warranty_info) ?></small>
        <?php endif; ?>
        <?php if(!$p->medical_description && !$p->usage_purpose && !$p->usage_guide && !$p->warranty_info): ?>
            <small class="text-muted">No additional details available for this product.</small>
        <?php endif; ?>
    </div>
</div>

<div id="tab-education" class="tab-pane" style="display:none;">
    <div class="m-card">
        <?php
            $embedUrl = null;
            if ($p->video_url) {
                if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]+)#', $p->video_url, $m)) {
                    $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                } elseif (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $p->video_url, $m) || preg_match('/id=([a-zA-Z0-9_-]+)/', $p->video_url, $m)) {
                    $embedUrl = 'https://drive.google.com/file/d/'.$m[1].'/preview';
                }
            }
        ?>
        <?php if($embedUrl): ?>
            <p class="fw-bold mb-2" style="font-size:11px;">🎥 VIDEO GUIDE</p>
            <div class="ratio ratio-16x9 mb-3">
                <iframe src="<?= $embedUrl ?>" allowfullscreen allow="autoplay; encrypted-media" style="border-radius:10px; border:1px solid #ddd;"></iframe>
            </div>
        <?php elseif($p->video_url): ?>
            <p class="fw-bold mb-2" style="font-size:11px;">🎥 REFERENCE LINK</p>
            <a href="<?= esc($p->video_url) ?>" target="_blank" class="d-block mb-3" style="font-size:11px;"><?= esc($p->video_url) ?></a>
        <?php endif; ?>
        <?php if($p->healthcare_tips): ?>
            <p class="fw-bold mb-1" style="font-size:11px;">💡 HEALTHCARE TIPS</p>
            <small class="text-muted d-block mb-3"><?= nl2br(esc($p->healthcare_tips)) ?></small>
        <?php endif; ?>
        <?php if($p->storage_info): ?>
            <p class="fw-bold mb-1" style="font-size:11px;">📦 STORAGE INFORMATION</p>
            <small class="text-muted d-block"><?= nl2br(esc($p->storage_info)) ?></small>
        <?php endif; ?>
        <?php if(!$embedUrl && !$p->video_url && !$p->healthcare_tips && !$p->storage_info): ?>
            <small class="text-muted">No educational content available for this product.</small>
        <?php endif; ?>
    </div>
</div>

<div id="tab-warnings" class="tab-pane" style="display:none;">
    <div class="m-card">
        <?php if($p->warnings): ?>
        <div class="alert alert-warning small mb-0">
            <p class="fw-bold mb-1">⚠ WARNINGS</p>
            <?= nl2br(esc($p->warnings)) ?>
        </div>
        <?php else: ?>
            <small class="text-muted">No specific warnings listed for this product. Always consult a healthcare professional if unsure.</small>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
            this.classList.add('active');
            document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
        });
    });
</script>
<?= $this->endSection() ?>