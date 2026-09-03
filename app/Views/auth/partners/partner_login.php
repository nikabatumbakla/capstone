<?php 
    $title = "Partner Gateway | PharMediSync";
    $pgSubtitle = "Robin Rose Trading · Secure Partner Access";
    include APPPATH . 'Views/shared/pg_header.php'; 
?>
<div class="glass-card">
    <form action="<?= base_url('auth/login/external') ?>" method="POST">
        <?= csrf_field() ?>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-1 small border-0 bg-danger bg-opacity-25 text-white mb-3" style="font-size:10px;">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('info')): ?>
            <div class="alert alert-info py-1 small border-0 bg-info bg-opacity-25 text-white mb-3" style="font-size:10px;">
                <?= session()->getFlashdata('info') ?>
            </div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success py-1 small border-0 bg-success bg-opacity-25 text-white mb-3" style="font-size:10px;">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <div class="text-start">
            <label class="formal-label">EMAIL ADDRESS</label>
            <input type="email" name="email" class="formal-input" placeholder="your@email.com" value="<?= old('email') ?>" required>

            <label class="formal-label">PASSWORD</label>
            <input type="password" name="password" class="formal-input" placeholder="Enter Your Password" required>
            <div class="text-end mt-1">
                <a href="#" id="pgForgotLink" class="text-white-50 text-decoration-none small" style="font-size: 10px; font-weight: 700;">Forgot Password?</a>
            </div>
        </div>

        <div class="d-flex align-items-start gap-2 text-start mt-3 mb-1" style="font-size: 10.5px; color: var(--text-dim); line-height: 1.5;">
            <input type="checkbox" name="agree_terms" id="agreeTermsPg" class="form-check-input mt-1" required style="flex-shrink:0;">
            <label for="agreeTermsPg">
                I agree to the <a href="#" id="openTermsPg" class="text-white fw-bold" style="text-decoration: underline;">Terms &amp; Conditions</a> and <a href="#" id="openPrivacyPg" class="text-white fw-bold" style="text-decoration: underline;">Privacy Policy</a>.
            </label>
        </div>

        <button type="submit" class="btn-pg-primary shadow-lg mt-3">LOGIN</button>
    </form>

    <div class="footer-copy text-center">
        <p class="mb-2" style="font-size: 12px; letter-spacing: 0.5px; margin-bottom: 100px;">NEW PARTNER?</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
    <a href="<?= base_url('partner-gateway/register/client') ?>" class="btn btn-xs btn-light rounded-pill px-3 py-2 fw-bold text-dark text-decoration-none" style="font-size: 10.5px; letter-spacing: 0.3px;">
        <i class="fas fa-hospital me-1"></i>Register as Client
    </a>
    <a href="<?= base_url('partner-gateway/register/supplier') ?>" class="btn btn-xs btn-light rounded-pill px-3 py-2 fw-bold text-dark text-decoration-none" style="font-size: 10.5px; letter-spacing: 0.3px;">
        <i class="fas fa-truck-loading me-1"></i>Register as Supplier
    </a>
</div>
    </div>
</div>

<!-- Step 1: Request Code -->
<div class="modal-backdrop-custom" id="pgForgotModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div class="glass-card" style="max-width:380px;">
        <h5 class="fw-bold text-white mb-3 text-center">Reset Password</h5>
        <p class="text-white-50 small mb-3 text-center" style="font-size: 11px;">Enter your account email — we'll send a 6-digit verification code.</p>
        <form action="<?= base_url('auth/forgot-password/send-external') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="text-start">
                <label class="formal-label">EMAIL ADDRESS</label>
                <input type="email" name="email" class="formal-input" placeholder="your@email.com" required>
            </div>
            <button type="submit" class="btn-pg-primary shadow-lg mt-3">SEND CODE</button>
        </form>
        <button type="button" class="btn btn-link btn-sm mt-2 w-100 text-white-50" id="pgCloseForgot">Cancel</button>
    </div>
</div>

<!-- Step 2: Verify Code — only shows if the server just sent one -->
<?php if(session()->getFlashdata('show_reset_form')): ?>
<div class="modal-backdrop-custom" style="display:flex; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center;">
    <div class="glass-card" style="max-width:380px;">
        <h5 class="fw-bold text-white mb-3 text-center">Enter Verification Code</h5>
        <p class="text-white-50 small mb-3 text-center" style="font-size: 11px;">Check your email for the 6-digit code. Verifying will log you in directly.</p>
        <form action="<?= base_url('auth/forgot-password/verify-external') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="text-start">
                <label class="formal-label">EMAIL ADDRESS</label>
                <input type="email" name="email" value="<?= old('email') ?>" class="formal-input" required>
                <label class="formal-label">VERIFICATION CODE</label>
                <input type="text" name="code" class="formal-input" placeholder="6-digit code" maxlength="6" required>
            </div>
            <button type="submit" class="btn-pg-primary shadow-lg mt-3">LOG IN</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Terms & Conditions -->
<div class="modal-backdrop-custom" id="pgTermsModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center; padding:20px;">
    <div class="glass-card text-start" style="max-width:480px; max-height:80vh; overflow-y:auto;">
        <h5 class="fw-bold text-white mb-3">Terms &amp; Conditions</h5>
        <div style="font-size: 11px; line-height: 1.7; color: var(--text-dim);">
            <p>By registering for or accessing the PharMediSync Partner Gateway, you agree to use this platform solely for legitimate business dealings with Robin Rose Trading, including placing or fulfilling orders, managing account information, and communicating regarding transactions.</p>
            <p>You are responsible for maintaining the confidentiality of your login credentials. Any activity performed under your account is your responsibility.</p>
            <p>Robin Rose Trading reserves the right to suspend or terminate partner access in cases of fraudulent activity, breach of agreed terms, or misuse of the platform.</p>
            <p>Pricing, stock availability, and order statuses shown on this platform are subject to change and are not final until confirmed in writing by Robin Rose Trading.</p>
        </div>
        <button type="button" class="btn-pg-primary shadow-lg mt-3 close-legal-modal">Close</button>
    </div>
</div>

<!-- Privacy Policy -->
<div class="modal-backdrop-custom" id="pgPrivacyModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center; padding:20px;">
    <div class="glass-card text-start" style="max-width:480px; max-height:80vh; overflow-y:auto;">
        <h5 class="fw-bold text-white mb-3">Privacy Policy</h5>
        <div style="font-size: 11px; line-height: 1.7; color: var(--text-dim);">
            <p>PharMediSync collects and stores information you provide during registration and ongoing use — including organization details, contact information, order history, and account activity — strictly to facilitate business transactions with Robin Rose Trading.</p>
            <p>Your data is not sold or shared with third parties, except where necessary for order fulfillment or as required by law.</p>
            <p>You may request a review or correction of your account information at any time by contacting Robin Rose Trading directly.</p>
            <p>Login activity, including IP address and timestamps, may be logged for account security and fraud prevention purposes.</p>
        </div>
        <button type="button" class="btn-pg-primary shadow-lg mt-3 close-legal-modal">Close</button>
    </div>
</div>

<script>
    const pgForgotModal = document.getElementById('pgForgotModal');
    document.getElementById('pgForgotLink').addEventListener('click', function(e) {
        e.preventDefault();
        pgForgotModal.style.display = 'flex';
    });
    document.getElementById('pgCloseForgot').addEventListener('click', function() {
        pgForgotModal.style.display = 'none';
    });

    const pgTermsModal = document.getElementById('pgTermsModal');
    const pgPrivacyModal = document.getElementById('pgPrivacyModal');
    document.getElementById('openTermsPg').addEventListener('click', function(e) {
        e.preventDefault();
        pgTermsModal.style.display = 'flex';
    });
    document.getElementById('openPrivacyPg').addEventListener('click', function(e) {
        e.preventDefault();
        pgPrivacyModal.style.display = 'flex';
    });
    document.querySelectorAll('.close-legal-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            pgTermsModal.style.display = 'none';
            pgPrivacyModal.style.display = 'none';
        });
    });
</script>

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>