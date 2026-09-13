<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-1">
    <?php if(($page_name ?? '') !== 'home'): ?>
        <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <?php endif; ?>
    <h5 class="fw-bold mb-0">Operations Tasks</h5>
</div>
<p class="text-muted mb-3" style="font-size:11px;">Manage inventory and delivery duties</p>

<div class="row g-2 mb-3 text-center">
    <div class="col-4">
        <div class="m-card py-2"><h4 class="fw-bold mb-0"><?= $pending_count ?></h4><small class="text-muted">PENDING</small></div>
    </div>
    <div class="col-4">
        <div class="m-card py-2"><h4 class="fw-bold mb-0 text-danger"><?= $urgent_count ?></h4><small class="text-muted">URGENT</small></div>
    </div>
    <div class="col-4">
        <div class="m-card py-2"><h4 class="fw-bold mb-0" style="color:#457b9d;"><?= date('M d') ?></h4><small class="text-muted">TODAY</small></div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <p class="fw-bold mb-0" style="font-size:12px;">Active To-Do List</p>
    <span class="badge" style="background:#1d3557;"><?= count($tasks) ?> Total</span>
</div>

<?php if(empty($tasks)): ?>
    <div class="m-card text-center py-4"><p class="text-muted mb-0">No active tasks.</p></div>
<?php else: foreach($tasks as $t): ?>
<div class="m-card" style="border-left:4px solid <?= $t['priority']=='high' ? '#c0392b' : '#457b9d' ?>;">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <span class="badge mb-1" style="background:<?= $t['priority']=='high' ? '#f8d7da' : '#d6e4f0' ?>; color:<?= $t['priority']=='high' ? '#c0392b' : '#457b9d' ?>; font-size:9px;">
                <?= $t['priority']=='high' ? 'URGENT' : 'MEDIUM' ?>
            </span>
            <p class="fw-bold mb-1" style="font-size:12px;"><?= esc($t['message']) ?></p>
            <?php if(!empty($t['notes'])): ?><small class="text-muted d-block mb-1"><?= esc($t['notes']) ?></small><?php endif; ?>
        </div>
        <?php if($t['alert_type'] === 'assigned_task'): ?>
            <a href="<?= base_url('m/staff/tasks/complete/'.$t['alert_id']) ?>" class="btn btn-sm rounded-circle" style="width:32px; height:32px; border:1px solid #ccc;"><i class="fas fa-check text-muted" style="font-size:11px;"></i></a>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; endif; ?>

<?= $this->endSection() ?>