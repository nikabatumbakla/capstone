<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-3">
    <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">My Profile</h5>
</div>

<div class="text-center mb-3">
    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:70px; height:70px; background:#7b1113; color:#fff; font-weight:800; font-size:22px;">
        <?= strtoupper(substr(session()->get('full_name'), 0, 2)) ?>
    </div>
    <h6 class="fw-bold mb-0"><?= esc(session()->get('full_name')) ?></h6>
    <small class="text-muted">Staff</small>
</div>

<div class="m-card">
    <p class="fw-bold mb-2" style="font-size:11px;"><i class="fas fa-chart-bar me-1"></i> TODAY'S STATS</p>
    <small class="text-muted">Scans: <?= $stats['scans'] ?> · POS Txns: <?= $stats['pos'] ?> · GRR: <?= $stats['grr'] ?></small>
</div>

<a href="<?= base_url('m/logout') ?>" class="btn w-100 py-3 rounded-pill fw-bold" style="background:#fdecea; color:#c0392b; border:none;">Logout</a>

<?= $this->endSection() ?>