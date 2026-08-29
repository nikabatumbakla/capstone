<?php 
    $title = "Supplier Registration | PharMediSync";
    $pgSubtitle = "Robin Rose Trading · New Supplier Registration";
    include APPPATH . 'Views/shared/pg_header.php'; 
?>

<div class="btn-group bg-dark bg-opacity-25 rounded-pill p-1 mb-4">
        <a href="<?= base_url('partner-gateway') ?>" class="btn btn-sm text-white rounded-pill px-4 fw-bold opacity-50 text-decoration-none">LOGIN</a>
        <button class="btn btn-sm btn-dark rounded-pill px-4 fw-bold">REGISTER</button>
    </div>

<div class="glass-card" style="max-width: 650px;">

    <!-- Progress Steps -->
    <div class="pg-steps mb-4">
        <div class="pg-step active"><span class="num">1</span> Organization</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">2</span> Products</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">3</span> Account</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">4</span> Done</div>
    </div>

    <!-- Info Box -->
    <div class="pg-info-box text-start mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-warehouse me-3 mt-1" style="color: #3498db; font-size: 16px;"></i>
            <span>Register as a supplier to receive Purchase Orders, update delivery schedules, and manage your catalog for <b>Robin Rose Trading</b>.</span>
        </div>
    </div>

    <form action="<?= base_url('register/supplier/step1') ?>" method="POST" class="text-start">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="formal-label">COMPANY / SUPPLIER NAME *</label>
            <input type="text" name="supplier_name" class="formal-input" placeholder="e.g. Pentagon Medical" required>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6"><label class="formal-label">CONTACT PERSON *</label><input type="text" name="contact_person" class="formal-input" required></div>
            <div class="col-6"><label class="formal-label">POSITION / TITLE</label><input type="text" name="position" class="formal-input"></div>
        </div>

        <div class="mb-3"><label class="formal-label">BUSINESS ADDRESS *</label><input type="text" name="address" class="formal-input" required></div>

        <div class="row g-3 mb-4">
            <div class="col-6"><label class="formal-label">PHONE NUMBER *</label><input type="tel" name="phone" class="formal-input" required></div>
            <div class="col-6"><label class="formal-label">BUSINESS EMAIL *</label><input type="email" name="biz_email" class="formal-input" required></div>
        </div>

        <button type="submit" class="btn-pg-primary shadow-lg">Next: Product Details</button>
    </form>
</div>

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>