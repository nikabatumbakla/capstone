<?php include APPPATH . 'Views/shared/pg_header.php'; ?>

<div class="glass-card" style="max-width: 650px;">
    <!-- PROGRESS STEPS -->
    <div class="pg-steps mb-5">
        <div class="pg-step done"><span class="num"><i class="fas fa-check"></i></span> Organization</div>
        <div class="line done"></div>
        <div class="pg-step active"><span class="num">2</span> Products</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">3</span> Account</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">4</span> Done</div>
    </div>

    <form action="<?= base_url('register/supplier/step2') ?>" method="POST" enctype="multipart/form-data" class="text-start">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="formal-label">PRODUCT CATEGORIES YOU SUPPLY *</label>
            <input type="text" name="product_categories" class="formal-input" placeholder="e.g. Medical Equipment, PPE, Surgical Supplies" required>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <label class="formal-label">AVERAGE LEAD TIME (DAYS) *</label>
                <input type="number" name="lead_time" class="formal-input" placeholder="e.g. 7" required>
            </div>
            <div class="col-6">
                <label class="formal-label">PAYMENT TERMS</label>
                <input type="text" name="payment_terms" class="formal-input" placeholder="e.g. Net 30">
            </div>
        </div>

        <!-- DASHED UPLOAD BOX (FIGMA MATCH) -->
        <div class="mb-4">
            <label class="formal-label">BUSINESS PERMIT / DTI REGISTRATION</label>
            <div class="upload-dashed-box text-center p-4" id="dropZone">
                <input type="file" name="permit" id="permitFile" class="d-none" onchange="updateFileName()">
                <label for="permitFile" style="cursor: pointer;">
                    <i class="fas fa-file-invoice text-white-50 mb-2 fs-3"></i>
                    <p class="mb-0 fw-bold small" id="fileNameDisplay">Upload Business Permit or DTI Certificate</p>
                    <small class="text-white-50" style="font-size: 8px;">PDF, JPG, PNG · Max 5MB</small>
                </label>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2" onclick="history.back()">Back</button>
            <button type="submit" class="btn-pg-primary shadow-lg">Next: Account Setup</button>
        </div>
    </form>
</div>

<script>
function updateFileName() {
    const input = document.getElementById('permitFile');
    const display = document.getElementById('fileNameDisplay');
    if (input.files.length > 0) {
        display.innerText = "✓ " + input.files[0].name;
        display.style.color = "#2ecc71";
    }
}
</script>

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>