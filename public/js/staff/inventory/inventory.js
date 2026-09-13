document.addEventListener("DOMContentLoaded", function() {
            const viewBtns = document.querySelectorAll('.btn-view-details');
            const detailsDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('detailsDrawer'));
            const adjustDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('adjustDrawer'));
            const newBatchDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('newBatchDrawer'));
            const content = document.getElementById('drawerContent');
            const adjContent = document.getElementById('adjustContent');
            const newBatchContent = document.getElementById('newBatchContent');

            function val(v, fallback = 'Not on file') {
                return (v === null || v === undefined || v === '') ? fallback : v;
            }

            // Shared read-only product info block — used in both View Details and Add Batch
            function buildProductInfoBlock(data) {
                const imageHtml = data.image_path ?
                    `<img src="${BASE_URL}/${data.image_path}" class="rounded-4 mb-3" style="width:100%; max-height:160px; object-fit:cover;">` :
                    `<div class="bg-light rounded-4 mb-3 d-flex align-items-center justify-content-center" style="height:120px;"><i class="fas fa-image fs-2 text-muted"></i></div>`;

                return `
            ${imageHtml}
            <div class="row g-3 px-1 text-start mb-2">
                <div class="col-6"><label class="info-label">Brand</label><p class="info-value mb-0">${val(data.brand)}</p></div>
                <div class="col-6"><label class="info-label">Manufacturer</label><p class="info-value mb-0">${val(data.manufacturer)}</p></div>
                <div class="col-12"><label class="info-label">Supplier</label><p class="info-value mb-0">${val(data.supplier_name)}${data.supplier_contact ? ' — ' + data.supplier_contact : ''}${data.supplier_phone ? ' (' + data.supplier_phone + ')' : ''}</p></div>
                <div class="col-12"><label class="info-label">Description</label><p class="info-value mb-0 text-muted" style="font-size:10.5px;">${val(data.description, 'No description on file.')}</p></div>
                ${data.notes ? `<div class="col-12"><label class="info-label">Notes</label><p class="info-value mb-0 text-muted" style="font-size:10.5px;">${data.notes}</p></div>` : ''}
            </div>
        `;
    }

    // ============ ADD NEW BATCH (no-stock products) ============
    document.querySelectorAll('.btn-add-batch').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const unit = this.getAttribute('data-unit');

            newBatchContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-success"></div></div>`;
            newBatchDrawer.show();

            fetch(`${BASE_URL}/staff/inventory/get-product-info/${productId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { newBatchContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                    newBatchContent.innerHTML = `
                        <div class="text-center mb-3">
                            <h6 class="fw-bold mb-1">${data.name}</h6>
                            <span class="badge bg-dark">${data.cat_name}</span>
                            <p class="text-muted mt-1 mb-0" style="font-size:10px;">Barcode: ${val(data.barcode_value)} · Currently has no recorded stock</p>
                        </div>

                        ${buildProductInfoBlock(data)}

                        <hr class="my-4">

                        <form action="${BASE_URL}/staff/inventory/create-batch" method="POST">
    <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
    <input type="hidden" name="product_id" value="${data.product_id}">
                            <div class="row g-3 text-start">
                                <div class="col-6"><label class="formal-label">Batch Number *</label><input type="text" name="batch_number" class="formal-input" placeholder="e.g. B2026-05" required></div>
                                <div class="col-6"><label class="formal-label">Quantity (${unit}) *</label><input type="number" name="quantity" class="formal-input" min="1" required></div>
                                <div class="col-6"><label class="formal-label">Cost Price (₱)</label><input type="number" step="0.01" name="cost_price" class="formal-input" placeholder="0.00"></div>
                                <div class="col-6"><label class="formal-label">Sell Price (₱) *</label><input type="number" step="0.01" name="sell_price" class="formal-input" required></div>
                                <div class="col-6"><label class="formal-label">Reorder Level</label><input type="number" name="reorder_level" class="formal-input" value="5"></div>
                                <div class="col-6"><label class="formal-label">Expiry Date (optional)</label><input type="date" name="expires_at" class="formal-input"></div>
                            </div>
                            <div class="mt-5 d-flex gap-2">
                                <button type="submit" class="btn btn-save-adj flex-grow-1 py-3">✓ Save Batch</button>
                                <button type="button" class="btn btn-cancel-adj px-4" data-bs-dismiss="offcanvas">Cancel</button>
                            </div>
                        </form>
                    `;
                })
                .catch(err => {
                    newBatchContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load product information.</div>`;
                    console.error(err);
                });
        });
    });

    // ============ SEARCH (auto-submit) ============
    const searchInput = document.getElementById('liveSearch');
    if (searchInput) {
        let typingTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => searchInput.closest('form').submit(), 600);
        });
    }

    // ============ VIEW DETAILS ============
    viewBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.getAttribute('data-id');
        detailsDrawer.show();
        content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;

        fetch(`${BASE_URL}/staff/inventory/get-product-batches/${productId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) { content.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                const totalStock = data.batches.reduce((sum, b) => sum + parseInt(b.quantity_avail), 0);

                const batchRowsHtml = data.batches.length ? data.batches.map(b => `
                    <tr>
                        <td class="fw-bold">${b.batch_number}</td>
                        <td>${b.expires_at || '—'}</td>
                        <td class="text-center">${b.quantity_avail}</td>
                        <td class="text-end">₱${parseFloat(b.sell_price).toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2 btn-adjust-batch"
                                data-batch='${JSON.stringify(b).replace(/'/g, "&apos;")}' data-product-id="${productId}" data-product-name="${data.name}" data-unit="${data.unit}">
                                <i class="fas fa-adjust"></i>
                            </button>
                        </td>
                    </tr>
                `).join('') : `<tr><td colspan="5" class="text-center text-muted py-3">No batches recorded yet.</td></tr>`;

                content.innerHTML = `
                    <div class="text-center mb-3 px-4 pt-4">
                        <h5 class="fw-bold mb-1">${data.name}</h5>
                        <span class="badge bg-dark">${data.cat_name}</span>
                    </div>

                    <div class="px-4">
                        ${buildProductInfoBlock(data)}
                    </div>

                    <hr class="mx-4">

                    <div class="row g-3 px-4 mb-3 text-start">
                        <div class="col-6"><label class="info-label">Barcode</label><p class="info-value">${val(data.barcode_value)}</p></div>
                        <div class="col-6"><label class="info-label">Total Stock</label><p class="info-value fs-6 text-maroon">${totalStock} ${data.unit}</p></div>
                    </div>

                    <div class="px-4 mb-4">
                        <p class="fw-bold small mb-2">Batch Breakdown</p>
                        <div class="table-responsive">
                            <table class="table table-sm" style="font-size:10px">
                                <thead class="table-dark"><tr><th>Batch #</th><th>Expiry</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-center">Adjust</th></tr></thead>
                                <tbody>${batchRowsHtml}</tbody>
                            </table>
                        </div>
                    </div>
                `;

                document.querySelectorAll('.btn-adjust-batch').forEach(adjBtn => {
                    adjBtn.addEventListener('click', function() {
                        const batch = JSON.parse(this.getAttribute('data-batch').replace(/&apos;/g, "'"));
                        detailsDrawer.hide();
                        openAdjustForm({
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
                content.innerHTML = `<div class="text-center text-danger p-5">Failed to load product details.</div>`;
                console.error(err);
            });
    });
});

    function openAdjustForm(data) {
        adjContent.innerHTML = `
            <div class="mb-4">
                <h6 class="fw-bold mb-3" style="color: #b30000; font-size: 12px; letter-spacing: 0.5px;">
                    <i class="fas fa-pencil-alt me-2" style="color:#333"></i>ADD STOCK ADJUSTMENT
                </h6>
            </div>
            <form action="${BASE_URL}/staff/inventory/adjust-stock" method="POST">
    <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
    <input type="hidden" name="batch_id" value="${data.batch_id}">
                <input type="hidden" name="product_id" value="${data.product_id}">
                <input type="hidden" name="qty_before" value="${data.quantity_avail}">

                <div class="row g-3 text-start">
                    <div class="col-6"><label class="formal-label">Product</label><input type="text" class="formal-input read-only-input" value="${data.name}" readonly></div>
                    <div class="col-6"><label class="formal-label">Batch Number</label><input type="text" class="formal-input read-only-input" value="${data.batch_number}" readonly></div>
                    <div class="col-6"><label class="formal-label">System Quantity</label><input type="text" class="formal-input read-only-input" value="${data.quantity_avail}" readonly></div>
                    <div class="col-6"><label class="formal-label">Actual Corrected Qty *</label><input type="number" name="qty_after" class="formal-input" min="0" required></div>
                    <div class="col-12">
                        <label class="formal-label">Adjustment Reason *</label>
                        <select name="reason" class="form-select formal-input" required>
                            <option value="Physical Count">Physical Inventory Count</option>
                            <option value="Damage">Damaged Goods</option>
                            <option value="Expired">Expired Stock</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="formal-label">Staff Remarks *</label><textarea name="notes" class="formal-input" rows="4" required placeholder="Describe the reason for adjustment..."></textarea></div>
                </div>

                <div class="mt-5 d-flex gap-2">
                    <button type="submit" class="btn btn-save-adj flex-grow-1 py-3" style="background:#1a2a6c">✓ Confirm Adjustment</button>
                    <button type="button" class="btn btn-cancel-adj px-4" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        `;
        adjustDrawer.show();
    }
});