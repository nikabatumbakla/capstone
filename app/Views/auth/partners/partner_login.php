<?php 
    $title = "Partner Login | PharMediSync";
    $pgSubtitle = "Robin Rose Trading · Supplier & Client Portal";
    include APPPATH . 'Views/shared/pg_header.php'; 
?>

<div class="btn-group bg-dark bg-opacity-25 rounded-pill p-1 mb-4">
        <button class="btn btn-sm btn-dark rounded-pill px-4 fw-bold">LOGIN</button>
        <!-- Dynamic Link: Updated by JS -->
        <a href="<?= base_url('partner-gateway/register/client') ?>" id="regLink" class="btn btn-sm text-white rounded-pill px-4 fw-bold opacity-50 text-decoration-none">REGISTER</a>
    </div>

<div class="glass-card">

    <form action="<?= base_url('auth/login/external') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="role" id="roleInput" value="institutional_client">

        <div class="role-group">
            <div class="role-card active" id="cardClient" onclick="setRole('institutional_client')">
                <i class="fas fa-hospital"></i>
                <strong>Institutional Client</strong>
                <small>Hospital, School, LGU, Brgy</small>
            </div>
            <div class="role-card" id="cardSupplier" onclick="setRole('supplier')">
                <i class="fas fa-truck-loading"></i>
                <strong>Supplier</strong>
                <small>Pentagon, Sanicare, Etc.</small>
            </div>
        </div>

        <div class="text-start">
            <label class="formal-label">EMAIL ADDRESS</label>
            <input type="email" name="email" class="formal-input" placeholder="your@company.com" required>
            <label class="formal-label">PASSWORD</label>
            <input type="password" name="password" class="formal-input" placeholder="Enter Your Password" required>
        </div>

        <button type="submit" class="btn-pg-primary shadow-lg mt-3">ENTER DASHBOARD</button>
    </form>

    <div class="mt-4 pt-3 border-top border-white-10">
        <small class="text-white-50" id="newPartnerText">NEW CLIENT?</small><br>
        <a href="<?= base_url('partner-gateway/register/client') ?>" id="footerRegBtn" class="btn btn-sm btn-light rounded-pill px-4 mt-2 fw-bold text-dark text-decoration-none" style="font-size: 10px;">Create Account</a>
    </div>
</div>

<script>
    function setRole(role) {
        document.getElementById('roleInput').value = role;
        document.getElementById('cardClient').classList.toggle('active', role === 'institutional_client');
        document.getElementById('cardSupplier').classList.toggle('active', role === 'supplier');
        
        // DYNAMIC SWITCHING LOGIC
        const regLink = document.getElementById('regLink');
        const footerBtn = document.getElementById('footerRegBtn');
        const footerText = document.getElementById('newPartnerText');

        if(role === 'supplier') {
            regLink.href = "<?= base_url('partner-gateway/register/supplier') ?>";
            footerBtn.href = "<?= base_url('partner-gateway/register/supplier') ?>";
            footerBtn.innerText = "Create Account";
            footerText.innerText = "NEW SUPPLIER?";
        } else {
            regLink.href = "<?= base_url('partner-gateway/register/client') ?>";
            footerBtn.href = "<?= base_url('partner-gateway/register/client') ?>";
            footerBtn.innerText = "Create Account";
            footerText.innerText = "NEW CLIENT?";
        }
    }
</script>
<?php include APPPATH . 'Views/shared/pg_footer.php'; ?>