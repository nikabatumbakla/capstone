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
                <h5 class="fw-bold mb-0">User Management</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-user-shield me-2"></i>User Access Control & Management</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Admin · Staff · Supplier · Institutional Client · Walk-In Customer</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-3">
        <a href="?group=<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $active_group=='' ? 'border-bottom border-3 border-maroon' : '' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">ACTIVE ACCOUNTS</small>
                <h3 class="fw-bold mb-0"><?= $count_active ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?group=staff<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $active_group=='staff' ? 'border-bottom border-3 border-primary' : '' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">STAFF</small>
                <h3 class="fw-bold mb-0 text-primary"><?= $count_staff ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?group=clients<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $active_group=='clients' ? 'border-bottom border-3 border-success' : '' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">CLIENT PORTALS</small>
                <h3 class="fw-bold mb-0 text-success"><?= $count_clients ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?group=suppliers<?= $search ? '&search='.urlencode($search) : '' ?>" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative <?= $active_group=='suppliers' ? 'border-bottom border-3 border-maroon' : '' ?>">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">SUPPLIERS</small>
                <h3 class="fw-bold mb-0 text-maroon"><?= $count_suppliers ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
</div>

<?php if ($active_group): ?>
<div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
    <span><strong>
        <?php
            $labels = ['staff' => 'Staff Accounts', 'clients' => 'Client Portal Accounts', 'suppliers' => 'Supplier Accounts'];
            echo $labels[$active_group] ?? strtoupper($active_group);
        ?>
    </strong></span>
    <a href="?<?= $search ? 'search='.urlencode($search) : '' ?>" class="text-danger fw-bold text-decoration-none"> ×</a>
</div>
<?php endif; ?>

            <div class="custom-table-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-user-friends me-2 text-maroon"></i>Identity Registry</h6>
        <div class="d-flex gap-2">
            <form id="filterForm" action="" method="GET">
                <input type="hidden" name="group" value="<?= esc($active_group) ?>">
                <input type="text" name="search" id="userSearch" class="form-control form-control-sm rounded-pill" placeholder="Search name or email..." style="width:200px;" value="<?= esc($search) ?>">
            </form>
            <a href="<?= base_url('admin/management/users/pending-applications') ?>" class="btn btn-sm rounded-pill px-4 shadow-sm fw-bold <?= $count_pending > 0 ? 'btn-danger' : 'btn-outline-secondary' ?>">
                <i class="fas fa-clock me-1"></i>Pending Approval <?= $count_pending > 0 ? '('.$count_pending.')' : '' ?>
            </a>
            <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold" id="btnAddNewUser" data-bs-toggle="offcanvas" data-bs-target="#userDrawer">
                <i class="fas fa-plus me-1"></i>New Account
            </button>
        </div>
    </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th class="ps-4">User ID</th><th>Full Identity</th><th>Role</th><th>System Access</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody id="userTableBody">
                            <?php if(empty($users)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No accounts match this filter.</td></tr>
                            <?php else: foreach($users as $u): ?>
                            <tr>
                                <td class="ps-4 text-muted">#USR-<?= str_pad($u['user_id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td><span class="fw-bold text-dark d-block"><?= esc($u['full_name']) ?></span><small class="text-muted"><?= esc($u['email']) ?></small></td>
                                <td><span class="badge bg-light text-dark border px-3"><?= strtoupper(str_replace('_', ' ', $u['role'])) ?></span></td>
                                <td><span class="badge rounded-pill <?= $u['is_active'] ? 'bg-success' : 'bg-danger' ?> px-3"><?= $u['is_active'] ? 'ACTIVE' : 'LOCKED' ?></span></td>
                                
                                <td class="text-center">
    <div class="d-flex gap-1 justify-content-center">
        <button class="btn btn-xs btn-outline-secondary rounded-pill btn-manage-access" data-id="<?= $u['user_id'] ?>" title="Manage Access">
            <i class="fas fa-user-edit"></i>
        </button>
        <?php if($u['user_id'] != session()->get('user_id')): ?>
            <?php if($u['is_active']): ?>
                <a href="<?= base_url('admin/management/users/delete/'.$u['user_id']) ?>" class="btn btn-xs btn-outline-warning rounded-pill" onclick="return confirm('Deactivate this account? They will no longer be able to log in, but their history stays intact.')" title="Deactivate">
                    <i class="fas fa-user-slash"></i>
                </a>
            <?php endif; ?>
            <a href="<?= base_url('admin/management/users/hard-delete/'.$u['user_id']) ?>" class="btn btn-xs btn-outline-danger rounded-pill" onclick="return confirm('Permanently delete this account? This cannot be undone, and will fail if the account has any recorded activity.')" title="Delete Permanently">
                <i class="fas fa-trash"></i>
            </a>
        <?php else: ?>
            <span class="badge bg-light text-muted border" style="font-size:8px;"></span>
        <?php endif; ?>
    </div>
</td>

                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
    $q = '&group='.$active_group.'&search='.urlencode($search);
    $windowSize=3; $currentBlock=(int)ceil($current_page/$windowSize);
    $windowStart=(($currentBlock-1)*$windowSize)+1; $windowEnd=min($windowStart+$windowSize-1,$total_pages);
?>
<div class="d-flex justify-content-end mt-4">
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
    </div>
</div>

<!-- EDIT DRAWER: access control only — every other field is read-only, owned by the account holder -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="accessDrawer" style="width: 480px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Manage Account Access</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <div class="p-3 bg-light rounded-4 mb-4 border">
            <p class="info-label mb-1">Account</p>
            <h6 class="fw-bold mb-0" id="access_display_name">—</h6>
            <small class="text-muted" id="access_display_email">—</small>
        </div>

        <p class="text-muted mb-4" style="font-size:10.5px;">
            <i class="fas fa-lock me-1"></i>Profile details belong to the account holder and cannot be edited here. As admin, you control only whether this account can log in and whether it's verified.
        </p>

    <form action="<?= base_url('admin/management/users/update-access') ?>" method="POST" id="accessForm">
    <input type="hidden" name="user_id" id="access_user_id">
    <input type="hidden" name="return_role" value="<?= esc($active_group) ?>">

            <div class="p-3 bg-light rounded-4 mb-3 border">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="access_active">
                    <label class="small fw-bold" for="access_active">Allow System Access (Enable Login)</label>
                </div>
                <p class="text-muted mb-0 mt-1" style="font-size:9.5px;">While disabled, this account cannot log in under any circumstance.</p>
            </div>

            <div class="p-3 bg-light rounded-4 mb-3 border">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_verified" id="access_verified">
                    <label class="small fw-bold" for="access_verified">Account Verified</label>
                </div>
                <p class="text-muted mb-0 mt-1" style="font-size:9.5px;">Unverified accounts cannot log in even if access is enabled — they must be verified first.</p>
            </div>

            <div class="mb-4">
                <label class="formal-label">Verification / Access Notes (optional)</label>
                <textarea name="verification_notes" id="access_notes" class="formal-input" rows="3" placeholder="e.g. Verified business permit and TIN on file, March 2026."></textarea>
            </div>

            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow">✓ SAVE ACCESS CHANGES</button>
        </form>
    </div>
</div>

<!-- NEW ACCOUNT DRAWER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="userDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Provision New Account</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/users/create') ?>" method="POST" id="userForm">
            <div class="mb-3"><label class="formal-label">Full Legal Name / Contact Person *</label>
                <input type="text" name="name" id="form_name" class="formal-input" required></div>

            <div class="row g-3 mb-3">
                <div class="col-6"><label class="formal-label">Email Address *</label><input type="email" name="email" id="form_email" class="formal-input" required></div>
                <div class="col-6"><label class="formal-label">Phone Number</label><input type="text" name="phone" id="form_phone" class="formal-input"></div>
            </div>

            <div class="mb-3"><label class="formal-label">System Role *</label>
                <select name="role" id="form_role" class="form-select formal-input">
                    <option value="admin">System Administrator</option>
                    <option value="staff">Operational Staff</option>
                    <option value="supplier">Supplier Account</option>
                    <option value="institutional_client">Institutional Client</option>
                    <option value="customer">Walk-in Customer</option>
                </select>
            </div>

            <div id="customerFields" class="p-3 bg-light rounded-4 mb-3" style="display:none;">
                <p class="fw-bold mb-2" style="font-size:11px;">Customer Profile</p>
                <div class="mb-2"><label class="formal-label">Address</label><textarea name="address" class="formal-input" rows="2"></textarea></div>
                <div class="row g-3 mb-2">
                    <div class="col-6"><label class="formal-label">ID Type</label><input type="text" name="id_type" class="formal-input" placeholder="e.g. Driver's License"></div>
                    <div class="col-6"><label class="formal-label">ID Number</label><input type="text" name="id_number" class="formal-input"></div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_id_verified" id="form_id_verified">
                    <label class="small fw-bold" for="form_id_verified">ID Verified</label>
                </div>
            </div>

            <div id="supplierFields" class="p-3 bg-light rounded-4 mb-3" style="display:none;">
                <p class="fw-bold mb-2" style="font-size:11px;">Supplier Profile</p>
                <div class="mb-2"><label class="formal-label">Company Address</label><textarea name="address" class="formal-input" rows="2"></textarea></div>
                <div class="row g-3">
                    <div class="col-6"><label class="formal-label">Payment Terms</label><input type="text" name="payment_terms" class="formal-input" placeholder="e.g. Net 30"></div>
                    <div class="col-6"><label class="formal-label">Lead Time (days)</label><input type="number" name="lead_time_days" class="formal-input" value="7"></div>
                </div>
            </div>

            <div id="clientFields" class="p-3 bg-light rounded-4 mb-3" style="display:none;">
                <p class="fw-bold mb-2" style="font-size:11px;">Client Profile</p>
                <div class="mb-2"><label class="formal-label">Organization Name *</label><input type="text" name="organization" class="formal-input"></div>
                <div class="row g-3 mb-2">
                    <div class="col-6"><label class="formal-label">Institution Type</label>
                        <select name="client_type" class="form-select formal-input">
                            <option value="school">School / University</option>
                            <option value="hospital">Hospital</option>
                            <option value="clinic">Clinic</option>
                            <option value="barangay">Barangay Unit</option>
                            <option value="sk">SK</option>
                            <option value="lgu">LGU / Government</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-6"><label class="formal-label">TIN</label><input type="text" name="tin" class="formal-input"></div>
                </div>
                <div class="mb-2"><label class="formal-label">Address</label><textarea name="address" class="formal-input" rows="2"></textarea></div>
                <div class="mb-0"><label class="formal-label">Credit Limit (₱)</label><input type="number" step="0.01" name="credit_limit" class="formal-input" value="0"></div>
            </div>

            <div class="mb-3"><label class="formal-label">Password *</label>
                <input type="password" name="password" id="form_password" class="formal-input" required minlength="8">
                <small class="helper-text">Minimum 8 characters.</small>
            </div>
            <div class="mb-4"><label class="formal-label">Confirm Password *</label>
                <input type="password" name="confirm_password" id="form_confirm_password" class="formal-input" required minlength="8">
                <div id="passwordMismatchError" style="display:none; color:#dc3545; font-size:10px;">Passwords do not match.</div>
            </div>

            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow">✓ SAVE NEW IDENTITY</button>
        </form>
    </div>
</div>

<script src="<?= base_url('public/js/admin/management/management/users.js') ?>"></script>
<?= view('partials/admin/footer') ?>