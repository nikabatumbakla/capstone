<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?= $title ?? 'PharMediSync Mobile' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('public/css/mobile/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/mobile/customer_chatbot.css') ?>">
    <style>
        .m-custom-dropdown { position: relative; margin-bottom: 12px; }
.m-dropdown-toggle {
    width: 100%; text-align:left; background:#fff; border:1px solid #ddd; border-radius:10px;
    padding: 12px 14px; font-size:13px; color:#333; display:flex; justify-content:space-between; align-items:center;
}
.m-dropdown-toggle i { color:#999; font-size:11px; }
.m-dropdown-menu {
    display:none; position:absolute; top:105%; left:0; right:0; background:#fff;
    border:1px solid #ddd; border-radius:10px; box-shadow:0 6px 16px rgba(0,0,0,0.12);
    max-height:260px; overflow-y:auto; z-index:1500;
}
.m-dropdown-menu.open { display:block; }
.m-dropdown-item { padding:12px 14px; font-size:13px; color:#333; border-bottom:1px solid #f2f2f2; }
.m-dropdown-item:last-child { border-bottom:none; }
.m-dropdown-item:active { background:#f8f0f0; }
.m-dropdown-item.selected { background:#fdecea; color:#7b1113; font-weight:700; }
    </style>
</head>
<body>

<?php if (isset($isAuthPage) && $isAuthPage): ?>
    <?= $this->renderSection('content') ?>
<?php else: ?>
    <div class="m-topbar"></div>
    <div class="m-header">
        <i class="fas fa-heart-pulse"></i>
        <i class="fas fa-plus-square"></i>
        <span class="brand-title">ROBIN ROSE TRADING</span>
    </div>

    <div class="m-content">
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success py-2 small"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
    </div>

    <?php if(session()->get('role') === 'staff'): ?>
<nav class="m-bottom-nav">
    <a href="<?= base_url('m/staff/home') ?>" class="m-nav-item <?= ($page_name ?? '') === 'home' ? 'active' : '' ?>"><i class="fas fa-home"></i>HOME</a>
    <a href="<?= base_url('m/staff/tasks') ?>" class="m-nav-item <?= ($page_name ?? '') === 'tasks' ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i>TASKS</a>
    <a href="<?= base_url('m/staff/scan') ?>" class="m-nav-center <?= ($page_name ?? '') === 'scan' ? 'active' : '' ?>">
        <div class="m-nav-center-circle"><img src="<?= base_url('public/images/mobile/staff-iscan3.png') ?>"></div>
        iSCAN
    </a>
    <a href="<?= base_url('m/staff/alerts') ?>" class="m-nav-item <?= ($page_name ?? '') === 'alerts' ? 'active' : '' ?>"><i class="fas fa-bell"></i>ALERTS</a>
    <a href="<?= base_url('m/staff/profile') ?>" class="m-nav-item <?= ($page_name ?? '') === 'profile' ? 'active' : '' ?>"><i class="fas fa-user"></i>PROFILE</a>
</nav>
<?php elseif(session()->get('role') === 'customer'): ?>
<nav class="m-bottom-nav">
    <a href="<?= base_url('m/customer/home') ?>" class="m-nav-item <?= ($page_name ?? '') === 'home' ? 'active' : '' ?>"><i class="fas fa-home"></i>HOME</a>
    <a href="<?= base_url('m/customer/browse') ?>" class="m-nav-item <?= ($page_name ?? '') === 'browse' ? 'active' : '' ?>"><i class="fas fa-th-large"></i>BROWSE</a>
    <a href="<?= base_url('m/customer/scan') ?>" class="m-nav-center <?= ($page_name ?? '') === 'scan' ? 'active' : '' ?>">
        <div class="m-nav-center-circle"><img src="<?= base_url('public/images/mobile/staff-iscan3.png') ?>"></div>
        iSCAN
    </a>
    <a href="<?= base_url('m/customer/chatbot') ?>" class="m-nav-item <?= ($page_name ?? '') === 'chatbot' ? 'active' : '' ?>"><i class="fas fa-comment-dots"></i>PHARBOT</a>
    <a href="<?= base_url('m/customer/profile') ?>" class="m-nav-item <?= ($page_name ?? '') === 'profile' ? 'active' : '' ?>"><i class="fas fa-user"></i>PROFILE</a>
</nav>
<?php endif; ?>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>const BASE_URL = "<?= base_url() ?>";</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>