<?= $this->extend('public_site/layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .pc-hero { background: linear-gradient(135deg, #7b1113, #4a0000); color: #fff; padding: 60px 20px; text-align: center; }
    .pc-hero h1 { font-family: 'Playfair Display', serif; font-size: 32px; margin-bottom: 8px; }
    .pc-wrap { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
    .pc-filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 30px; justify-content: center; }
    .pc-filter-btn { padding: 6px 16px; border-radius: 20px; border: 1px solid #ddd; background: #fff; color: #333; font-size: 12px; text-decoration: none; transition: 0.2s; }
    .pc-filter-btn.active, .pc-filter-btn:hover { background: #7b1113; color: #fff; border-color: #7b1113; }
    .pc-search { max-width: 400px; margin: 0 auto 30px; }
    .pc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 24px; }
    .pc-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.06); transition: transform 0.2s; }
    .pc-card:hover { transform: translateY(-4px); }
    .pc-card img { width: 100%; height: 160px; object-fit: cover; background: #f5f5f5; }
    .pc-card-body { padding: 16px; }
    .pc-card-body h5 { font-size: 14px; margin-bottom: 4px; color: #222; }
    .pc-card-body small { color: #888; font-size: 11px; }
    .pc-price { color: #7b1113; font-weight: 700; font-size: 15px; margin-top: 8px; }
    .pc-stock { font-size: 10px; margin-top: 4px; }
    .pc-pager { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
    .pc-pager a { padding: 6px 12px; border: 1px solid #ddd; border-radius: 6px; text-decoration: none; color: #333; font-size: 12px; }
    .pc-pager a.active { background: #7b1113; color: #fff; border-color: #7b1113; }
</style>

<div class="pc-hero">
    <h1>Product Catalog</h1>
    <p>Quality medical supplies for hospitals, clinics, schools, and communities</p>
</div>

<div class="pc-wrap">
    <form action="" method="GET" class="pc-search">
        <input type="text" name="search" placeholder="Search products..." value="<?= esc($search) ?>" class="form-control" style="border-radius: 20px; padding: 10px 18px;">
    </form>

    <div class="pc-filters">
        <a href="<?= base_url('products') ?>" class="pc-filter-btn <?= $active_cat == 'all' ? 'active' : '' ?>">All</a>
        <?php foreach($categories as $cat): ?>
            <a href="<?= base_url('products?cat=' . $cat['slug']) ?>" class="pc-filter-btn <?= $active_cat == $cat['slug'] ? 'active' : '' ?>"><?= esc($cat['name']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="pc-grid">
        <?php if(empty($products)): ?>
            <p style="grid-column: 1/-1; text-align:center; color:#888;">No products found in this category.</p>
        <?php else: foreach($products as $p): ?>
        <div class="pc-card reveal">
            <?php if($p['image_path']): ?>
                <img src="<?= base_url($p['image_path']) ?>" alt="<?= esc($p['name']) ?>">
            <?php else: ?>
                <img src="<?= base_url('public/images/product-placeholder.png') ?>" alt="No image">
            <?php endif; ?>
            <div class="pc-card-body">
                <small><?= esc($p['cat_name']) ?></small>
                <h5><?= esc($p['name']) ?></h5>
                <div class="pc-price"><?= $p['price'] ? '₱' . number_format($p['price'], 2) : 'Contact for pricing' ?></div>
                <div class="pc-stock" style="color: <?= ($p['stock'] > 0) ? '#2e7d32' : '#999' ?>;">
                    <?= ($p['stock'] > 0) ? 'In Stock' : 'Contact for availability' ?>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="pc-pager">
        <?php
            $q = ($active_cat !== 'all' ? '&cat=' . $active_cat : '') . ($search ? '&search=' . urlencode($search) : '');
            for ($i = 1; $i <= $total_pages; $i++):
        ?>
            <a href="?page=<?= $i . $q ?>" class="<?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>