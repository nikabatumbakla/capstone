<?php include APPPATH . 'Views/shared/pg_header.php'; ?>

<div class="glass-card" style="max-width: 650px;">
    <!-- PROGRESS STEPS -->
    <div class="pg-steps mb-5">
        <div class="pg-step done"><span class="num"><i class="fas fa-check"></i></span> Organization</div>
        <div class="line done"></div>
        <div class="pg-step done"><span class="num"><i class="fas fa-check"></i></span> Products</div>
        <div class="line done"></div>
        <div class="pg-step active"><span class="num">3</span> Account</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">4</span> Done</div>
    </div>

    <form action="<?= base_url('register/supplier/submit') ?>" method="POST" class="text-start">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="formal-label">LOGIN EMAIL *</label>
            <input type="email" name="email" class="formal-input" placeholder="official@company.com" required>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6"><label class="formal-label">PASSWORD *</label><input type="password" name="password" class="formal-input" required></div>
            <div class="col-6"><label class="formal-label">CONFIRM PASSWORD *</label><input type="password" name="password_confirm" class="formal-input" required></div>
        </div>

        <!-- SYSTEM NOTIFICATION (FIGMA MATCH) -->
        <div class="pg-info-box mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-bell me-3 mt-1" style="color: #f1c40f;"></i>
                <span style="font-size: 10px;">Once approved by <b>Robin Rose Trading</b>, you'll receive Purchase Orders directly to your portal and can update delivery schedules in real time.</span>
            </div>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="terms" required>
            <label class="form-check-label small text-white-50" for="terms" style="font-size:10px;">
                I agree to the <a href="#" class="text-white">Terms of Service</a> and <a href="#" class="text-white">Privacy Policy</a>
            </label>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2" onclick="history.back()">Back</button>
            <button type="submit" class="btn-pg-primary shadow-lg">Submit Application</button>
        </div>
    </form>
</div>   

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>