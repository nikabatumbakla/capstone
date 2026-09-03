document.addEventListener("DOMContentLoaded", function() {
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('productDrawer'));
            const content = document.getElementById('productDrawerContent');

            function peso(n) {
                return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
            }

            function safe(val, fallback = '') {
                return (val === null || val === undefined || val === '') ? fallback : val;
            }

            function toGDrivePreview(url) {
                if (!url) return null;
                const match = url.match(/\/d\/([a-zA-Z0-9_-]+)/) || url.match(/id=([a-zA-Z0-9_-]+)/);
                if (!match) return null;
                return `https://drive.google.com/file/d/${match[1]}/preview`;
            }

            document.querySelectorAll('.btn-view-product').forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const id = this.getAttribute('data-id');
                                    content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                                    drawer.show();

                                    fetch(`${BASE_URL}/client/orders/get-product-details/${id}`)
                                        .then(res => res.json())
                                        .then(data => {
                                                if (!data || data.error) {
                                                    content.innerHTML = `<div class="text-danger text-center p-5">${data && data.error ? data.error : 'Failed to load product.'}</div>`;
                                                    return;
                                                }

                                                const c = data.content || {};
                                                const embedUrl = toGDrivePreview(c.video_url);
                                                const brandLine = [safe(data.brand), safe(data.manufacturer)].filter(Boolean).join(' · ');

                                                const stock = parseInt(data.total_stock || 0);
                                                const stockBadge = stock <= 0 ?
                                                    `<span class="badge bg-danger">Out of Stock</span>` :
                                                    `<span class="badge bg-success">Available</span>`;

                                                content.innerHTML = `
                    <div style="height:220px; background:#f4f4f4; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        ${data.image_path
                            ? `<img src="${BASE_URL}/${data.image_path}" style="width:100%; height:100%; object-fit:cover;">`
                            : `<i class="fas fa-box-open" style="font-size:48px; color:#ccc;"></i>`}
                    </div>

                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-dark">${safe(data.category_name, 'Uncategorized')}</span>
                            ${stockBadge}
                        </div>
                        <h5 class="fw-bold mb-1">${safe(data.name, 'Unnamed Product')}</h5>
                        ${brandLine ? `<p class="text-muted mb-2" style="font-size:10.5px;">${brandLine}</p>` : ''}
                        <p class="text-muted mb-3" style="font-size:10.5px;">
                            SKU: ${safe(data.sku, '—')} &nbsp;|&nbsp; ${data.is_vat_exempt == 1 ? 'VAT-Exempt' : 'VAT-Inclusive'}
                        </p>
                        <h4 class="fw-bold text-maroon mb-4">${peso(data.sell_price)} <small class="text-muted fw-normal" style="font-size:11px;">per ${safe(data.unit, 'unit')}</small></h4>

                        ${embedUrl ? `
                            <p class="fw-bold mb-2" style="font-size:12px;"><i class="fas fa-play-circle me-2 text-primary"></i>Product Guide Video</p>
                            <div class="ratio ratio-16x9 mb-4">
                                <iframe src="${embedUrl}" allow="autoplay" allowfullscreen style="border-radius:10px; border:1px solid #ddd;"></iframe>
                            </div>` : ''}

                        ${c.medical_description ? `
                            <div class="mb-3">
                                <p class="fw-bold mb-1" style="font-size:12px;"><i class="fas fa-notes-medical me-2 text-primary"></i>Medical Description</p>
                                <p class="mb-0" style="font-size:11.5px; line-height:1.6; color:#333;">${c.medical_description}</p>
                            </div>` : ''}

                        ${c.usage_purpose ? `
                            <div class="mb-3">
                                <p class="fw-bold mb-1" style="font-size:12px;">Usage Purpose</p>
                                <p class="mb-0" style="font-size:11.5px; line-height:1.6; color:#333;">${c.usage_purpose}</p>
                            </div>` : ''}

                        ${c.usage_guide ? `
                            <div class="p-3 rounded-4 mb-3" style="background:#eef6ff; border:1px solid #d3e8ff;">
                                <p class="fw-bold mb-2" style="font-size:12px; color:#0d2e4f;"><i class="fas fa-graduation-cap me-2"></i>How to Use</p>
                                <p class="mb-0" style="font-size:11.5px; line-height:1.7; color:#333;">${c.usage_guide}</p>
                            </div>` : ''}

                        ${c.warnings ? `
                            <div class="p-3 rounded-4 mb-3" style="background:#fff4f4; border:1px solid #ffd6d6;">
                                <p class="fw-bold mb-2" style="font-size:12px; color:#8a1c1c;"><i class="fas fa-exclamation-triangle me-2"></i>Warnings</p>
                                <p class="mb-0" style="font-size:11.5px; line-height:1.7; color:#333;">${c.warnings}</p>
                            </div>` : ''}

                        ${c.storage_info ? `
                            <div class="mb-3">
                                <p class="fw-bold mb-1" style="font-size:12px;">Storage Information</p>
                                <p class="mb-0" style="font-size:11.5px; line-height:1.6; color:#333;">${c.storage_info}</p>
                            </div>` : ''}

                        ${c.healthcare_tips ? `
                            <div class="mb-3">
                                <p class="fw-bold mb-1" style="font-size:12px;">Healthcare Tips</p>
                                <p class="mb-0" style="font-size:11.5px; line-height:1.6; color:#333;">${c.healthcare_tips}</p>
                            </div>` : ''}

                        ${c.warranty_info ? `
                            <div class="mb-3">
                                <p class="fw-bold mb-1" style="font-size:12px;">Warranty</p>
                                <p class="mb-0" style="font-size:11.5px; line-height:1.6; color:#333;">${c.warranty_info}</p>
                            </div>` : ''}

                        ${data.description ? `
                            <p class="fw-bold mb-2" style="font-size:12px;">Description</p>
                            <p class="mb-4" style="font-size:11.5px; line-height:1.6; color:#555;">${data.description}</p>` : ''}

                        <form action="${BASE_URL}/client/orders/add-to-cart" method="POST" class="d-flex gap-2 pt-3 border-top">
                            <input type="hidden" name="product_id" value="${safe(data.product_id, id)}">
                            <input type="number" name="qty" value="1" min="1" max="${stock}" class="form-control form-control-sm" style="width:80px;" ${stock <= 0 ? 'disabled' : ''}>
                            <button type="submit" class="btn btn-dark rounded-pill flex-grow-1 fw-bold" ${stock <= 0 ? 'disabled' : ''}>
                                <i class="fas fa-cart-plus me-2"></i>${stock <= 0 ? 'Out of Stock' : 'Add to Cart'}
                            </button>
                        </form>
                    </div>`;
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-danger text-center p-5">Failed to load product details.</div>`;
                    console.error(err);
                });
        });
    });
});