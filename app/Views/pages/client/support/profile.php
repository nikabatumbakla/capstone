<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">My Profile</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-credit-card me-2"></i>My Profile</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Update contact information · Change password</p>
            </div>
            
            <div class="custom-table-container p-5 border-0 shadow-sm" style="max-width: 800px; border-radius:30px;">
                <h5 class="fw-bold mb-4 text-maroon">INSTITUTIONAL PROFILE SETTINGS</h5>
                <form action="<?= base_url('client/support/profile/update') ?>" method="POST">
                    <div class="row g-4">
                        <div class="col-md-12"><label class="formal-label">Organization Name</label><input type="text" class="formal-input read-only-input" value="<?= $client->organization ?>" readonly></div>
                        <div class="col-md-6"><label class="formal-label">Authorized Contact Person</label><input type="text" name="contact" class="formal-input" value="<?= $client->contact_person ?>"></div>
                        <div class="col-md-6"><label class="formal-label">Primary Phone</label><input type="text" name="phone" class="formal-input" value="<?= $client->phone ?>"></div>
                        <div class="col-md-12"><label class="formal-label">Delivery Address</label><textarea name="address" class="formal-input" rows="3"><?= $client->address ?></textarea></div>
                    </div>
                    <button type="submit" class="btn btn-dark px-5 py-3 fw-bold rounded-pill mt-4 shadow">✓ UPDATE PROFILE INTELLIGENCE</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>