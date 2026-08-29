<nav id="sidebar">
    <!-- Brand Section -->
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
        <div class="platform-indicator"><span class="pulse-dot"></span> Web Platform</div>
    </div>

    <!-- Scrollable Navigation Container (ID added for JS Scroll Fix) -->
    <div class="sidebar-nav-container" id="sidebarScrollContainer">
        <ul class="list-unstyled components" id="adminAccordion">
            
            <li class="nav-label">MAIN</li>
            <li class="nav-item">
                <a href="<?= base_url('admin/dashboard') ?>" class="nav-link-custom <?= ($page_name == 'dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-label">OPERATIONS</li>
            
            <!-- Inventory Folder -->
            <?php $inv_active = in_array($page_name, ['stock-management', 'adjustment-logs']); ?>
            <li class="nav-item">
                <a href="#inventoryCollapse" data-bs-toggle="collapse" class="nav-link-custom <?= $inv_active ? 'active' : 'collapsed' ?>" aria-expanded="<?= $inv_active ? 'true' : 'false' ?>">
                    <i class="fas fa-boxes"></i> <span>Inventory</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass <?= $inv_active ? 'show' : '' ?>" id="inventoryCollapse" data-bs-parent="#adminAccordion">
                    <li><a href="<?= base_url('admin/inventory/stock-management') ?>" class="<?= ($page_name == 'stock-management') ? 'active-sub' : '' ?>">Stock Management</a></li>
                    <li><a href="<?= base_url('admin/inventory/adjustment-logs') ?>" class="<?= ($page_name == 'adjustment-logs') ? 'active-sub' : '' ?>">Adjustment Logs</a></li>
                </ul>
            </li>

            <!-- Procurement Folder -->
            <?php $proc_active = in_array($page_name, ['suppliers', 'purchase-orders', 'goods-receipt']); ?>
            <li class="nav-item">
                <a href="#procurementCollapse" data-bs-toggle="collapse" class="nav-link-custom <?= $proc_active ? 'active' : 'collapsed' ?>" aria-expanded="<?= $proc_active ? 'true' : 'false' ?>">
                    <i class="fas fa-truck-loading"></i> <span>Procurement</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass <?= $proc_active ? 'show' : '' ?>" id="procurementCollapse" data-bs-parent="#adminAccordion">
                    <li><a href="<?= base_url('admin/procurement/suppliers') ?>" class="<?= ($page_name == 'suppliers') ? 'active-sub' : '' ?>">Suppliers</a></li>
                    <li><a href="<?= base_url('admin/procurement/purchase-orders') ?>" class="<?= ($page_name == 'purchase-orders') ? 'active-sub' : '' ?>">Purchase Orders</a></li>
                    <li><a href="<?= base_url('admin/procurement/goods-receipt') ?>" class="<?= ($page_name == 'goods-receipt') ? 'active-sub' : '' ?>">Goods Receipt (GRR)</a></li>
                </ul>
            </li>

            <!-- Sales Folder -->
            <?php $sales_active = in_array($page_name, ['institutional-clients', 'sales-orders', 'sales-returns', 'pos']); ?>
            <li class="nav-item">
                <a href="#salesCollapse" data-bs-toggle="collapse" class="nav-link-custom <?= $sales_active ? 'active' : 'collapsed' ?>" aria-expanded="<?= $sales_active ? 'true' : 'false' ?>">
                    <i class="fas fa-cash-register"></i> <span>Sales</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass <?= $sales_active ? 'show' : '' ?>" id="salesCollapse" data-bs-parent="#adminAccordion">
                    <li><a href="<?= base_url('admin/sales/institutional-clients') ?>" class="<?= ($page_name == 'institutional-clients') ? 'active-sub' : '' ?>">Institutional Clients</a></li>
                    <li><a href="<?= base_url('admin/sales/sales-orders') ?>" class="<?= ($page_name == 'sales-orders') ? 'active-sub' : '' ?>">Sales Orders</a></li>
                    <li><a href="<?= base_url('admin/sales/sales-returns') ?>" class="<?= ($page_name == 'sales-returns') ? 'active-sub' : '' ?>">Sales Returns</a></li>
                    <li><a href="<?= base_url('admin/sales/pos') ?>" class="<?= ($page_name == 'pos') ? 'active-sub' : '' ?>">Point of Sale</a></li>
                </ul>
            </li>

            <li class="nav-label">STRATEGY</li>
            
            <!-- Analytics Folder -->
            <?php $strat_active = in_array($page_name, ['predictive-dss', 'reports', 'analytics']); ?>
            <li class="nav-item">
                <a href="#analyticsCollapse" data-bs-toggle="collapse" class="nav-link-custom <?= $strat_active ? 'active' : 'collapsed' ?>" aria-expanded="<?= $strat_active ? 'true' : 'false' ?>">
                    <i class="fas fa-brain"></i> <span>Analytics</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass <?= $strat_active ? 'show' : '' ?>" id="analyticsCollapse" data-bs-parent="#adminAccordion">
                    <li><a href="<?= base_url('admin/strategy/analytics/predictive-dss') ?>" class="<?= ($page_name == 'predictive-dss') ? 'active-sub' : '' ?>">Predictive / DSS</a></li>
                    <li><a href="<?= base_url('admin/strategy/analytics/reports') ?>" class="<?= ($page_name == 'reports') ? 'active-sub' : '' ?>">Reports & Analytics</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="<?= base_url('admin/strategy/compliance') ?>" class="nav-link-custom <?= ($page_name == 'compliance') ? 'active' : '' ?>">
                    <i class="fas fa-balance-scale"></i> <span>BIR Compliance</span>
                </a>
            </li>

            <li class="nav-label">MANAGEMENT</li>
            <li class="nav-item"><a href="<?= base_url('admin/management/alerts-tasks') ?>" class="nav-link-custom <?= ($page_name == 'alerts') ? 'active' : '' ?>"><i class="fas fa-tasks"></i> <span>Alerts & Tasks</span></a></li>
            <li class="nav-item"><a href="<?= base_url('admin/management/bulletin-board') ?>" class="nav-link-custom <?= ($page_name == 'bulletin') ? 'active' : '' ?>"><i class="fas fa-chalkboard"></i> <span>Bulletin Board</span></a></li>
            <li class="nav-item"><a href="<?= base_url('admin/management/user-management') ?>" class="nav-link-custom <?= ($page_name == 'users') ? 'active' : '' ?>"><i class="fas fa-user-shield"></i> <span>User Management</span></a></li>
            <li class="nav-item"><a href="<?= base_url('admin/management/chatbot') ?>" class="nav-link-custom <?= ($page_name == 'chatbot') ? 'active' : '' ?>"><i class="fas fa-comment-dots"></i> <span>ChatBot</span></a></li>
            <li class="nav-item"><a href="<?= base_url('admin/management/customer-engagement') ?>" class="nav-link-custom <?= ($page_name == 'engagement') ? 'active' : '' ?>"><i class="fas fa-heart"></i> <span>Customer Engagement</span></a></li>
        </ul>
    </div>

    <!-- User Footer -->
    <div class="sidebar-user-section">
        <div class="user-glass-pill">
            <div class="user-profile-img"><?= substr($fullname, 0, 1) ?></div>
            <div class="user-meta">
                <span class="user-display-name"><?= $fullname ?></span>
                <span class="user-display-role">Administrator</span>
            </div>
            <a href="<?= base_url('logout') ?>" class="logout-minimal"><i class="fas fa-power-off"></i></a>
        </div>
    </div>
</nav>