<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<h5 class="fw-bold mb-1">
    <?php
        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        echo esc($greeting) . ', ' . esc(explode(' ', session()->get('full_name'))[0]) . '!';
    ?>
</h5>
<p class="text-muted mb-3" style="font-size:11px;"><?= date('l, F d, Y') ?></p>

<div class="row g-2 mb-3">
    <div class="col-6">
        <div class="m-card text-center py-3">
            <h3 class="fw-bold mb-0" style="color:#7b1113;"><?= $summary['my_scans_today'] ?></h3>
            <small class="text-muted">My Scans Today</small>
        </div>
    </div>
    <div class="col-6">
        <div class="m-card text-center py-3">
            <h3 class="fw-bold mb-0 text-warning"><?= $summary['my_alerts'] ?></h3>
            <small class="text-muted">My Open Alerts</small>
        </div>
    </div>
</div>

<a href="<?= base_url('m/staff/scan') ?>" class="text-decoration-none">
    <div class="m-card text-center" style="background:#7b1113; color:#fff;">
        <i class="fas fa-barcode fs-2 mb-2"></i>
        <p class="fw-bold mb-0">Start Scanning</p>
        <small style="opacity:.8;">Inbound · Outbound · GRR</small>
    </div>
</a>

<div class="m-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <p class="fw-bold mb-0" style="font-size:12px;">My Tasks & Alerts</p>
        <a href="<?= base_url('m/staff/tasks') ?>" class="text-decoration-none" style="font-size:10px; color:#7b1113;">View All</a>
    </div>
    <?php if(empty($my_tasks)): ?>
        <small class="text-muted">You're all caught up — no open tasks or alerts.</small>
    <?php else:
        $iconMap = ['low_stock' => 'fa-box-open', 'near_expiry' => 'fa-hourglass-half', 'expired' => 'fa-ban', 'po_approval' => 'fa-file-alt', 'assigned_task' => 'fa-clipboard-check'];
        $colorMap = ['low_stock' => '#e74c3c', 'near_expiry' => '#f1c40f', 'expired' => '#7b1113', 'po_approval' => '#3498db', 'assigned_task' => '#3498db'];
        foreach($my_tasks as $t):
            $icon = $iconMap[$t['alert_type']] ?? 'fa-bell';
            $color = $colorMap[$t['alert_type']] ?? '#6c757d';
    ?>
        <div class="d-flex align-items-start border-bottom py-2">
            <i class="fas <?= $icon ?> me-2 mt-1" style="color:<?= $color ?>; font-size:12px;"></i>
            <div class="flex-grow-1">
                <p class="mb-0" style="font-size:11px;"><?= esc($t['message']) ?></p>
                <?php if($t['priority'] === 'high'): ?><span class="badge bg-danger" style="font-size:8px;">HIGH</span><?php endif; ?>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<div class="m-card">
    <p class="fw-bold mb-2" style="font-size:12px;">Recent Scans</p>
    <?php if(empty($recent_scans)): ?>
        <small class="text-muted">No scans recorded yet.</small>
    <?php else: foreach($recent_scans as $s): ?>
        <div class="d-flex justify-content-between border-bottom py-2">
            <span><?= esc($s['product_name']) ?></span>
            <span class="badge bg-light text-dark border"><?= strtoupper($s['movement_type']) ?></span>
        </div>
    <?php endforeach; endif; ?>
</div>

<?= $this->endSection() ?>