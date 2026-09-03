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

        <!-- Login Form -->
        <div class="login-form-box">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-1 small text-center" style="font-size: 10px;">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('info')): ?>
                <div class="alert alert-info py-1 small text-center" style="font-size: 10px;">
                    <?= session()->getFlashdata('info') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-1 small text-center" style="font-size: 10px;">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('auth/login/internal') ?>" method="POST" id="loginForm">
                <?= csrf_field() ?>

                <div class="input-wrapper">
                    <input type="email" name="email" class="custom-input" placeholder="Email" value="<?= old('email') ?>" required>
                </div>

                <div class="input-wrapper">
                    <input type="password" name="password" class="custom-input" placeholder="Password" required>
                </div>

                <div class="text-end mb-3">
                    <a href="#" class="forgot-link" id="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-maroon">LOGIN</button>
            </form>
        </div>

        <!-- Footer inside Glass Card -->
        <div class="footer-section">
            <p>© 2026 PharMediSync | Robin Rose Trading</p>
        </div>
    </div>

    <!-- Forgot Password: Step 1 -->
    <div class="modal-backdrop-custom" id="forgotModal">
        <div class="modal-card">
            <h5 class="fw-bold mb-3">Reset Password</h5>
            <p class="small text-muted mb-3">Enter your account email — we'll send a 6-digit verification code.</p>
            <form action="<?= base_url('auth/forgot-password/send-internal') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="email" name="email" class="custom-input mb-3" placeholder="Your email" required>
                <button type="submit" class="btn-maroon w-100">Send Code</button>
            </form>
            <button type="button" class="btn btn-link btn-sm mt-2 w-100 text-muted" id="closeForgotModal">Cancel</button>
        </div>
    </div>

    <!-- Forgot Password: Step 2 -->
    <?php if(session()->getFlashdata('show_reset_form')): ?>
    <div class="modal-backdrop-custom" style="display:flex;">
        <div class="modal-card">
            <h5 class="fw-bold mb-3">Enter Verification Code</h5>
            <p class="small text-muted mb-3">Check your email for the 6-digit code. Verifying will log you in directly.</p>
            <form action="<?= base_url('auth/forgot-password/verify-internal') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="email" name="email" class="custom-input mb-2" placeholder="Your email" value="<?= old('email') ?>" required>
                <input type="text" name="code" class="custom-input mb-3" placeholder="6-digit code" maxlength="6" required>
                <button type="submit" class="btn-maroon w-100">Verify & Log In</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="<?= base_url('public/js/login.js') ?>"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        const forgotModal = document.getElementById('forgotModal');
        document.getElementById('forgot-link').addEventListener('click', function(e) {
            e.preventDefault();
            forgotModal.style.display = 'flex';
        });
        document.getElementById('closeForgotModal').addEventListener('click', function() {
            forgotModal.style.display = 'none';
        });
    </script>
</body>
</html>