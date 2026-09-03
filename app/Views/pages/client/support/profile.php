<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">My Profile</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-user-circle me-2"></i>Institutional Profile</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Update your contact information and password</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="row g-4">
    <div class="col-lg-8">
        <div class="custom-table-container">
            <form action="<?= base_url('client/support/profile/update') ?>" method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img src="<?= $client->avatar_path ? base_url($client->avatar_path) : base_url('public/images/default-avatar.png') ?>" id="avatarPreview" class="rounded-circle mb-2" style="width:90px; height:90px; object-fit:cover; border:3px solid #eee;">
                    <div>
                        <label class="btn btn-xs btn-outline-dark rounded-pill px-3" style="cursor:pointer;">
                            <i class="fas fa-camera me-1"></i>Change Logo/Photo
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none;">
                        </label>
                    </div>
                </div>

                <p class="fw-bold mb-3" style="font-size:12px;">Business Identity (read-only)</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-8"><label class="formal-label">Organization Name</label><input type="text" class="formal-input read-only-input" value="<?= esc($client->organization) ?>" readonly></div>
                    <div class="col-md-4"><label class="formal-label">TIN</label><input type="text" class="formal-input read-only-input" value="<?= esc($client->tin ?: 'N/A') ?>" readonly></div>
                </div>

                <hr>
                <p class="fw-bold mb-3 mt-3" style="font-size:12px;">Contact Information</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label class="formal-label">Authorized Contact Person *</label><input type="text" name="contact" class="formal-input" value="<?= esc($client->contact_person) ?>" required></div>
                    <div class="col-md-6"><label class="formal-label">Login Email *</label><input type="email" name="email" class="formal-input" value="<?= esc($client->login_email) ?>" required></div>
                    <div class="col-md-6"><label class="formal-label">Primary Phone *</label><input type="text" name="phone" class="formal-input" value="<?= esc($client->phone) ?>" required></div>
                    <div class="col-md-6"><label class="formal-label">Alternative Phone</label><input type="text" name="alt_phone" class="formal-input" value="<?= esc($client->alt_phone) ?>"></div>
                    <div class="col-md-6"><label class="formal-label">Business Address</label><textarea name="address" class="formal-input" rows="2"><?= esc($client->address) ?></textarea></div>
                    <div class="col-md-6"><label class="formal-label">Default Delivery Address</label><textarea name="delivery_address" class="formal-input" rows="2"><?= esc($client->delivery_address) ?></textarea></div>
                </div>

                <hr>
                <p class="fw-bold mb-3 mt-3" style="font-size:12px;">Change Password (optional)</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label class="formal-label">New Password</label><input type="password" name="password" class="formal-input" placeholder="Leave blank to keep current" minlength="8"></div>
                    <div class="col-md-6"><label class="formal-label">Confirm New Password</label><input type="password" name="confirm_password" class="formal-input" minlength="8"></div>
                </div>

                <button type="submit" class="btn btn-dark px-5 py-3 fw-bold rounded-pill shadow">✓ SAVE CHANGES</button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="d-flex flex-column gap-3 h-50">
            <div class="inventory-kpi-card flex-grow-1 d-flex flex-column justify-content-center">
                <small class="text-muted fw-bold d-block mb-1">TOTAL ORDERS PLACED</small>
                <h3 class="fw-bold mb-0"><?= $stats['total_orders'] ?></h3>
            </div>
            <div class="inventory-kpi-card flex-grow-1 d-flex flex-column justify-content-center">
                <small class="text-muted fw-bold d-block mb-1">PARTNER SINCE</small>
                <h3 class="fw-bold mb-0"><?= $stats['member_since'] ? date('M Y', strtotime($stats['member_since'])) : 'N/A' ?></h3>
            </div>
            <div class="inventory-kpi-card flex-grow-1 d-flex flex-column justify-content-center">
                <small class="text-muted fw-bold d-block mb-1">ACCOUNT STATUS</small>
                <h3 class="fw-bold mb-0 text-success">ACTIVE</h3>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>