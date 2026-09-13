<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="m-auth-wrap">
    <div class="m-auth-icons">
        <img src="<?= base_url('public/images/mobile/logo.png') ?>" class="m-auth-logo-icon" alt="PharMediSync">
    </div>
    <div class="m-brand-name">PharMedi<span class="accent">Sync</span></div>
    <div class="m-brand-sub">ROBIN ROSE TRADING</div>
    <img src="<?= base_url('public/images/hero-medkit.png') ?>" class="m-medkit-img" alt="PharMediSync">

    <div class="m-auth-section-title">STAFF LOGIN</div>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 small w-100" style="max-width:340px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('m/login/staff') ?>" method="POST" style="width:100%; max-width:340px;">
        <?= csrf_field() ?>
        <label class="m-auth-label">EMAIL</label>
        <input type="email" name="username" class="m-auth-input" required value="<?= old('username') ?>">

        <label class="m-auth-label">PASSWORD</label>
        <input type="password" name="password" class="m-auth-input" required>

        <button type="submit" class="m-btn-pill m-btn-red" style="border:none; cursor:pointer;">LOGIN</button>
        <a href="<?= base_url('m/') ?>" class="m-btn-pill" style="color:#999; text-decoration:underline; background:none; padding:4px;">← BACK</a>
    </form>

    <p class="m-auth-footer-note">STAFF ISCAN PORTAL</p>
</div>

<?= $this->endSection() ?>