<nav class="navbar main-header px-3 shadow-sm bg-white">
    <div class="d-flex align-items-center">
        <!-- Sidebar Toggle (Burger) -->
        <button id="sidebarToggle" class="btn-burger me-3">
            <i class="fas fa-bars"></i>
        </button>
        <div class="breadcrumb-area">
            <span class="fw-bold small" style="color: #4a0000;">Robin Rose Trading — Medical Supply</span>
        </div>
    </div>
    
    <div class="header-icons d-flex align-items-center">
        <button class="icon-btn position-relative me-3">
            <i class="far fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="padding: 3px; border: 2px solid white;"> </span>
        </button>

        <button class="icon-btn me-3">
            <i class="far fa-envelope"></i>
        </button>

        <a href="<?= base_url('admin/sales/pos') ?>" class="btn btn-sm btn-maroon rounded-pill px-3 me-3 d-flex align-items-center shadow-sm" style="font-size: 10px; height: 32px; font-weight: 700; text-decoration: none;">
            <i class="fas fa-cash-register me-2"></i> POS
        </a>
    </div>
</nav>