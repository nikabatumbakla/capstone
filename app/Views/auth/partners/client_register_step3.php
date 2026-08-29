<?php include APPPATH . 'Views/shared/pg_header.php'; ?>

<div class="glass-card glass-card-wide">
    <div class="btn-group bg-dark bg-opacity-25 rounded-pill p-1 mb-4">
        <a href="<?= base_url('partner-gateway') ?>" class="btn btn-sm text-white rounded-pill px-4 fw-bold opacity-50 text-decoration-none">LOGIN</a>
        <button class="btn btn-sm btn-dark rounded-pill px-4 fw-bold">REGISTER</button>
    </div>

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

        <div class="row g-3 mb-4">
            <div class="col-6">
                <label class="formal-label">PASSWORD *</label>
                <input type="password" name="password" class="formal-input" required>
            </div>
            <div class="col-6">
                <label class="formal-label">CONFIRM PASSWORD *</label>
                <input type="password" name="password_confirm" class="formal-input" required>
            </div>
        </div>

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

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="termsCheck" required>
            <label class="form-check-label small text-white-50" for="termsCheck" style="font-size: 10px;">
                I agree to the <a href="#" class="text-white">Terms of Service</a> and <a href="#" class="text-white">Privacy Policy</a>
            </label>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2 fw-bold" onclick="history.back()" style="font-size: 11px;">Back</button>
            <button type="submit" class="btn-pg-primary shadow-lg">Submit Application</button>
        </div>
    </form>
</div>

<!-- JAVASCRIPT FOR FILE FEEDBACK -->
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

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>