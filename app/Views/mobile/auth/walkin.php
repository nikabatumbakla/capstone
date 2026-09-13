<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="m-auth-wrap">

    <div class="m-auth-icons">
        <img src="<?= base_url('public/images/mobile/logo.png') ?>" class="m-auth-logo-icon" alt="PharMediSync">
    </div>
    <div class="m-brand-name">PharMedi<span class="accent">Sync</span></div>
    <div class="m-brand-sub">ROBIN ROSE TRADING</div>
    <img src="<?= base_url('public/images/hero-medkit.png') ?>" class="m-medkit-img" alt="PharMediSync">

    <div class="m-auth-section-title">WALK-IN CUSTOMER</div>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 small w-100" style="max-width:340px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('m/login/walkin') ?>" method="POST" style="width:100%; max-width:340px;">
        <?= csrf_field() ?>
        <label class="m-auth-label">FULL NAME</label>
        <input type="text" name="full_name" class="m-auth-input" required value="<?= old('full_name') ?>">

        <label class="m-auth-label">ADDRESS</label>
        <input type="text" name="address" class="m-auth-input" required value="<?= old('address') ?>">

        <button type="submit" class="m-btn-pill m-btn-red" style="border:none; cursor:pointer;">LOGIN</button>
        <a href="<?= base_url('m/') ?>" class="m-btn-pill" style="color:#999; text-decoration:underline; background:none; padding:4px;">← BACK</a>
    </form>

    <p class="m-auth-footer-note">SCAN PRODUCTS · BROWSE CATALOG · CHAT SUPPORT · REVIEWS</p>
</div>

<?= $this->endSection() ?>