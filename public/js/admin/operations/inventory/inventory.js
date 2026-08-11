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

    addStockButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const pid = this.getAttribute('data-pid');
            addStockContent.innerHTML = `
        <form action="${BASE_URL}/admin/inventory/create-batch" method="POST">
            <input type="hidden" name="product_id" value="${pid}">
            <div class="mb-3">
                <label class="formal-label">Batch Number *</label>
                <input type="text" name="batch_number" class="formal-input" placeholder="e.g. B2026-05" required>
            </div>
            <div class="mb-3">
                <label class="formal-label">Quantity *</label>
                <input type="number" name="quantity" class="formal-input" min="1" required>
            </div>
            <div class="mb-3">
                <label class="formal-label">Reorder Level</label>
                <input type="number" name="reorder_level" class="formal-input" value="5">
            </div>
            <div class="mb-3">
                <label class="formal-label">Cost Price</label>
                <input type="number" step="0.01" name="cost_price" class="formal-input">
            </div>
            <div class="mb-3">
                <label class="formal-label">Sell Price *</label>
                <input type="number" step="0.01" name="sell_price" class="formal-input" required>
            </div>
            <div class="mb-3">
                <label class="formal-label">Expiry Date</label>
                <input type="date" name="expires_at" class="formal-input">
            </div>
            <button type="submit" class="btn btn-save-adj">✓ Save Batch</button>
            <button type="button" class="btn btn-cancel-adj" data-bs-dismiss="offcanvas">Cancel</button>
        </form>`;
            addStockDrawer.show();
        });
    });

    // 1. SEARCH
    const searchInput = document.getElementById('inventorySearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            let filter = searchInput.value.toLowerCase();
            document.querySelectorAll('#inventoryTableBody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
            });
        });
    }

    // 2. VIEW PRODUCT
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            productDrawer.show();
            drawerContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${BASE_URL}/admin/inventory/get-details/${id}`)
                .then(res => res.json())
                .then(data => {

                    function val(v, fallback = '—') {
                        return (v === null || v === undefined || v === '') ? fallback : v;
                    }

                    // inside the .then(data => { ... }) of btn-view:
                    drawerContent.innerHTML = `
    <div class="p-4 bg-light rounded-4 text-center mb-4">
        <i class="fas fa-microscope fs-1 text-maroon opacity-25 mb-3"></i>
        <h5 class="fw-bold mb-1">${val(data.name)}</h5>
        <span class="badge bg-dark">${val(data.cat_name)}</span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6"><p class="info-label mb-0">SKU</p><p class="info-value">${val(data.sku)}</p></div>
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
    </div>
`;

                    document.getElementById('btnGoToAdjust').addEventListener('click', function() {
                        productDrawer.hide();
                        openAdjustmentForm(data);
                    });

                    document.getElementById('btnGoToEdit').addEventListener('click', function() {
                        productDrawer.hide();
                        openEditForm(data);
                    });
                })
                .catch(err => {
                    drawerContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load product details.</div>`;
                    console.error(err);
                });
        });
    });

    // 3. EDIT button in table row (skips straight to edit form)
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            fetch(`${BASE_URL}/admin/inventory/get-product/${id}`)
                .then(res => res.json())
                .then(data => openEditForm(data))
                .catch(err => console.error(err));
        });
    });

    // 4. ADJUSTMENT FORM
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

    // 5. EDIT PRODUCT INFO FORM
    function openEditForm(data) {
        const supplierOptions = (data.all_suppliers || []).map(s =>
            `<option value="${s.supplier_id}" ${s.supplier_id == data.supplier_id ? 'selected' : ''}>${s.name}</option>`
        ).join('');

        const categoryOptions = (data.all_categories || []).map(c =>
            `<option value="${c.category_id}" ${c.category_id == data.category_id ? 'selected' : ''}>${c.name}</option>`
        ).join('');

        editContent.innerHTML = `
    <form action="${BASE_URL}/admin/inventory/update-info" method="POST">
        <input type="hidden" name="product_id" value="${data.product_id}">
        <div class="mb-3">
            <label class="formal-label">Product Name *</label>
            <input type="text" name="name" class="formal-input" value="${data.name ?? ''}" required>
        </div>
        <div class="mb-3">
            <label class="formal-label">Category *</label>
            <select name="category_id" class="form-select formal-input" required>${categoryOptions}</select>
        </div>
        <div class="mb-3">
            <label class="formal-label">Supplier</label>
            <select name="supplier_id" class="form-select formal-input">
                <option value="">— None —</option>
                ${supplierOptions}
            </select>
        </div>
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
        <button type="submit" class="btn btn-save-adj">✓ Save Changes</button>
        <button type="button" class="btn btn-cancel-adj" data-bs-dismiss="offcanvas">Cancel</button>
    </form>`;
        editDrawer.show();
    }
});