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
                <h5 class="fw-bold mb-0">User Management</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>User Access Control & Management</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Admin · Staff · Supplier · Institutional Client · Walk-In Customer</p>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-4">
    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm fw-bold" id="btnAddNewUser"
            data-bs-toggle="offcanvas"
            data-bs-target="#userDrawer">
        + New Account
    </button>
</div>

            <!-- Analytical Tiles -->
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius:20px"><small class="info-label">ACTIVE ACCOUNTS</small><h4 class="fw-bold"><?= $count_active ?></h4></div></div>
                <div class="col-md-3"><div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius:20px"><small class="info-label">STAFF ENTRIES</small><h4 class="fw-bold text-primary"><?= $count_staff ?></h4></div></div>
                <div class="col-md-3"><div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius:20px"><small class="info-label">CLIENT PORTALS</small><h4 class="fw-bold text-success"><?= $count_clients ?></h4></div></div>
                <div class="col-md-3"><div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius:20px"><small class="info-label">SUPPLIERS</small><h4 class="fw-bold text-maroon"><?= $count_suppliers ?></h4></div></div>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:12px"><i class="fas fa-user-friends me-2 text-maroon"></i>Identity Registry</h6>
                    
                    <div class="d-flex gap-2">
                        <!-- ROLE FILTER -->
                        <form action="" method="GET">
                            <select name="role" class="form-select form-select-sm rounded-pill border" style="width:150px" onchange="this.form.submit()">
                                <option value="">All Roles</option>
                                <option value="admin" <?= $active_role == 'admin' ? 'selected' : '' ?>>Administrators</option>
                                <option value="staff" <?= $active_role == 'staff' ? 'selected' : '' ?>>Staff Members</option>
                                <option value="supplier" <?= $active_role == 'supplier' ? 'selected' : '' ?>>Suppliers</option>
                                <option value="institutional_client" <?= $active_role == 'institutional_client' ? 'selected' : '' ?>>Clients</option>
                            </select>
                        </form>
                        <input type="text" id="userSearch" class="form-control form-control-sm rounded-pill border" placeholder="Search identity..." style="width:200px">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">User ID</th>
                                <th>Full Identity</th>
                                <th>Role</th>
                                <th>System Access</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td class="ps-4 text-muted">#USR-<?= str_pad($u['user_id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <span class="fw-bold text-dark d-block"><?= $u['full_name'] ?></span>
                                    <small class="text-muted"><?= $u['email'] ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border px-3"><?= strtoupper(str_replace('_', ' ', $u['role'])) ?></span></td>
                                <td>
                                    <span class="badge rounded-pill <?= $u['is_active'] ? 'bg-success' : 'bg-danger' ?> px-3">
                                        <?= $u['is_active'] ? 'ACTIVE' : 'LOCKED' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-xs btn-outline-secondary rounded-pill btn-edit-user" data-id="<?= $u['user_id'] ?>"><i class="fas fa-user-edit"></i></button>
                                        <a href="<?= base_url('admin/management/users/delete/'.$u['user_id']) ?>" class="btn btn-xs btn-outline-danger rounded-pill" onclick="return confirm('Permanently remove this account?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: USER IDENTITY FORM -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="userDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0" id="drawerTitle">Account Specification</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/users/save') ?>" method="POST" id="userForm">
            <input type="hidden" name="user_id" id="form_user_id">
            
            <div class="mb-3"><label class="formal-label">Full Legal Name *</label>
                <input type="text" name="name" id="form_name" class="formal-input" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6"><label class="formal-label">Email Address *</label><input type="email" name="email" id="form_email" class="formal-input" required></div>
                <div class="col-6"><label class="formal-label">Phone Number</label><input type="text" name="phone" id="form_phone" class="formal-input"></div>
            </div>

            <div class="mb-3"><label class="formal-label">System Role / Permission Group</label>
                <select name="role" id="form_role" class="form-select formal-input">
                    <option value="admin">System Administrator</option>
                    <option value="staff">Operational Staff</option>
                    <option value="supplier">Supplier Account</option>
                    <option value="institutional_client">Institutional Client</option>
                    <option value="customer">Walk-in Customer</option>
                </select>
            </div>

            <div class="mb-3"><label class="formal-label">Account Password</label>
                <input type="password" name="password" class="formal-input" placeholder="Leave blank to keep current">
                <small class="helper-text">Encrypted with modern PHP hashing.</small>
            </div>

            <div class="p-3 bg-light rounded-4 mb-4 border">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="form_active" checked>
                    <label class="small fw-bold">Allow System Access (Enable Account)</label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow" id="btnSubmit">SAVE IDENTITY CHANGES</button>
        </form>
    </div>
</div>

<script src="<?= base_url('public/js/admin/users.js') ?>"></script>
<?= view('partials/admin/footer') ?>