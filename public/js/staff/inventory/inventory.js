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
                            <p class="text-muted mt-1 mb-0" style="font-size:10px;">SKU: ${val(data.sku)} · Currently has no recorded stock</p>
                        </div>

                        ${buildProductInfoBlock(data)}

                        <hr class="my-4">

                        <form action="${BASE_URL}/staff/inventory/create-batch" method="POST">
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
            const id = this.getAttribute('data-id');
            if (!id) { alert('This product has no stock batch to view yet.'); return; }

            detailsDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;

            fetch(`${BASE_URL}/staff/inventory/get-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                    content.innerHTML = `
                        <div class="text-center mb-3 px-4 pt-4">
                            <h5 class="fw-bold mb-1">${data.name}</h5>
                            <span class="badge bg-dark">${data.cat_name}</span>
                        </div>

                        <div class="px-4">
                            ${buildProductInfoBlock(data)}
                        </div>

                        <hr class="mx-4">

                        <div class="row g-3 px-4 mb-4 text-start">
                            <div class="col-6"><label class="info-label">SKU</label><p class="info-value">${val(data.sku)}</p></div>
                            <div class="col-6"><label class="info-label">Barcode</label><p class="info-value">${val(data.barcode_value)}</p></div>
                            <div class="col-6"><label class="info-label">Current Stock</label><p class="info-value fs-6 text-maroon">${data.quantity_avail} ${data.unit}</p></div>
                            <div class="col-6"><label class="info-label">Batch No.</label><p class="info-value text-primary">${data.batch_number}</p></div>
                        </div>

                        <div class="d-grid gap-2 px-4 pb-4">
                            <button type="button" id="btnStaffAdjust" class="btn btn-warning py-3 fw-bold rounded-3 shadow-sm text-dark">
                                <i class="fas fa-adjust me-2"></i>PROCESS STOCK ADJUSTMENT
                            </button>
                        </div>
                    `;

                    document.getElementById('btnStaffAdjust').addEventListener('click', function() {
                        detailsDrawer.hide();
                        openAdjustForm(data);
                    });
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-center text-danger p-5">Failed to load item details.</div>`;
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
            <form action="${BASE_URL}/staff/inventory/adjust_stock" method="POST">
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