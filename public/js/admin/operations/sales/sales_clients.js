document.addEventListener("DOMContentLoaded", function() {
    // ============ SEARCH / CATEGORY (auto-submit) — wired first, independent ============
    const searchForm = document.getElementById('searchForm');
    const liveSearch = document.getElementById('liveSearch');
    const typeFilter = document.getElementById('typeFilter');

    let typingTimer;
    liveSearch.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => searchForm.submit(), 500);
    });
    typeFilter.addEventListener('change', function() {
        searchForm.submit();
    });

    function peso(n) {
        return '₱' + (n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ============ VIEW CLIENT — wired first, independent of the New Order drawer ============
    const viewBtns = document.querySelectorAll('.btn-view-client');
    const clientDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('clientDrawer'));
    const content = document.getElementById('clientDrawerContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            clientDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${BASE_URL}/admin/sales/get-client-details/${id}`)
                .then(res => {
                    if (!res.ok) throw new Error('Route not found');
                    return res.json();
                })
                .then(data => {
                    const c = data.client;
                    const ordersHtml = data.orders.map(o => `
                        <div class="p-3 border rounded-4 mb-2 bg-white shadow-sm text-start">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold mb-0" style="font-size:11px">${o.order_number}</h6>
                                <span class="badge bg-light text-dark border small">${o.status.toUpperCase()}</span>
                            </div>
                            <small class="text-muted">${o.created_at}</small>
                            <h6 class="text-maroon mt-2 mb-0 fw-bold">${peso(parseFloat(o.total))}</h6>
                        </div>
                    `).join('');

                    content.innerHTML = `
                        <div class="p-4 border-bottom bg-light">
                            <h5 class="fw-bold mb-1 text-start">${c.organization}</h5>
                            <span class="badge bg-dark d-inline-block">${c.client_type.toUpperCase()}</span>
                            ${c.is_verified ? '<span class="badge bg-success d-inline-block ms-1">Verified</span>' : '<span class="badge bg-warning text-dark d-inline-block ms-1">Unverified</span>'}
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-4 text-start">
                                <div class="col-6"><label class="info-label">Current Balance</label><p class="info-value text-danger">${peso(parseFloat(c.credit_used))}</p></div>
                                <div class="col-6"><label class="info-label">Credit Limit</label><p class="info-value">${peso(parseFloat(c.credit_limit))}</p></div>
                                <div class="col-12"><label class="info-label">Address</label><p class="info-value text-muted" style="font-size:10px">${c.address || 'N/A'}</p></div>
                            </div>
                            <button type="button" class="btn btn-maroon w-100 py-2 mb-4 fw-bold btn-new-sales-order" data-id="${c.client_id}" data-name="${c.organization}" data-type="${c.client_type}">
                                <i class="fas fa-plus me-2"></i>New Sales Order
                            </button>
                            <h6 class="fw-bold mb-3 border-bottom pb-2 text-start">ORDER HISTORY</h6>
                            <div style="max-height: 350px; overflow-y: auto;">
                                ${ordersHtml || '<p class="text-center text-muted py-4 small">No history.</p>'}
                            </div>
                        </div>
                    `;

                    const newOrderTriggerBtn = content.querySelector('.btn-new-sales-order');
                    if (newOrderTriggerBtn) {
                        newOrderTriggerBtn.addEventListener('click', function() {
                            document.getElementById('newOrderClientId').value = this.getAttribute('data-id');
                            document.getElementById('newOrderClientDisplay').textContent =
                                `${this.getAttribute('data-name')} (${this.getAttribute('data-type').charAt(0).toUpperCase() + this.getAttribute('data-type').slice(1)})`;
                            if (typeof resetOrderForm === 'function') resetOrderForm();
                            clientDrawer.hide();
                            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('newOrderDrawer')).show();
                        });
                    }
                })
                .catch(err => {
                    content.innerHTML = `<div class="alert alert-danger m-3 small text-center">Error: Could not retrieve data.</div>`;
                    console.error(err);
                });
        });
    });

    // ============ NEW SALES ORDER DRAWER — isolated so a failure here can't break View ============
    let resetOrderForm;

    try {
        const newOrderForm = document.getElementById('newOrderForm');
        const rowsContainer = document.getElementById('orderRowsContainer');
        const discountTypeSelect = document.getElementById('discountTypeSelect');
        const schoolRateDisplay = document.getElementById('schoolRateDisplay');
        if (schoolRateDisplay) schoolRateDisplay.textContent = SCHOOL_DISCOUNT_RATE;

        const previewGross = document.getElementById('previewGross');
        const previewDiscount = document.getElementById('previewDiscount');
        const previewDiscountLabel = document.getElementById('previewDiscountLabel');
        const previewSubtotal = document.getElementById('previewSubtotal');
        const previewVat = document.getElementById('previewVat');
        const previewTotal = document.getElementById('previewTotal');

        function buildOrderRow() {
            const catOptions = PRODUCT_CATEGORIES.map(c => `<option value="${c.category_id}">${c.name}</option>`).join('');
            return `
                <div class="row g-2 mb-2 order-row align-items-center">
                    <div class="col-4">
                        <select class="form-select form-select-sm order-category-select">
                            <option value="">Select category</option>
                            ${catOptions}
                        </select>
                    </div>
                    <div class="col-3">
                        <select name="items[]" class="form-select form-select-sm order-product-select" disabled>
                            <option value="">Select category first</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <input type="number" name="qtys[]" class="form-control form-control-sm order-qty-input" value="1" min="1">
                    </div>
                    <div class="col-2 text-end fw-bold order-row-subtotal" style="font-size:11px;">₱0.00</div>
                    <div class="col-1">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-remove-order-row"><i class="fas fa-times"></i></button>
                    </div>
                </div>`;
        }

        // Mirrors save_order()'s server-side math exactly, so what the admin sees
        // before submitting is the real total, not an estimate.
        function recalcPreview() {
            let gross = 0;

            rowsContainer.querySelectorAll('.order-row').forEach(row => {
                const select = row.querySelector('.order-product-select');
                const qty = parseFloat(row.querySelector('.order-qty-input').value || 0);
                const opt = select.options[select.selectedIndex];
                const price = opt ? parseFloat(opt.getAttribute('data-price') || 0) : 0;
                const lineSubtotal = price * qty;
                gross += lineSubtotal;
                row.querySelector('.order-row-subtotal').textContent = peso(lineSubtotal);
            });

            const type = discountTypeSelect.value;
            let discountAmount = 0,
                netTotal, vatAmount, subtotal, label = '';

            if (type === 'pwd' || type === 'senior') {
                const vatExclusive = gross / 1.12;
                discountAmount = vatExclusive * 0.20;
                netTotal = vatExclusive - discountAmount;
                vatAmount = 0;
                subtotal = netTotal;
                label = ' (20% + VAT Exempt)';
            } else {
                let pct = 0;
                if (type === 'school') {
                    pct = SCHOOL_DISCOUNT_RATE;
                    label = ` (${pct}%)`;
                }
                if (type === 'custom') {
                    pct = parseFloat(document.querySelector('[name="discount_percent"]').value || 0);
                    label = ` (${pct}%)`;
                }
                discountAmount = gross * (pct / 100);
                netTotal = gross - discountAmount;
                vatAmount = netTotal - (netTotal / 1.12);
                subtotal = netTotal / 1.12;
            }

            previewGross.textContent = peso(gross);
            previewDiscount.textContent = '-' + peso(discountAmount);
            previewDiscountLabel.textContent = label;
            previewSubtotal.textContent = peso(subtotal);
            previewVat.textContent = peso(vatAmount);
            previewTotal.textContent = peso(netTotal);
        }

        function wireRow(row) {
            const catSelect = row.querySelector('.order-category-select');
            const prodSelect = row.querySelector('.order-product-select');

            catSelect.addEventListener('change', function() {
                const catId = this.value;
                if (!catId) {
                    prodSelect.innerHTML = `<option value="">Select category first</option>`;
                    prodSelect.disabled = true;
                    recalcPreview();
                    return;
                }
                const matches = CLIENT_PRODUCTS.filter(p => p.category_id == catId);
                prodSelect.innerHTML = matches.length ?
                    matches.map(p => `<option value="${p.product_id}" data-price="${p.latest_sell_price || 0}" ${p.total_stock <= 0 ? 'disabled' : ''}>${p.name} — ₱${parseFloat(p.latest_sell_price || 0).toFixed(2)} (${p.total_stock} ${p.unit} in stock)</option>`).join('') :
                    `<option value="">No products in this category</option>`;
                prodSelect.disabled = false;
                recalcPreview();
            });

            prodSelect.addEventListener('change', recalcPreview);
            row.querySelector('.order-qty-input').addEventListener('input', recalcPreview);
        }

        resetOrderForm = function() {
            newOrderForm.reset();
            rowsContainer.innerHTML = '';
            rowsContainer.insertAdjacentHTML('beforeend', buildOrderRow());
            wireRow(rowsContainer.querySelector('.order-row'));
            document.querySelectorAll('#discountIdWrap, #discountHolderWrap, #discountCustomWrap, #discountSchoolWrap').forEach(el => el.style.display = 'none');
            recalcPreview();
        };

        document.getElementById('btnAddOrderRow').addEventListener('click', function() {
            rowsContainer.insertAdjacentHTML('beforeend', buildOrderRow());
            wireRow(rowsContainer.lastElementChild);
            recalcPreview();
        });

        rowsContainer.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.btn-remove-order-row');
            if (!removeBtn) return;
            if (rowsContainer.querySelectorAll('.order-row').length <= 1) return;
            removeBtn.closest('.order-row').remove();
            recalcPreview();
        });

        discountTypeSelect.addEventListener('change', function() {
            const type = this.value;
            document.getElementById('discountIdWrap').style.display = (type === 'pwd' || type === 'senior') ? 'block' : 'none';
            document.getElementById('discountHolderWrap').style.display = (type === 'pwd' || type === 'senior') ? 'block' : 'none';
            document.getElementById('discountCustomWrap').style.display = (type === 'custom') ? 'block' : 'none';
            document.getElementById('discountSchoolWrap').style.display = (type === 'school') ? 'block' : 'none';
            recalcPreview();
        });
        document.querySelector('[name="discount_percent"]').addEventListener('input', recalcPreview);

        resetOrderForm();
    } catch (err) {
        console.error('New Sales Order drawer failed to initialize:', err);
    }
});