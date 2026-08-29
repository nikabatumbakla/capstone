<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Profile Settings</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>Profile Settings</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Update your company informations</p>
            </div>
            
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-7">
                    <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius:20px;">
                        <h6 class="fw-bold mb-4"><i class="fas fa-building me-2 text-maroon"></i> Company Info</h6>
                        <form action="<?= base_url('supplier/account/profile/update') ?>" method="POST">
                            <div class="mb-3"><label class="formal-label">Company Name</label><input type="text" name="name" class="formal-input" value="<?= $profile->name ?>"></div>
                            <div class="mb-3"><label class="formal-label">Contact Person</label><input type="text" name="contact" class="formal-input" value="<?= $profile->contact_person ?>"></div>
                            <div class="row g-2">
                                <div class="col-6 mb-3"><label class="formal-label">Phone</label><input type="text" name="phone" class="formal-input" value="<?= $profile->phone ?>"></div>
                                <div class="col-6 mb-3"><label class="formal-label">Email</label><input type="text" name="email" class="formal-input" value="<?= $profile->email ?>"></div>
                            </div>
                            <div class="mb-3"><label class="formal-label">Address</label><textarea name="address" class="formal-input" rows="2"><?= $profile->address ?></textarea></div>
                            <button type="submit" class="btn btn-dark px-4 py-2 fw-bold rounded-pill">Save Changes</button>
                        </form>
                    </div>
                </div>
                <!-- Security -->
                <div class="col-lg-5">
                    <div class="custom-table-container p-4 border-0 shadow-sm" style="border-radius:20px;">
                        <h6 class="fw-bold mb-4"><i class="fas fa-key me-2 text-warning"></i> Change Password</h6>
                        <form action="#">
                            <div class="mb-3"><label class="formal-label">Current Password</label><input type="password" class="formal-input"></div>
                            <div class="mb-3"><label class="formal-label">New Password</label><input type="password" class="formal-input"></div>
                            <button type="submit" class="btn btn-outline-dark w-100 py-2 fw-bold rounded-pill">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>