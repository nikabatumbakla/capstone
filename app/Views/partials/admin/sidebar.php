<nav id="sidebar">
    <!-- Brand Section: Glassmorphism look -->
    <div class="sidebar-brand-wrapper">
        <div class="d-flex align-items-center">
            <div class="brand-logo-hex">
                <img src="<?= base_url('public/images/briefcase.png') ?>" width="16">
            </div>
            <div class="ms-3">
                <h6 class="brand-name-main">PharMediSync</h6>
                <p class="brand-tag">Robin Rose Trading</p>
            </div>
        </div>
        <div class="platform-indicator">
            <span class="pulse-dot"></span> Web Platform
        </div>
    </div>

    <!-- Scrollable Navigation -->
    <div class="sidebar-nav-container">
        <ul class="list-unstyled components" id="adminAccordion">
            
            <li class="nav-label">MAIN</li>
            <li class="nav-item">
                <a href="<?= base_url('admin/dashboard') ?>" class="nav-link-custom active">
                    <i class="fas fa-chart-line"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-label">OPERATIONS</li>
            
            <!-- Inventory Dropdown -->
            <li class="nav-item">
                <a href="#inventoryCollapse" data-bs-toggle="collapse" class="nav-link-custom collapsed">
                    <i class="fas fa-boxes"></i> <span>Inventory</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass" id="inventoryCollapse" data-bs-parent="#adminAccordion">
                    <li><a href="<?= base_url('admin/inventory/stock-management') ?>">Stock Management</a></li>
                    <li><a href="<?= base_url('admin/inventory/adjustment-logs') ?>">Adjustment Logs</a></li>
                </ul>
            </li>

            <!-- Procurement Dropdown -->
            <li class="nav-item">
                <a href="#procurementCollapse" data-bs-toggle="collapse" class="nav-link-custom collapsed">
                    <i class="fas fa-truck-loading"></i> <span>Procurement</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass" id="procurementCollapse" data-bs-parent="#adminAccordion">
                    <li><a href="<?= base_url('admin/procurement/suppliers') ?>">Suppliers</a></li>
                    <li><a href="<?= base_url('admin/procurement/purchase-orders') ?>">Purchase Orders</a></li>
                    <li><a href="<?= base_url('admin/procurement/goods-receipt') ?>">Goods Receipt (GRR)</a></li>
                </ul>
            </li>

            <!-- Sales Dropdown -->
            <li class="nav-item">
                <a href="#salesCollapse" data-bs-toggle="collapse" class="nav-link-custom collapsed">
                    <i class="fas fa-cash-register"></i> <span>Sales</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass" id="salesCollapse" data-bs-parent="#adminAccordion">
                    <li><a href="<?= base_url('admin/sales/institutional-clients') ?>">Institutional Clients</a></li>
                    <li><a href="<?= base_url('admin/sales/sales-orders') ?>">Sales Orders</a></li>
                    <li><a href="<?= base_url('admin/sales/sales-returns') ?>">Sales Returns</a></li>
                    <li><a href="<?= base_url('admin/sales/pos') ?>">Point of Sale</a></li>
                </ul>
            </li>

            <li class="nav-label">STRATEGY</li>
            <!-- Analytics Dropdown -->
            <li class="nav-item">
                <a href="#analyticsCollapse" data-bs-toggle="collapse" class="nav-link-custom collapsed">
                    <i class="fas fa-brain"></i> <span>Analytics</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass" id="analyticsCollapse" data-bs-parent="#adminAccordion">
                    <li><a href="<?= base_url('admin/analytics/predictive-dss') ?>">Predictive / DSS</a></li>
                    <li><a href="<?= base_url('admin/analytics/reports') ?>">Reports & Analytics</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link-custom"><i class="fas fa-balance-scale"></i> <span>BIR Compliance</span></a>
            </li>

            <li class="nav-label">MANAGEMENT</li>
            <li class="nav-item"><a href="<?= base_url('admin/system/alerts-tasks') ?>" class="nav-link-custom"><i class="fas fa-tasks"></i> <span>Alerts & Tasks</span></a></li>
            <li class="nav-item"><a href="<?= base_url('admin/system/bulletin-board') ?>" class="nav-link-custom"><i class="fas fa-chalkboard"></i> <span>Bulletin Board</span></a></li>
            <li class="nav-item"><a href="<?= base_url('admin/system/user-management') ?>" class="nav-link-custom"><i class="fas fa-user-shield"></i> <span>User Management</span></a></li>
            <li class="nav-item"><a href="<?= base_url('admin/system/chatbot') ?>" class="nav-link-custom"><i class="fas fa-comment-dots"></i> <span>ChatBot</span></a></li>
            <li class="nav-item"><a href="<?= base_url('admin/system/customer-engagement') ?>" class="nav-link-custom"><i class="fas fa-heart"></i> <span>Customer Engagement</span></a></li>
        </ul>
    </div>

    <!-- User Footer: Floating Style -->
    <div class="sidebar-user-section">
        <div class="user-glass-pill">
            <div class="user-profile-img"><?= substr($fullname, 0, 1) ?></div>
            <div class="user-meta">
                <span class="user-display-name"><?= $fullname ?></span>
                <span class="user-display-role">Administrator</span>
            </div>
            <a href="<?= base_url('auth/logout') ?>" class="logout-minimal"><i class="fas fa-power-off"></i></a>
        </div>
    </div>
</nav>