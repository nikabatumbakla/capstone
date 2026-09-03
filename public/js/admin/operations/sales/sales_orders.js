document.addEventListener("DOMContentLoaded", function() {
            const filterForm = document.getElementById('filterForm');
            const liveSearch = document.getElementById('liveSearch');
            const typeFilter = document.getElementById('typeFilter');

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
                <td>${i.name}<br><small>${i.sku || '—'}</small></td>
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
                    <tr><td colspan="2"><b>Billed To:</b> ${o.organization}</td></tr>
                    <tr><td colspan="2">${o.client_addr || ''} ${o.phone ? '| ' + o.phone : ''} ${o.client_tin ? '| TIN: ' + o.client_tin : ''}</td></tr>
                    ${fulfillmentRow}
                    <tr><td><b>Payment Method:</b> ${o.payment_method.toUpperCase()}</td><td style="text-align:right;"><b>Payment Status:</b> ${o.payment_status.toUpperCase()}</td></tr>
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
                                        .then(res => res.json())
                                        .then(data => {
                                                if (data.error) {
                                                    content.innerHTML = `<div class="alert alert-danger m-3 small text-center">${data.error}</div>`;
                                                    return;
                                                }

                                                const o = data.order;
                                                const items = data.items;
                                                const store = data.store_info;
                                                const isPickup = o.fulfillment_type === 'pickup';

                                                const fulfillmentBadge = isPickup ?
                                                    `<span class="badge bg-success"><i class="fas fa-store me-1"></i>Store Pickup</span>` :
                                                    `<span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Delivery</span>`;

                                                const fulfillmentRow = isPickup ?
                                                    `<div class="col-12"><small class="info-label">Fulfillment</small><p class="mb-0">Store Pickup — client to claim in person</p></div>` :
                                                    `<div class="col-12"><small class="info-label">Delivery Address</small><p class="mb-0">${o.delivery_address || '—'}</p></div>`;

                                                const itemsHtml = items.map(i => `
                        <tr>
                            <td style="border-bottom:1px solid #eee; padding:8px;"><b>${i.name}</b><br><small>${i.sku || '—'}</small></td>
                            <td style="border-bottom:1px solid #eee; padding:8px; text-align:center;">${i.quantity}</td>
                            <td style="border-bottom:1px solid #eee; padding:8px; text-align:right;">${peso(i.unit_price)}</td>
                            <td style="border-bottom:1px solid #eee; padding:8px; text-align:right;">${peso(i.subtotal)}</td>
                        </tr>`).join('');

                                                content.innerHTML = `
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">${o.order_number}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                                <div class="col-6"><small class="info-label">Organization</small><p class="mb-0 fw-bold">${o.organization}</p></div>
                                <div class="col-6 text-end"><small class="info-label">Total Amount</small><h5 class="fw-bold text-maroon">${peso(o.total)}</h5></div>
                                <div class="col-6"><small class="info-label">Payment</small><p class="mb-0">${o.payment_method.toUpperCase()} — ${o.payment_status.toUpperCase()}</p></div>
                                <div class="col-6 text-end"><small class="info-label">Status</small><p class="mb-0">${o.status.toUpperCase()}</p></div>
                                <div class="col-12 mb-1">${fulfillmentBadge}</div>
                                ${fulfillmentRow}
                            </div>
                            <table class="table table-sm border-bottom" style="font-size:11px">
    <thead><tr class="table-dark"><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>${itemsHtml}</tbody>
</table>

                           ${o.payment_status !== 'paid' ? `
    <form action="${BASE_URL}/staff/operations/confirm-payment" method="POST" class="p-3 bg-light rounded-4 mt-3">
        <input type="hidden" name="order_id" value="${o.order_id}">
        <label class="formal-label">Confirm Payment Received</label>
        <input type="text" name="payment_reference" class="formal-input mb-2" placeholder="Reference # (optional for cash/pickup)">
        <button type="submit" class="btn btn-success w-100">✓ Mark as Paid</button>
    </form>` : ''}

<div class="mt-5">
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