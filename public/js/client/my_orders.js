document.addEventListener("DOMContentLoaded", function() {
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('orderDrawer'));
            const content = document.getElementById('orderDrawerContent');

            // ============ REPORT AN ISSUE DRAWER (post-delivery) ============
            const issueDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('issueDrawer'));
            const issueContent = document.getElementById('issueDrawerContent');

            document.querySelectorAll('.btn-report-issue').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    issueContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                    issueDrawer.show();

                    fetch(`${BASE_URL}/client/orders/get-issue-report-info/${orderId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) { issueContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                            const order = data.order;
                            const items = data.items;

                            const rowsHtml = items.map(i => `
                        <tr>
                            <td class="fw-bold">
                                ${i.name}<br><small class="text-muted">${i.barcode_value || '—'}</small>
                                <input type="hidden" name="product_id[]" value="${i.product_id}">
                                <input type="hidden" name="batch_id[]" value="${i.batch_id || ''}">
                                <input type="hidden" name="qty_ordered[]" value="${i.quantity}">
                            </td>
                            <td class="text-center">${i.quantity} ${i.unit || ''}</td>
                            <td>
                                <select name="condition[]" class="form-select form-select-sm issue-condition" data-row="${i.product_id}">
                                    <option value="good" selected>No issue</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="wrong_item">Wrong Item</option>
                                    <option value="missing">Never Received</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="qty_flagged[]" class="form-control form-control-sm issue-qty-flagged d-none" data-row="${i.product_id}" min="0" max="${i.quantity}" value="0" style="width:90px">
                            </td>
                        </tr>`).join('');

                            issueContent.innerHTML = `
                        <p class="fw-bold mb-3">Order ${order.order_number} — What Went Wrong?</p>
                        <form action="${BASE_URL}/client/orders/process-report-issue" method="POST" id="reportIssueForm">
                            <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                            <input type="hidden" name="order_id" value="${order.order_id}">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle" style="font-size:10px">
                                    <thead class="table-dark"><tr><th>Product</th><th class="text-center">Qty</th><th>Issue</th><th>Qty Affected</th></tr></thead>
                                    <tbody>${rowsHtml}</tbody>
                                </table>
                            </div>
                            <p class="helper-text mb-4">Flag only the item(s) that had a problem. This will be sent to Robin Rose Trading for review.</p>
                            <button type="submit" class="btn btn-danger w-100 py-3 fw-bold rounded-pill shadow">SUBMIT REPORT</button>
                        </form>`;

                            document.querySelectorAll('.issue-condition').forEach(select => {
                                select.addEventListener('change', function() {
                                    const row = this.getAttribute('data-row');
                                    const flaggedInput = document.querySelector(`.issue-qty-flagged[data-row="${row}"]`);
                                    if (this.value === 'good') {
                                        flaggedInput.classList.add('d-none');
                                        flaggedInput.value = 0;
                                    } else { flaggedInput.classList.remove('d-none'); if (parseInt(flaggedInput.value || 0) === 0) flaggedInput.value = flaggedInput.max; }
                                });
                            });

                            document.getElementById('reportIssueForm').addEventListener('submit', function(e) {
                                const anyFlagged = Array.from(document.querySelectorAll('.issue-condition')).some(s => s.value !== 'good');
                                if (!anyFlagged) {
                                    e.preventDefault();
                                    alert('Please select an issue for at least one item.');
                                }
                            });
                        })
                        .catch(err => {
                            issueContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load order details.</div>`;
                            console.error(err);
                        });
                });
            });

            function peso(n) {
                return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
            }

            function safe(val, fallback = '—') {
                return (val === null || val === undefined || val === '') ? fallback : val;
            }

            // Reflects the REAL lifecycle used throughout the system —
            // pending -> ready_for_pickup / out_for_delivery -> delivered (or cancelled)
            function statusMeta(status, isPickup) {
                const labels = {
                    pending: 'Pending — Being Prepared',
                    ready_for_pickup: 'Ready for Pickup',
                    out_for_delivery: 'Out for Delivery',
                    delivered: isPickup ? 'Order Completed' : 'Delivered',
                    cancelled: 'Cancelled',
                };
                const colors = {
                    pending: '#6c757d',
                    ready_for_pickup: '#0dcaf0',
                    out_for_delivery: '#0d6efd',
                    delivered: '#198754',
                    cancelled: '#dc3545',
                };
                return {
                    label: labels[status] || status.replace(/_/g, ' ').toUpperCase(),
                    color: colors[status] || '#6c757d',
                };
            }

            document.querySelectorAll('.btn-view-order').forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const id = this.getAttribute('data-id');
                                    content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                                    drawer.show();

                                    fetch(`${BASE_URL}/client/orders/get-order-details/${id}`)
                                        .then(res => res.json())
                                        .then(data => {
                                                if (data.error) {
                                                    content.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                                                    return;
                                                }

                                                const o = data.order;
                                                const items = data.items || [];

                                                const isPickup = o.fulfillment_type === 'pickup';
                                                const fulfillmentBadge = isPickup ?
                                                    `<span class="badge bg-success"><i class="fas fa-store me-1"></i>Store Pickup</span>` :
                                                    `<span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Delivery</span>`;

                                                const sm = statusMeta(o.status, isPickup);
                                                const statusBadge = `<span class="badge" style="background:${sm.color};">${sm.label}</span>`;

                                                const isPaid = o.payment_status === 'paid';
                                                const paymentBadge = isPaid ?
                                                    `<span class="badge bg-success">PAID</span>` :
                                                    `<span class="badge bg-danger">UNPAID</span>`;

                                                const needsPaymentSubmission = !isPickup && ['cheque', 'bank_transfer'].includes(o.payment_method) &&
                                                    !isPaid;

                                                const paymentNote = needsPaymentSubmission ? (
                                                    o.client_payment_submitted_at ?
                                                    `<p class="text-muted mb-0 mt-1" style="font-size:10px;"><i class="fas fa-hourglass-half me-1"></i>Reference submitted — awaiting confirmation.</p>` :
                                                    `<p class="text-danger mb-0 mt-1" style="font-size:10px;"><i class="fas fa-exclamation-circle me-1"></i>Payment reference not yet submitted for this delivery order.</p>`
                                                ) : '';

                                                const itemsHtml = items.map(item => `
                        <div class="d-flex align-items-center gap-3 py-3 border-bottom">
                            <div style="width:56px; height:56px; border-radius:10px; overflow:hidden; background:#f4f4f4; flex-shrink:0;">
                                ${item.image_path
                                    ? `<img src="${BASE_URL}/${item.image_path}" style="width:100%; height:100%; object-fit:cover;">`
                                    : `<div class="d-flex align-items-center justify-content-center h-100"><i class="fas fa-box-open text-muted"></i></div>`}
                            </div>
                            <div class="flex-grow-1">
                                <p class="fw-bold mb-0" style="font-size:12px;">${safe(item.name)}</p>
                                <p class="text-muted mb-0" style="font-size:10px;">${safe(item.barcode_value)} • ${item.quantity} ${safe(item.unit,'unit')}(s) × ${peso(item.unit_price)}</p>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold mb-0" style="font-size:12px;">${peso(item.subtotal)}</p>
                            </div>
                        </div>
                    `).join('');

                    content.innerHTML = `
                    <div class="p-4 border-bottom bg-light">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-1">${safe(o.order_number)}</h5>
                                <p class="text-muted mb-0" style="font-size:10.5px;">Invoice: ${safe(o.invoice_number)}</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="d-flex gap-2 mb-3">${fulfillmentBadge}${statusBadge}${paymentBadge}</div>

                        <p class="info-label mb-2">Order Placed</p>
                        <p class="info-value mb-4">${new Date(o.created_at.replace(' ','T')).toLocaleString('en-US', { month:'short', day:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit', hour12:true })}</p>

                        ${isPickup ? `
                        <div class="p-3 rounded-4 mb-4" style="background:#eefbf0; border:1px solid #c9f0d1;">
                            <p class="fw-bold mb-1" style="font-size:12px; color:#1a7431;"><i class="fas fa-store me-2"></i>Store Pickup</p>
                            <p class="mb-0" style="font-size:11px; color:#333;">
                                This order will be claimed at the store. Please bring a valid ID and your order number upon pickup.
                            </p>
                        </div>` : `
                        <div class="p-3 rounded-4 mb-4" style="background:#eef6ff; border:1px solid #d3e8ff;">
                            <p class="fw-bold mb-1" style="font-size:12px; color:#0d2e4f;"><i class="fas fa-truck me-2"></i>Delivery Address</p>
                            <p class="mb-0" style="font-size:11px; color:#333;">${safe(o.delivery_address, 'No address on file.')}</p>
                            ${paymentNote}
                        </div>`}

                        <div class="row g-3 mb-4">
                            <div class="col-6"><p class="info-label mb-0">Payment Method</p><p class="info-value">${(o.payment_method || '').toUpperCase()}</p></div>
                            <div class="col-6"><p class="info-label mb-0">TIN</p><p class="info-value">${safe(o.tin)}</p></div>
                            <div class="col-6"><p class="info-label mb-0">Organization</p><p class="info-value">${safe(o.organization)}</p></div>
                            <div class="col-6"><p class="info-label mb-0">Contact Phone</p><p class="info-value">${safe(o.phone)}</p></div>
                        </div>

                        ${o.notes ? `
                        <div class="mb-4">
                            <p class="info-label mb-1">Order Notes</p>
                            <p class="info-value fw-normal">${safe(o.notes)}</p>
                        </div>` : ''}

                        <p class="fw-bold mb-2" style="font-size:12px;">Items Ordered</p>
                        <div class="mb-4">
                            ${itemsHtml}
                        </div>

                        <div class="p-3 rounded-4" style="background:#1a0505; color:#fff;">
                            <div class="d-flex justify-content-between mb-1" style="font-size:11px; opacity:0.8;">
                                <span>Subtotal</span><span>${peso(o.subtotal)}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" style="font-size:11px; opacity:0.8;">
                                <span>VAT</span><span>${peso(o.vat_amount)}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold" style="font-size:14px; border-top:1px solid rgba(255,255,255,0.15); padding-top:8px;">
                                <span>Total</span><span>${peso(o.total)}</span>
                            </div>
                        </div>
                    </div>`;
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-center text-danger p-5">Failed to load order details.</div>`;
                    console.error(err);
                });
        });
    });

    // ============ SUBMIT PAYMENT DRAWER ============
    const paymentDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('paymentDrawer'));
    const paymentContent = document.getElementById('paymentDrawerContent');

    document.querySelectorAll('.btn-submit-payment').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            paymentContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            paymentDrawer.show();

            fetch(`${BASE_URL}/client/orders/get-payment-info/${orderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { paymentContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                    const o = data.order;
                    const store = data.store_info;

                    paymentContent.innerHTML = `
                        <p class="fw-bold mb-2" style="font-size:12px;">Send Payment To</p>
                        <div class="p-3 bg-light rounded-4 mb-3">
                            <small class="info-label">Bank</small><p class="mb-2 fw-bold">${store.store_bank_name || 'Not set'}</p>
                            <small class="info-label">Account Name</small><p class="mb-2 fw-bold">${store.store_bank_account_name || 'Not set'}</p>
                            <small class="info-label">Account Number</small><p class="mb-0 fw-bold fs-5">${store.store_bank_account_number || 'Not set'}</p>
                        </div>
                        <p class="text-muted mb-4" style="font-size:10px;">Amount Due: <b class="text-maroon">${peso(o.total)}</b> · Order ${o.order_number} · Paying via ${o.payment_method.replace('_',' ').toUpperCase()}</p>

                        <p class="fw-bold mb-2" style="font-size:12px;">Submit Your Payment Reference</p>
                        <form action="${BASE_URL}/client/orders/process-submit-payment" method="POST">
                            <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                            <input type="hidden" name="order_id" value="${o.order_id}">
                            <div class="mb-3">
                                <label class="formal-label">Reference / Cheque Number *</label>
                                <input type="text" name="reference" class="formal-input" placeholder="e.g. Bank transaction ref or cheque number" required>
                            </div>
                            <p class="helper-text mb-3">Once submitted, Robin Rose Trading will verify the payment landed and confirm it in your order status.</p>
                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-pill shadow">✓ SUBMIT REFERENCE</button>
                        </form>`;
                })
                .catch(err => {
                    paymentContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load payment details.</div>`;
                    console.error(err);
                });
        });
    });

        // ============ CONFIRM RECEIPT DRAWER ============
    const receiptDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('receiptDrawer'));
    const receiptContent = document.getElementById('receiptDrawerContent');

    document.querySelectorAll('.btn-confirm-receipt').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            receiptContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            receiptDrawer.show();

            fetch(`${BASE_URL}/client/orders/get-confirm-receipt-info/${orderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { receiptContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                    const order = data.order;
                    const items = data.items;

                    const rowsHtml = items.map(i => `
                        <tr>
                            <td class="fw-bold">
                                ${i.name}<br><small class="text-muted">${i.barcode_value || '—'}</small>
                                <input type="hidden" name="product_id[]" value="${i.product_id}">
                                <input type="hidden" name="batch_id[]" value="${i.batch_id || ''}">
                                <input type="hidden" name="qty_ordered[]" value="${i.quantity}">
                            </td>
                            <td class="text-center">${i.quantity} ${i.unit || ''}</td>
                            <td>
                                <select name="condition[]" class="form-select form-select-sm confirm-condition" data-row="${i.product_id}">
                                    <option value="good" selected>Good — as ordered</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="wrong_item">Wrong Item</option>
                                    <option value="missing">Never Received</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="qty_flagged[]" class="form-control form-control-sm confirm-qty-flagged d-none" data-row="${i.product_id}" min="0" max="${i.quantity}" value="0" style="width:90px">
                            </td>
                        </tr>`).join('');

                    receiptContent.innerHTML = `
    <p class="fw-bold mb-3">Order ${order.order_number} — Did You Receive These Correctly?</p>
    <form action="${BASE_URL}/client/orders/process-confirm-receipt" method="POST" id="confirmReceiptForm">
        <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
        <input type="hidden" name="order_id" value="${order.order_id}">
        <div class="table-responsive">
            <table class="table table-sm align-middle" style="font-size:10px">
                <thead class="table-dark"><tr><th>Product</th><th class="text-center">Qty</th><th>Condition</th><th>Qty Affected</th></tr></thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        </div>
        <p class="helper-text mb-4">If everything is marked "Good," this closes your order as delivered. If you flag any item, this order is sent to <b>My Returns</b> for review instead — nothing is marked delivered until that's resolved.</p>
        <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow"><i class="fas fa-check-double me-2"></i>SUBMIT</button>
    </form>`;

                    document.querySelectorAll('.confirm-condition').forEach(select => {
                        select.addEventListener('change', function() {
                            const row = this.getAttribute('data-row');
                            const flaggedInput = document.querySelector(`.confirm-qty-flagged[data-row="${row}"]`);
                            if (this.value === 'good') { flaggedInput.classList.add('d-none'); flaggedInput.value = 0; }
                            else { flaggedInput.classList.remove('d-none'); if (parseInt(flaggedInput.value || 0) === 0) flaggedInput.value = flaggedInput.max; }
                        });
                    });

                    document.getElementById('confirmReceiptForm').addEventListener('submit', function(e) {
                        let hasError = false;
                        document.querySelectorAll('.confirm-condition').forEach(select => {
                            if (select.value === 'good') return;
                            const row = select.getAttribute('data-row');
                            const input = document.querySelector(`.confirm-qty-flagged[data-row="${row}"]`);
                            if (parseInt(input.value || 0) <= 0) hasError = true;
                        });
                        if (hasError) { e.preventDefault(); alert('Please enter the quantity affected for every item not marked "Good".'); }
                    });
                })
                .catch(err => {
                    receiptContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load order details.</div>`;
                    console.error(err);
                });
        });
    });


});