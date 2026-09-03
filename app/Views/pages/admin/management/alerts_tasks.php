<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

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
                <p class="mb-0 opacity-75" style="font-size: 10px;">Low Stock · Near Expiry · Expired · PO Approval — auto-generated from live inventory and procurement data</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <a href="?status=open&type=low_stock" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $type_filter=='low_stock' ? 'border-bottom border-3 border-warning' : '' ?>">
                            <small class="text-muted fw-bold d-block mb-1">LOW STOCK</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $count_low_stock ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?status=open&type=near_expiry" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $type_filter=='near_expiry' ? 'border-bottom border-3 border-info' : '' ?>">
                            <small class="text-muted fw-bold d-block mb-1">NEAR EXPIRY</small>
                            <h3 class="fw-bold mb-0 text-info"><?= $count_near_expiry ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?status=open&type=expired" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $type_filter=='expired' ? 'border-bottom border-3 border-dark' : '' ?>">
                            <small class="text-muted fw-bold d-block mb-1">EXPIRED</small>
                            <h3 class="fw-bold mb-0 text-dark"><?= $count_expired ?></h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?status=open&type=po_approval" class="text-decoration-none">
                        <div class="inventory-kpi-card <?= $type_filter=='po_approval' ? 'border-bottom border-3 border-danger' : '' ?>">
                            <small class="text-muted fw-bold d-block mb-1">PO PENDING</small>
                            <h3 class="fw-bold mb-0 text-danger"><?= $count_po ?></h3>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="custom-table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-stream me-2 text-maroon"></i>Alert Feed</h6>
                            <button class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#taskDrawer" id="btnNewTask">
                                <i class="fas fa-plus me-1"></i>New Task
                            </button>
                        </div>

                        <form id="filterForm" action="" method="GET" class="d-flex flex-wrap gap-2 mb-3 pb-3 border-bottom">
                            <select name="type" class="form-select form-select-sm" style="width:150px;">
                                <option value="">All Types</option>
                                <option value="low_stock" <?= $type_filter=='low_stock'?'selected':'' ?>>Low Stock</option>
                                <option value="near_expiry" <?= $type_filter=='near_expiry'?'selected':'' ?>>Near Expiry</option>
                                <option value="expired" <?= $type_filter=='expired'?'selected':'' ?>>Expired</option>
                                <option value="po_approval" <?= $type_filter=='po_approval'?'selected':'' ?>>PO Approval</option>
                                <option value="assigned_task" <?= $type_filter=='assigned_task'?'selected':'' ?>>Manual Task</option>
                            </select>
                            <select name="priority" class="form-select form-select-sm" style="width:120px;">
                                <option value="">All Priorities</option>
                                <option value="high" <?= $priority_filter=='high'?'selected':'' ?>>High</option>
                                <option value="normal" <?= $priority_filter=='normal'?'selected':'' ?>>Normal</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill px-3">Filter</button>
                        </form>

                        <?php if(empty($alerts)): ?>
                            <div class="p-5 text-center text-muted">No alerts match this filter.</div>
                        <?php else: foreach($alerts as $a):
                            $isOverdue = $a['due_date'] && !$a['is_resolved'] && strtotime($a['due_date']) < strtotime(date('Y-m-d'));
                        ?>
                            <div class="p-3 mb-2 rounded-4 border d-flex align-items-start <?= $a['is_resolved'] ? 'bg-light' : 'bg-white shadow-sm' ?>">
                                <div class="me-3 mt-1"><i class="fas fa-circle <?= $a['priority']=='high' ? 'text-danger' : 'text-secondary' ?>" style="font-size:8px"></i></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:9px; letter-spacing:0.5px;"><?= str_replace('_', ' ', $a['alert_type']) ?></small>
                                        <?php if($a['is_resolved']): ?><span class="badge bg-success" style="font-size:8px;">RESOLVED</span><?php endif; ?>
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
                                        <button class="btn btn-xs btn-success rounded-pill px-3 btn-resolve" data-id="<?= $a['alert_id'] ?>" title="Resolve"><i class="fas fa-check"></i></button>
                                    <?php endif; ?>
                                    <?php if($a['alert_type'] == 'assigned_task'): ?>
                                        <button class="btn btn-xs btn-outline-dark rounded-pill px-3 btn-edit-task" data-id="<?= $a['alert_id'] ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                    <?php endif; ?>
                                    <a href="<?= base_url('admin/management/alerts/delete/'.$a['alert_id']) ?>" class="btn btn-xs btn-outline-danger rounded-pill px-2" onclick="return confirm('Delete this log permanently?')" title="Delete"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>

                        <?php
                            $q = '&status='.$status_filter.'&type='.$type_filter.'&priority='.$priority_filter.'&assigned_to='.$assigned_filter;
                            $windowSize=3; $currentBlock=(int)ceil($current_page/$windowSize);
                            $windowStart=(($currentBlock-1)*$windowSize)+1; $windowEnd=min($windowStart+$windowSize-1,$total_pages);
                        ?>
                        <div class="d-flex justify-content-end mt-3">
                            <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                                <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
                                <?php for($i=$windowStart;$i<=$windowEnd;$i++): ?>
                                    <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
                            </ul></nav>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="custom-table-container">
                        <h6 class="fw-bold mb-3" style="font-size:12px;"><i class="fas fa-info-circle me-2 text-maroon"></i>About This Feed</h6>
                        <p class="text-muted mb-2" style="font-size:10px;">Low Stock, Near Expiry, Expired, and PO Approval alerts are generated automatically from live inventory and procurement data — they resolve themselves once the underlying issue is fixed.</p>
                        <p class="text-muted mb-0" style="font-size:10px;">Manual tasks are for anything else. Overdue tasks are automatically flagged High priority. Records are never auto-deleted — resolved items stay for the audit trail; use Delete to remove a genuine mistake.</p>
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
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0">Resolve Alert</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
        <p class="text-muted mb-3" style="font-size:11px;">Add a short note on what was done (optional but recommended for the audit trail).</p>
        <textarea id="resolveNote" class="formal-input mb-3" rows="3" placeholder="e.g. Restocked 50 units via PO-2026-0042"></textarea>
        <button type="button" class="btn btn-success w-100 py-2 fw-bold rounded-3" id="btnConfirmResolve"><i class="fas fa-check me-2"></i>Confirm Resolve</button>
    </div>
</div>

<script src="<?= base_url('public/js/admin/management/alerts.js') ?>"></script>
<?= view('partials/admin/footer') ?>