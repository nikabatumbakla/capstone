<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-1">
    <?php if(($page_name ?? '') !== 'home'): ?>
        <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <?php endif; ?>
    <h5 class="fw-bold mb-0">Notifications</h5>
</div>
<p class="text-muted mb-2" style="font-size:11px;">Inventory alerts and system updates</p>

<?php $unreadCount = count(array_filter($alerts, fn($a) => empty($a['is_read']))); ?>
<div class="d-flex justify-content-between mb-3">
    <small class="text-muted"><?= $unreadCount ?> Unread</small>
    <a href="#" class="text-decoration-none" style="font-size:11px; color:#1d3557;">Mark all as read</a>
</div>

<?php if(empty($alerts)): ?>
    <div class="m-card text-center py-4">
        <i class="fas fa-check-circle fs-2 text-success mb-2"></i>
        <p class="text-muted mb-0">You're all caught up.</p>
    </div>
<?php else:
    $iconMap = ['low_stock' => 'fa-box-open', 'near_expiry' => 'fa-hourglass-half', 'expired' => 'fa-ban', 'po_approval' => 'fa-file-alt', 'assigned_task' => 'fa-clipboard-check'];
    $colorMap = ['low_stock' => '#e74c3c', 'near_expiry' => '#f1c40f', 'expired' => '#7b1113', 'po_approval' => '#3498db', 'assigned_task' => '#3498db'];
    foreach($alerts as $a):
        $icon = $iconMap[$a['alert_type']] ?? 'fa-bell';
        $color = $colorMap[$a['alert_type']] ?? '#6c757d';
?>
<div class="m-card">
    <div class="d-flex align-items-start">
        <i class="fas <?= $icon ?> me-2 mt-1" style="color:<?= $color ?>;"></i>
        <div class="flex-grow-1">
            <p class="mb-1" style="font-size:12px;"><?= esc($a['message']) ?></p>
            <?php if($a['priority'] === 'high'): ?><span class="badge bg-danger me-1" style="font-size:9px;">HIGH</span><?php endif; ?>
            <small class="text-muted"><?= date('M d, h:i A', strtotime($a['created_at'])) ?></small>
        </div>
    </div>
    <?php if($a['alert_type'] === 'assigned_task'): ?>
        <a href="<?= base_url('m/staff/alerts/complete/'.$a['alert_id']) ?>" class="btn btn-sm w-100 mt-2 text-white" style="background:#28a745;">✓ Mark Complete</a>
    <?php endif; ?>
</div>
<?php endforeach; endif; ?>

<?= $this->endSection() ?>