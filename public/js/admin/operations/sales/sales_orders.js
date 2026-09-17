document.addEventListener("DOMContentLoaded", function() {
            const filterForm = document.getElementById('filterForm');
            const liveSearch = document.getElementById('liveSearch');
            const typeFilter = document.getElementById('typeFilter');

            // --- New Sales Order (Walk-in) drawer ---
            const newOrderDrawerEl = document.getElementById('newOrderDrawer');
            const newOrderDrawer = bootstrap.Offcanvas.getOrCreateInstance(newOrderDrawerEl);
            const orderModeField = document.getElementById('orderModeField');
            const registeredClientBlock = document.getElementById('registeredClientBlock');
            const walkinClientBlock = document.getElementById('walkinClientBlock');
            const newOrderForm = document.getElementById('newOrderForm');
            const orderRowsContainer = document.getElementById('orderRowsContainer');
            const btnAddOrderRow = document.getElementById('btnAddOrderRow');
            const discountTypeSelect = document.getElementById('discountTypeSelect');
            const fulfillmentTypeSelect = document.getElementById('fulfillmentTypeSelect');
            const deliveryAddressWrap = document.getElementById('deliveryAddressWrap');

            let rowIndex = 0;

            function productsForCategory(catId) {
                return PRODUCTS_DATA.filter(p => String(p.category_id) === String(catId));
            }

            function addOrderRow() {
                const row = document.createElement('div');
                row.className = 'row g-2 mb-2 align-items-center order-row';

                const catOptions = CATEGORIES_DATA.map(c => `<option value="${c.category_id}">${c.name}</option>`).join('');
                row.innerHTML = `
        <div class="col-4">
            <select class="form-select form-select-sm row-category">
                <option value="">Select...</option>${catOptions}
            </select>
        </div>
        <div class="col-3">
            <select class="form-select form-select-sm row-product" name="items[]" disabled>
                <option value="">Select category first</option>
            </select>
        </div>
        <div class="col-2">
            <input type="number" class="form-control form-control-sm row-qty" name="qtys[]" min="1" value="1" disabled>
        </div>
        <div class="col-2 text-end row-subtotal fw-bold">₱0.00</div>
        <div class="col-1 text-end">
            <button type="button" class="btn btn-sm btn-link text-danger btn-remove-row"><i class="fas fa-times"></i></button>
        </div>`;
                orderRowsContainer.appendChild(row);

                row.querySelector('.row-category').addEventListener('change', function() {
                    const productSelect = row.querySelector('.row-product');
                    const qtyInput = row.querySelector('.row-qty');
                    const opts = productsForCategory(this.value);
                    productSelect.innerHTML = opts.length ?
                        `<option value="">Select product</option>` + opts.map(p =>
                            `<option value="${p.product_id}" data-price="${p.latest_sell_price || 0}">${p.name} (${p.total_stock ?? 0} in stock)</option>`).join('') :
                        `<option value="">No products</option>`;
                    productSelect.disabled = !opts.length;
                    qtyInput.disabled = !opts.length;
                    updateRowSubtotal(row);
                });

                row.querySelector('.row-product').addEventListener('change', () => updateRowSubtotal(row));
                row.querySelector('.row-qty').addEventListener('input', () => updateRowSubtotal(row));
                row.querySelector('.btn-remove-row').addEventListener('click', function() {
                    row.remove();
                    recalcPreview();
                });
            }

            function updateRowSubtotal(row) {
                const productSelect = row.querySelector('.row-product');
                const qty = parseInt(row.querySelector('.row-qty').value || 0, 10);
                const selected = productSelect.options[productSelect.selectedIndex];
                const price = selected ? parseFloat(selected.dataset.price || 0) : 0;
                row.querySelector('.row-subtotal').textContent = peso(price * qty);
                recalcPreview();
            }

            function recalcPreview() {
                let gross = 0;
                orderRowsContainer.querySelectorAll('.order-row').forEach(row => {
                    const productSelect = row.querySelector('.row-product');
                    const selected = productSelect.options[productSelect.selectedIndex];
                    const price = selected ? parseFloat(selected.dataset.price || 0) : 0;
                    const qty = parseInt(row.querySelector('.row-qty').value || 0, 10);
                    gross += price * qty;
                });

                const type = discountTypeSelect.value;
                let discount = 0,
                    vatExclusive, vat, total, label = '';

                if (type === 'pwd' || type === 'senior') {
                    vatExclusive = gross / 1.12;
                    discount = vatExclusive * 0.20;
                    total = vatExclusive - discount;
                    vat = 0;
                    label = ' (20% + VAT Exempt)';
                } else {
                    let percent = 0;
                    if (type === 'school') {
                        percent = SCHOOL_DISCOUNT_RATE;
                        label = ` (School ${percent}%)`;
                    } else if (type === 'custom') {
                        percent = parseFloat(document.querySelector('[name="discount_percent"]').value || 0);
                        label = ` (${percent}%)`;
                    }
                    discount = gross * (percent / 100);
                    const net = gross - discount;
                    vat = net - (net / 1.12);
                    vatExclusive = net / 1.12;
                    total = net;
                }

                document.getElementById('previewGross').textContent = peso(gross);
                document.getElementById('previewDiscount').textContent = '-' + peso(discount);
                document.getElementById('previewDiscountLabel').textContent = label;
                document.getElementById('previewSubtotal').textContent = peso(vatExclusive);
                document.getElementById('previewVat').textContent = peso(vat);
                document.getElementById('previewTotal').textContent = peso(total);
            }

            discountTypeSelect.addEventListener('change', function() {
                document.getElementById('discountIdWrap').style.display = ['pwd', 'senior'].includes(this.value) ? '' : 'none';
                document.getElementById('discountHolderWrap').style.display = ['pwd', 'senior'].includes(this.value) ? '' : 'none';
                document.getElementById('discountCustomWrap').style.display = this.value === 'custom' ? '' : 'none';
                document.getElementById('discountSchoolWrap').style.display = this.value === 'school' ? '' : 'none';
                document.getElementById('schoolRateDisplay').textContent = SCHOOL_DISCOUNT_RATE;
                recalcPreview();
            });
            document.querySelector('[name="discount_percent"]').addEventListener('input', recalcPreview);

            fulfillmentTypeSelect.addEventListener('change', function() {
                deliveryAddressWrap.style.display = this.value === 'pickup' ? 'none' : '';
            });

            btnAddOrderRow.addEventListener('click', addOrderRow);

            document.getElementById('btnWalkinSale').addEventListener('click', function() {
                newOrderForm.reset();
                orderModeField.value = 'walkin';
                registeredClientBlock.style.display = 'none';
                walkinClientBlock.style.display = '';
                orderRowsContainer.innerHTML = '';
                rowIndex = 0;
                addOrderRow();
                recalcPreview();
                newOrderDrawer.show();
            });

            let typingTimer;
            liveSearch.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => filterForm.submit(), 600);
            });
            typeFilter.addEventListener('change', function() {
                filterForm.submit();
            });

            function peso(n) {
                return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
            }

            function buildInvoiceHtml(o, items, store) {
                const isPickup = o.fulfillment_type === 'pickup';

                const rows = items.map(i => `
            <tr>
                <td>${i.name}<br><small>${i.barcode_value || '—'}</small></td>
                <td style="text-align:center;">${i.quantity}</td>
                <td style="text-align:right;">${peso(i.unit_price)}</td>
                <td style="text-align:right;">${peso(i.subtotal)}</td>
            </tr>`).join('');

                const discountLine = (o.discount && parseFloat(o.discount) > 0) ? `
            <tr><td colspan="3" style="text-align:right;">Discount (${o.discount_type ? o.discount_type.toUpperCase() : ''})</td><td style="text-align:right;">-${peso(o.discount)}</td></tr>` : '';

                const discountHolderLine = o.discount_holder_name ? `
            <p style="font-size:10px;">Discount ID Holder: ${o.discount_holder_name} (${o.discount_id_number || 'N/A'})</p>` : '';

                const fulfillmentRow = isPickup ?
                    `<tr><td colspan="2"><b>Fulfillment:</b> Store Pickup — client to claim in person</td></tr>` :
                    `<tr><td colspan="2"><b>Delivery Address:</b> ${o.delivery_address || '—'}</td></tr>`;

                return `
            <div style="font-family: Arial, sans-serif; padding: 30px; color:#000;">
                <div style="text-align:center; margin-bottom: 20px;">
                    <h4 style="margin:0;">${store.store_name || 'Store'}</h4>
                    <p style="margin:0; font-size:11px;">${store.store_address || ''}</p>
                    <p style="margin:0; font-size:11px;">TIN: ${store.store_tin || 'N/A'} | ${store.store_phone_1 || ''}</p>
                </div>
                <hr>
                <h5 style="text-align:center;">SALES INVOICE</h5>
                <table style="width:100%; font-size:11px; margin-bottom:15px;">
                    <tr><td><b>Invoice No:</b> ${o.invoice_number || o.order_number}</td><td style="text-align:right;"><b>Date:</b> ${o.created_at}</td></tr>
                    <tr><td><b>Order No:</b> ${o.order_number}</td><td style="text-align:right;"><b>Status:</b> ${o.status.toUpperCase()}</td></tr>
                    <tr><td colspan="2"><b>Billed To:</b> ${o.organization}${o.guest_client_id ? ' (Walk-in — No Account)' : ''}</td></tr>
                    <tr><td colspan="2">${o.client_addr || ''} ${o.phone ? '| ' + o.phone : ''} ${o.client_tin ? '| TIN: ' + o.client_tin : ''}</td></tr>
                    ${fulfillmentRow}
                    <tr><td><b>Payment Method:</b> ${(o.payment_method || '').toUpperCase()}</td><td style="text-align:right;"><b>Payment Status:</b> ${o.payment_status.toUpperCase()}</td></tr>
                </table>
                ${discountHolderLine}
                <table style="width:100%; border-collapse: collapse; font-size:11px;" border="1" cellpadding="6">
                    <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr><td colspan="3" style="text-align:right;">Subtotal (VAT-exclusive)</td><td style="text-align:right;">${peso(o.subtotal)}</td></tr>
                        <tr><td colspan="3" style="text-align:right;">VAT</td><td style="text-align:right;">${peso(o.vat_amount)}</td></tr>
                        ${discountLine}
                        <tr><td colspan="3" style="text-align:right;"><b>TOTAL</b></td><td style="text-align:right;"><b>${peso(o.total)}</b></td></tr>
                    </tfoot>
                </table>
                <div style="margin-top: 60px; display:flex; justify-content:space-between; font-size:11px;">
                    <div>_____________________<br>Prepared By: ${o.encoder || 'System'}</div>
                    <div>_____________________<br>Received By</div>
                </div>
            </div>`;
            }

            function printInvoice(o, items, store) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`<html><head><title>${o.invoice_number || o.order_number}</title></head><body>${buildInvoiceHtml(o, items, store)}</body></html>`);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }

            const viewBtns = document.querySelectorAll('.btn-view-so');
            const soDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('soDrawer'));
            const content = document.getElementById('soDrawerContent');

            viewBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const id = this.getAttribute('data-id');
                                    soDrawer.show();
                                    content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

                                    fetch(`${BASE_URL}/admin/sales/get-order-details/${id}`)
                                        .then(async res => {
                                            const text = await res.text();
                                            try {
                                                return JSON.parse(text);
                                            } catch (e) {
                                                console.error('[Sales Orders] Server did not return valid JSON:', text);
                                                throw new Error('Server returned an unexpected response — check the console for details.');
                                            }
                                        })
                                        .then(data => {
                                                if (data.error) {
                                                    content.innerHTML = `<div class="alert alert-danger m-3 small text-center">${data.error}</div>`;
                                                    return;
                                                }
                                                const o = data.order;
                                                const items = data.items;
                                                const store = data.store_info;
                                                const isPickup = o.fulfillment_type === 'pickup';
                                                const isPaid = o.payment_status === 'paid'; // was MISSING — this was the actual bug

                                                const fulfillmentBadge = isPickup ?
                                                    `<span class="badge bg-info text-dark"><i class="fas fa-store me-1"></i>Store Pickup</span>` :
                                                    `<span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Delivery</span>`;

                                                const originBadge = o.guest_client_id ?
                                                    `<span class="badge bg-secondary ms-1"><i class="fas fa-user-plus me-1"></i>Walk-in — No Account</span>` : '';

                                                const fulfillmentRow = isPickup ?
                                                    `<div class="col-12"><small class="info-label">Fulfillment</small><p class="mb-0">Store Pickup — client to claim in person</p></div>` :
                                                    `<div class="col-12"><small class="info-label">Delivery Address</small><p class="mb-0">${o.delivery_address || '—'}</p></div>`;

                                                const itemsHtml = items.map(i => `
                        <tr>
                            <td style="border-bottom:1px solid #eee; padding:8px;"><b>${i.name}</b><br><small>${i.barcode_value || '—'}</small></td>
                            <td style="border-bottom:1px solid #eee; padding:8px; text-align:center;">${i.quantity}</td>
                            <td style="border-bottom:1px solid #eee; padding:8px; text-align:right;">${peso(i.unit_price)}</td>
                            <td style="border-bottom:1px solid #eee; padding:8px; text-align:right;">${peso(i.subtotal)}</td>
                        </tr>`).join('');

                                                // Status progression
                                                let statusActionHtml = '';
                                                if (o.status === 'pending') {
                                                    if (isPickup) {
                                                        statusActionHtml = `
            <form action="${BASE_URL}/admin/sales/update-order-status" method="POST" class="mt-3">
                <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                <input type="hidden" name="order_id" value="${o.order_id}">
                <input type="hidden" name="status" value="ready_for_pickup">
                <button type="submit" class="btn btn-info text-dark w-100"><i class="fas fa-box-open me-2"></i>Mark Ready for Pickup</button>
            </form>`;
                                                    } else {
                                                        const blocked = ['cheque', 'bank_transfer'].includes(o.payment_method) && !isPaid;
                                                        statusActionHtml = blocked ? `
            <div class="alert alert-warning small mt-3 mb-0"><i class="fas fa-lock me-1"></i>Confirm payment before this order can be dispatched — paid via ${(o.payment_method||'').replace('_',' ').toUpperCase()}.</div>` : `
            <form action="${BASE_URL}/admin/sales/update-order-status" method="POST" class="mt-3">
                <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                <input type="hidden" name="order_id" value="${o.order_id}">
                <input type="hidden" name="status" value="out_for_delivery">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-truck me-2"></i>Dispatch for Delivery</button>
            </form>`;
                                                    }
                                                } else if (o.status === 'ready_for_pickup') {
                                                    // Pickup — settled in person at the counter; disabled until admin confirms payment below.
                                                    statusActionHtml = `
        <form action="${BASE_URL}/admin/sales/update-order-status" method="POST" class="mt-3">
            <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
            <input type="hidden" name="order_id" value="${o.order_id}">
            <input type="hidden" name="status" value="delivered">
            <button type="submit" class="btn btn-success w-100" ${!isPaid ? 'disabled title="Confirm payment first"' : ''}>
                <i class="fas fa-check-circle me-2"></i>Mark as Picked Up
            </button>
        </form>
        ${!isPaid ? `<p class="text-muted small mt-1 mb-0"><i class="fas fa-info-circle me-1"></i>Confirm payment below once the client arrives, then mark picked up.</p>` : ''}`;
                                                                                                } else if (o.status === 'out_for_delivery') {
                                                    statusActionHtml = `
        <div class="alert alert-info small mt-3 mb-2"><i class="fas fa-hourglass-half me-1"></i>Waiting for the client to confirm receipt in their portal. Flagged items will automatically appear in Sales Returns once they do.</div>
        <form action="${BASE_URL}/admin/sales/update-order-status" method="POST">
            <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
            <input type="hidden" name="order_id" value="${o.order_id}">
            <input type="hidden" name="status" value="delivered">
            <button type="submit" class="btn btn-outline-dark w-100 btn-sm" onclick="return confirm('This marks the order Delivered WITHOUT client confirmation — use only if the client cannot access the portal. Continue?')">
                <i class="fas fa-exclamation-triangle me-2"></i>Force Mark Delivered (No Client Verification)
            </button>
        </form>`;
                                                } else if (o.status === 'return_pending') {
                                                    statusActionHtml = `
        <div class="alert alert-warning small mt-3 mb-0"><i class="fas fa-undo-alt me-1"></i>The client reported an issue with this delivery. Review and resolve it under <b>Sales Returns</b> — approving the return there will finalize this order's outcome.</div>`;
                                                }
                                                // Confirm Payment Received — gated so it never shows for pickup
                                                // before the order is actually Ready for Pickup.
                                                const needsAdminPaymentUI = isPickup
                                                    ? (o.status === 'ready_for_pickup' && !isPaid)
                                                    : !isPaid;

                                                const paymentFormHtml = needsAdminPaymentUI ? `
    <form action="${BASE_URL}/admin/sales/confirm-payment" method="POST" class="p-3 bg-light rounded-4 mt-3">
        <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
        <input type="hidden" name="order_id" value="${o.order_id}">
        <label class="formal-label">Confirm Payment Received</label>
        ${o.client_payment_ref ? `<div class="alert alert-info py-2 small mb-2"><i class="fas fa-info-circle me-1"></i>Client submitted reference: <b>${o.client_payment_ref}</b> on ${o.client_payment_submitted_at || ''}</div>` : ''}
        <select name="payment_method" class="form-select formal-input mb-2" required>
            <option value="" disabled ${!o.payment_method ? 'selected' : ''}>Select method actually used</option>
            <option value="cash" ${o.payment_method === 'cash' ? 'selected' : ''}>Cash</option>
            <option value="bank_transfer" ${o.payment_method === 'bank_transfer' ? 'selected' : ''}>Bank Transfer</option>
            <option value="cheque" ${o.payment_method === 'cheque' ? 'selected' : ''}>Cheque</option>
        </select>
        <input type="text" name="payment_reference" class="formal-input mb-2" value="${o.client_payment_ref || ''}" placeholder="Reference # (required for bank transfer/cheque)">
        <button type="submit" class="btn btn-success w-100">✓ Mark as Paid</button>
    </form>` : '';

                                                content.innerHTML = `
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">${o.order_number}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                                <div class="col-6"><small class="info-label">Organization</small><p class="mb-0 fw-bold">${o.organization}</p></div>
                                <div class="col-6 text-end"><small class="info-label">Total Amount</small><h5 class="fw-bold text-maroon">${peso(o.total)}</h5></div>
                                <div class="col-6"><small class="info-label">Payment</small><p class="mb-0">${(o.payment_method||'').toUpperCase()} — ${o.payment_status.toUpperCase()}</p></div>
                                <div class="col-6 text-end"><small class="info-label">Status</small><p class="mb-0">${o.status === 'return_pending' ? 'RETURN UNDER REVIEW' : o.status.replace('_',' ').toUpperCase()}</p></div>
                                <div class="col-12 mb-1">${fulfillmentBadge}${originBadge}</div>
                                ${fulfillmentRow}
                            </div>
                            <table class="table table-sm border-bottom" style="font-size:11px">
    <thead><tr class="table-dark"><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>${itemsHtml}</tbody>
</table>

                            ${paymentFormHtml}
                            ${statusActionHtml}

<div class="mt-4">
    <button class="btn btn-dark w-100 py-3 fw-bold rounded-pill" id="btnPrintInvoice">PRINT INVOICE</button>
</div>
                        </div>`;

                    document.getElementById('btnPrintInvoice').addEventListener('click', () => printInvoice(o, items, store));
                })
                .catch(err => {
                    content.innerHTML = `<div class="alert alert-danger m-3 small text-center">Could not retrieve order data.</div>`;
                    console.error(err);
                });
        });
    });
});