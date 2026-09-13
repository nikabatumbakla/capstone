document.addEventListener("DOMContentLoaded", function() {
            function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

            const searchInput = document.getElementById('liveSearch');
            if (searchInput) {
                let typingTimer;
                searchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => document.getElementById('filterForm').submit(), 600);
                });
            }

            const viewBtns = document.querySelectorAll('.btn-view-so');
            const soDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('soDrawer'));
            const content = document.getElementById('soDrawerContent');

            viewBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const id = this.getAttribute('data-id');
                                    soDrawer.show();
                                    content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

                                    fetch(`${BASE_URL}/staff/operations/get-order-details/${id}`)
                                        .then(res => res.json())
                                        .then(data => {
                                                if (data.error) { content.innerHTML = `<div class="alert alert-danger m-3 small text-center">${data.error}</div>`; return; }
                                                const o = data.order;
                                                const items = data.items;
                                                const isPickup = o.fulfillment_type === 'pickup';
                                                const isPaid = o.payment_status === 'paid';

                                                const fulfillmentBadge = isPickup ?
                                                    `<span class="badge bg-info text-dark"><i class="fas fa-store me-1"></i>Store Pickup</span>` :
                                                    `<span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Delivery</span>`;
                                                const originBadge = o.guest_client_id ? `<span class="badge bg-secondary ms-1">Walk-in — No Account</span>` : '';

                                                const itemsHtml = items.map(i => `
                        <tr><td>${i.name}<br><small>${i.barcode_value || '—'}</small></td><td class="text-center">${i.quantity}</td><td class="text-end">${peso(i.unit_price)}</td><td class="text-end">${peso(i.subtotal)}</td></tr>
                    `).join('');

                                                let statusActionHtml = '';
                                                if (o.status === 'pending') {
                                                    if (isPickup) {
                                                        statusActionHtml = `
                                <form action="${BASE_URL}/staff/operations/update-order-status" method="POST" class="mt-3">
                                    <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                                    <input type="hidden" name="order_id" value="${o.order_id}">
                                    <input type="hidden" name="status" value="ready_for_pickup">
                                    <button type="submit" class="btn btn-info text-dark w-100"><i class="fas fa-box-open me-2"></i>Mark Ready for Pickup</button>
                                </form>`;
                                                    } else {
                                                        const blocked = ['cheque', 'bank_transfer'].includes(o.payment_method) && !isPaid;
                                                        statusActionHtml = blocked ? `
                                <div class="alert alert-warning small mt-3 mb-0"><i class="fas fa-lock me-1"></i>Confirm payment before this order can be dispatched.</div>` : `
                                <form action="${BASE_URL}/staff/operations/update-order-status" method="POST" class="mt-3">
                                    <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                                    <input type="hidden" name="order_id" value="${o.order_id}">
                                    <input type="hidden" name="status" value="out_for_delivery">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-truck me-2"></i>Dispatch for Delivery</button>
                                </form>`;
                                                    }
                                                } else if (o.status === 'ready_for_pickup') {
                                                    statusActionHtml = `
                            <form action="${BASE_URL}/staff/operations/update-order-status" method="POST" class="mt-3">
                                <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                                <input type="hidden" name="order_id" value="${o.order_id}">
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="btn btn-success w-100" ${!isPaid ? 'disabled title="Confirm payment first"' : ''}><i class="fas fa-check-circle me-2"></i>Mark as Picked Up</button>
                            </form>
                            ${!isPaid ? `<p class="text-muted small mt-1 mb-0">Confirm payment below once the client arrives.</p>` : ''}`;
                    } else if (o.status === 'out_for_delivery') {
                        statusActionHtml = `<div class="alert alert-info small mt-3 mb-0"><i class="fas fa-hourglass-half me-1"></i>Waiting for the client to confirm receipt in their portal.</div>`;
                    } else if (o.status === 'return_pending') {
                        statusActionHtml = `<div class="alert alert-warning small mt-3 mb-0"><i class="fas fa-undo-alt me-1"></i>Client reported an issue — this needs admin review under Sales Returns.</div>`;
                    }

                    const needsPaymentUI = isPickup ? (o.status === 'ready_for_pickup' && !isPaid) : !isPaid;
                    const paymentFormHtml = needsPaymentUI ? `
                        <form action="${BASE_URL}/staff/operations/confirm-payment" method="POST" class="p-3 bg-light rounded-4 mt-3">
                            <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                            <input type="hidden" name="order_id" value="${o.order_id}">
                            <label class="formal-label">Confirm Payment Received</label>
                            ${o.client_payment_ref ? `<div class="alert alert-info py-2 small mb-2">Client submitted reference: <b>${o.client_payment_ref}</b></div>` : ''}
                            <select name="payment_method" class="form-select formal-input mb-2" required>
                                <option value="" disabled selected>Select method used</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                            <input type="text" name="payment_reference" class="formal-input mb-2" value="${o.client_payment_ref || ''}" placeholder="Reference # (bank transfer/cheque)">
                            <button type="submit" class="btn btn-success w-100">✓ Mark as Paid</button>
                        </form>` : '';

                    content.innerHTML = `
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">${o.order_number}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                                <div class="col-6"><small class="info-label">Client</small><p class="mb-0 fw-bold">${o.organization}</p></div>
                                <div class="col-6 text-end"><small class="info-label">Total</small><h5 class="fw-bold text-maroon">${peso(o.total)}</h5></div>
                                <div class="col-12 mb-1">${fulfillmentBadge}${originBadge}</div>
                                ${!isPickup ? `<div class="col-12"><small class="info-label">Delivery Address</small><p class="mb-0">${o.delivery_address || o.client_addr || '—'}</p></div>` : `<div class="col-12"><div class="p-2 rounded-3" style="background:#eefbf0; border:1px solid #c9f0d1;"><small class="fw-bold" style="color:#1a7431;"><i class="fas fa-info-circle me-1"></i>Client will claim this order in-store. Verify a valid ID before releasing.</small></div></div>`}
                                <div class="col-12"><small class="info-label">Contact</small><p class="mb-0">${o.phone || '—'}</p></div>
                            </div>
                            <table class="table table-sm" style="font-size:11px"><thead><tr class="table-dark"><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit</th><th class="text-end">Subtotal</th></tr></thead><tbody>${itemsHtml}</tbody></table>
                            ${paymentFormHtml}
                            ${statusActionHtml}
                        </div>`;
                })
                .catch(err => { content.innerHTML = `<div class="alert alert-danger m-3 small text-center">Could not load order data.</div>`; console.error(err); });
        });
    });
});