<nav id="sidebar">
    <div class="sidebar-brand-wrapper">
        <div class="d-flex align-items-center">
            <div class="brand-logo-hex"><img src="<?= base_url('public/images/briefcase.png') ?>" width="16"></div>
            <div class="ms-3">
                <h6 class="brand-name-main">PharMediSync</h6>
                <p class="brand-tag">Supplier Terminal</p>
            </div>
        </div>
        <div class="platform-indicator"><span class="badge bg-white text-danger px-3" style="font-size:8px">SUPPLIER</span></div>
    </div>

    <div class="sidebar-nav-container" id="sidebarScrollContainer">
        
        <ul class="list-unstyled components" id="supplierAccordion">
    <li class="nav-label">MAIN</li>
    <li class="nav-item">
        <a href="<?= base_url('supplier/dashboard') ?>" class="nav-link-custom <?= ($page_name == 'dashboard') ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> <span>Dashboard</span>
        </a>
    </li>

    <li class="nav-label">PURCHASE ORDERS</li>
    <li class="nav-item">
        <a href="<?= base_url('supplier/orders/inbox') ?>" class="nav-link-custom <?= ($page_name == 'po_inbox') ? 'active' : '' ?>">
            <i class="fas fa-inbox"></i> <span>PO Inbox</span>
        </a>
    </li>
    <!-- Acknowledge PO usually redirects to the Inbox where they can pick one to approve -->
    <li>
        <a href="<?= base_url('supplier/orders/inbox?tab=open') ?>" class="nav-link-custom">
            <i class="fas fa-check-double"></i> <span>Acknowledge PO</span>
        </a>
    </li>
    <li>
        <a href="<?= base_url('supplier/orders/delivery') ?>" class="nav-link-custom <?= ($page_name == 'delivery') ? 'active' : '' ?>">
            <i class="fas fa-truck"></i> <span>Delivery Updates</span>
        </a>
    </li>

    <li class="nav-label">PRODUCTS & ACCOUNT</li>
    <li><a href="<?= base_url('supplier/inventory/catalog') ?>" class="nav-link-custom <?= ($page_name == 'catalog') ? 'active' : '' ?>"><i class="fas fa-box-open"></i> <span>My Product Catalog</span></a></li>
    <li><a href="<?= base_url('supplier/account/scorecard') ?>" class="nav-link-custom <?= ($page_name == 'scorecard') ? 'active' : '' ?>"><i class="fas fa-chart-pie"></i> <span>My Scorecard</span></a></li>
    <li><a href="<?= base_url('supplier/account/profile') ?>" class="nav-link-custom <?= ($page_name == 'profile') ? 'active' : '' ?>"><i class="fas fa-user-cog"></i> <span>Profile Settings</span></a></li>
</ul>
    </div>

    <div class="sidebar-user-section">
        <div class="user-glass-pill d-flex align-items-center">
            <div class="user-profile-img"><?= substr($fullname, 0, 1) ?></div>
            <div class="user-meta ms-2 flex-grow-1">
                <span class="user-display-name text-truncate" style="max-width:110px"><?= $fullname ?></span>
                <span class="user-display-role">Certified Partner</span>
            </div>
            <a href="<?= base_url('logout') ?>" class="logout-minimal"><i class="fas fa-power-off"></i></a>
        </div>
    </div>
</nav>
