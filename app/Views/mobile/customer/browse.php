<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-3">
    <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Browse Products</h5>
</div>

<form action="" method="GET" id="browseForm" class="mb-2">
    <input type="text" name="search" class="form-control mb-2" placeholder="Search products..." value="<?= esc($search) ?>">
    <input type="hidden" name="cat" id="catInput" value="<?= esc($active_cat) ?>">

    <div class="m-custom-dropdown">
        <button type="button" class="m-dropdown-toggle" id="dropdownToggle">
            <span id="dropdownLabel">
                <?php
                    $activeCatName = 'All Categories';
                    foreach($categories as $c) { if ($c['category_id'] == $active_cat) { $activeCatName = $c['name']; break; } }
                    echo esc($activeCatName);
                ?>
            </span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="m-dropdown-menu" id="dropdownMenu">
            <div class="m-dropdown-item <?= $active_cat=='' ? 'selected' : '' ?>" data-value="" data-label="All Categories">All Categories</div>
            <?php foreach($categories as $cat): ?>
                <div class="m-dropdown-item <?= $active_cat == $cat['category_id'] ? 'selected' : '' ?>" data-value="<?= $cat['category_id'] ?>" data-label="<?= esc($cat['name']) ?>"><?= esc($cat['name']) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</form>

<?php if($active_cat): ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted" style="font-size:10px;"><?= count($products) ?> product(s) found</span>
    <a href="<?= base_url('m/customer/browse') ?>" class="text-decoration-none" style="font-size:10px; color:#7b1113;">
        <i class="fas fa-times-circle me-1"></i>Clear Filter
    </a>
</div>
<?php endif; ?>

<?php if(empty($products)): ?>
    <div class="m-card text-center py-4"><p class="text-muted mb-0">No products found.</p></div>
<?php else: ?>
<div class="row g-2">
    <?php foreach($products as $p): ?>
    <div class="col-6">
        <a href="<?= base_url('m/customer/product/'.$p['product_id']) ?>" class="text-decoration-none">
            <div class="m-card p-2">
                <div style="height:80px; background:#f4f4f4; border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:6px;">
                    <?php if($p['image_path']): ?><img src="<?= base_url($p['image_path']) ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?><i class="fas fa-box-open text-muted"></i><?php endif; ?>
                </div>
                <p class="mb-1 fw-bold" style="font-size:10px; color:#333;"><?= esc($p['name']) ?></p>
                <p class="mb-0 fw-bold" style="font-size:11px; color:#7b1113;">₱<?= number_format($p['price'] ?? 0, 2) ?></p>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php if($total_pages > 1): ?>
<div class="d-flex justify-content-center gap-2 mt-4">
    <?php
        $q = ($active_cat ? '&cat='.$active_cat : '') . ($search ? '&search='.urlencode($search) : '');
        for ($i = 1; $i <= $total_pages; $i++):
    ?>
        <a href="?page=<?= $i.$q ?>" class="badge text-decoration-none px-3 py-2"
           style="font-size:10px; background:<?= $i==$current_page ? '#7b1113' : '#f1f1f1' ?>; color:<?= $i==$current_page ? '#fff' : '#333' ?>;">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggle = document.getElementById('dropdownToggle');
    const menu = document.getElementById('dropdownMenu');
    const label = document.getElementById('dropdownLabel');
    const catInput = document.getElementById('catInput');
    const form = document.getElementById('browseForm');

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.classList.toggle('open');
    });

    document.querySelectorAll('.m-dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            catInput.value = this.dataset.value;
            label.textContent = this.dataset.label;
            document.querySelectorAll('.m-dropdown-item').forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            menu.classList.remove('open');
            form.submit();
        });
    });

    document.addEventListener('click', function() {
        menu.classList.remove('open');
    });
});
</script>
<?= $this->endSection() ?>