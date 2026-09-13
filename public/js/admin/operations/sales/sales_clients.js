document.addEventListener("DOMContentLoaded", function() {
    // ============ SEARCH / TYPE (auto-submit) — wired first, independent ============
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

    function val(v, fallback = 'N/A') {
        return (v === null || v === undefined || v === '') ? fallback : v;
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
        <div class="d-flex gap-1 mt-1">
            <span class="badge ${o.fulfillment_type === 'pickup' ? 'bg-info text-dark' : 'bg-primary'}" style="font-size:9px;">
                <i class="fas fa-${o.fulfillment_type === 'pickup' ? 'store' : 'truck'} me-1"></i>${o.fulfillment_type === 'pickup' ? 'PICKUP' : 'DELIVERY'}
            </span>
            <span class="badge ${o.payment_status === 'paid' ? 'bg-success' : 'bg-secondary'}" style="font-size:9px;">${(o.payment_status || 'unpaid').toUpperCase()}</span>
        </div>
        <h6 class="text-maroon mt-2 mb-0 fw-bold">${peso(parseFloat(o.total))}</h6>
    </div>
`).join('');

                    const permitHtml = c.permit_path ? `
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border mb-2" style="background:#f8f9fa;">
                            <span style="font-size:11px;"><i class="fas fa-file-invoice me-2 text-success"></i>File on record</span>
                            <a href="${BASE_URL}/${c.permit_path}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">View File</a>
                        </div>` : `
                        <div class="p-3 rounded-3 border text-muted" style="font-size:11px; background:#f8f9fa;">No document on file.</div>`;

                    content.innerHTML = `
                        <div class="p-4 border-bottom bg-light">
                            <h5 class="fw-bold mb-1 text-start">${c.organization}</h5>
                            <span class="badge bg-dark d-inline-block">${(c.client_type || '').toUpperCase()}</span>
                            ${c.is_verified ? '<span class="badge bg-success d-inline-block ms-1">Verified</span>' : '<span class="badge bg-warning text-dark d-inline-block ms-1">Unverified</span>'}
                        </div>
                        <div class="p-4">
                            <p class="fw-bold mb-3 text-start" style="font-size:12px;">Business Identity</p>
                            <div class="row g-3 mb-4 text-start">
                                <div class="col-6"><label class="info-label">TIN</label><p class="info-value">${val(c.tin)}</p></div>
                                <div class="col-6"><label class="info-label">Reference #</label><p class="info-value">${val(c.registration_ref)}</p></div>
                                <div class="col-12"><label class="info-label">Business Address</label><p class="info-value">${val(c.address)}</p></div>
                            </div>

                            <hr>
                            <p class="fw-bold mb-3 mt-3 text-start" style="font-size:12px;">Contact Information</p>
                            <div class="row g-3 mb-4 text-start">
                                <div class="col-6"><label class="info-label">Contact Person</label><p class="info-value">${val(c.contact_person)}</p></div>
                                <div class="col-6"><label class="info-label">Position</label><p class="info-value">${val(c.position)}</p></div>
                                <div class="col-6"><label class="info-label">Login Email</label><p class="info-value text-primary">${val(c.login_email)}</p></div>
                                <div class="col-6"><label class="info-label">Phone</label><p class="info-value">${val(c.phone)}</p></div>
                                <div class="col-6"><label class="info-label">Alt. Phone</label><p class="info-value">${val(c.alt_phone)}</p></div>
                                <div class="col-12"><label class="info-label">Delivery Address</label><p class="info-value">${val(c.delivery_address, 'Same as business address')}</p></div>
                            </div>

                            <hr>
                            <p class="fw-bold mb-2 mt-3 text-start" style="font-size:12px;">Supporting Document</p>
                            <div class="mb-4">${permitHtml}</div>

                            <button type="button" class="btn btn-maroon w-100 py-2 mb-4 fw-bold btn-new-sales-order" data-id="${c.client_id}" data-name="${c.organization}" data-type="${c.client_type}">
                                <i class="fas fa-plus me-2"></i>New Sales Order
                            </button>

                            <h6 class="fw-bold mb-3 border-bottom pb-2 text-start">ORDER HISTORY</h6>
                            <div style="max-height: 300px; overflow-y: auto;">
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
                            clientDrawer.hide();
                            openNewOrderDrawer('registered');
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

        const fulfillmentSelect = document.getElementById('fulfillmentTypeSelect');
        const deliveryAddressWrap = document.getElementById('deliveryAddressWrap');
        const deliveryAddressInput = document.getElementById('deliveryAddressInput');
        const paymentMethodSelect = document.getElementById('paymentMethodSelect');
        const paymentNote = document.getElementById('paymentNote');

        const btnWalkinSale = document.getElementById('btnWalkinSale');
        const orderModeField = document.getElementById('orderModeField');
        const registeredClientBlock = document.getElementById('registeredClientBlock');
        const walkinClientBlock = document.getElementById('walkinClientBlock');
        const guestNameInput = document.getElementById('guestNameInput');

        function openNewOrderDrawer(mode) {
            orderModeField.value = mode;
            if (mode === 'walkin') {
                registeredClientBlock.style.display = 'none';
                walkinClientBlock.style.display = 'block';
                guestNameInput.required = true;
            } else {
                registeredClientBlock.style.display = 'block';
                walkinClientBlock.style.display = 'none';
                guestNameInput.required = false;
            }
            if (typeof resetOrderForm === 'function') resetOrderForm();
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('newOrderDrawer')).show();
        }

        if (btnWalkinSale) {
            btnWalkinSale.addEventListener('click', function() {
                openNewOrderDrawer('walkin');
            });
        }

        function updateFulfillmentUI() {
            const isPickup = fulfillmentSelect.value === 'pickup';
            deliveryAddressWrap.style.display = isPickup ? 'none' : 'block';
            deliveryAddressInput.required = !isPickup;

            if (isPickup) {
                paymentNote.innerHTML = '<i class="fas fa-store me-1"></i>Client will pay in person when they pick up the order at the store.';
                paymentNote.className = 'helper-text mb-3';
            } else if (['cheque', 'bank_transfer'].includes(paymentMethodSelect.value)) {
                paymentNote.innerHTML = '<i class="fas fa-exclamation-triangle me-1 text-warning"></i><b>Payment must be confirmed before this order can be dispatched for delivery.</b>';
                paymentNote.className = 'helper-text mb-3 text-warning';
            } else {
                paymentNote.innerHTML = '<i class="fas fa-truck me-1"></i>Standard delivery — no advance payment confirmation required for this method.';
                paymentNote.className = 'helper-text mb-3';
            }
        }

        fulfillmentSelect.addEventListener('change', updateFulfillmentUI);
        paymentMethodSelect.addEventListener('change', updateFulfillmentUI);
        updateFulfillmentUI();

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
            updateFulfillmentUI();
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