<?php 
    $title = "Institutional Registration | PharMediSync";
    $pgSubtitle = "Robin Rose Trading · New Client Registration";
    include APPPATH . 'Views/shared/pg_header.php'; 
?>

<div class="btn-group bg-dark bg-opacity-25 rounded-pill p-1 mb-4">
        <a href="<?= base_url('partner-gateway') ?>" class="btn btn-sm text-white rounded-pill px-4 fw-bold opacity-50 text-decoration-none">LOGIN</a>
        <button class="btn btn-sm btn-dark rounded-pill px-4 fw-bold">REGISTER</button>
    </div>

<!-- Added glass-card-wide to fit the stepper nicely -->
<div class="glass-card glass-card-wide">

    <!-- FIGMA PROGRESS STEPS (Styled via CSS above) -->
    <div class="pg-steps">
        <div class="pg-step active"><span class="num">1</span> Organization</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">2</span> Contact</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">3</span> Account</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">4</span> Done</div>
    </div>

    <!-- BLUE INTELLIGENCE NOTE (Styled via CSS above) -->
    <div class="pg-info-box text-start mt-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle me-3 mt-1"></i>
            <span>Register as an institutional client to place bulk orders, track deliveries, and receive BIR-compliant invoices from <b>Robin Rose Trading</b>.</span>
        </div>
    </div>

    <!-- STEP 1 FORM -->
    <form action="<?= base_url('register/client/step1') ?>" method="POST" class="text-start mt-4">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="formal-label">ORGANIZATION NAME *</label>
            <input type="text" name="organization_name" class="formal-input" placeholder="e.g. Mediatrix Hospital" required>
        </div>

        <div class="mb-3">
            <label class="formal-label">ORGANIZATION TYPE</label>
            <select name="organization_type" class="form-select formal-input" required>
                <option value="" disabled selected>Select type...</option>
                <option value="hospital">Hospital / Clinic</option>
                <option value="school">School / University</option>
                <option value="lgu">LGU / Government</option>
                <option value="barangay">Barangay Unit</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="formal-label">TIN (TAX IDENTIFICATION NUMBER)</label>
            <input type="text" name="tin" class="formal-input" placeholder="000-000-000-000">
        </div>

        <div class="mb-4">
            <label class="formal-label">COMPLETE ADDRESS *</label>
            <textarea name="complete_address" class="formal-input" rows="2" placeholder="Full street address, City, Province" required></textarea>
        </div>

        <button type="submit" class="btn-pg-primary shadow-lg">Next: Contact Details</button>
    </form>
</div>

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>