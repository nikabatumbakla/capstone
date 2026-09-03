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

    <div class="mt-3">
        <label class="formal-label">DON'T SEE YOUR CATEGORY? ADD NEW</label>
        <div id="newCategoryRows">
            <div class="d-flex gap-2 mb-2 new-category-row">
                <input type="text" name="new_categories[]" class="formal-input mb-0" placeholder="e.g. Veterinary Supplies">
                <button type="button" class="btn btn-outline-light rounded-pill px-3 btn-remove-category" style="display:none;">×</button>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" id="btnAddCategoryRow" style="font-size:10px;">
            <i class="fas fa-plus me-1"></i>Add Another Category
        </button>
    </div>

    <p class="text-white-50 mt-2 mb-0" style="font-size: 9.5px;">New categories will be reviewed by Robin Rose Trading before appearing publicly.</p>
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

<script>
    document.getElementById('btnAddCategoryRow').addEventListener('click', function() {
        const container = document.getElementById('newCategoryRows');
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 mb-2 new-category-row';
        row.innerHTML = `
            <input type="text" name="new_categories[]" class="formal-input mb-0" placeholder="e.g. Veterinary Supplies">
            <button type="button" class="btn btn-outline-light rounded-pill px-3 btn-remove-category">×</button>
        `;
        container.appendChild(row);
        row.querySelector('.btn-remove-category').addEventListener('click', () => row.remove());
    });
</script>

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>