<nav id="sidebar">
    <div class="sidebar-brand-wrapper">
        <div class="d-flex align-items-center">
            <div class="brand-logo-hex"><img src="<?= base_url('public/images/briefcase.png') ?>" width="16"></div>
            <div class="ms-3">
                <h6 class="brand-name-main">PharMediSync</h6>
                <p class="brand-tag">Robin Rose Trading</p>
            </div>
        </div>
        <div class="platform-indicator"><span class="badge bg-white text-danger px-3" style="font-size:8px">INSTITUTIONAL CLIENT</span></div>
    </div>

    <div class="sidebar-nav-container" id="sidebarScrollContainer">
        <ul class="list-unstyled components" id="clientAccordion">
            
            <li class="nav-label">MAIN</li>
            <li class="nav-item">
                <a href="<?= base_url('client/dashboard') ?>" class="nav-link-custom <?= ($page_name == 'dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-label">ORDERS</li>
            <!-- UPDATED LINKS -->
            <li class="nav-item">
                <a href="<?= base_url('client/orders/browse') ?>" class="nav-link-custom <?= ($page_name == 'browse') ? 'active' : '' ?>">
                    <i class="fas fa-shopping-bag"></i> <span>Browse Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('client/orders/place-order') ?>" class="nav-link-custom <?= ($page_name == 'place') ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i> <span>Place Order</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('client/orders/my-orders') ?>" class="nav-link-custom <?= ($page_name == 'orders') ? 'active' : '' ?>">
                    <i class="fas fa-list-ul"></i> <span>My Orders</span>
                </a>
            </li>

            <li class="nav-label">ACCOUNT</li>
            <li class="nav-item"><a href="<?= base_url('client/account/payment') ?>" class="nav-link-custom"><i class="fas fa-credit-card"></i> <span>Payment</span></a></li>
            <li class="nav-item"><a href="<?= base_url('client/account/invoices') ?>" class="nav-link-custom"><i class="fas fa-file-invoice-dollar"></i> <span>Invoices</span></a></li>

            <li class="nav-label">SUPPORT</li>
            <li class="nav-item"><a href="<?= base_url('client/support/chatbot') ?>" class="nav-link-custom"><i class="fas fa-comment-dots"></i> <span>ChatBot Support</span></a></li>
            <li class="nav-item"><a href="<?= base_url('client/support/announcements') ?>" class="nav-link-custom"><i class="fas fa-bullhorn"></i> <span>Announcements</span></a></li>
            <li class="nav-item"><a href="<?= base_url('client/support/profile') ?>" class="nav-link-custom"><i class="fas fa-user-circle"></i> <span>My Profile</span></a></li>
        </ul>
    </div>

    <!-- User Section -->
    <div class="sidebar-user-section">
        <div class="user-glass-pill d-flex align-items-center">
            <div class="user-profile-img"><?= substr($fullname, 0, 1) ?></div>
            <div class="user-meta ms-2 flex-grow-1">
                <span class="user-display-name text-truncate" style="max-width:110px"><?= $fullname ?></span>
                <span class="user-display-role">Procurement Officer</span>
            </div>
            <a href="<?= base_url('logout') ?>" class="logout-minimal"><i class="fas fa-power-off"></i></a>
        </div>
    </div>
</nav>