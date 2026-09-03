<?php include APPPATH . 'Views/shared/pg_header.php'; ?>
<div class="glass-card text-center">
    <i class="fas fa-check-circle text-success mb-3" style="font-size: 48px;"></i>
    <h4 class="fw-bold text-white mb-2">Application Submitted</h4>
    <p class="text-white-50 small mb-4">
        Your <?= esc($role) ?> registration has been received. Robin Rose Trading will review your application and notify you
        once your account is reviewed and approved.
    </p>

    <div class="p-2 px-4 rounded-pill d-inline-block mb-5" style="background: rgba(52, 152, 219, 0.15); border: 1px solid rgba(52, 152, 219, 0.3);">
        <small class="text-white fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">
            ✓ Application Reference: <?= esc($reference) ?>
        </small>
    </div>

    <div class="mt-2">
        <a href="<?= base_url('partner-gateway') ?>" class="btn-pg-primary w-100 py-3 shadow-lg text-decoration-none">
            Now Login
        </a>
    </div>
</div>
<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>