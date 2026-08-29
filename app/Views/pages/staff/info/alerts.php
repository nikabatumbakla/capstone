<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 12px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">My Alerts</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i>My Alerts and Tasks</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Assigned to staff only</p>
            </div>

            <!-- Summary Tiles -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card p-3 shadow-sm border-0 bg-white rounded-4">
                        <small class="info-label text-danger">URGENT NOTIFICATIONS</small>
                        <h3 class="fw-bold mb-0 text-danger"><?= count($alerts) ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-3 shadow-sm border-0 bg-white rounded-4">
                        <small class="info-label">NEW MESSAGES</small>
                        <h3 class="fw-bold mb-0 text-primary"><?= $unread_count ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-3 shadow-sm border-0 bg-dark text-white rounded-4">
                        <small class="info-label text-white-50">SYSTEM STATUS</small>
                        <h4 class="fw-bold mb-0 text-success">MONITORING ACTIVE</h4>
                    </div>
                </div>
            </div>

            <div class="custom-table-container">
                <h6 class="fw-bold mb-4" style="font-size: 13px;"><i class="fas fa-stream me-2 text-maroon"></i>Intelligence Feed</h6>
                
                <div class="alert-list">
                    <?php if(empty($alerts)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fs-1 opacity-25 mb-2"></i>
                            <p>No active alerts. Your terminal is up to date.</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach($alerts as $a): ?>
                    <div class="p-3 mb-2 rounded-4 border bg-white shadow-sm d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas <?= ($a['alert_type'] == 'low_stock') ? 'fa-box-open text-warning' : 'fa-bell text-primary' ?>"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 9px;"><?= str_replace('_', ' ', $a['alert_type']) ?></small>
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 12px;"><?= $a['message'] ?></h6>
                                <small class="text-muted"><?= date('M d, Y h:i A', strtotime($a['created_at'])) ?></small>
                            </div>
                        </div>
                        <div>
                            <?php if(!$a['is_read']): ?>
                                <a href="<?= base_url('staff/info/read-alert/'.$a['alert_id']) ?>" class="btn btn-xs btn-dark rounded-pill px-3">Dismiss</a>
                            <?php else: ?>
                                <i class="fas fa-check-double text-success me-2"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('partials/staff/footer') ?>