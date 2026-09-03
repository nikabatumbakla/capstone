<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= base_url('public/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('public/js/admin/main/dashboard.js') ?>"></script>
<script src="<?= base_url('public/js/admin/header_notifications.js') ?>"></script>


<script>
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
    });
</script>

<div class="offcanvas offcanvas-end" tabindex="-1" id="myProfileDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0"><i class="fas fa-user-shield me-2"></i>My Profile</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" id="myProfileContent">
        <div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>
    </div>
</div>

<script>
    if (typeof BASE_URL === 'undefined') {
        var BASE_URL = "<?= base_url() ?>";
    }
</script>

<script src="<?= base_url('public/js/admin/my_profile.js') ?>"></script>
</body>
</html>