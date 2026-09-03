document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('manageOrderDrawer'));
    const content = document.getElementById('orderDrawerContent');
    const title = document.getElementById('orderDrawerTitle');

    const searchInput = document.getElementById('liveSearch');
    if (searchInput) {
        let typingTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => document.getElementById('filterForm').submit(), 600);
        });
    }

    // ============ NEXT-STEP MAP — split by fulfillment type ============
    const deliveryStepMap = {
        pending: { next: 'processing', label: 'Start Processing', icon: 'fa-box-open' },
        confirmed: { next: 'processing', label: 'Start Processing', icon: 'fa-box-open' },
        processing: { next: 'shipped', label: 'Mark as Shipped', icon: 'fa-truck' },
        shipped: { next: 'delivered', label: 'Mark as Delivered', icon: 'fa-check-circle' },
    };
    const pickupStepMap = {
        pending: { next: 'processing', label: 'Start Processing', icon: 'fa-box-open' },
        confirmed: { next: 'processing', label: 'Start Processing', icon: 'fa-box-open' },
        processing: { next: 'shipped', label: 'Mark as Ready for Pickup', icon: 'fa-store' },
        shipped: { next: 'delivered', label: 'Mark as Claimed', icon: 'fa-check-circle' },
    };

    function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

    document.querySelectorAll('.btn-manage-order').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            title.textContent = 'Manage Order';
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            drawer.show();

            fetch(`${BASE_URL}/staff/operations/get-order-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                    const o = data.order;
                    title.textContent = o.order_number;

                    const isPickup = o.fulfillment_type === 'pickup';
                    const fulfillmentBadge = isPickup ?
                        `<span class="badge bg-success"><i class="fas fa-store me-1"></i>Store Pickup</span>` :
                        `<span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Delivery</span>`;

                    const itemsHtml = data.items.map(i => `
                        <tr>
                            <td><small class="fw-bold d-block">${i.name}</small><code class="extra-small">${i.sku || '—'}</code></td>
                            <td class="text-center">${i.quantity}</td>
                            <td class="text-end">${peso(i.subtotal)}</td>
                        </tr>`).join('');

                    const stepMap = isPickup ? pickupStepMap : deliveryStepMap;
                    const step = stepMap[o.status];
                    const actionHtml = step ? `
                        <button type="button" class="btn btn-maroon w-100 py-3 fw-bold rounded-3 shadow-sm mb-2" id="btnAdvanceStatus" data-next="${step.next}" data-id="${o.order_id}">
                            <i class="fas ${step.icon} me-2"></i>${step.label}
                        </button>
                    ` : (o.status === 'delivered' ? `
                        <div class="p-3 bg-light rounded-3 text-center text-muted"><i class="fas fa-check-circle text-success me-1"></i>${isPickup ? 'This order has been claimed.' : 'This order has been delivered.'}</div>
                    ` : `
                        <div class="p-3 bg-light rounded-3 text-center text-muted">This order is ${o.status} and cannot be advanced further.</div>
                    `);

                    const fulfillmentInfoHtml = isPickup ? `
                        <div class="col-12 mt-2">
                            <div class="p-2 rounded-3" style="background:#eefbf0; border:1px solid #c9f0d1;">
                                <small class="fw-bold" style="color:#1a7431;"><i class="fas fa-info-circle me-1"></i>Client will claim this order in-store. Verify a valid ID before releasing.</small>
                            </div>
                        </div>
                    ` : `
                        <div class="col-12"><small class="info-label">Delivery Address</small><p class="mb-0">${o.delivery_address || o.client_addr || '—'}</p></div>
                    `;

                    content.innerHTML = `
                        <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                            <div class="col-6"><small class="info-label">Client</small><p class="mb-0 fw-bold">${o.organization}</p></div>
                            <div class="col-6 text-end"><small class="info-label">Total</small><h5 class="fw-bold text-maroon mb-0">${peso(o.total)}</h5></div>
                            <div class="col-12 mb-1">${fulfillmentBadge}</div>
                            ${fulfillmentInfoHtml}
                            <div class="col-12"><small class="info-label">Contact</small><p class="mb-0">${o.phone || o.client_phone || '—'}</p></div>
                        </div>

                        <table class="table table-sm border-bottom mb-4" style="font-size:11px">
                            <thead><tr class="table-dark"><th>Product</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
                            <tbody>${itemsHtml}</tbody>
                        </table>

                        ${actionHtml}
                    `;

                    const advanceBtn = document.getElementById('btnAdvanceStatus');
                    if (advanceBtn) {
                        advanceBtn.addEventListener('click', function() {
                            const nextStatus = this.getAttribute('data-next');
                            const orderId = this.getAttribute('data-id');
                            const confirmLabel = isPickup && nextStatus === 'shipped' ? 'Ready for Pickup' : nextStatus.toUpperCase();
                            if (!confirm(`Move this order to "${confirmLabel}"?`)) return;

                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `${BASE_URL}/staff/operations/update-order-status`;
                            form.innerHTML = `<input type="hidden" name="order_id" value="${orderId}"><input type="hidden" name="status" value="${nextStatus}">`;
                            document.body.appendChild(form);
                            form.submit();
                        });
                    }
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-center text-danger p-5">Failed to load order details.</div>`;
                    console.error(err);
                });
        });
    });
});