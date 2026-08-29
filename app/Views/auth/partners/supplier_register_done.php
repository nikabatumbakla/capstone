<?php 
    $title = "Application Received | PharMediSync";
    $pgSubtitle = "Robin Rose Trading · Supplier Portal";
    include APPPATH . 'Views/shared/pg_header.php'; 
?>

<div class="glass-card text-center" style="max-width: 500px; padding: 60px 40px;">
    <!-- Confetti Icon -->
    <div class="mb-4">
        <span style="font-size: 60px;">🎉</span>
    </div>

    <h2 class="fw-bold mb-3" style="font-size: 28px;">Application Received!</h2>
    
    <div class="px-2">
        <p class="text-white-50 mb-4" style="font-size: 13px; line-height: 1.6;">
            Your supplier registration has been <br> submitted to <b>Robin Rose Trading</b> for review.
        </p>

        <p class="text-white-50 mb-5" style="font-size: 11px;">
            You will be notified via email within <b class="text-white">2-3 business days</b>. <br>
            Once approved, you'll have full access to the Supplier Portal.
        </p>
    </div>

    <!-- Reference Pill (Figma Match) -->
    <div class="p-2 px-4 rounded-pill d-inline-block mb-5" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">
        <small class="text-white fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">
            ✓ Reference: <?= esc($reference) ?>
        </small>
    </div>

    <div class="mt-2">
        <a href="<?= base_url('partner-gateway') ?>" class="btn-pg-primary w-100 py-3 shadow-lg text-decoration-none">
            Back to Login
        </a>
    </div>
</div>

<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>