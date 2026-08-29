<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PharMediSync</title>
    <link rel="stylesheet" href="<?= base_url('public/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/auth/internallogin.css') ?>">
</head>
<body>

    <div class="glass-card">
        <!-- Top Header -->
        <div class="header-section">
            <div class="access-pill">INTERNAL ACCESS CONTROL</div>
            <p class="company-name">Robin Rose Trading</p>
            <h1 class="system-title">PharMediSync</h1>
            <p class="system-subtitle">Operation & Management Command Center</p>
        </div>

        <!-- Role Toggle -->
        <div class="role-toggle-group">
            <button type="button" class="btn-role active" id="btn-admin" onclick="switchRole('admin')">ADMIN</button>
            <button type="button" class="btn-role" id="btn-staff" onclick="switchRole('staff')">STAFF</button>
        </div>

        <!-- Login Form -->
        <div class="login-form-box">
            <!-- Display Error Messages -->
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger py-1 small text-center" style="font-size: 10px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>
            <div class="icon-box">
                <img id="role-icon" src="<?= base_url('public/images/briefcase.png') ?>" alt="icon" width="30">
            </div>
            <h4 id="role-title">Admin</h4>
            <p id="role-desc" class="role-desc">Full System Control</p>

            <form action="<?= base_url('auth/login/internal') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="role" id="input-role" value="admin">
                
                <div class="input-wrapper">
                    <input type="email" name="email" class="custom-input" placeholder="Email" required>
                </div>

                <div class="input-wrapper">
                    <input type="password" name="password" class="custom-input" placeholder="Password" required>
                </div>

                <div class="text-end mb-3">
                    <a href="#" class="forgot-link" id="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-maroon">ENTER DASHBOARD</button>
            </form>
        </div>

        <!-- Footer inside Glass Card -->
        <div class="footer-section">
            <p>© 2026 PharMediSync | Robin Rose Trading</p>
        </div>
    </div>

    <script src="<?= base_url('public/js/login.js') ?>"></script>
</body>
</html>