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
        <div class="header-section">
            <div class="access-pill">INTERNAL ACCESS CONTROL</div>
            <p class="company-name">Robin Rose Trading</p>
            <h1 class="system-title">PharMediSync</h1>
            <p class="system-subtitle">Operation & Management Command Center</p>
        </div>

        <div class="login-form-box">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-1 small text-center" style="font-size: 10px;"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('info')): ?>
                <div class="alert alert-info py-1 small text-center" style="font-size: 10px;"><?= session()->getFlashdata('info') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-1 small text-center" style="font-size: 10px;"><?= session()->getFlashdata('success') ?></div>
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

                <div class="d-flex align-items-start gap-2 text-start mb-3" style="font-size: 10.5px; color:#555; line-height: 1.5;">
                    <input type="checkbox" name="agree_terms" id="agreeTermsInternal" class="form-check-input mt-1" required style="flex-shrink:0;">
                    <label for="agreeTermsInternal">
                        I agree to the <a href="#" id="openTermsInternal" class="fw-bold" style="text-decoration: underline; color:#7b1113;">Terms &amp; Conditions</a> and confirm this access is for authorized business use only.
                    </label>
                </div>

                <button type="submit" class="btn-maroon">LOGIN</button>
            </form>
        </div>

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

    <!-- Terms & Conditions -->
    <div class="modal-backdrop-custom" id="internalTermsModal">
        <div class="modal-card text-start" style="max-width:480px; max-height:80vh; overflow-y:auto;">
            <h5 class="fw-bold mb-3">Terms &amp; Conditions — Internal Access</h5>
            <div style="font-size: 11px; line-height: 1.7; color: #444;">
                <p><b>1. Authorized Use.</b> Access to PharMediSync is granted solely to authorized personnel of Robin Rose Trading for legitimate business operations — inventory, procurement, sales, and reporting activities within the scope of your assigned role.</p>
                <p><b>2. Confidentiality.</b> You agree to keep confidential all data accessed through this system, including client records, supplier information, pricing, and financial data, and not to disclose it to unauthorized parties.</p>
                <p><b>3. Account Responsibility.</b> You are responsible for all activity conducted under your account. Do not share your login credentials with anyone.</p>
                <p><b>4. Data Accuracy.</b> You agree to enter and maintain accurate records to the best of your knowledge, and to report any discrepancies you identify to your supervisor.</p>
                <p><b>5. Acceptable Use.</b> This system may not be used for any purpose outside your assigned duties, including personal use or actions that could compromise system integrity or security.</p>
                <p><b>6. Monitoring.</b> System activity may be logged and reviewed for security, audit, and compliance purposes.</p>
                <p><b>7. Termination of Access.</b> Robin Rose Trading reserves the right to revoke system access at any time, particularly in cases of policy violation or end of employment/engagement.</p>
            </div>
            <button type="button" class="btn-maroon w-100 mt-3 close-legal-modal">Close</button>
        </div>
    </div>

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

        const internalTermsModal = document.getElementById('internalTermsModal');
        document.getElementById('openTermsInternal').addEventListener('click', function(e) {
            e.preventDefault();
            internalTermsModal.style.display = 'flex';
        });
        document.querySelectorAll('.close-legal-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                internalTermsModal.style.display = 'none';
            });
        });
    </script>
</body>
</html>