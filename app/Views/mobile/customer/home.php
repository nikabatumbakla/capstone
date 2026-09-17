<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<h5 class="fw-bold mb-1">
    <?php
        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $displayName = session()->get('walkin_name') ?: session()->get('full_name');
        echo esc($greeting) . ', ' . esc(explode(' ', $displayName)[0]) . '!';
    ?>
</h5>
<p class="text-muted mb-3" style="font-size:11px;"><?= date('l, F d, Y') ?></p>

<a href="<?= base_url('m/customer/scan') ?>" class="text-decoration-none">
    <div class="m-card text-center" style="background:#fdecea; border:none;">
        <p class="fw-bold mb-1" style="font-size:12px; color:#7b1113;"><i class="fas fa-barcode me-1"></i>SCAN ANY PRODUCT BARCODE</p>
        <small class="text-muted">Get detailed product info, safety tips, and reviews instantly</small>
    </div>
</a>

<?php
    $iconMap = [
        'diagnostic'    => ['fa-stethoscope', '#457b9d'],
        'monitoring'    => ['fa-heart-pulse', '#457b9d'],
        'respiratory'   => ['fa-lungs', '#2a9d8f'],
        'mobility'      => ['fa-wheelchair', '#264653'],
        'rehab'         => ['fa-wheelchair', '#264653'],
        'orthopedic'    => ['fa-bone', '#495057'],
        'consumable'    => ['fa-box-open', '#f77f00'],
        'supply'        => ['fa-box-open', '#f77f00'],
        'supplies'      => ['fa-box-open', '#f77f00'],
        'surgical'      => ['fa-syringe', '#9d0208'],
        'syringe'       => ['fa-syringe', '#9d0208'],
        'needle'        => ['fa-syringe', '#9d0208'],
        'diabetic'      => ['fa-syringe', '#9d0208'],
        'ppe'           => ['fa-shield-virus', '#e76f51'],
        'infection'     => ['fa-shield-virus', '#e76f51'],
        'safety'        => ['fa-hard-hat', '#f4a261'],
        'wound'         => ['fa-kit-medical', '#c0392b'],
        'emergency'     => ['fa-kit-medical', '#c0392b'],
        'rescue'        => ['fa-kit-medical', '#c0392b'],
        'first aid'     => ['fa-briefcase-medical', '#e63946'],
        'incontinence'  => ['fa-droplet', '#3a86ff'],
        'fluid'         => ['fa-droplet', '#3a86ff'],
        'education'     => ['fa-graduation-cap', '#8338ec'],
        'specialty'     => ['fa-graduation-cap', '#8338ec'],
        'general'       => ['fa-bag-shopping', '#6c757d'],
        'merchandise'   => ['fa-bag-shopping', '#6c757d'],
        'hygiene'       => ['fa-hand-sparkles', '#06aed5'],
        'personal'      => ['fa-hand-sparkles', '#06aed5'],
        'furniture'     => ['fa-chair', '#6c757d'],
        'fixture'       => ['fa-chair', '#6c757d'],
        'otc'           => ['fa-pills', '#e63946'],
        'medicine'      => ['fa-pills', '#e63946'],
        'prescription'  => ['fa-prescription-bottle', '#d62828'],
        'nutrition'     => ['fa-apple-whole', '#588157'],
        'vitamin'       => ['fa-apple-whole', '#588157'],
    ];
?>

<p class="fw-bold mb-2" style="font-size:12px;">Browse By Category</p>
<div class="row g-2 mb-3">
    <?php foreach($categories as $cat):
        [$icon, $color] = getCategoryIcon($cat['name'], $iconMap);
    ?>
    <div class="col-4 d-flex">
        <a href="<?= base_url('m/customer/browse?cat='.$cat['category_id']) ?>" class="text-decoration-none w-100">
            <div class="m-card text-center d-flex flex-column align-items-center justify-content-center" style="background:#f4f6f8; height:88px; padding:10px 6px;">
                <i class="fas <?= $icon ?> fs-5 mb-1" style="color:<?= $color ?>;"></i>
                <p class="mb-0" style="font-size:9px; color:#333; line-height:1.2; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?= esc($cat['name']) ?></p>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <p class="fw-bold mb-0" style="font-size:12px;">Featured Products</p>
    <a href="<?= base_url('m/customer/browse') ?>" class="text-decoration-none" style="font-size:10px; color:#7b1113;">See All</a>
</div>
<div class="d-flex gap-2 mb-3" style="overflow-x:auto;">
    <?php foreach($featured as $p): ?>
    <a href="<?= base_url('m/customer/product/'.$p['product_id']) ?>" class="text-decoration-none flex-shrink-0" style="width:120px;">
        <div class="m-card p-2">
            <div style="height:70px; background:#f4f4f4; border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:6px;">
                <?php if($p['image_path']): ?><img src="<?= base_url($p['image_path']) ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?><i class="fas fa-box-open text-muted"></i><?php endif; ?>
            </div>
            <p class="mb-1 fw-bold" style="font-size:10px; color:#333;"><?= esc($p['name']) ?></p>
            <p class="mb-1 fw-bold" style="font-size:11px; color:#7b1113;">₱<?= number_format($p['price'] ?? 0, 2) ?></p>
            <span class="badge <?= $p['stock']>0 ? 'bg-success' : 'bg-secondary' ?>" style="font-size:8px;"><?= $p['stock']>0 ? 'In Stock' : 'Out' ?></span>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php if(!empty($announcements)): ?>
<p class="fw-bold mb-2" style="font-size:12px;">Announcements</p>
<?php foreach($announcements as $a): ?>
<div class="m-card">
    <p class="fw-bold mb-1" style="font-size:11px;"><?= esc($a['title']) ?></p>
    <small class="text-muted"><?= esc(substr($a['content'], 0, 80)) ?>...</small>
</div>
<?php endforeach; endif; ?>

<?= $this->endSection() ?>