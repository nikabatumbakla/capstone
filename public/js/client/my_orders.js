document.addEventListener("DOMContentLoaded", function() {
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('orderDrawer'));
            const content = document.getElementById('orderDrawerContent');

            function peso(n) {
                return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
            }

            function safe(val, fallback = '—') {
                return (val === null || val === undefined || val === '') ? fallback : val;
            }

            function translateTrackingStatus(status, isPickup) {
                const pickupLabels = {
                    preparing: 'Preparing Your Order',
                    packed: 'Ready for Pickup',
                    delivered: 'Claimed / Picked Up',
                };
                const deliveryLabels = {
                    preparing: 'Preparing Your Order',
                    packed: 'Packed',
                    dispatched: 'Out for Delivery',
                    in_transit: 'In Transit',
                    delivered: 'Delivered',
                    failed: 'Delivery Failed',
                };
                const map = isPickup ? pickupLabels : deliveryLabels;
                return map[status] || status.replace('_', ' ').toUpperCase();
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
                                                const tracking = data.tracking;

                                                const isPickup = o.fulfillment_type === 'pickup';
                                                const fulfillmentBadge = isPickup ?
                                                    `<span class="badge bg-success"><i class="fas fa-store me-1"></i>Store Pickup</span>` :
                                                    `<span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Delivery</span>`;

                                                const statusColors = {
                                                    pending: 'secondary',
                                                    confirmed: 'info',
                                                    processing: 'primary',
                                                    shipped: 'warning',
                                                    delivered: 'success',
                                                    cancelled: 'danger'
                                                };
                                                const statusColor = statusColors[o.status] || 'secondary';

                                                const itemsHtml = items.map(item => `
                        <div class="d-flex align-items-center gap-3 py-3 border-bottom">
                            <div style="width:56px; height:56px; border-radius:10px; overflow:hidden; background:#f4f4f4; flex-shrink:0;">
                                ${item.image_path
                                    ? `<img src="${BASE_URL}/${item.image_path}" style="width:100%; height:100%; object-fit:cover;">`
                                    : `<div class="d-flex align-items-center justify-content-center h-100"><i class="fas fa-box-open text-muted"></i></div>`}
                            </div>
                            <div class="flex-grow-1">
                                <p class="fw-bold mb-0" style="font-size:12px;">${safe(item.name)}</p>
                                <p class="text-muted mb-0" style="font-size:10px;">SKU: ${safe(item.sku)} • ${item.quantity} ${safe(item.unit,'unit')}(s) × ${peso(item.unit_price)}</p>
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
                        </div>`}

                        ${tracking ? `
<div class="mb-4">
    <p class="info-label mb-1">Latest Status Update</p>
    <p class="info-value text-primary mb-0">${translateTrackingStatus(tracking.status, isPickup)}</p>
    ${tracking.notes ? `<p class="text-muted mb-0" style="font-size:10.5px;">${tracking.notes}</p>` : ''}
</div>` : ''}

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
});