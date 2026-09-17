<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>
<script>
    const CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
    const CSRF_HASH = "<?= csrf_hash() ?>";
</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Alerts & Tasks</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-bell me-2"></i>Alerts & Task Management</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">System-detected conditions are tracked separately from staff-assigned work</p>
            </div>

            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill border" id="mainTabs" style="max-width:420px;">
                <li class="nav-item flex-grow-1"><button class="nav-link active rounded-pill w-100 fw-bold" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-alerts" type="button">System Alerts</button></li>
                <li class="nav-item flex-grow-1"><button class="nav-link rounded-pill w-100 fw-bold" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-tasks" type="button">My Tasks</button></li>
            </ul>

            <div class="tab-content">

                <!-- ============ SYSTEM ALERTS TAB ============ -->
                <div class="tab-pane fade show active" id="tab-alerts">
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <a href="?type=low_stock" class="text-decoration-none kpi-filter-link">
                                <div class="inventory-kpi-card position-relative <?= $type_filter == 'low_stock' ? 'border-bottom border-3 border-warning' : '' ?>">
                                    <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                                    <small class="text-muted fw-bold d-block mb-1">LOW STOCK</small>
                                    <h3 class="fw-bold mb-0 text-warning"><?= $count_low_stock ?></h3>
                                    <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?type=near_expiry" class="text-decoration-none kpi-filter-link">
                                <div class="inventory-kpi-card position-relative <?= $type_filter == 'near_expiry' ? 'border-bottom border-3 border-info' : '' ?>">
                                    <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                                    <small class="text-muted fw-bold d-block mb-1">NEAR EXPIRY</small>
                                    <h3 class="fw-bold mb-0 text-info"><?= $count_near_expiry ?></h3>
                                    <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?type=expired" class="text-decoration-none kpi-filter-link">
                                <div class="inventory-kpi-card position-relative <?= $type_filter == 'expired' ? 'border-bottom border-3 border-dark' : '' ?>">
                                    <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                                    <small class="text-muted fw-bold d-block mb-1">EXPIRED</small>
                                    <h3 class="fw-bold mb-0 text-dark"><?= $count_expired ?></h3>
                                    <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?type=po_approval" class="text-decoration-none kpi-filter-link">
                                <div class="inventory-kpi-card position-relative <?= $type_filter == 'po_approval' ? 'border-bottom border-3 border-danger' : '' ?>">
                                    <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                                    <small class="text-muted fw-bold d-block mb-1">PO PENDING</small>
                                    <h3 class="fw-bold mb-0 text-danger"><?= $count_po ?></h3>
                                    <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
                                </div>
                            </a>
                        </div>
                    </div>

                    <?php if ($type_filter): ?>
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
                        <span><strong><?= ['low_stock'=>'Low Stock','near_expiry'=>'Near Expiry','expired'=>'Expired','po_approval'=>'PO Pending'][$type_filter] ?? ucfirst($type_filter) ?></strong></span>
                        <a href="?" class="text-danger fw-bold text-decoration-none">×</a>
                    </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="custom-table-container">
                                <h6 class="fw-bold mb-3" style="font-size:13px;"><i class="fas fa-stream me-2 text-maroon"></i>Alert Feed</h6>
                                <p class="text-muted mb-3" style="font-size:10px;">Generated automatically from live inventory and procurement data. These resolve themselves once the underlying condition no longer applies.</p>

                                <?php if(empty($alerts)): ?>
                                    <div class="p-5 text-center text-muted">No open alerts — everything is currently within normal range.</div>
                                <?php else: foreach($alerts as $a): ?>
                                <div class="p-3 mb-2 rounded-4 border bg-white shadow-sm d-flex align-items-start">
                                    <div class="me-3 mt-1"><i class="fas fa-circle <?= $a['priority']=='high' ? 'text-danger' : 'text-secondary' ?>" style="font-size:8px"></i></div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:9px; letter-spacing:0.5px;"><?= str_replace('_', ' ', $a['alert_type']) ?></small>
                                        <p class="mb-0 fw-bold text-dark" style="font-size:12px;"><?= esc($a['message']) ?></p>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-success rounded-pill px-3 btn-resolve" data-id="<?= $a['alert_id'] ?>" data-label="Resolve">Resolve</button>
                                </div>
                                <?php endforeach; endif; ?>

                                <?php if($alert_total_pages > 1): ?>
                                <div class="d-flex justify-content-end mt-3">
                                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                                        <?php for($i=1;$i<=$alert_total_pages;$i++): ?>
                                            <li class="page-item <?= $i==$alert_current_page?'active':'' ?>"><a class="page-link" href="?alert_page=<?= $i ?>&type=<?= $type_filter ?>"><?= $i ?></a></li>
                                        <?php endfor; ?>
                                    </ul></nav>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="custom-table-container">
                                <h6 class="fw-bold mb-3" style="font-size:12px;"><i class="fas fa-info-circle me-2 text-maroon"></i>About This Feed</h6>
                                <p class="text-muted mb-0" style="font-size:10px;">Low Stock, Near Expiry, Expired, and PO Approval alerts reflect the current state of the business in real time. Resolved alerts are cleared automatically — there is nothing to delete or manage manually here.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ MY TASKS TAB ============ -->
                <div class="tab-pane fade" id="tab-tasks">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="custom-table-container">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-clipboard-list me-2 text-maroon"></i>Assigned Tasks</h6>
                                    <button type="button" class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#taskDrawer" id="btnNewTask">
                                        <i class="fas fa-plus me-1"></i>New Task
                                    </button>
                                </div>

                                <form id="taskFilterForm" action="" method="GET" class="d-flex flex-wrap gap-2 mb-3 pb-3 border-bottom">
                                    <input type="hidden" name="type" value="<?= esc($type_filter) ?>">
                                    <select name="task_status" class="form-select form-select-sm" style="width:130px;">
                                        <option value="open" <?= $task_status_filter=='open'?'selected':'' ?>>Open</option>
                                        <option value="resolved" <?= $task_status_filter=='resolved'?'selected':'' ?>>Completed</option>
                                    </select>
                                    <select name="task_priority" class="form-select form-select-sm" style="width:130px;">
                                        <option value="">All Priorities</option>
                                        <option value="high" <?= $task_priority_filter=='high'?'selected':'' ?>>High</option>
                                        <option value="normal" <?= $task_priority_filter=='normal'?'selected':'' ?>>Normal</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill px-3">Filter</button>
                                </form>

                                <?php if(empty($tasks)): ?>
                                    <div class="p-5 text-center text-muted">No tasks match this filter.</div>
                                <?php else: foreach($tasks as $a):
                                    $isOverdue = $a['due_date'] && !$a['is_resolved'] && strtotime($a['due_date']) < strtotime(date('Y-m-d'));
                                ?>
                                <div class="p-3 mb-2 rounded-4 border d-flex align-items-start <?= $a['is_resolved'] ? 'bg-light' : 'bg-white shadow-sm' ?>">
                                    <div class="me-3 mt-1"><i class="fas fa-circle <?= $a['priority']=='high' ? 'text-danger' : 'text-secondary' ?>" style="font-size:8px"></i></div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <?php if($a['is_resolved']): ?><span class="badge bg-success" style="font-size:8px;">COMPLETED</span><?php endif; ?>
                                            <?php if($isOverdue): ?><span class="badge bg-danger" style="font-size:8px;">OVERDUE</span><?php endif; ?>
                                        </div>
                                        <p class="mb-1 fw-bold text-dark" style="font-size:12px;"><?= esc($a['message']) ?></p>
                                        <?php if($a['notes']): ?><p class="mb-1 text-muted" style="font-size:10px;"><?= esc($a['notes']) ?></p><?php endif; ?>
                                        <div class="d-flex gap-3 text-muted" style="font-size:9.5px;">
                                            <?php if($a['assigned_name']): ?><span><i class="fas fa-user me-1"></i><?= esc($a['assigned_name']) ?></span><?php endif; ?>
                                            <?php if($a['due_date']): ?><span><i class="fas fa-calendar me-1"></i>Due <?= date('M d, Y', strtotime($a['due_date'])) ?></span><?php endif; ?>
                                            <?php if($a['is_resolved'] && $a['resolution_note']): ?><span><i class="fas fa-check-circle me-1"></i><?= esc($a['resolution_note']) ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <?php if(!$a['is_resolved']): ?>
                                            <button type="button" class="btn btn-xs btn-success rounded-pill px-3 btn-resolve" data-id="<?= $a['alert_id'] ?>" data-label="Complete" title="Mark Complete"><i class="fas fa-check"></i></button>
                                            <button type="button" class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-edit-task" data-id="<?= $a['alert_id'] ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                        <?php endif; ?>
                                        <a href="<?= base_url('admin/management/alerts/delete/'.$a['alert_id']) ?>" class="btn btn-xs btn-outline-danger rounded-pill px-2" onclick="return confirm('Delete this task permanently?')" title="Delete"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                                <?php endforeach; endif; ?>

                                <?php if($task_total_pages > 1): ?>
                                <div class="d-flex justify-content-end mt-3">
                                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                                        <?php for($i=1;$i<=$task_total_pages;$i++): ?>
                                            <li class="page-item <?= $i==$task_current_page?'active':'' ?>"><a class="page-link" href="?task_page=<?= $i ?>&task_status=<?= $task_status_filter ?>&task_priority=<?= $task_priority_filter ?>"><?= $i ?></a></li>
                                        <?php endfor; ?>
                                    </ul></nav>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="custom-table-container">
                                <h6 class="fw-bold mb-3" style="font-size:12px;"><i class="fas fa-info-circle me-2 text-maroon"></i>About Tasks</h6>
                                <p class="text-muted mb-0" style="font-size:10px;">Tasks are staff-assigned work items — anything outside the system's automatic alerts. Overdue tasks are automatically escalated to High priority. Completed tasks remain on record for the audit trail; use Delete to remove a genuine mistake.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="taskDrawer" style="width: 480px;">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0" id="taskDrawerTitle">New Task</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/alerts/save') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="alert_id" id="task_id">
            <div class="row g-3 mb-3">
                <div class="col-6"><label class="formal-label">Priority *</label>
                    <select name="priority" id="task_priority" class="form-select formal-input" required>
                        <option value="normal">Normal</option>
                        <option value="high">Urgent / High</option>
                    </select>
                </div>
                <div class="col-6"><label class="formal-label">Due Date</label>
                    <input type="date" name="due_date" id="task_due" class="formal-input">
                </div>
            </div>
            <div class="mb-3"><label class="formal-label">Assign To</label>
                <select name="assigned_to" id="task_assigned" class="form-select formal-input">
                    <option value="">Unassigned</option>
                    <?php foreach($assignable_staff as $s): ?>
                        <option value="<?= $s['user_id'] ?>"><?= esc($s['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3"><label class="formal-label">Task Message *</label>
                <textarea name="message" id="task_message" class="formal-input" rows="4" required placeholder="Describe the task..."></textarea>
            </div>
            <div class="mb-4"><label class="formal-label">Notes (optional)</label>
                <textarea name="notes" id="task_notes" class="formal-input" rows="2" placeholder="Additional context..."></textarea>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow">✓ SAVE TASK</button>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="resolveDrawer" style="width: 400px;">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0" id="resolveDrawerTitle">Resolve Alert</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
        <p class="text-muted mb-3" style="font-size:11px;">Add a short note on what was done (optional but recommended for the audit trail).</p>
        <textarea id="resolveNote" class="formal-input mb-3" rows="3" placeholder="e.g. Restocked 50 units via PO-2026-0042"></textarea>
        <button type="button" class="btn btn-success w-100 py-2 fw-bold rounded-3" id="btnConfirmResolve"><i class="fas fa-check me-2"></i>Confirm</button>
    </div>
</div>

<script>
    setTimeout(function() {
        const el = document.getElementById('flashSuccess');
        if (el) { el.style.transition = 'opacity 0.5s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }
    }, 5000);
</script>
<script src="<?= base_url('public/js/admin/management/alerts.js') ?>"></script>
<?= view('partials/admin/footer') ?>