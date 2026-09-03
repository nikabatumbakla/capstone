<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 12px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">My Alerts</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-bell me-2"></i>My Alerts and Tasks</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">System alerts and tasks assigned to you, or open to any staff on shift</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="?status=active" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">ACTIVE ALERTS</small>
                <h3 class="fw-bold mb-0"><?= $total_active ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="?status=active&priority=high" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">HIGH PRIORITY</small>
                <h3 class="fw-bold mb-0 text-danger"><?= $high_priority ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="?status=active&unread=1" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">UNREAD</small>
                <h3 class="fw-bold mb-0 text-primary"><?= $unread_count ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
</div>

<?php if ($status_filter !== 'active' || $priority_filter || $unread_filter): ?>
<div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
    <span><strong>
        <?php
            if ($priority_filter === 'high') {
                echo 'High Priority';
            } elseif ($unread_filter === '1') {
                echo 'Unread';
            } elseif ($status_filter === 'completed') {
                echo 'Completed';
            } else {
                echo 'Active Alerts';
            }
        ?>
    </strong></span>
    <a href="?status=active" class="text-danger fw-bold text-decoration-none"> ×</a>
</div>
<?php endif; ?>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex gap-2">
                        <a href="?status=active<?= $type_filter ? '&type='.$type_filter : '' ?>" class="btn btn-sm rounded-pill px-3 <?= $status_filter=='active'?'btn-dark':'btn-outline-dark' ?>">Active</a>
                        <a href="?status=completed<?= $type_filter ? '&type='.$type_filter : '' ?>" class="btn btn-sm rounded-pill px-3 <?= $status_filter=='completed'?'btn-dark':'btn-outline-dark' ?>">Completed</a>
                    </div>
                    <form id="filterForm" action="" method="GET">
                        <input type="hidden" name="status" value="<?= esc($status_filter) ?>">
                        <select name="type" class="form-select form-select-sm" style="width:170px;">
                            <option value="">All Types</option>
                            <option value="low_stock" <?= $type_filter=='low_stock'?'selected':'' ?>>Low Stock</option>
                            <option value="near_expiry" <?= $type_filter=='near_expiry'?'selected':'' ?>>Near Expiry</option>
                            <option value="expired" <?= $type_filter=='expired'?'selected':'' ?>>Expired</option>
                            <option value="assigned_task" <?= $type_filter=='assigned_task'?'selected':'' ?>>My Tasks</option>
                        </select>
                    </form>
                </div>

                <div class="alert-list">
                    <?php
                        $typeMeta = [
                            'low_stock'     => ['icon' => 'fa-box-open', 'color' => 'text-warning'],
                            'near_expiry'   => ['icon' => 'fa-hourglass-half', 'color' => 'text-info'],
                            'expired'       => ['icon' => 'fa-ban', 'color' => 'text-danger'],
                            'po_approval'   => ['icon' => 'fa-file-alt', 'color' => 'text-primary'],
                            'assigned_task' => ['icon' => 'fa-clipboard-check', 'color' => 'text-primary'],
                        ];
                    ?>
                    <?php if(empty($alerts)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fs-1 opacity-25 mb-2"></i>
                            <p><?= $status_filter === 'completed' ? 'No completed items yet.' : "No active alerts. You're all caught up." ?></p>
                        </div>
                    <?php else: foreach($alerts as $a):
                        $meta = $typeMeta[$a['alert_type']] ?? ['icon' => 'fa-bell', 'color' => 'text-muted'];
                        $isOverdue = $a['due_date'] && !$a['is_resolved'] && strtotime($a['due_date']) < strtotime(date('Y-m-d'));
                    ?>

                    <div class="p-3 mb-2 rounded-4 border bg-white shadow-sm d-flex align-items-center justify-content-between <?= ($a['is_read'] && $status_filter=='active') ? 'opacity-75' : '' ?>">
    <div class="d-flex align-items-center">
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
            <i class="fas <?= $meta['icon'] ?> <?= $meta['color'] ?>"></i>
        </div>
        <div>
            <small class="text-muted text-uppercase fw-bold" style="font-size: 9px;">
                <?= str_replace('_', ' ', $a['alert_type']) ?>
                <?php if($a['priority'] === 'high'): ?><span class="badge bg-danger ms-1" style="font-size:8px;">HIGH</span><?php endif; ?>
                <?php if($isOverdue): ?><span class="badge bg-warning text-dark ms-1" style="font-size:8px;">OVERDUE</span><?php endif; ?>
            </small>
            <h6 class="fw-bold text-dark mb-0" style="font-size: 12px;"><?= esc($a['message']) ?></h6>
            <?php if($a['notes']): ?><p class="mb-0 text-muted" style="font-size:10px;"><?= esc($a['notes']) ?></p><?php endif; ?>
            <small class="text-muted">
                <?= date('M d, Y h:i A', strtotime($a['created_at'])) ?>
                <?php if($a['due_date']): ?> · Due <?= date('M d, Y', strtotime($a['due_date'])) ?><?php endif; ?>
                <?php if($a['is_resolved'] && $a['resolved_at']): ?> · Completed <?= date('M d, Y h:i A', strtotime($a['resolved_at'])) ?><?php endif; ?>
            </small>
        </div>
    </div>

    <div class="d-flex gap-1">
    <?php if($a['product_id'] && $a['product_name']): ?>
        <a href="<?= base_url('staff/inventory/stock?search=' . urlencode($a['product_name'])) ?>" class="btn btn-xs btn-outline-primary rounded-circle" title="View Product" style="width:30px; height:30px;">
            <i class="fas fa-box-open"></i>
        </a>
    <?php endif; ?>
    <?php if($a['alert_type'] === 'assigned_task' && !$a['is_resolved']): ?>
        <a href="<?= base_url('staff/info/complete-task/'.$a['alert_id']) ?>" class="btn btn-xs btn-success rounded-circle" title="Mark Complete" style="width:30px; height:30px;">
            <i class="fas fa-check"></i>
        </a>
    <?php elseif(!$a['is_resolved'] && !$a['is_read']): ?>
        <a href="<?= base_url('staff/info/read-alert/'.$a['alert_id']) ?>" class="btn btn-xs btn-outline-dark rounded-circle" title="Mark as Read" style="width:30px; height:30px;">
            <i class="fas fa-envelope-open"></i>
        </a>
    <?php elseif($a['is_resolved']): ?>
        <i class="fas fa-check-double text-success" title="Completed"></i>
    <?php else: ?>
        <i class="fas fa-check-double text-success" title="Read"></i>
    <?php endif; ?>
</div>

</div>

                    <?php endforeach; endif; ?>
                </div>

                <?php
    $q = '&type='.$type_filter.'&status='.$status_filter;
    $w=3; $cb=(int)ceil($current_page/$w); $ws=(($cb-1)*$w)+1; $we=min($ws+$w-1,$total_pages);
?>
<?php if($total_pages > 1): ?>
<div class="d-flex justify-content-end mt-3">
    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
        <?php for($i=$ws;$i<=$we;$i++): ?><li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li><?php endfor; ?>
        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
    </ul></nav>
</div>
<?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('public/js/staff/info/alerts.js') ?>"></script>
<?= view('partials/staff/footer') ?>