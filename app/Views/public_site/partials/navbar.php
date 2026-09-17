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
        <?php
            $db = \Config\Database::connect();
            $navCats = $db->table('categories')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();
            foreach ($navCats as $nc):
        ?>
        <a href="<?= base_url('products?cat=' . $nc['category_id']) ?>"><?= esc($nc['name']) ?></a>
        <?php endforeach; ?>
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
