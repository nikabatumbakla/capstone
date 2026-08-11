<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Alerts & Task</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>Alerts & Task Management</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Low Stock · Near Expiry · Incoming Delivery · PO Approval · GRR Discrepancy</p>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-4">
    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold"
            data-bs-toggle="offcanvas"
            data-bs-target="#taskDrawer">
        + Register New Task
    </button>
</div>

            <div class="row g-4">
                <!-- LEFT: THE ALERT FEED -->
                <div class="col-lg-7">
                    <h6 class="fw-bold mb-3" style="font-size:13px"><i class="fas fa-stream me-2 text-maroon"></i>Intelligence Alert Feed</h6>
                    
                    <?php foreach($alerts as $a): 
                        $redirect = base_url('admin/dashboard');
                        if($a['alert_type'] == 'low_stock') $redirect = base_url('admin/inventory/stock-management');
                        if($a['alert_type'] == 'po_approval') $redirect = base_url('admin/procurement/purchase-orders');
                    ?>
                    <div class="p-3 mb-2 bg-white rounded-4 border shadow-sm d-flex align-items-center">
                        <div class="me-3"><i class="fas fa-circle <?= ($a['notes'] == 'High') ? 'text-danger' : 'text-warning' ?>" style="font-size:8px"></i></div>
                        <div class="flex-grow-1">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:8px;"><?= str_replace('_', ' ', $a['alert_type']) ?></small>
                            <p class="mb-0 fw-bold text-dark" style="font-size:11.5px;"><?= $a['message'] ?></p>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="<?= $redirect ?>" class="btn btn-xs btn-dark rounded-pill px-3">Go to Task</a>
                            <a href="<?= base_url('admin/management/alerts/delete/'.$a['alert_id']) ?>" class="btn btn-xs btn-outline-danger rounded-pill" onclick="return confirm('Delete this log?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- RIGHT: THE SUMMARY TILES (FIGMA STYLE) -->
                <div class="col-lg-5">
                    <div class="custom-table-container p-4">
                        <h6 class="fw-bold mb-4" style="font-size:12px">Alert Summary Intelligence</h6>
                        <div class="row g-3 text-white">
                            <div class="col-6"><div class="p-3 rounded-4 shadow-sm" style="background:#f1c40f"><h2><?= $count_low_stock ?></h2><small class="fw-bold opacity-75">LOW STOCK</small></div></div>
                            <div class="col-6"><div class="p-3 rounded-4 shadow-sm" style="background:#2ecc71"><h2><?= $count_near_expiry ?></h2><small class="fw-bold opacity-75">NEAR EXPIRY</small></div></div>
                            <div class="col-6"><div class="p-3 rounded-4 shadow-sm" style="background:#2c3e50"><h2><?= $count_expired ?></h2><small class="fw-bold opacity-75">EXPIRED</small></div></div>
                            <div class="col-6"><div class="p-3 rounded-4 shadow-sm" style="background:#e74c3c"><h2><?= $count_po ?></h2><small class="fw-bold opacity-75">PO PENDING</small></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: TASK FORM (CRUD) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="taskDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom"><h6 class="fw-bold mb-0">Alert Specification Form</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/alerts/save') ?>" method="POST">
            <input type="hidden" name="alert_id" id="task_id">
            <div class="mb-3"><label class="formal-label">Alert Category</label>
                <select name="type" id="task_type" class="form-select formal-input">
                    <option value="assigned_task">Manual Assignment</option>
                    <option value="low_stock">Inventory Alert</option>
                    <option value="po_approval">Procurement Request</option>
                </select>
            </div>
            <div class="mb-3"><label class="formal-label">Priority Intelligence</label>
                <select name="priority" id="task_priority" class="form-select formal-input">
                    <option value="Normal">Normal Priority</option>
                    <option value="High">Urgent / High</option>
                </select>
            </div>
            <div class="mb-4"><label class="formal-label">Instructional Message *</label>
                <textarea name="message" id="task_message" class="formal-input" rows="5" required placeholder="Describe the alert detail..."></textarea>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow">✓ SAVE ALERT TO FEED</button>
        </form>
    </div>
</div>

<script src="<?= base_url('public/js/admin/management_alerts.js') ?>"></script>
<?= view('partials/admin/footer') ?>