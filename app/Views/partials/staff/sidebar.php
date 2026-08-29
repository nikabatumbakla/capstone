<nav id="sidebar">
    <div class="sidebar-brand-wrapper">
        <div class="d-flex align-items-center">
            <div class="brand-logo-hex"><img src="<?= base_url('public/images/briefcase.png') ?>" width="16"></div>
            <div class="ms-3">
                <h6 class="brand-name-main">PharMediSync</h6>
                <p class="brand-tag">Robin Rose Trading</p>
            </div>
        </div>
        <div class="platform-indicator"><span class="badge bg-white text-danger px-3">STAFF</span></div>
    </div>

    <div class="sidebar-nav-container" id="sidebarScrollContainer">
        <ul class="list-unstyled components" id="staffAccordion">
            
            <li class="nav-label">MAIN</li>
            <li class="nav-item">
                <a href="<?= base_url('staff/dashboard') ?>" class="nav-link-custom <?= ($page_name == 'dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-label">INVENTORY</li>
            <?php $inv_active = in_array($page_name, ['stock', 'logs']); ?>
            <li class="nav-item">
                <a href="#invCollapse" data-bs-toggle="collapse" class="nav-link-custom <?= $inv_active ? 'active' : 'collapsed' ?>">
                    <i class="fas fa-boxes"></i> <span>Inventory</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass <?= $inv_active ? 'show' : '' ?>" id="invCollapse" data-bs-parent="#staffAccordion">
                    <li><a href="<?= base_url('staff/inventory/stock') ?>" class="<?= ($page_name == 'stock') ? 'active-sub' : '' ?>">Stock View</a></li>
                    <li><a href="<?= base_url('staff/inventory/logs') ?>" class="<?= ($page_name == 'logs') ? 'active-sub' : '' ?>">Adjustment Logs</a></li>
                </ul>
            </li>

            <li class="nav-label">OPERATIONS</li>
            <?php $ops_active = in_array($page_name, ['pos', 'orders', 'returns', 'grr']); ?>
            <li class="nav-item">
                <a href="#opsCollapse" data-bs-toggle="collapse" class="nav-link-custom <?= $ops_active ? 'active' : 'collapsed' ?>">
                    <i class="fas fa-cash-register"></i> <span>Operations</span>
                </a>
                <ul class="collapse list-unstyled sub-menu-glass <?= $ops_active ? 'show' : '' ?>" id="opsCollapse" data-bs-parent="#staffAccordion">
                    <li><a href="<?= base_url('staff/operations/pos') ?>" class="<?= ($page_name == 'pos') ? 'active-sub' : '' ?>">Point of Sale</a></li>
                    <li><a href="<?= base_url('staff/operations/sales-orders') ?>" class="<?= ($page_name == 'orders') ? 'active-sub' : '' ?>">Sales Orders</a></li>
                    <li><a href="<?= base_url('staff/operations/sales-returns') ?>" class="<?= ($page_name == 'returns') ? 'active-sub' : '' ?>">Sales Returns</a></li>
                    <li><a href="<?= base_url('staff/operations/goods-receipt') ?>" class="<?= ($page_name == 'grr') ? 'active-sub' : '' ?>">Goods Receipt (GRR)</a></li>
                </ul>
            </li>

            <li class="nav-label">INFO</li>

<li class="nav-item">
    <a href="<?= base_url('staff/info/alerts') ?>" class="nav-link-custom <?= ($page_name == 'alerts') ? 'active' : '' ?>">
        <i class="fas fa-bell"></i> <span>My Alerts</span>
    </a>
</li>

<li class="nav-item">
    <a href="<?= base_url('staff/info/dss') ?>" class="nav-link-custom <?= ($page_name == 'dss') ? 'active' : '' ?>">
        <i class="fas fa-brain"></i> <span>DSS View</span>
    </a>
</li>

<li class="nav-item">
    <a href="<?= base_url('staff/info/bulletin') ?>" class="nav-link-custom <?= ($page_name == 'bulletin') ? 'active' : '' ?>">
        <i class="fas fa-bullhorn"></i> <span>Bulletin Board</span>
    </a>
</li>
        </ul>
    </div>

    <!-- Staff Identity Pill -->
    <div class="sidebar-user-section">
        <div class="user-glass-pill d-flex align-items-center">
            <div class="user-profile-img"><?= substr($fullname, 0, 1) ?></div>
            <div class="user-meta ms-2 flex-grow-1">
                <span class="user-display-name"><?= $fullname ?></span>
                <span class="user-display-role">Inventory Staff</span>
            </div>
            <a href="<?= base_url('logout') ?>" class="logout-minimal"><i class="fas fa-power-off"></i></a>
        </div>
    </div>
</nav>