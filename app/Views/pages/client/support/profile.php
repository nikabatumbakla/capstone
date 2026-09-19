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
                <div class="alert alert-danger py-2 small" id="flashError"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="custom-table-container">
                        <form action="<?= base_url('client/support/profile/update') ?>" method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>

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
                                <div class="<?= !empty($client->registration_ref) ? 'col-md-6' : 'col-md-8' ?>">
                                    <label class="formal-label">Organization Name</label>
                                    <input type="text" class="formal-input read-only-input" value="<?= esc($client->organization) ?>" readonly>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="formal-label">Organization Type</label>
                                    <input type="text" class="formal-input read-only-input" value="<?= esc(ucwords(str_replace('_', ' ', $client->client_type ?: 'N/A'))) ?>" readonly>
                                </div>
                                <?php if (!empty($client->registration_ref)): ?>
                                <div class="col-md-4">
                                    <label class="formal-label">Reference #</label>
                                    <input type="text" class="formal-input read-only-input" value="<?= esc($client->registration_ref) ?>" readonly>
                                </div>
                                <?php endif; ?>
                            </div>

                            <hr>
                            <p class="fw-bold mb-3 mt-3" style="font-size:12px;">Contact Information</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6"><label class="formal-label">Authorized Contact Person *</label><input type="text" name="contact" class="formal-input" value="<?= esc($client->contact_person) ?>" required></div>
                                <div class="col-md-6"><label class="formal-label">Position / Title</label><input type="text" name="position" class="formal-input" value="<?= esc($client->position) ?>" placeholder="e.g. Procurement Officer, Administrator"></div>
                                <div class="col-md-6"><label class="formal-label">Login Email *</label><input type="email" name="email" class="formal-input" value="<?= esc($client->login_email) ?>" required></div>
                                <div class="col-md-6"><label class="formal-label">Primary Phone *</label><input type="text" name="phone" class="formal-input" value="<?= esc($client->phone) ?>" required></div>
                                <div class="col-md-6"><label class="formal-label">Alternative Phone</label><input type="text" name="alt_phone" class="formal-input" value="<?= esc($client->alt_phone) ?>"></div>
                                <div class="col-md-6"><label class="formal-label">Business Address</label><textarea name="address" class="formal-input" rows="2"><?= esc($client->address) ?></textarea></div>
                                <div class="col-md-12"><label class="formal-label">Default Delivery Address</label><textarea name="delivery_address" class="formal-input" rows="2"><?= esc($client->delivery_address) ?></textarea></div>
                            </div>

                            <hr>
                            <p class="fw-bold mb-3 mt-3" style="font-size:12px;">Supporting Document</p>
                            <?php if (!empty($client->permit_path)): ?>
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border mb-2" style="background:#f8f9fa;">
                                    <span style="font-size:11px;"><i class="fas fa-file-invoice me-2 text-success"></i>File on record</span>
                                    <a href="<?= base_url($client->permit_path) ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">View File</a>
                                </div>
                            <?php else: ?>
                                <div class="p-3 rounded-3 border mb-2 text-muted" style="font-size:11px; background:#f8f9fa;">No document on file yet.</div>
                            <?php endif; ?>
                            <div class="upload-dashed-box text-center p-3 mb-4" id="dropZone">
                                <input type="file" name="permit" id="permitFile" class="d-none" accept=".pdf,.jpg,.jpeg,.png" onchange="updatePermitFileName()">
                                <label for="permitFile" style="cursor: pointer;">
                                    <i class="fas fa-upload text-muted mb-1"></i>
                                    <p class="mb-0 fw-bold small" id="permitFileNameDisplay"><?= !empty($client->permit_path) ? 'Click to replace with a new file' : 'Click to upload' ?></p>
                                    <small class="text-muted" style="font-size: 8px;">PDF, JPG, PNG · Max 5MB</small>
                                </label>
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
                    <div class="d-flex flex-column gap-3 h-25">
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

<script>
    setTimeout(function() {
        ['flashError', 'flashSuccess'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }
        });
    }, 5000);

    document.getElementById('avatarInput').addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('avatarPreview').src = URL.createObjectURL(this.files[0]);
        }
    });

    function updatePermitFileName() {
        const input = document.getElementById('permitFile');
        const display = document.getElementById('permitFileNameDisplay');
        if (input.files.length > 0) {
            display.innerText = "✓ " + input.files[0].name;
            display.style.color = "#087b38";
        }
    }
</script>
<?= view('partials/client/footer') ?>