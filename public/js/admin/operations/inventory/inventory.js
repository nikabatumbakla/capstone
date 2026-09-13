document.addEventListener("DOMContentLoaded", function() {
            const productDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('productDrawer'));
            const drawerContent = document.getElementById('drawerContent');
            const adjustDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('adjustDrawer'));
            const editDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('editProductDrawer'));
            const editContent = document.getElementById('editProductContent');
            const addStockDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('addStockDrawer'));
            const addStockContent = document.getElementById('addStockContent');
            const educationDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('educationDrawer'));
            const educationContent = document.getElementById('educationContent');

            const categorySelect = document.getElementById('categorySelect');
            const newCategoryWrap = document.getElementById('newCategoryWrap');
            const newCategoryInput = document.getElementById('newCategoryName');
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    if (this.value === '__new__') {
                        newCategoryWrap.style.display = 'block';
                        newCategoryInput.setAttribute('required', 'required');
                    } else {
                        newCategoryWrap.style.display = 'none';
                        newCategoryInput.removeAttribute('required');
                    }
                });
            }

            // ============ TAB NAVIGATION (Add Product drawer) ============
            document.querySelectorAll('.btn-next-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetSelector = this.dataset.next;
                    const targetPane = document.querySelector(targetSelector);
                    const targetPill = document.querySelector(`[data-bs-target="${targetSelector}"]`);
                    if (targetPane && targetPill) {
                        bootstrap.Tab.getOrCreateInstance(targetPill).show();
                    }
                });
            });
            document.querySelectorAll('.btn-prev-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetSelector = this.dataset.prev;
                    const targetPill = document.querySelector(`[data-bs-target="${targetSelector}"]`);
                    if (targetPill) {
                        bootstrap.Tab.getOrCreateInstance(targetPill).show();
                    }
                });
            });

            function toGDrivePreview(url) {
                if (!url) return null;
                const match = url.match(/\/d\/([a-zA-Z0-9_-]+)/);
                if (match && match[1]) return `https://drive.google.com/file/d/${match[1]}/preview`;
                return url;
            }

            function val(v, fallback = '—') {
                return (v === null || v === undefined || v === '') ? fallback : v;
            }

            function openEducationDrawer(productId) {
                educationContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                educationDrawer.show();

                fetch(`${BASE_URL}/admin/inventory/get-education/${productId}`)
                    .then(res => res.json())
                    .then(data => {
                            if (data.error) { educationContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                            const c = data.content || {};
                            const images = data.images || [];

                            let imageGallery = '';
                            if (images.length > 0) {
                                imageGallery = `<div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">${images.map(img => `<img src="${BASE_URL}/${img.image_path}" style="width: 110px; height: 110px; object-fit: cover; border-radius: 10px;" class="border">`).join('')}</div>`;
            }

            let videoEmbed = '';
            if (c.video_url) {
                const previewUrl = toGDrivePreview(c.video_url);
                videoEmbed = `<div class="mb-4"><p class="info-label mb-2">Product Video</p><div class="ratio ratio-16x9 rounded-3 overflow-hidden border"><iframe src="${previewUrl}" allow="autoplay" allowfullscreen></iframe></div></div>`;
            }

            const sections = [
                ['Medical Description', c.medical_description], ['Usage Purpose', c.usage_purpose],
                ['Usage Guide', c.usage_guide], ['Warnings', c.warnings],
                ['Storage Information', c.storage_info], ['Healthcare Tips', c.healthcare_tips],
                ['Warranty Information', c.warranty_info],
            ];
            const sectionsHtml = sections.map(([label, value]) => `
                <div class="mb-3"><p class="info-label mb-1">${label}</p>
                <p class="text-dark mb-0" style="font-size: 12px; line-height: 1.6;">${value && value.trim() !== '' ? value.replace(/\n/g, '<br>') : '<span class="text-muted">Not yet provided.</span>'}</p></div>
            `).join('');

            educationContent.innerHTML = `
                <div class="text-center mb-4"><h6 class="fw-bold mb-1">${data.name}</h6><p class="text-muted small mb-0">Barcode: ${data.barcode_value || '—'}</p></div>
                ${imageGallery}${videoEmbed}${sectionsHtml}
                ${!c || Object.keys(c).length === 0 ? `<div class="text-center p-4 bg-light rounded-3 text-muted mt-3">No educational content has been added yet.<br>Go to <strong>Edit Product Info</strong> to add it.</div>` : ''}
            `;
        })
        .catch(err => {
            educationContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load educational content.</div>`;
            console.error(err);
        });
}

    // ============ SEARCH (server-side) ============
    const searchInput = document.getElementById('inventorySearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const params = new URLSearchParams(window.location.search);
                params.set('page', 1);
                const term = searchInput.value.trim();
                if (term !== '') params.set('search', term); else params.delete('search');
                window.location.href = window.location.pathname + '?' + params.toString();
            }, 500);
        });
    }

    // ============ ADD STOCK ============
    function handleAddStockClick() {
        const pid = this.getAttribute('data-pid');
        addStockContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
        addStockDrawer.show();

        fetch(`${BASE_URL}/admin/inventory/get-stock-context/${pid}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) { addStockContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

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
                    </div>` : `<div class="p-3 bg-light rounded-3 mb-4 text-muted" style="font-size: 11px;">No previous batch on record for this product yet — this will be the first.</div>`;

                addStockContent.innerHTML = `
                    <div class="p-3 mb-3 border-bottom">
                        <p class="info-label mb-1">Adding stock for</p>
                        <h6 class="fw-bold mb-0">${data.name}</h6>
                        <span class="text-muted" style="font-size: 11px;">${data.cat_name} • Barcode: ${data.barcode_value ?? '—'}</span>
                    </div>
                    ${referenceCard}
                    <form action="${BASE_URL}/admin/inventory/create-batch" method="POST">
                        <input type="hidden" name="product_id" value="${data.product_id}">
                        <div class="mb-3"><label class="formal-label">Batch Number *</label><input type="text" name="batch_number" class="formal-input" placeholder="e.g. B2026-05" required></div>
                        <div class="mb-3"><label class="formal-label">Lot Number</label><input type="text" name="lot_number" class="formal-input" placeholder="e.g. LOT-0472"></div>
                        <div class="mb-3"><label class="formal-label">Quantity Received *</label><input type="number" name="quantity" class="formal-input" placeholder="e.g. 50" min="1" required></div>
                        <div class="mb-3"><label class="formal-label">Reorder Level</label><input type="number" name="reorder_level" class="formal-input" placeholder="e.g. 10" value="${lb ? lb.reorder_level : 5}"></div>
                        <div class="mb-3"><label class="formal-label">Cost Price (per unit)</label><input type="number" step="0.01" name="cost_price" class="formal-input" placeholder="e.g. 600.00" value="${lb ? lb.cost_price : ''}"></div>
                        <div class="mb-3"><label class="formal-label">Sell Price (per unit) *</label><input type="number" step="0.01" name="sell_price" class="formal-input" placeholder="e.g. 850.00" required></div>
                        <div class="mb-3"><label class="formal-label">Manufactured Date</label><input type="date" name="manufactured_at" class="formal-input"></div>
                        <div class="mb-3"><label class="formal-label">Expiry Date</label><input type="date" name="expires_at" class="formal-input"></div>
                        <button type="submit" class="btn btn-save-adj">✓ Save Batch</button>
                        <button type="button" class="btn btn-cancel-adj" data-bs-dismiss="offcanvas">Cancel</button>
                    </form>`;
            })
            .catch(err => {
                addStockContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load product context.</div>`;
                console.error(err);
            });
    }

    // ============ VIEW PRODUCT ============
    function handleViewClick() {
    const productId = this.getAttribute('data-id');
    productDrawer.show();
    drawerContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

    fetch(`${BASE_URL}/admin/inventory/get-product-batches/${productId}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) { drawerContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

            const totalStock = data.batches.reduce((sum, b) => sum + parseInt(b.quantity_avail), 0);

            const batchRowsHtml = data.batches.length ? data.batches.map(b => `
                <tr>
                    <td class="fw-bold">${b.batch_number}</td>
                    <td>${b.lot_number || '—'}</td>
                    <td>${b.expires_at || '—'}</td>
                    <td class="text-center">${b.quantity_avail}</td>
                    <td class="text-end">₱${parseFloat(b.sell_price).toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2 btn-adjust-batch"
                            data-batch='${JSON.stringify(b).replace(/'/g, "&apos;")}' data-product-id="${productId}" data-product-name="${data.name}">
                            <i class="fas fa-adjust"></i>
                        </button>
                    </td>
                </tr>
            `).join('') : `<tr><td colspan="6" class="text-center text-muted py-3">No batches recorded yet.</td></tr>`;

            drawerContent.innerHTML = `
                <div class="mb-3">
                    <div style="height:180px; background:#f4f4f4; border-radius:16px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                        ${data.image_path
                            ? `<img src="${BASE_URL}/${data.image_path}" style="width:100%; height:100%; object-fit:cover;">`
                            : `<i class="fas fa-box-open" style="font-size:48px; color:#ccc;"></i>`}
                    </div>
                </div>
                <div class="text-center mb-3">
                    <h5 class="fw-bold mb-1">${data.name}</h5>
                    <p class="text-muted mb-2" style="font-size: 11px;">Barcode: ${data.barcode_value || '—'}</p>
                    <span class="badge bg-light text-dark border">${data.cat_name}</span>
                </div>
                <div class="row g-3 mb-4 text-center">
                    <div class="col-6"><p class="info-label mb-0">Total Stock</p><p class="info-value text-maroon fs-5">${totalStock} ${data.unit || ''}</p></div>
                    <div class="col-6"><p class="info-label mb-0">Total Batches</p><p class="info-value fs-5">${data.batches.length}</p></div>
                </div>
                <p class="fw-bold small mb-2">Batch Breakdown</p>
                <div class="table-responsive">
                    <table class="table table-sm" style="font-size:10px">
                        <thead class="table-dark"><tr><th>Batch #</th><th>Lot #</th><th>Expiry</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-center">Adjust</th></tr></thead>
                        <tbody>${batchRowsHtml}</tbody>
                    </table>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="button" id="btnGoToEducation" class="btn btn-outline-primary py-2"><i class="fas fa-book-medical me-2"></i>VIEW EDUCATIONAL CONTENT</button>
                </div>`;

            document.getElementById('btnGoToEducation').addEventListener('click', function() {
                productDrawer.hide();
                openEducationDrawer(productId);
            });

            document.querySelectorAll('.btn-adjust-batch').forEach(btn => {
                btn.addEventListener('click', function() {
                    const batch = JSON.parse(this.getAttribute('data-batch').replace(/&apos;/g, "'"));
                    productDrawer.hide();
                    openAdjustmentForm({
                        batch_id: batch.batch_id,
                        product_id: this.getAttribute('data-product-id'),
                        name: this.getAttribute('data-product-name'),
                        batch_number: batch.batch_number,
                        quantity_avail: batch.quantity_avail,
                    });
                });
            });
        })
        .catch(err => {
            drawerContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load product details.</div>`;
            console.error(err);
        });
}

    // ============ EDIT ============
    function handleEditClick() {
        const id = this.getAttribute('data-id');
        fetch(`${BASE_URL}/admin/inventory/get-product/${id}`)
            .then(res => res.json())
            .then(data => openEditForm(data))
            .catch(err => console.error(err));
    }

    function openAdjustmentForm(data) {
        const content = document.getElementById('adjustDrawerContent');
        content.innerHTML = `
            <div class="mb-4"><h6 class="fw-bold mb-3" style="color: #b30000; font-size: 12px; letter-spacing: 0.5px;"><i class="fas fa-pencil-alt me-2" style="color:#333"></i>ADD STOCK ADJUSTMENT</h6></div>
            <form action="${BASE_URL}/admin/inventory/adjust-stock" method="POST">
                <input type="hidden" name="batch_id" value="${data.batch_id}">
                <input type="hidden" name="product_id" value="${data.product_id}">
                <input type="hidden" name="qty_before" value="${data.quantity_avail}">
                <div class="row g-3">
                    <div class="col-6"><label class="formal-label">Product *</label><input type="text" class="formal-input read-only-input" value="${data.name}" readonly></div>
                    <div class="col-6"><label class="formal-label">Batch Number *</label><input type="text" class="formal-input read-only-input" value="${data.batch_number}" readonly></div>
                    <div class="col-6"><label class="formal-label">Current Quantity (auto-filled)</label><input type="text" class="formal-input read-only-input" value="${data.quantity_avail}" readonly><p class="helper-text">Read-only — fetched from inventory</p></div>
                    <div class="col-6"><label class="formal-label">New Quantity After Adjustment *</label><input type="number" name="qty_after" class="formal-input" placeholder="Enter correct quantity" required></div>
                    <div class="col-6"><label class="formal-label">Reason for Adjustment *</label>
                        <select name="reason" class="form-select formal-input" style="font-size: 11px;" required>
                            <option value="" selected disabled>Select reason</option>
                            <option value="Physical Count">Physical Inventory Count</option>
                            <option value="Damage">Damaged Goods</option>
                            <option value="Expired">Expired Stock</option>
                            <option value="Loss">Loss / Theft</option>
                        </select>
                    </div>
                    <div class="col-6"><label class="formal-label">Adjusted By (auto-filled)</label><input type="text" class="formal-input read-only-input" value="Administrator" readonly></div>
                    <div class="col-12"><label class="formal-label">Notes / Additional Details</label><textarea name="notes" class="formal-input" rows="4" placeholder="Describe the reason in more detail" required></textarea></div>
                </div>
                <div class="mt-4 pt-3"><button type="submit" class="btn btn-save-adj">✓ Save Adjustment</button><button type="button" class="btn btn-cancel-adj" data-bs-dismiss="offcanvas">Cancel</button></div>
            </form>`;
        adjustDrawer.show();
    }

    function openEditForm(data) {
    const supplierOptions = (data.all_suppliers || []).map(s => `<option value="${s.supplier_id}" ${s.supplier_id == data.supplier_id ? 'selected' : ''}>${s.name}</option>`).join('');
    const categoryOptions = (data.all_categories || []).map(c => `<option value="${c.category_id}" ${c.category_id == data.category_id ? 'selected' : ''}>${c.name}</option>`).join('');
    const c = data.content || {};

    editContent.innerHTML = `
        <ul class="nav nav-pills p-3 bg-light border-bottom" id="editProductTabs">
            <li class="nav-item flex-fill"><button type="button" class="nav-link active w-100 rounded-pill" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-edit-info">Product Info</button></li>
            <li class="nav-item flex-fill"><button type="button" class="nav-link w-100 rounded-pill" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-edit-edu">Education</button></li>
        </ul>
        <form action="${BASE_URL}/admin/inventory/update-info" method="POST" enctype="multipart/form-data" id="editProductForm">
            <input type="hidden" name="product_id" value="${data.product_id}">
            <div class="p-4">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-edit-info">
                        <div class="mb-3">
                            <label class="formal-label">Product Image</label>
                            ${data.image_path ? `<img src="${BASE_URL}/${data.image_path}" class="w-100 rounded-3 mb-2" style="height: 140px; object-fit: cover;">` : `<p class="helper-text mb-2">No image uploaded yet.</p>`}
                            <input type="file" name="product_image" class="formal-input" accept="image/*">
                        </div>
                        <div class="mb-3"><label class="formal-label">Product Name *</label><input type="text" name="name" class="formal-input" value="${data.name ?? ''}" required></div>
                        <div class="mb-3"><label class="formal-label">Category *</label><select name="category_id" class="form-select formal-input" required>${categoryOptions}</select></div>
                        <div class="mb-3"><label class="formal-label">Supplier</label><select name="supplier_id" class="form-select formal-input"><option value="">— None —</option>${supplierOptions}</select></div>
                        <div class="mb-3"><label class="formal-label">Barcode Value (leave blank to auto-generate)</label><input type="text" name="barcode" class="formal-input" value="${data.barcode_value ?? ''}"></div>
                        <div class="mb-3"><label class="formal-label">Brand</label><input type="text" name="brand" class="formal-input" value="${data.brand ?? ''}"></div>
                        <div class="mb-3"><label class="formal-label">Manufacturer</label><input type="text" name="manufacturer" class="formal-input" value="${data.manufacturer ?? ''}"></div>
                        <div class="mb-3"><label class="formal-label">Unit</label><input type="text" name="unit" class="formal-input" value="${data.unit ?? 'piece'}"></div>
                        <div class="mb-3"><label class="formal-label">Description</label><textarea name="description" class="formal-input" rows="3">${data.description ?? ''}</textarea></div>
                        <div class="mb-3 form-check"><input type="checkbox" name="is_vat_exempt" class="form-check-input" id="vatExempt" ${data.is_vat_exempt == 1 ? 'checked' : ''}><label class="form-check-label formal-label mb-0" for="vatExempt">VAT Exempt</label></div>
                        <div class="mb-3"><label class="formal-label">Notes</label><textarea name="notes" class="formal-input" rows="2">${data.notes ?? ''}</textarea></div>
                    </div>
                    <div class="tab-pane fade" id="tab-edit-edu">
                        <div class="mb-3"><label class="formal-label">Video (Google Drive link)</label><input type="text" name="video_url" class="formal-input" value="${c.video_url ?? ''}"><p class="helper-text">Paste the normal Drive share link — it will convert automatically.</p></div>
                        <div class="mb-3"><label class="formal-label">Medical Description</label><textarea name="medical_description" class="formal-input" rows="2">${c.medical_description ?? ''}</textarea></div>
                        <div class="mb-3"><label class="formal-label">Usage Purpose</label><textarea name="usage_purpose" class="formal-input" rows="2">${c.usage_purpose ?? ''}</textarea></div>
                        <div class="mb-3"><label class="formal-label">Usage Guide</label><textarea name="usage_guide" class="formal-input" rows="2">${c.usage_guide ?? ''}</textarea></div>
                        <div class="mb-3"><label class="formal-label">Warnings</label><textarea name="warnings" class="formal-input" rows="2">${c.warnings ?? ''}</textarea></div>
                        <div class="mb-3"><label class="formal-label">Storage Information</label><textarea name="storage_info" class="formal-input" rows="2">${c.storage_info ?? ''}</textarea></div>
                        <div class="mb-3"><label class="formal-label">Healthcare Tips</label><textarea name="healthcare_tips" class="formal-input" rows="2">${c.healthcare_tips ?? ''}</textarea></div>
                        <div class="mb-3"><label class="formal-label">Warranty Information</label><input type="text" name="warranty_info" class="formal-input" value="${c.warranty_info ?? ''}"></div>
                    </div>
                </div>
            </div>
            <div class="p-4 pt-0">
                <button type="submit" class="btn btn-save-adj w-100">✓ Save Changes</button>
            </div>
        </form>`;

    editDrawer.show();
}

    function wireRowButtons() {
        document.querySelectorAll('.btn-view').forEach(btn => btn.addEventListener('click', handleViewClick));
        document.querySelectorAll('.btn-edit').forEach(btn => btn.addEventListener('click', handleEditClick));
        document.querySelectorAll('.btn-add-stock').forEach(btn => btn.addEventListener('click', handleAddStockClick));
    }
    wireRowButtons();

    // ============ SMART REFRESH — only reloads the table when stock genuinely changed elsewhere ============
const tbody = document.getElementById('inventoryTableBody');
if (tbody && tbody.dataset.autoRefresh) {
    let lastKnownUpdate = null;

    function checkForStockChanges() {
        if (document.hidden) return;
        if (document.activeElement && document.activeElement.closest('#inventoryTableBody')) return;

        fetch(`${BASE_URL}/admin/inventory/check-stock-updated`)
            .then(res => res.json())
            .then(data => {
                if (lastKnownUpdate === null) {
                    // First check on page load — just record the baseline, don't reload
                    lastKnownUpdate = data.last_updated;
                    return;
                }
                if (data.last_updated && data.last_updated !== lastKnownUpdate) {
                    lastKnownUpdate = data.last_updated;
                    refreshTableContent();
                }
            })
            .catch(err => console.error('Stock check failed', err));
    }

    function refreshTableContent() {
        const params = new URLSearchParams(window.location.search);
        fetch(`${BASE_URL}/admin/inventory/stock-table-data?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                // Only replace the table if we genuinely got valid data back —
                // never wipe a working table because of a bad/empty response.
                if (typeof data.rows_html !== 'string') return;

                tbody.innerHTML = data.rows_html;
                wireRowButtons();

                const kpiCards = document.querySelectorAll('.inventory-kpi-card h3');
                if (kpiCards[0] && data.total_products !== undefined) kpiCards[0].textContent = data.total_products;
                if (kpiCards[1] && data.low_stock !== undefined) kpiCards[1].textContent = data.low_stock;
                if (kpiCards[2] && data.near_expiry !== undefined) kpiCards[2].textContent = data.near_expiry;

                const rangeInfo = document.getElementById('rangeInfo');
                if (rangeInfo && data.range_start !== undefined) {
                    rangeInfo.textContent = `Showing ${data.range_start}-${data.range_end} of ${data.total_rows} Entries`;
                }
            })
            .catch(err => console.error('Table refresh failed', err));
    }

    checkForStockChanges(); // establish baseline immediately
    setInterval(checkForStockChanges, 5000); // cheap check every 5s, only acts if something actually changed
}

});