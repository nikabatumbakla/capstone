<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="text-center mb-3">
    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:70px; height:70px; background:#7b1113; color:#fff; font-weight:800; font-size:22px;">
        <?= strtoupper(substr($name, 0, 2)) ?>
    </div>
    <h6 class="fw-bold mb-0"><?= esc($name) ?></h6>
    <small class="text-muted"><?= esc($address) ?></small>
</div>

<?php if(session()->getFlashdata('success')): ?><div class="alert alert-success small"><?= session()->getFlashdata('success') ?></div><?php endif; ?>
<?php if(session()->getFlashdata('error')): ?><div class="alert alert-danger small"><?= session()->getFlashdata('error') ?></div><?php endif; ?>

<div class="m-card text-center">
    <p class="fw-bold mb-2" style="font-size:12px;">Rate Robin Rose Trading</p>
    <?php if($store_rating['avg']): ?>
        <p class="mb-2" style="color:#f1c40f; font-size:14px;">
            <?php for($i=1;$i<=5;$i++): ?><i class="fa<?= $i <= round($store_rating['avg']) ? 's' : 'r' ?> fa-star"></i><?php endfor; ?>
            <span style="font-size:10px; color:#666;"> <?= $store_rating['avg'] ?>/5 (<?= $store_rating['count'] ?> ratings)</span>
        </p>
    <?php else: ?>
        <p class="text-muted mb-2" style="font-size:10px;">Be the first to rate us!</p>
    <?php endif; ?>
    <form action="<?= base_url('m/customer/profile/rate-store') ?>" method="POST">
        <div id="storeStarPicker" class="mb-3" style="font-size:26px; color:#ccc;">
            <?php for($i=1;$i<=5;$i++): ?><i class="far fa-star store-star-pick" data-val="<?= $i ?>" style="cursor:pointer; margin:0 3px;"></i><?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="storeRatingInput" value="0">
        <button type="submit" class="btn w-100 text-white" style="background:#1d3557;">Submit Rating</button>
    </form>
</div>

<p class="fw-bold mb-2" style="font-size:12px;"><i class="fas fa-bullhorn me-1" style="color:#7b1113;"></i>Announcements</p>
<?php if(empty($announcements)): ?>
    <div class="m-card text-center py-4">
        <p class="text-muted mb-0" style="font-size:11px;">No announcements right now.</p>
    </div>
<?php else: foreach($announcements as $a): ?>
    <div class="m-card">
        <div class="d-flex justify-content-between align-items-start mb-1">
            <p class="fw-bold mb-0" style="font-size:11px;"><?= esc($a['title']) ?></p>
            <?php if($a['is_pinned']): ?><span class="badge bg-danger" style="font-size:8px;">PINNED</span><?php endif; ?>
        </div>
        <?php if(!empty($a['image_path'])): ?>
            <img src="<?= base_url($a['image_path']) ?>" class="rounded-3 mb-2" style="width:100%; max-height:140px; object-fit:cover;">
        <?php endif; ?>
        <p class="mb-1" style="font-size:11px; color:#444; line-height:1.5;"><?= esc($a['content']) ?></p>
        <small class="text-muted"><?= date('M d, Y', strtotime($a['created_at'])) ?></small>
    </div>
<?php endforeach; endif; ?>

<a href="<?= base_url('m/logout') ?>" class="btn w-100 py-3 rounded-pill fw-bold mt-2" style="background:#fdecea; color:#c0392b; border:none;">Logout</a>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('public/js/mobile/customer_profile.js') ?>"></script>
<?= $this->endSection() ?>