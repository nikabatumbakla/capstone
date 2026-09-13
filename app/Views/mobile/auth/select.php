<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="m-auth-wrap">
    <div class="m-auth-icons">
        <img src="<?= base_url('public/images/mobile/logo.png') ?>" class="m-auth-logo-icon" alt="PharMediSync">
    </div>
    <div class="m-brand-name">PharMedi<span class="accent">Sync</span></div>
    <div class="m-brand-sub">ROBIN ROSE TRADING</div>
    <img src="<?= base_url('public/images/hero-medkit.png') ?>" class="m-medkit-img" alt="PharMediSync">

    <div style="width:100%; max-width:340px;">
        <a href="<?= base_url('m/login/walkin') ?>" class="m-btn-pill m-btn-red">WALK-IN CUSTOMER</a>
        <a href="<?= base_url('m/login/staff') ?>" class="m-btn-pill m-btn-red">STAFF</a>
    </div>

    <p class="m-auth-footer-note">SCAN PRODUCTS · BROWSE CATALOG · CHAT SUPPORT · REVIEWS</p>
</div>

<?= $this->endSection() ?>