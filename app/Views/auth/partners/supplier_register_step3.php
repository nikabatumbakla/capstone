<?php include APPPATH . 'Views/shared/pg_header.php'; ?>

<div class="glass-card" style="max-width: 650px;">
    <div class="pg-steps mb-5">
        <div class="pg-step done"><span class="num"><i class="fas fa-check"></i></span> Organization</div>
        <div class="line done"></div>
        <div class="pg-step done"><span class="num"><i class="fas fa-check"></i></span> Products</div>
        <div class="line done"></div>
        <div class="pg-step active"><span class="num">3</span> Account</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">4</span> Done</div>
    </div>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 small text-white bg-danger bg-opacity-25 border-0"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('register/supplier/submit') ?>" method="POST" class="text-start">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="formal-label">LOGIN EMAIL *</label>
            <input type="email" name="email" class="formal-input" placeholder="official@company.com" value="<?= old('email') ?>" required>
        </div>

        <div class="row g-3 mb-1">
            <div class="col-6">
                <label class="formal-label">PASSWORD *</label>
                <input type="password" name="password" id="regPassword" class="formal-input" minlength="8" required>
            </div>
            <div class="col-6">
                <label class="formal-label">CONFIRM PASSWORD *</label>
                <input type="password" name="password_confirm" id="regPasswordConfirm" class="formal-input" minlength="8" required>
            </div>
        </div>
        <div id="passwordMatchMsg" class="mb-4" style="font-size: 10.5px; display: none;"></div>

        <div class="pg-info-box mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-bell me-3 mt-1" style="color: #f1c40f;"></i>
                <span style="font-size: 10px;">Once approved by <b>Robin Rose Trading</b>, you'll receive Purchase Orders directly to your portal and can update delivery schedules in real time.</span>
            </div>
        </div>

        <div class="d-flex align-items-start gap-2 mb-4" style="font-size: 10.5px; color: var(--text-dim); line-height: 1.5;">
            <input type="checkbox" name="agree_terms" id="agreeTermsPg" class="form-check-input mt-1" required style="flex-shrink:0;">
            <label for="agreeTermsPg">
                I agree to the <a href="#" id="openTermsPg" class="text-white fw-bold" style="text-decoration: underline;">Terms &amp; Conditions</a> and <a href="#" id="openPrivacyPg" class="text-white fw-bold" style="text-decoration: underline;">Privacy Policy</a>.
            </label>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2" onclick="history.back()">Back</button>
            <button type="submit" class="btn-pg-primary shadow-lg">Submit Application</button>
        </div>
    </form>
</div>

<!-- Terms & Conditions -->
<div class="modal-backdrop-custom" id="pgTermsModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center; padding:20px;">
    <div class="glass-card text-start" style="max-width:480px; max-height:80vh; overflow-y:auto;">
        <h5 class="fw-bold text-white mb-3">Terms &amp; Conditions</h5>
        <div style="font-size: 11px; line-height: 1.7; color: var(--text-dim);">
            <p>By registering for the PharMediSync Partner Gateway, you agree to use this platform solely for legitimate business dealings with Robin Rose Trading.</p>
            <p>You are responsible for maintaining the confidentiality of your login credentials. Any activity performed under your account is your responsibility.</p>
            <p>Robin Rose Trading reserves the right to suspend or terminate partner access in cases of fraudulent activity or misuse of the platform.</p>
            <p>Your registration will be reviewed and approved by Robin Rose Trading before account access is granted.</p>
        </div>
        <button type="button" class="btn-pg-primary shadow-lg mt-3 close-legal-modal">Close</button>
    </div>
</div>

<!-- Privacy Policy -->
<div class="modal-backdrop-custom" id="pgPrivacyModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1050; align-items:center; justify-content:center; padding:20px;">
    <div class="glass-card text-start" style="max-width:480px; max-height:80vh; overflow-y:auto;">
        <h5 class="fw-bold text-white mb-3">Privacy Policy</h5>
        <div style="font-size: 11px; line-height: 1.7; color: var(--text-dim);">
            <p>PharMediSync collects the information you provide during registration — company details, contact information, and supporting documents — strictly to facilitate business transactions with Robin Rose Trading.</p>
            <p>Your data is not sold or shared with third parties, except where necessary for order fulfillment or as required by law.</p>
            <p>You may request a review or correction of your account information at any time by contacting Robin Rose Trading directly.</p>
        </div>
        <button type="button" class="btn-pg-primary shadow-lg mt-3 close-legal-modal">Close</button>
    </div>
</div>

<script>
    const pwField = document.getElementById('regPassword');
    const confirmField = document.getElementById('regPasswordConfirm');
    const matchMsg = document.getElementById('passwordMatchMsg');

    function checkPasswordMatch() {
        if (confirmField.value === '') { matchMsg.style.display = 'none'; return; }
        if (pwField.value === confirmField.value) {
            matchMsg.textContent = '✓ Passwords match';
            matchMsg.style.color = '#087b38';
        } else {
            matchMsg.textContent = '✗ Passwords do not match';
            matchMsg.style.color = '#e74c3c';
        }
        matchMsg.style.display = 'block';
    }
    pwField.addEventListener('input', checkPasswordMatch);
    confirmField.addEventListener('input', checkPasswordMatch);

    document.querySelector('form').addEventListener('submit', function(e) {
        if (pwField.value !== confirmField.value) {
            e.preventDefault();
            checkPasswordMatch();
            confirmField.focus();
        }
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