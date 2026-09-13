<?= view('partials/admin/head') ?>
<?php include APPPATH . 'Views/shared/pg_header.php'; ?>

<div class="btn-group bg-dark bg-opacity-25 rounded-pill p-1 mb-4">
        <a href="<?= base_url('partner-gateway') ?>" class="btn btn-sm text-white rounded-pill px-4 fw-bold opacity-50 text-decoration-none">LOGIN</a>
        <button class="btn btn-sm btn-dark rounded-pill px-4 fw-bold">REGISTER</button>
    </div>

<div class="glass-card" style="max-width: 650px;">
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

        <div class="mb-4">
            <label class="formal-label">PRODUCT CATEGORIES YOU SUPPLY *</label>
            <div class="p-3 rounded-4" style="background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); max-height: 220px; overflow-y: auto;">
                <?php foreach($categories as $cat): ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="existing_categories[]" value="<?= $cat['category_id'] ?>" id="cat_<?= $cat['category_id'] ?>">
                        <label class="form-check-label text-white" for="cat_<?= $cat['category_id'] ?>" style="font-size: 11px;"><?= esc($cat['name']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-white-50 mt-2 mb-0" style="font-size: 9.5px;">Select all categories that apply to the products you supply.</p>
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

            <div class="mb-4">
    <label class="formal-label">BANK PAYMENT DETAILS</label>
    <div class="row g-3">
        <div class="col-12">
            <input type="text" name="bank_name" class="formal-input" placeholder="Bank Name (e.g. BDO, BPI, Metrobank)">
        </div>
        <div class="col-6">
            <input type="text" name="bank_account_name" class="formal-input" placeholder="Account Name">
        </div>
        <div class="col-6">
            <input type="text" name="bank_account_number" class="formal-input" placeholder="Account Number">
        </div>
    </div>
    <p class="text-white-50 mt-2 mb-0" style="font-size: 9.5px;">Used by Robin Rose Trading to process payments for fulfilled orders. Optional — can be added later from your profile.</p>
</div>

        </div>

        

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
            <button type="submit" class="btn-pg-primary shadow-lg">Account Setup <i class="fas fa-arrow-right"></i></button>
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