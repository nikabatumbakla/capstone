<?php include APPPATH . 'Views/shared/pg_header.php'; ?>

<div class="glass-card glass-card-wide">

    <div class="pg-steps mb-5">
        <div class="pg-step done"><span class="num"><i class="fas fa-check"></i></span> Organization</div>
        <div class="line done"></div>
        <div class="pg-step done"><span class="num"><i class="fas fa-check"></i></span> Contact</div>
        <div class="line done"></div>
        <div class="pg-step active"><span class="num">3</span> Account</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">4</span> Done</div>
    </div>

    <form action="<?= base_url('register/client/submit') ?>" method="POST" enctype="multipart/form-data" class="text-start">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="formal-label">EMAIL (LOGIN CREDENTIALS) *</label>
            <input type="email" name="email" class="formal-input" placeholder="official@company.com" required>
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

        <!-- UPLOAD AREA WITH FEEDBACK -->
        <div class="mb-4">
            <label class="formal-label">SUPPORTING DOCUMENT (OPTIONAL)</label>
            <div class="upload-dashed-box text-center p-4" id="dropZone">
                <input type="file" name="permit" id="permitFile" class="d-none" onchange="updateFileName()">
                <label for="permitFile" style="cursor: pointer;" id="uploadLabel">
                    <i class="fas fa-file-invoice text-white-50 mb-2 fs-3"></i>
                    <p class="mb-0 fw-bold small" id="fileNameDisplay">Business Permit / Authority Letter</p>
                    <small class="text-white-50" style="font-size: 8px;">PDF, JPG, PNG · Max 5MB</small>
                </label>
            </div>
        </div>

        <div class="d-flex align-items-start gap-2 mb-4" style="font-size: 10.5px; color: var(--text-dim); line-height: 1.5;">
    <input type="checkbox" name="agree_terms" id="agreeTermsPg" class="form-check-input mt-1" required style="flex-shrink:0;">
    <label for="agreeTermsPg">
        I agree to the <a href="#" id="openTermsPg" class="text-white fw-bold" style="text-decoration: underline;">Terms &amp; Conditions</a> and <a href="#" id="openPrivacyPg" class="text-white fw-bold" style="text-decoration: underline;">Privacy Policy</a>.
    </label>
</div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2 fw-bold" onclick="history.back()" style="font-size: 11px;">Back</button>
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
            <p>PharMediSync collects the information you provide during registration — organization details, contact information, and supporting documents — strictly to facilitate business transactions with Robin Rose Trading.</p>
            <p>Your data is not sold or shared with third parties, except where necessary for order fulfillment or as required by law.</p>
            <p>You may request a review or correction of your account information at any time by contacting Robin Rose Trading directly.</p>
        </div>
        <button type="button" class="btn-pg-primary shadow-lg mt-3 close-legal-modal">Close</button>
    </div>
</div>

<script>
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

<script>
function updateFileName() {
    const input = document.getElementById('permitFile');
    const display = document.getElementById('fileNameDisplay');
    const box = document.getElementById('dropZone');
    
    if (input.files && input.files.length > 0) {
        display.innerText = "✓ " + input.files[0].name; // Show the added filename
        display.style.color = "#2ecc71"; // Change to success green
        box.style.borderColor = "#2ecc71";
    }
}
</script>

<script>
    const pwField = document.getElementById('regPassword');
    const confirmField = document.getElementById('regPasswordConfirm');
    const matchMsg = document.getElementById('passwordMatchMsg');

    function checkPasswordMatch() {
        if (confirmField.value === '') {
            matchMsg.style.display = 'none';
            return;
        }

        if (pwField.value === confirmField.value) {
            matchMsg.textContent = '✓ Passwords match';
            matchMsg.style.color = '#00943e';
            matchMsg.style.display = 'block';
        } else {
            matchMsg.textContent = '✗ Passwords do not match';
            matchMsg.style.color = '#e74c3c';
            matchMsg.style.display = 'block';
        }
    }

    pwField.addEventListener('input', checkPasswordMatch);
    confirmField.addEventListener('input', checkPasswordMatch);

    // Block submission client-side too, as a first line of defense
    document.querySelector('form').addEventListener('submit', function(e) {
        if (pwField.value !== confirmField.value) {
            e.preventDefault();
            matchMsg.textContent = '✗ Passwords do not match';
            matchMsg.style.color = '#e74c3c';
            matchMsg.style.display = 'block';
            confirmField.focus();
        }
    });
</script>

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>