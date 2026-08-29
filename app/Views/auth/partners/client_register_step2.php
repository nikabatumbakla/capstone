<?php include APPPATH . 'Views/shared/pg_header.php'; ?>

 <!-- LOGIN/REGISTER TOGGLE -->
    <div class="btn-group bg-dark bg-opacity-25 rounded-pill p-1 mb-4">
        <a href="<?= base_url('partner-gateway') ?>" class="btn btn-sm text-white rounded-pill px-4 fw-bold opacity-50 text-decoration-none">LOGIN</a>
        <button class="btn btn-sm btn-dark rounded-pill px-4 fw-bold">REGISTER</button>
    </div>

<div class="glass-card" style="max-width: 650px;">

    <!-- PROGRESS STEPS (Step 2 is Active) -->
    <div class="pg-steps mb-5">
        <div class="pg-step done"><span class="num"><i class="fas fa-check"></i></span> Organization</div>
        <div class="line done"></div>
        <div class="pg-step active"><span class="num">2</span> Contact</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">3</span> Account</div>
        <div class="line"></div>
        <div class="pg-step"><span class="num">4</span> Done</div>
    </div>

    <!-- FORM -->
    <form action="<?= base_url('register/client/step2') ?>" method="POST" class="text-start">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="formal-label text-uppercase">Contact Person (Authorized Procurement Officer) *</label>
            <input type="text" name="contact_person" class="formal-input" placeholder="Enter Full Name" required>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="formal-label text-uppercase">Phone Number *</label>
                <input type="tel" name="phone" class="formal-input" placeholder="0917-000-0000" required>
            </div>
            <div class="col-6">
                <label class="formal-label text-uppercase">Alternative Phone</label>
                <input type="tel" name="alt_phone" class="formal-input" placeholder="Landline or Secondary Mobile">
            </div>
        </div>

        <div class="mb-3">
            <label class="formal-label text-uppercase">Official Email Address *</label>
            <input type="email" name="official_email" class="formal-input" placeholder="office@company.com" required>
        </div>

        <div class="mb-4">
            <label class="formal-label text-uppercase">Delivery Address (If different from above)</label>
            <textarea name="delivery_address" class="formal-input" rows="2" placeholder="Leave blank if same as business address"></textarea>
        </div>

        <!-- BUTTONS: Back and Next -->
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2 fw-bold" onclick="history.back()" style="font-size: 11px;">Back</button>
            <button type="submit" class="btn-pg-primary shadow-lg">Next: Account Setup</button>
        </div>
    </form>
</div>

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>