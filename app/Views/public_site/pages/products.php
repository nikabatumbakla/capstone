<?= $this->extend('public_site/layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-hero">
    <h1>Product Catalog</h1>
    <p>Quality medical supplies for hospitals, clinics, schools, and communities</p>
    <div class="breadcrumb">
        <a href="<?= base_url('/') ?>">Home</a> <i class="fa fa-chevron-right"></i> Products
    </div>
</div>

<section class="section">
    <div class="sec-wrap">
        <div class="sec-head reveal">
            <div class="sec-tag">Browse</div>
            <h2 class="sec-title">Our <span>Products</span></h2>
            <p class="sec-sub">Everything your healthcare facility needs — all in one trusted supplier.</p>
        </div>

        <div style="display:flex; justify-content:center; margin-bottom:2rem;">
            <form action="" method="GET" style="max-width:400px; width:100%;">
                <input type="text" name="search" placeholder="Search products..." value="<?= esc($search) ?>"
                    style="width:100%; padding:.75rem 1.25rem; border-radius:30px; border:1px solid #ddd; font-family:var(--font-b);">
            </form>
        </div>

        <?php if(empty($products)): ?>
            <div style="text-align:center; padding:4rem 1rem; color:var(--gray);">
                <i class="fa fa-box-open" style="font-size:3rem; opacity:.25; margin-bottom:1rem; display:block;"></i>
                <p>No products found in this category.</p>
            </div>
        <?php else: ?>
        <div class="prod-grid">
            <?php foreach($products as $p): ?>
            <div class="prod-card reveal">
                <div class="prod-img">
                    <?php if($p['image_path']): ?>
                        <img src="<?= base_url($p['image_path']) ?>" alt="<?= esc($p['name']) ?>" style="max-height:140px;object-fit:contain;">
                    <?php else: ?>
                        <i class="fa fa-box-open" style="font-size:3rem;color:var(--blue-light);"></i>
                    <?php endif; ?>
                    <span class="prod-stock <?= $p['stock'] > 0 ? 'in' : 'out' ?>"><?= $p['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?></span>
                </div>
                <div class="prod-body">
                    <div class="prod-sku"><?= esc($p['cat_name']) ?></div>
                    <div class="prod-name"><?= esc($p['name']) ?></div>
                    <div class="prod-desc"><?= esc(substr($p['description'] ?? '', 0, 90)) ?><?= strlen($p['description'] ?? '') > 90 ? '...' : '' ?></div>
                    <div class="prod-foot">
                        <span class="prod-price"><?= $p['price'] ? '₱'.number_format($p['price'], 2) : 'Request Quote' ?></span>
                        <a href="<?= base_url('contact') ?>" class="btn-q">
                            <?= $p['price'] ? 'Order Now' : 'Get Quote' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if($total_pages > 1): ?>
        <div style="display:flex; justify-content:center; gap:.5rem; margin-top:3rem;">
            <?php
                $q = ($active_cat !== 'all' ? '&cat='.$active_cat : '') . ($search ? '&search='.urlencode($search) : '');
                for($p = 1; $p <= $total_pages; $p++):
            ?>
                <a href="?page=<?= $p.$q ?>" class="btn <?= $p == $current_page ? 'btn-red' : 'btn-outline' ?>" style="padding:.5rem 1rem; min-width:auto;"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<div class="cta-banner">
    <h2>Can't find what you're looking for?</h2>
    <p>Contact us directly — we carry over 500 products and can source specific items on request.</p>
    <div class="btns">
        <a href="tel:09292379053" class="btn btn-white"><i class="fa fa-phone"></i> Call Now</a>
        <a href="<?= base_url('contact') ?>" class="btn btn-outline-w"><i class="fa fa-envelope"></i> Full Inquiry Form</a>
    </div>
</div>

<?= $this->endSection() ?>