document.addEventListener("DOMContentLoaded", function() {
            const viewButtons = document.querySelectorAll('.btn-view');
            const editButtons = document.querySelectorAll('.btn-edit');

            const productDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('productDrawer'));
            const drawerContent = document.getElementById('drawerContent');

            const adjustDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('adjustDrawer'));
            const editDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('editProductDrawer'));
            const editContent = document.getElementById('editProductContent');

            const addDrawerEl = document.getElementById('addProductDrawer');
            const addDrawer = bootstrap.Offcanvas.getOrCreateInstance(addDrawerEl);

            const addStockButtons = document.querySelectorAll('.btn-add-stock');
            const addStockDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('addStockDrawer'));
            const addStockContent = document.getElementById('addStockContent');

            const educationDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('educationDrawer'));
            const educationContent = document.getElementById('educationContent');

            function toGDrivePreview(url) {
                if (!url) return null;
                // Matches /d/FILE_ID/ pattern from any Google Drive share link
                const match = url.match(/\/d\/([a-zA-Z0-9_-]+)/);
                if (match && match[1]) {
                    return `https://drive.google.com/file/d/${match[1]}/preview`;
                }
                // Already a preview link or unrecognized format — use as-is
                return url;
            }

            function openEducationDrawer(productId) {
                educationContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                educationDrawer.show();

                fetch(`${BASE_URL}/admin/inventory/get-education/${productId}`)
                    .then(res => res.json())
                    .then(data => {
                            if (data.error) {
                                educationContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                                return;
                            }

                            const c = data.content || {};
                            const images = data.images || [];

                            let imageGallery = '';
                            if (images.length > 0) {
                                imageGallery = `
                <div class="d-flex gap-2 mb-4" style="overflow-x: auto;">
                    ${images.map(img => `<img src="${BASE_URL}/${img.image_path}" style="width: 90px; height: 90px; object-fit: cover; border-radius: 10px; flex-shrink: 0;" class="border">`).join('')}
                </div>`;
            }

            let videoEmbed = '';
            if (c.video_url) {
                const previewUrl = toGDrivePreview(c.video_url);
                videoEmbed = `
                <div class="mb-4">
                    <p class="info-label mb-2">Product Video</p>
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden border">
                        <iframe src="${previewUrl}" allow="autoplay" allowfullscreen></iframe>
                    </div>
                </div>`;
            }

            const sections = [
                ['Medical Description', c.medical_description],
                ['Usage Purpose', c.usage_purpose],
                ['Usage Guide', c.usage_guide],
                ['Warnings', c.warnings],
                ['Storage Information', c.storage_info],
                ['Healthcare Tips', c.healthcare_tips],
                ['Warranty Information', c.warranty_info],
            ];

            const sectionsHtml = sections.map(([label, value]) => `
                <div class="mb-3">
                    <p class="info-label mb-1">${label}</p>
                    <p class="text-dark mb-0" style="font-size: 12px; line-height: 1.6;">
                        ${value && value.trim() !== '' ? value.replace(/\n/g, '<br>') : '<span class="text-muted">Not yet provided.</span>'}
                    </p>
                </div>
            `).join('');

            educationContent.innerHTML = `
                <div class="text-center mb-4">
                    <h6 class="fw-bold mb-1">${data.name}</h6>
                    <p class="text-muted small mb-0">SKU: ${data.sku || '—'}</p>
                </div>
                ${imageGallery}
                ${videoEmbed}
                ${sectionsHtml}
                ${!c || Object.keys(c).length === 0 ? `
                    <div class="text-center p-4 bg-light rounded-3 text-muted mt-3">
                        No educational content has been added yet.<br>
                        Go to <strong>Edit Product Info</strong> to add it.
                    </div>` : ''}
            `;
        })
        .catch(err => {
            educationContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load educational content.</div>`;
            console.error(err);
        });
}

            function val(v, fallback = '—') {
                return (v === null || v === undefined || v === '') ? fallback : v;
            }

            // ============ SEARCH (server-side, searches ALL products) ============
            const searchInput = document.getElementById('inventorySearch');
            if (searchInput) {
                let debounceTimer;
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        const params = new URLSearchParams(window.location.search);
                        params.set('page', 1);
                        const term = searchInput.value.trim();
                        if (term !== '') {
                            params.set('search', term);
                        } else {
                            params.delete('search');
                        }
                        window.location.href = window.location.pathname + '?' + params.toString();
                    }, 500);
                });
            }

            // ============ ADD STOCK ============
            addStockButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const pid = this.getAttribute('data-pid');
                    addStockContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                    addStockDrawer.show();

                    fetch(`${BASE_URL}/admin/inventory/get-stock-context/${pid}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) {
                                addStockContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                                return;
                            }

                            const lb = data.last_batch;
                            const referenceCard = lb ? `
                    <div class="p-3 bg-light rounded-3 mb-4">
                        <p class="info-label mb-2">Last Batch on Record (reference only)</p>
                        <div class="row g-2" style="font-size: 11px;">
                            <div class="col-6"><strong>Batch No:</strong> ${lb.batch_number}</div>
                            <div class="col-6"><strong>Qty Received:</strong> ${lb.quantity_in}</div>
                            <div class="col-6"><strong>Cost Price:</strong> ₱${lb.cost_price ?? '0.00'}</div>
                            <div class="col-6"><strong>Sell Price:</strong> ₱${lb.sell_price}</div>
                            <div class="col-6"><strong>Reorder Level:</strong> ${lb.reorder_level}</div>
                            <div class="col-6"><strong>Expires:</strong> ${lb.expires_at ?? '—'}</div>
                        </div>
                    </div>
                ` : `
                    <div class="p-3 bg-light rounded-3 mb-4 text-muted" style="font-size: 11px;">
                        No previous batch on record for this product yet — this will be the first.
                    </div>
                `;

                            addStockContent.innerHTML = `
                <div class="p-3 mb-3 border-bottom">
                    <p class="info-label mb-1">Adding stock for</p>
                    <h6 class="fw-bold mb-0">${data.name}</h6>
                    <span class="text-muted" style="font-size: 11px;">${data.cat_name} • SKU: ${data.sku ?? '—'} • Barcode: ${data.barcode_value ?? '—'}</span>
                </div>

                ${referenceCard}

                <form action="${BASE_URL}/admin/inventory/create-batch" method="POST">
                    <input type="hidden" name="product_id" value="${data.product_id}">

                    <div class="mb-3">
                        <label class="formal-label">Batch Number *</label>
                        <input type="text" name="batch_number" class="formal-input" placeholder="e.g. B2026-05" required>
                    </div>
                    <div class="mb-3">
                        <label class="formal-label">Lot Number</label>
                        <input type="text" name="lot_number" class="formal-input" placeholder="e.g. LOT-0472">
                    </div>
                    <div class="mb-3">
                        <label class="formal-label">Quantity Received *</label>
                        <input type="number" name="quantity" class="formal-input" placeholder="e.g. 50" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="formal-label">Reorder Level</label>
                        <input type="number" name="reorder_level" class="formal-input" placeholder="e.g. 10" value="${lb ? lb.reorder_level : 5}">
                    </div>
                    <div class="mb-3">
                        <label class="formal-label">Cost Price (per unit)</label>
                        <input type="number" step="0.01" name="cost_price" class="formal-input" placeholder="e.g. 600.00" value="${lb ? lb.cost_price : ''}">
                    </div>
                    <div class="mb-3">
                        <label class="formal-label">Sell Price (per unit) *</label>
                        <input type="number" step="0.01" name="sell_price" class="formal-input" placeholder="e.g. 850.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="formal-label">Manufactured Date</label>
                        <input type="date" name="manufactured_at" class="formal-input">
                    </div>
                    <div class="mb-3">
                        <label class="formal-label">Expiry Date</label>
                        <input type="date" name="expires_at" class="formal-input">
                    </div>

                    <button type="submit" class="btn btn-save-adj">✓ Save Batch</button>
                    <button type="button" class="btn btn-cancel-adj" data-bs-dismiss="offcanvas">Cancel</button>
                </form>`;
                        })
                        .catch(err => {
                            addStockContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load product context.</div>`;
                            console.error(err);
                        });
                });
            });

            // ============ VIEW PRODUCT ============
            viewButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const headerBg = data.image_path ?
                        `background-image: linear-gradient(rgba(26,5,5,0.55), rgba(26,5,5,0.75)), url('${BASE_URL}/${data.image_path}'); background-size: cover; background-position: center;` :
                        `background: #1a0505;`;
                    productDrawer.show();
                    drawerContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

                    fetch(`${BASE_URL}/admin/inventory/get-details/${id}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) {
                                drawerContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                                return;
                            }

                            drawerContent.innerHTML = `
                    <div class="p-4 rounded-4 text-center mb-4" style="${headerBg} color: #fff;">
    <h5 class="fw-bold mb-1">${val(data.name)}</h5>
    <p class="mb-1 opacity-75" style="font-size: 11px;">SKU: ${val(data.sku)}</p>
    <span class="badge bg-light text-dark">${val(data.cat_name)}</span>
</div>
                    <div class="row g-3 mb-3">
    <div class="col-6"><p class="info-label mb-0">Barcode</p><p class="info-value">${val(data.barcode_value)}</p></div>
    <div class="col-6"><p class="info-label mb-0">Brand</p><p class="info-value">${val(data.brand)}</p></div>
    <div class="col-6"><p class="info-label mb-0">Manufacturer</p><p class="info-value">${val(data.manufacturer)}</p></div>
    <div class="col-6"><p class="info-label mb-0">Supplier</p><p class="info-value">${val(data.supplier_name)}</p></div>
    <div class="col-6"><p class="info-label mb-0">VAT Exempt</p><p class="info-value">${data.is_vat_exempt == 1 ? 'Yes' : 'No'}</p></div>
    <div class="col-6"><p class="info-label mb-0">Batch No.</p><p class="info-value text-primary">${val(data.batch_number)}</p></div>
    <div class="col-6"><p class="info-label mb-0">Stock Available</p><p class="info-value text-maroon fs-6">${val(data.quantity_avail, 0)} ${val(data.unit, '')}</p></div>
    <div class="col-6"><p class="info-label mb-0">Reorder Level</p><p class="info-value">${val(data.reorder_level, 0)}</p></div>
    <div class="col-6"><p class="info-label mb-0">Sell Price</p><p class="info-value">₱${val(data.sell_price, '0.00')}</p></div>
    <div class="col-6"><p class="info-label mb-0">Expires</p><p class="info-value">${val(data.expires_at)}</p></div>
</div>
                    <div class="mb-3">
    <p class="info-label mb-0">Description</p>
    <p class="info-value fw-normal">${val(data.description, 'No description yet.')}</p>
</div>
<div class="mb-4">
    <p class="info-label mb-0">Notes</p>
    <p class="info-value fw-normal">${val(data.notes, 'No notes.')}</p>
</div>

<div class="d-grid gap-2">
    <button type="button" id="btnGoToAdjust" class="btn btn-warning py-3 fw-bold rounded-3 shadow-sm">
        <i class="fas fa-adjust me-2"></i>ADJUST STOCK LEVELS
    </button>
    <button type="button" id="btnGoToEdit" class="btn btn-outline-dark py-2">EDIT PRODUCT INFO</button>
    <button type="button" id="btnGoToEducation" class="btn btn-outline-primary py-2">
        <i class="fas fa-book-medical me-2"></i>VIEW EDUCATIONAL CONTENT
    </button>
</div>`;

                            document.getElementById('btnGoToAdjust').addEventListener('click', function() {
                                productDrawer.hide();
                                openAdjustmentForm(data);
                            });
                            document.getElementById('btnGoToEdit').addEventListener('click', function() {
                                productDrawer.hide();
                                openEditForm(data);
                            });
                            document.getElementById('btnGoToEducation').addEventListener('click', function() {
                                productDrawer.hide();
                                openEducationDrawer(data.product_id);
                            });
                        })
                        .catch(err => {
                            drawerContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load product details.</div>`;
                            console.error(err);
                        });
                });
            });

            // ============ EDIT (table row button) ============
            editButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    fetch(`${BASE_URL}/admin/inventory/get-product/${id}`)
                        .then(res => res.json())
                        .then(data => openEditForm(data))
                        .catch(err => console.error(err));
                });
            });

            // ============ ADJUSTMENT FORM ============
            function openAdjustmentForm(data) {
                const content = document.getElementById('adjustDrawerContent');
                content.innerHTML = `
        <div class="mb-4">
            <h6 class="fw-bold mb-3" style="color: #b30000; font-size: 12px; letter-spacing: 0.5px;">
                <i class="fas fa-pencil-alt me-2" style="color:#333"></i>ADD STOCK ADJUSTMENT
            </h6>
        </div>
        <form action="${BASE_URL}/admin/inventory/adjust-stock" method="POST">
            <input type="hidden" name="batch_id" value="${data.batch_id}">
            <input type="hidden" name="product_id" value="${data.product_id}">
            <input type="hidden" name="qty_before" value="${data.quantity_avail}">
            <div class="row g-3">
                <div class="col-6">
                    <label class="formal-label">Product *</label>
                    <input type="text" class="formal-input read-only-input" value="${data.name}" readonly>
                </div>
                <div class="col-6">
                    <label class="formal-label">Batch Number *</label>
                    <input type="text" class="formal-input read-only-input" value="${data.batch_number}" readonly>
                </div>
                <div class="col-6">
                    <label class="formal-label">Current Quantity (auto-filled)</label>
                    <input type="text" class="formal-input read-only-input" value="${data.quantity_avail}" readonly>
                    <p class="helper-text">Read-only — fetched from inventory</p>
                </div>
                <div class="col-6">
                    <label class="formal-label">New Quantity After Adjustment *</label>
                    <input type="number" name="qty_after" class="formal-input" placeholder="Enter correct quantity" required>
                </div>
                <div class="col-6">
                    <label class="formal-label">Reason for Adjustment *</label>
                    <select name="reason" class="form-select formal-input" style="font-size: 11px;" required>
                        <option value="" selected disabled>Select reason</option>
                        <option value="Physical Count">Physical Inventory Count</option>
                        <option value="Damage">Damaged Goods</option>
                        <option value="Expired">Expired Stock</option>
                        <option value="Loss">Loss / Theft</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="formal-label">Adjusted By (auto-filled)</label>
                    <input type="text" class="formal-input read-only-input" value="Administrator" readonly>
                </div>
                <div class="col-12">
                    <label class="formal-label">Notes / Additional Details</label>
                    <textarea name="notes" class="formal-input" rows="4" placeholder="Describe the reason in more detail" required></textarea>
                </div>
            </div>
            <div class="mt-4 pt-3">
                <button type="submit" class="btn btn-save-adj">✓ Save Adjustment</button>
                <button type="button" class="btn btn-cancel-adj" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>`;
                adjustDrawer.show();
            }

            // ============ EDIT PRODUCT INFO FORM ============
            function openEditForm(data) {
                const supplierOptions = (data.all_suppliers || []).map(s =>
                    `<option value="${s.supplier_id}" ${s.supplier_id == data.supplier_id ? 'selected' : ''}>${s.name}</option>`
                ).join('');
                const categoryOptions = (data.all_categories || []).map(c =>
                    `<option value="${c.category_id}" ${c.category_id == data.category_id ? 'selected' : ''}>${c.name}</option>`
                ).join('');

                const c = data.content || {};

                editContent.innerHTML = `
    <form action="${BASE_URL}/admin/inventory/update-info" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="${data.product_id}">

        <div class="mb-3">
            <label class="formal-label">Product Image</label>
            ${data.image_path ? `<img src="${BASE_URL}/${data.image_path}" class="w-100 rounded-3 mb-2" style="height: 140px; object-fit: cover;">` : `<p class="helper-text mb-2">No image uploaded yet.</p>`}
            <input type="file" name="product_image" class="formal-input" accept="image/*">
        </div>

        <div class="mb-3"><label class="formal-label">Product Name *</label>
            <input type="text" name="name" class="formal-input" value="${data.name ?? ''}" required></div>
        <div class="mb-3"><label class="formal-label">Category *</label>
            <select name="category_id" class="form-select formal-input" required>${categoryOptions}</select></div>
        <div class="mb-3"><label class="formal-label">Supplier</label>
            <select name="supplier_id" class="form-select formal-input"><option value="">— None —</option>${supplierOptions}</select></div>
        <div class="mb-3"><label class="formal-label">SKU</label>
            <input type="text" name="sku" class="formal-input" value="${data.sku ?? ''}"></div>
        <div class="mb-3"><label class="formal-label">Barcode</label>
            <input type="text" name="barcode" class="formal-input" value="${data.barcode_value ?? ''}"></div>
        <div class="mb-3"><label class="formal-label">Brand</label>
            <input type="text" name="brand" class="formal-input" value="${data.brand ?? ''}"></div>
        <div class="mb-3"><label class="formal-label">Manufacturer</label>
            <input type="text" name="manufacturer" class="formal-input" value="${data.manufacturer ?? ''}"></div>
        <div class="mb-3"><label class="formal-label">Unit</label>
            <input type="text" name="unit" class="formal-input" value="${data.unit ?? 'piece'}"></div>
        <div class="mb-3"><label class="formal-label">Description</label>
            <textarea name="description" class="formal-input" rows="3">${data.description ?? ''}</textarea></div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_vat_exempt" class="form-check-input" id="vatExempt" ${data.is_vat_exempt == 1 ? 'checked' : ''}>
            <label class="form-check-label formal-label mb-0" for="vatExempt">VAT Exempt</label>
        </div>
        <div class="mb-3"><label class="formal-label">Notes</label>
            <textarea name="notes" class="formal-input" rows="2">${data.notes ?? ''}</textarea></div>

        <hr class="my-4">
        <h6 class="fw-bold text-maroon mb-3" style="font-size: 12px;"><i class="fas fa-book-medical me-2"></i>Educational Content (iScan / Customer View)</h6>

        <div class="mb-3"><label class="formal-label">Video URL (YouTube embed link)</label>
            <input type="text" name="video_url" class="formal-input" placeholder="e.g. https://www.youtube.com/embed/xxxxx" value=""></div>
        <div class="mb-3"><label class="formal-label">Medical Description</label>
            <textarea name="medical_description" class="formal-input" rows="2">${c.medical_description ?? ''}</textarea></div>
        <div class="mb-3"><label class="formal-label">Usage Purpose</label>
            <textarea name="usage_purpose" class="formal-input" rows="2">${c.usage_purpose ?? ''}</textarea></div>
        <div class="mb-3"><label class="formal-label">Usage Guide</label>
            <textarea name="usage_guide" class="formal-input" rows="2">${c.usage_guide ?? ''}</textarea></div>
        <div class="mb-3"><label class="formal-label">Warnings</label>
            <textarea name="warnings" class="formal-input" rows="2">${c.warnings ?? ''}</textarea></div>
        <div class="mb-3"><label class="formal-label">Storage Information</label>
            <textarea name="storage_info" class="formal-input" rows="2">${c.storage_info ?? ''}</textarea></div>
        <div class="mb-3"><label class="formal-label">Healthcare Tips</label>
            <textarea name="healthcare_tips" class="formal-input" rows="2">${c.healthcare_tips ?? ''}</textarea></div>
        <div class="mb-3"><label class="formal-label">Warranty Information</label>
            <input type="text" name="warranty_info" class="formal-input" value="${c.warranty_info ?? ''}"></div>

        <button type="submit" class="btn btn-save-adj">✓ Save Changes</button>
        <button type="button" class="btn btn-cancel-adj" data-bs-dismiss="offcanvas">Cancel</button>
    </form>`;
    editDrawer.show();
}
});