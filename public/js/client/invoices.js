document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('invoiceDrawer'));
    const content = document.getElementById('invoiceDrawerContent');

    const paymentDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('paymentDrawer'));
    const paymentContent = document.getElementById('paymentDrawerContent');

    function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

    function buildInvoiceHtml(o, items, store) {
        const rows = items.map(i => `
        <tr>
            <td>${i.name}<br><small>${i.barcode_value || '—'}</small></td>
            <td style="text-align:center;">${i.quantity}</td>
            <td style="text-align:right;">${peso(i.unit_price)}</td>
            <td style="text-align:right;">${peso(i.subtotal)}</td>
        </tr>`).join('');

        return `
        <div style="font-family: Arial, sans-serif; padding: 30px; color:#000;">
            <div style="text-align:center; margin-bottom: 20px;">
                <h4 style="margin:0;">${store.store_name || 'Robin Rose Trading'}</h4>
                <p style="margin:0; font-size:11px;">${store.store_address || ''}</p>
                <p style="margin:0; font-size:11px;">TIN: ${store.store_tin || 'N/A'} | ${store.store_phone_1 || ''}</p>
            </div>
            <hr>
            <h5 style="text-align:center;">CLIENT COPY — SALES INVOICE</h5>
            <table style="width:100%; font-size:11px; margin-bottom:15px;">
                <tr><td><b>Invoice No:</b> ${o.invoice_number || o.order_number}</td><td style="text-align:right;"><b>Date:</b> ${o.created_at}</td></tr>
                <tr><td><b>Order No:</b> ${o.order_number}</td><td style="text-align:right;"><b>Status:</b> ${o.status.toUpperCase()}</td></tr>
                <tr><td colspan="2"><b>Billed To:</b> ${o.organization}</td></tr>
                <tr><td colspan="2">${o.address || ''} ${o.phone ? '| ' + o.phone : ''} ${o.tin ? '| TIN: ' + o.tin : ''}</td></tr>
                <tr><td><b>Payment Method:</b> ${(o.payment_method || '').toUpperCase()}</td><td style="text-align:right;"><b>Payment Status:</b> ${o.payment_status.toUpperCase()}</td></tr>
            </table>
            <table style="width:100%; border-collapse: collapse; font-size:11px;" border="1" cellpadding="6">
                <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
                <tbody>${rows}</tbody>
                <tfoot>
                    <tr><td colspan="3" style="text-align:right;">Subtotal</td><td style="text-align:right;">${peso(o.subtotal)}</td></tr>
                    <tr><td colspan="3" style="text-align:right;">VAT</td><td style="text-align:right;">${peso(o.vat_amount)}</td></tr>
                    <tr><td colspan="3" style="text-align:right;"><b>TOTAL</b></td><td style="text-align:right;"><b>${peso(o.total)}</b></td></tr>
                </tfoot>
            </table>
            <p style="margin-top:40px; font-size:10px; color:#666;">This is a client-side copy for your own records and does not replace an official BIR receipt.</p>
        </div>`;
    }

    function printInvoice(o, items, store) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`<html><head><title>${o.invoice_number || o.order_number}</title></head><body>${buildInvoiceHtml(o, items, store)}</body></html>`);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }

    // ============ SUBMIT PAYMENT DRAWER (shared with My Orders — same endpoints) ============
    // Moved to top level: was previously nested inside the view-invoice forEach,
    // which redefined it once per invoice row and could double-bind listeners.
    function openPaymentDrawer(orderId) {
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
    }

    document.querySelectorAll('.btn-submit-payment').forEach(btn => {
        btn.addEventListener('click', function() {
            openPaymentDrawer(this.getAttribute('data-id'));
        });
    });

    // ============ VIEW INVOICE ============
    document.querySelectorAll('.btn-view-invoice').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            drawer.show();

            fetch(`${BASE_URL}/client/account/get-invoice-details/${id}`)
                .then(async res => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('[Invoices] Server did not return valid JSON:', text);
                        throw new Error('Server returned an unexpected response — check the console.');
                    }
                })
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }
                    const o = data.order;
                    const vatAmount = o.vat_amount;

                    const itemsHtml = data.items.map(i => `
            <tr><td>${i.name}<br><small class="text-muted">${i.barcode_value || '—'}</small></td><td class="text-center">${i.quantity}</td><td class="text-end">${peso(i.unit_price)}</td><td class="text-end">${peso(i.subtotal)}</td></tr>
        `).join('');

                    const isPickup = o.fulfillment_type === 'pickup';
                    const isPaid = o.payment_status === 'paid';
                    const needsPaymentSubmission = !isPickup && ['cheque', 'bank_transfer'].includes(o.payment_method) &&
                        !isPaid &&
                        !o.client_payment_ref;

                    let paymentBlock = '';
                    if (isPickup) {
                        paymentBlock = `<div class="p-3 bg-light rounded-4 mt-4"><p class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>This is a pickup order — payment will be confirmed by our staff when you collect your items.</p></div>`;
                    } else if (isPaid) {
                        paymentBlock = `<div class="p-3 bg-light rounded-4 mt-4 text-success"><i class="fas fa-check-circle me-2"></i>Payment confirmed.</div>`;
                    } else if (o.client_payment_ref) {
                        paymentBlock = `<div class="p-3 bg-light rounded-4 mt-4"><p class="mb-0"><b>Reference submitted:</b> ${o.client_payment_ref}</p><small class="text-muted">Being verified by Robin Rose Trading.</small></div>`;
                    } else if (needsPaymentSubmission) {
                        paymentBlock = `
<div class="p-3 bg-light rounded-4 mt-4">
    <p class="mb-2"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Payment reference not yet submitted for this delivery order.</p>
    <button type="button" class="btn btn-dark w-100 btn-submit-payment-drawer" data-id="${o.order_id}">Submit Payment Reference</button>
</div>`;
                    } else {
                        paymentBlock = `<div class="p-3 bg-light rounded-4 mt-4"><p class="mb-0">Payment via ${(o.payment_method || '').toUpperCase()} — no reference required for this method.</p></div>`;
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
                <button type="button" class="btn btn-dark w-100 mt-4 fw-bold rounded-pill" id="btnPrintInvoice"><i class="fas fa-print me-2"></i>PRINT / SAVE COPY</button>
            </div>`;

                    const inlineSubmitBtn = content.querySelector('.btn-submit-payment-drawer');
                    if (inlineSubmitBtn) {
                        inlineSubmitBtn.addEventListener('click', function() {
                            drawer.hide();
                            openPaymentDrawer(this.getAttribute('data-id'));
                        });
                    }

                    const printBtn = document.getElementById('btnPrintInvoice');
                    if (printBtn) printBtn.addEventListener('click', () => printInvoice(o, data.items, data.store_info || {}));
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-danger text-center p-5">Failed to load invoice.</div>`;
                    console.error('[Invoices] Fetch failed:', err);
                });
        });
    });
});