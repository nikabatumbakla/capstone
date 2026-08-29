<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="<?= base_url('/') ?>" class="nav-brand">
            <img src="<?= base_url('public/images/logo.png') ?>" alt="Robin Rose Trading Logo" class="nav-logo">
            <div class="brand-text">
                <span class="brand-script">Robin Rose</span>
                <span class="brand-bold">Trading</span>
            </div>
        </a>
        <ul class="nav-links">
            <li><a href="<?= base_url('/') ?>" class="nav-link <?= ($active_nav ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= base_url('about') ?>" class="nav-link <?= ($active_nav ?? '') === 'about' ? 'active' : '' ?>">About Us</a></li>
            <li class="dropdown">
                <a href="<?= base_url('products') ?>" class="nav-link <?= ($active_nav ?? '') === 'products' ? 'active' : '' ?>">Products <i class="fa fa-chevron-down"></i></a>
                <div class="dropdown-menu">
                    <a href="<?= base_url('products?cat=diagnostic') ?>">Diagnostic & Monitoring</a>
                    <a href="<?= base_url('products?cat=otc') ?>">OTC Medicines</a>
                    <a href="<?= base_url('products?cat=respiratory') ?>">Respiratory Care</a>
                    <a href="<?= base_url('products?cat=mobility') ?>">Mobility & Rehabilitation</a>
                    <a href="<?= base_url('products?cat=ppe') ?>">PPE & Infection Control</a>
                    <a href="<?= base_url('products?cat=safety') ?>">Safety Equipment</a>
                    <a href="<?= base_url('products?cat=wound') ?>">Wound Care & Emergency</a>
                    <a href="<?= base_url('products?cat=incontinence') ?>">Incontinence Care</a>
                    <a href="<?= base_url('products?cat=educational') ?>">Educational & Specialty</a>
                    <a href="<?= base_url('products?cat=general') ?>">General Merchandise</a>
                </div>
            </li>
            <li><a href="<?= base_url('services') ?>" class="nav-link <?= ($active_nav ?? '') === 'services' ? 'active' : '' ?>">Services</a></li>
            <li><a href="<?= base_url('announcements') ?>" class="nav-link <?= ($active_nav ?? '') === 'announcements' ? 'active' : '' ?>">Announcements</a></li>
            <li><a href="<?= base_url('partner-gateway') ?>" class="nav-link <?= ($active_nav ?? '') === 'portal' ? 'active' : '' ?>">Portal</a></li>
            <li><a href="<?= base_url('contact') ?>" class="nav-link nav-cta <?= ($active_nav ?? '') === 'contact' ? 'active' : '' ?>">Contact</a></li>
        </ul>
        <button class="nav-toggle" id="navToggle"><i class="fa fa-bars"></i></button>
    </div>
</nav>
