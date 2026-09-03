document.addEventListener("DOMContentLoaded", function() {
            // ============ VIEW SUPPLIER ============
            const viewBtns = document.querySelectorAll('.btn-view-supplier');
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('supplierDrawer'));
            const content = document.getElementById('supplierDrawerContent');

            function fmtDate(d) {
                if (!d) return 'N/A';
                return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            }

            function peso(n) {
                return '₱' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            viewBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const id = this.getAttribute('data-id');
                                    drawer.show();
                                    content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

                                    fetch(`${BASE_URL}/admin/procurement/get-supplier-details/${id}`)
                                        .then(res => res.json())
                                        .then(data => {
                                                if (data.error) {
                                                    content.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                                                    return;
                                                }

                                                const hasRatings = data.on_time_rate !== null && data.on_time_rate !== undefined;
                                                const ratingsHtml = hasRatings ? `
                        <div class="row g-2 text-center mb-3">
                            <div class="col-6"><small class="info-label d-block">On-Time Rate</small><p class="fw-bold mb-0">${data.on_time_rate}%</p></div>
                            <div class="col-6"><small class="info-label d-block">Accuracy Rate</small><p class="fw-bold mb-0">${data.accuracy_rate}%</p></div>
                        </div>
                    ` : `<p class="extra-small text-muted mb-3">On-time/accuracy ratings not yet computed for this supplier.</p>`;

                                                const poListHtml = data.recent_pos.length ? `
                        <div class="mt-3">
                            ${data.recent_pos.map(po => `
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <p class="mb-0 fw-bold" style="font-size:11px;">${po.po_number}</p>
                                        <small class="text-muted">${fmtDate(po.created_at)}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark border">${po.status}</span>
                                        <p class="mb-0 fw-bold text-maroon" style="font-size:11px;">${peso(po.total_amount)}</p>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : `<p class="extra-small text-muted mb-0">No purchase orders recorded yet.</p>`;

                    content.innerHTML = `
                        <div class="text-center mb-4 p-4 bg-light rounded-4">
                            <i class="fas fa-building fs-1 text-maroon opacity-25 mb-3"></i>
                            <h5 class="fw-bold mb-1">${data.name}</h5>
                            <p class="text-muted small mb-0">${data.contact_person || 'No primary contact'}</p>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-12"><label class="info-label">Address</label><p class="info-value">${data.address || 'N/A'}</p></div>
                            <div class="col-6"><label class="info-label">Email</label><p class="info-value text-primary">${data.email || 'N/A'}</p></div>
                            <div class="col-6"><label class="info-label">Phone</label><p class="info-value">${data.phone || 'N/A'}</p></div>
                            <div class="col-6"><label class="info-label">Payment Terms</label><p class="info-value">${data.payment_terms || 'Not set'}</p></div>
                            <div class="col-6"><label class="info-label">Lead Time</label><p class="info-value">${data.lead_time_days} days</p></div>
                            <div class="col-6"><label class="info-label">Partner Since</label><p class="info-value">${fmtDate(data.created_at)}</p></div>
                        </div>
                        <div class="p-3 border rounded-3">
                            <h6 class="fw-bold small mb-2"><i class="fas fa-truck me-2"></i>Procurement Performance</h6>
                            ${ratingsHtml}
                            <div class="row g-2 text-center mb-2">
                                <div class="col-6"><small class="info-label d-block">Total POs</small><p class="fw-bold mb-0">${data.po_count}</p></div>
                                <div class="col-6"><small class="info-label d-block">Total Spend</small><p class="fw-bold mb-0">${peso(data.total_spent)}</p></div>
                            </div>
                            <hr class="my-2">
                            <p class="fw-bold small mb-0">Recent Orders</p>
                            ${poListHtml}
                        </div>
                    `;
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-center text-danger p-5">Failed to load supplier details.</div>`;
                    console.error(err);
                });
        });
    });

    // ============ SEARCH (server-side, live) ============
    const searchInput = document.getElementById('supplierSearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const params = new URLSearchParams(window.location.search);
                params.set('page', 1);
                const term = searchInput.value.trim();
                term !== '' ? params.set('search', term) : params.delete('search');
                window.location.href = window.location.pathname + '?' + params.toString();
            }, 500);
        });
    }

    // ============ CREATE PO (scoped to one supplier's products) ============
    const createPODrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('createPODrawer'));
    const createPOContent = document.getElementById('createPOContent');
    const createPOTitle = document.getElementById('createPOTitle');

    document.querySelectorAll('.btn-create-po').forEach(btn => {
        btn.addEventListener('click', function() {
            const supplierId = this.getAttribute('data-id');
            const supplierName = this.getAttribute('data-name');

            createPOTitle.textContent = `Create PO — ${supplierName}`;
            createPOContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            createPODrawer.show();

            fetch(`${BASE_URL}/admin/procurement/get-supplier-products/${supplierId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        createPOContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                        return;
                    }

                    if (!data.products.length) {
                        createPOContent.innerHTML = `
                            <div class="text-center text-muted p-4 bg-light rounded-3">
                                This supplier has no products linked to them yet.<br>
                                Assign products to them from Stock Management first.
                            </div>`;
                        return;
                    }

                    const grouped = {};
                    data.products.forEach(p => {
                        if (!grouped[p.cat_name]) grouped[p.cat_name] = [];
                        grouped[p.cat_name].push(p);
                    });

                    const optionsHtml = Object.keys(grouped).map(cat => `
                        <optgroup label="${cat}">
                            ${grouped[cat].map(p => `<option value="${p.product_id}" data-cost="${p.unit_cost ?? ''}">${p.name}</option>`).join('')}
                        </optgroup>
                    `).join('');

                    const buildRow = () => `
                        <div class="row g-2 mb-1 po-row align-items-end">
                            <div class="col-5">
                                <label class="info-label">Select Product</label>
                                <select name="products[]" class="form-select form-select-sm po-product-select">${optionsHtml}</select>
                            </div>
                            <div class="col-3"><label class="info-label">Quantity</label><input type="number" name="qtys[]" class="form-control form-control-sm" value="1" min="1"></div>
                            <div class="col-3">
                                <label class="info-label">Unit Cost</label>
                                <input type="number" step="0.01" name="costs[]" class="form-control form-control-sm po-cost-input" placeholder="0.00">
                            </div>
                            <div class="col-1">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-po-row" title="Remove item">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <p class="helper-text po-cost-hint mb-2"></p>`;

                    createPOContent.innerHTML = `
                        <form action="${BASE_URL}/admin/procurement/save-po" method="POST">
                            <input type="hidden" name="supplier_id" value="${supplierId}">

                            <div class="mb-4">
                                <p class="text-maroon fw-bold mb-3 border-bottom pb-1">Purchase Order Details</p>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="formal-label">Supplier</label>
                                        <input type="text" class="formal-input read-only-input" value="${data.supplier.name}" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="formal-label">Expected Delivery</label>
                                        <input type="date" name="expected_date" class="formal-input" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="formal-label">Notes (optional)</label>
                                    <textarea name="notes" class="formal-input" rows="2" placeholder="e.g. Rush order — needed before month-end stock count"></textarea>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="text-maroon fw-bold mb-3 border-bottom pb-1">Products to Order</p>
                                <p class="helper-text mb-2">Only products linked to ${data.supplier.name} are listed. Cost auto-fills from their price catalog when available — feel free to adjust it.</p>
                                <div id="poRowsContainer">${buildRow()}</div>
                                <button type="button" id="btnAddPORow" class="btn btn-xs btn-outline-dark mt-2">+ Add Item</button>
                            </div>

                            <button type="submit" class="btn btn-maroon w-100 py-3 mt-4 fw-bold">✓ SUBMIT PURCHASE ORDER</button>
                        </form>
                    `;

                    const rowsContainer = document.getElementById('poRowsContainer');

                    function applyCatalogCost(row) {
                        const select = row.querySelector('.po-product-select');
                        const costInput = row.querySelector('.po-cost-input');
                        const hint = row.nextElementSibling; // .po-cost-hint paragraph
                        const selectedOption = select.options[select.selectedIndex];
                        const catalogCost = selectedOption.getAttribute('data-cost');

                        if (catalogCost && catalogCost !== '') {
                            costInput.value = parseFloat(catalogCost).toFixed(2);
                            if (hint) hint.textContent = `Catalog price: ₱${parseFloat(catalogCost).toFixed(2)} — you can adjust it.`;
                        } else {
                            costInput.value = '';
                            if (hint) hint.textContent = 'No catalog price on file yet — please enter manually.';
                        }
                    }

                    // Apply on load for the first row, and whenever a product select changes
                    rowsContainer.querySelectorAll('.po-row').forEach(applyCatalogCost);

                    rowsContainer.addEventListener('change', function(e) {
                        if (e.target.classList.contains('po-product-select')) {
                            applyCatalogCost(e.target.closest('.po-row'));
                        }
                    });

                    document.getElementById('btnAddPORow').addEventListener('click', function() {
                        rowsContainer.insertAdjacentHTML('beforeend', buildRow());
                        const newRow = rowsContainer.querySelectorAll('.po-row');
                        applyCatalogCost(newRow[newRow.length - 1]);
                    });

                    rowsContainer.addEventListener('click', function(e) {
                        const removeBtn = e.target.closest('.btn-remove-po-row');
                        if (!removeBtn) return;

                        const rows = rowsContainer.querySelectorAll('.po-row');
                        if (rows.length <= 1) return; // keep at least one row

                        const row = removeBtn.closest('.po-row');
                        const hint = row.nextElementSibling;
                        row.remove();
                        if (hint && hint.classList.contains('po-cost-hint')) hint.remove();
                    });
                })
                .catch(err => {
                    createPOContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load supplier products.</div>`;
                    console.error(err);
                });
        });
    });
});