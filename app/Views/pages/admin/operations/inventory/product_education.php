<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Product Education Page</h5>
            </div>

            <div class="row g-4">
                <!-- LEFT: Images + basic info -->
                <div class="col-md-4">
                    <div class="custom-table-container p-4">
                        <?php if (!empty($images)): ?>
                            <div id="productCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                                <div class="carousel-inner rounded-3 border">
                                    <?php foreach($images as $i => $img): ?>
                                        <div class="carousel-item <?= $i == 0 ? 'active' : '' ?>">
                                            <img src="<?= base_url($img['image_path']) ?>" class="d-block w-100" style="height: 260px; object-fit: cover;">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($images) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-5 bg-light rounded-3 mb-3 text-muted">
                                <i class="fas fa-image fs-1 opacity-25 mb-2"></i>
                                <p class="mb-0" style="font-size: 12px;">No images uploaded yet</p>
                            </div>
                        <?php endif; ?>

                        <h5 class="fw-bold mb-1"><?= esc($product->name) ?></h5>
                        <span class="badge bg-dark mb-3"><?= esc($product->cat_name) ?></span>

                        <div class="row g-3 mt-2">
                            <div class="col-6"><p class="info-label mb-0">SKU</p><p class="info-value"><?= $product->sku ?? '—' ?></p></div>
                            <div class="col-6"><p class="info-label mb-0">Barcode</p><p class="info-value"><?= $product->barcode_value ?? '—' ?></p></div>
                            <div class="col-6"><p class="info-label mb-0">Brand</p><p class="info-value"><?= $product->brand ?? '—' ?></p></div>
                            <div class="col-6"><p class="info-label mb-0">Manufacturer</p><p class="info-value"><?= $product->manufacturer ?? '—' ?></p></div>
                            <div class="col-6"><p class="info-label mb-0">Supplier</p><p class="info-value"><?= $product->supplier_name ?? '—' ?></p></div>
                            <div class="col-6"><p class="info-label mb-0">Unit</p><p class="info-value"><?= $product->unit ?></p></div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Educational content -->
                <div class="col-md-8">
                    <div class="custom-table-container p-4">
                        <?php if ($content && $content->video_url): ?>
                        <div class="mb-4">
                            <p class="info-label mb-2">Product Video</p>
                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden border">
                                <iframe src="<?= esc($content->video_url, 'attr') ?>" allowfullscreen></iframe>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php
                        $sections = [
                            'Medical Description' => $content->medical_description ?? null,
                            'Usage Purpose'        => $content->usage_purpose ?? null,
                            'Usage Guide'           => $content->usage_guide ?? null,
                            'Warnings'              => $content->warnings ?? null,
                            'Storage Information'   => $content->storage_info ?? null,
                            'Healthcare Tips'       => $content->healthcare_tips ?? null,
                            'Warranty Information'  => $content->warranty_info ?? null,
                        ];
                        ?>
                        <?php foreach ($sections as $label => $value): ?>
                            <div class="mb-4">
                                <p class="info-label mb-1"><?= $label ?></p>
                                <p class="text-dark mb-0" style="font-size: 13px; line-height: 1.6;">
                                    <?= !empty($value) ? nl2br(esc($value)) : '<span class="text-muted">Not yet provided.</span>' ?>
                                </p>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!$content): ?>
                            <div class="text-center p-4 bg-light rounded-3 text-muted">
                                No educational content has been added for this product yet.
                                <br>Go to <strong>Edit Product Info</strong> to add descriptions, usage guides, and a video.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('partials/admin/footer') ?>