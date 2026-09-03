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
    <div class="dropdown me-3">
        <button class="icon-btn position-relative" id="notifBellBtn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="far fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notifBadge" style="font-size:8px; padding: 3px 5px; border: 2px solid white;"></span>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg border-0" style="width:320px;" id="notifDropdown">
            <div class="p-3 border-bottom bg-light">
                <h6 class="fw-bold mb-0" style="font-size:12px;">Notifications</h6>
            </div>
            <div id="notifList" style="max-height:320px; overflow-y:auto;">
                <div class="text-center text-muted p-4" style="font-size:11px;">Loading...</div>
            </div>
            <a href="<?= base_url('admin/management/alerts-tasks') ?>" class="d-block text-center p-2 border-top text-decoration-none" style="font-size:11px;">View All Alerts</a>
        </div>
    </div>

    <a href="<?= base_url('admin/pos-terminal') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-maroon rounded-pill px-3 me-3 d-flex align-items-center shadow-sm" style="font-size: 10px; height: 32px; font-weight: 700; text-decoration: none;">
        <i class="fas fa-cash-register me-2"></i> Open POS
    </a>
</div>
</nav>