document.addEventListener("DOMContentLoaded", function() {
    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL.replace(/\/+$/, '') : window.location.origin + '/PharMediSync';

    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('poDrawer'));
    const content = document.getElementById('poDrawerContent');

    function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

    function buildOrderSummaryHtml(po, items, store) {
        const rows = items.map(i => `
            <tr>
                <td>${i.name}</td>
                <td style="text-align:center;">${i.qty_ordered}</td>
                <td style="text-align:right;">${peso(i.unit_cost)}</td>
            </tr>`).join('');

        return `
            <div style="font-family: Arial, sans-serif; padding: 30px; color:#000;">
                <div style="text-align:center; margin-bottom: 20px;">
                    <h4 style="margin:0;">${store.store_name || 'Robin Rose Trading'}</h4>
                    <p style="margin:0; font-size:11px;">${store.store_address || ''}</p>
                    <p style="margin:0; font-size:11px;">${store.store_phone_1 || ''}</p>
                </div>
                <hr>
                <h5 style="text-align:center;">SUPPLIER COPY — ORDER SUMMARY</h5>
                <table style="width:100%; font-size:11px; margin-bottom:15px;">
                    <tr><td><b>PO Number:</b> ${po.po_number}</td><td style="text-align:right;"><b>Status:</b> ${po.status.toUpperCase()}</td></tr>
                    <tr><td><b>Expected Date:</b> ${po.expected_date || '—'}</td><td style="text-align:right;"><b>Payment:</b> ${po.payment_status === 'paid' ? 'PAID' : 'UNPAID'}</td></tr>
                </table>
                <table style="width:100%; border-collapse: collapse; font-size:11px;" border="1" cellpadding="6">
                    <thead><tr><th>Product</th><th>Qty</th><th>Unit Cost</th></tr></thead>
                    <tbody>${rows}</tbody>
                    <tfoot><tr><td colspan="2" style="text-align:right;"><b>TOTAL</b></td><td style="text-align:right;"><b>${peso(po.total_amount)}</b></td></tr></tfoot>
                </table>
                <p style="margin-top:30px; font-size:10px; color:#666;">This is a supplier-side copy for your own records and does not replace an official receipt.</p>
            </div>`;
    }

    function printOrderSummary(po, items, store) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`<html><head><title>${po.po_number}</title></head><body>${buildOrderSummaryHtml(po, items, store)}</body></html>`);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }

    document.querySelectorAll('.btn-view-po').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            drawer.show();

            fetch(`${baseUrl}/supplier/orders/get-po-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }
                    const po = data.po;

                    const replacementBanner = po.replacement_for_return_id ? `
                        <div class="alert alert-info d-flex align-items-start gap-2 mb-3 mx-3 mt-3" style="font-size: 11px;">
                            <i class="fas fa-exchange-alt mt-1"></i>
                            <div><b>Replacement Delivery</b><br>This order replaces defective/incorrect items from Return #SRT-${String(po.replacement_for_return_id).padStart(4,'0')} — no payment is required, it's a straight exchange.</div>
                        </div>` : '';

                    const itemsHtml = data.items.map(i => `
                        <tr><td>${i.name}<br><small class="text-muted">${i.barcode_value || '—'}</small></td><td class="text-center">${i.qty_ordered} ${i.unit}</td><td class="text-end">${peso(i.unit_cost)}</td></tr>
                    `).join('');

                    let actionBlock = '';
                    if (po.status === 'sent') {
                        actionBlock = `
                            <div class="mt-3">
                                <div class="d-flex gap-2 mb-2">
                                    <a href="${baseUrl}/supplier/orders/acknowledge/${po.po_id}" class="btn btn-success flex-fill">✓ Review & Acknowledge</a>
                                    <button type="button" class="btn btn-outline-danger flex-fill" id="btnShowDecline">✗ Decline</button>
                                </div>
                                <form action="${baseUrl}/supplier/orders/process-decline" method="POST" class="p-3 bg-light rounded-4" id="declineForm" style="display:none;">
                                    <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                                    <input type="hidden" name="po_id" value="${po.po_id}">
                                    <label class="formal-label">Reason for Declining *</label>
                                    <textarea name="reason" class="formal-input mb-2" rows="2" placeholder="e.g. Out of stock, cannot meet delivery date" required></textarea>
                                    <button type="submit" class="btn btn-danger w-100">✗ Confirm Decline</button>
                                </form>
                            </div>`;
                    } else {
                        const paymentNote = (po.status === 'acknowledged' || po.status === 'in_transit') ?
                            (po.payment_status === 'paid' ?
                                `<br><span class="badge bg-success mt-1">PAYMENT RECEIVED</span>` :
                                (po.replacement_for_return_id ? '' : `<br><span class="badge bg-secondary mt-1">AWAITING PAYMENT FROM ROBIN ROSE TRADING</span>`)) :
                            '';
                        actionBlock = `
                            <div class="p-3 bg-light rounded-4 mt-3 text-muted">Status: <strong>${po.status.toUpperCase()}</strong>${paymentNote}</div>
                            <button type="button" class="btn btn-outline-dark w-100 mt-2" id="btnPrintSummary"><i class="fas fa-print me-2"></i>Print Order Summary</button>`;
                    }

                    content.innerHTML = `
                        ${replacementBanner}
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">${po.po_number}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-3 bg-light p-3 rounded-4">
                                <div class="col-6"><small class="info-label">Expected Date</small><p class="mb-0">${po.expected_date || '—'}</p></div>
                                <div class="col-6"><small class="info-label">Status</small><p class="mb-0">${po.status.toUpperCase()}</p></div>
                            </div>
                            <table class="table table-sm" style="font-size:11px"><thead><tr class="table-dark"><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit Cost</th></tr></thead><tbody>${itemsHtml}</tbody></table>
                            ${actionBlock}
                        </div>`;

                    if (po.status === 'sent') {
                        document.getElementById('btnShowDecline').addEventListener('click', function() {
                            document.getElementById('declineForm').style.display = 'block';
                        });
                    } else {
                        const printBtn = document.getElementById('btnPrintSummary');
                        if (printBtn) printBtn.addEventListener('click', () => printOrderSummary(po, data.items, data.store_info || {}));
                    }
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-danger text-center p-5">Failed to load order.</div>`;
                    console.error(err);
                });
        });
    });
});