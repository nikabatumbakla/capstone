document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('invoiceDrawer'));
    const content = document.getElementById('invoiceDrawerContent');

    function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

    document.querySelectorAll('.btn-view-invoice').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            drawer.show();

            fetch(`${BASE_URL}/client/account/invoices/get-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }
                    const o = data.order;
                    const vatAmount = o.vat_amount;

                    const itemsHtml = data.items.map(i => `
                        <tr><td>${i.name}</td><td class="text-center">${i.quantity}</td><td class="text-end">${peso(i.unit_price)}</td><td class="text-end">${peso(i.subtotal)}</td></tr>
                    `).join('');

                    let paymentBlock = '';
                    if (o.fulfillment_method === 'pickup') {
                        paymentBlock = `<div class="p-3 bg-light rounded-4 mt-4"><p class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>This is a pickup order — payment will be confirmed by our staff when you collect your items.</p></div>`;
                    } else if (o.payment_status === 'unpaid') {
                        paymentBlock = `
        <form action="${BASE_URL}/client/account/invoices/submit-payment" method="POST" class="p-3 bg-light rounded-4 mt-4">
            <input type="hidden" name="order_id" value="${o.order_id}">
            <label class="formal-label">Payment Reference (Check # / GCash Ref) *</label>
            <input type="text" name="payment_reference" class="formal-input" placeholder="Enter your payment reference" required>
            <button type="submit" class="btn btn-dark w-100 mt-2">Submit Payment Reference</button>
        </form>`;
                    } else if (o.payment_status === 'submitted') {
                        paymentBlock = `<div class="p-3 bg-light rounded-4 mt-4"><p class="mb-0"><b>Reference submitted:</b> ${o.payment_reference}</p><small class="text-muted">Being verified by Robin Rose Trading.</small></div>`;
                    } else {
                        paymentBlock = `<div class="p-3 bg-light rounded-4 mt-4 text-success"><i class="fas fa-check-circle me-2"></i>Payment confirmed.</div>`;
                    }

                    content.innerHTML = `
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">${o.invoice_number || 'Invoice'}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                                <div class="col-6"><small class="info-label">Order #</small><p class="mb-0">${o.order_number}</p></div>
                                <div class="col-6"><small class="info-label">Total</small><h5 class="fw-bold text-maroon mb-0">${peso(o.total)}</h5></div>
                                <div class="col-6"><small class="info-label">VAT (12%)</small><p class="mb-0">${peso(vatAmount)}</p></div>
                                <div class="col-6"><small class="info-label">Status</small><p class="mb-0">${o.payment_status.toUpperCase()}</p></div>
                            </div>
                            <table class="table table-sm" style="font-size:11px"><thead><tr class="table-dark"><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit</th><th class="text-end">Subtotal</th></tr></thead><tbody>${itemsHtml}</tbody></table>
                            ${paymentBlock}
                        </div>`;
                })
                .catch(() => { content.innerHTML = `<div class="text-danger text-center p-5">Failed to load invoice.</div>`; });
        });
    });
});