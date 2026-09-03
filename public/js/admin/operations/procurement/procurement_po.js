document.addEventListener("DOMContentLoaded", function() {
            const viewBtns = document.querySelectorAll('.btn-view-po');
            const viewDrawerEl = document.getElementById('poViewDrawer');
            const viewDrawer = bootstrap.Offcanvas.getOrCreateInstance(viewDrawerEl);
            const content = document.getElementById('poViewContent');

            function peso(n) {
                return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
            }

            function buildReceiptHtml(po, items, store) {
                const rows = items.map(i => `
            <tr>
                <td>${i.name}</td>
                <td style="text-align:center;">${i.qty_ordered}</td>
                <td style="text-align:right;">${peso(i.unit_cost)}</td>
                <td style="text-align:right;">${peso(i.subtotal)}</td>
            </tr>`).join('');

                return `
            <div id="printReceiptArea" style="font-family: Arial, sans-serif; padding: 30px; color:#000;">
                <div style="text-align:center; margin-bottom: 20px;">
                    <h4 style="margin:0;">${store.store_name || 'Store'}</h4>
                    <p style="margin:0; font-size:11px;">${store.store_address || ''}</p>
                    <p style="margin:0; font-size:11px;">TIN: ${store.store_tin || 'N/A'} | ${store.store_phone_1 || ''}</p>
                </div>
                <hr>
                <h5 style="text-align:center;">OFFICIAL PURCHASE ORDER RECEIPT</h5>
                <table style="width:100%; font-size:11px; margin-bottom:15px;">
                    <tr><td><b>PO Number:</b> ${po.po_number}</td><td style="text-align:right;"><b>Date:</b> ${po.created_at}</td></tr>
                    <tr><td><b>Supplier:</b> ${po.sname}</td><td style="text-align:right;"><b>Received:</b> ${po.received_date || '—'}</td></tr>
                    <tr><td colspan="2"><b>Status:</b> Received / Complete</td></tr>
                </table>
                <table style="width:100%; border-collapse: collapse; font-size:11px;" border="1" cellpadding="6">
                    <thead><tr><th>Product</th><th>Qty</th><th>Unit Cost</th><th>Subtotal</th></tr></thead>
                    <tbody>${rows}</tbody>
                    <tfoot><tr><td colspan="3" style="text-align:right;"><b>TOTAL</b></td><td style="text-align:right;"><b>${peso(po.total_amount)}</b></td></tr></tfoot>
                </table>
                <div style="margin-top: 60px; display:flex; justify-content:space-between; font-size:11px;">
                    <div>_____________________<br>Received By</div>
                    <div>_____________________<br>Authorized By</div>
                </div>
            </div>`;
            }

            function printReceipt(po, items, store) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`<html><head><title>${po.po_number}</title></head><body>${buildReceiptHtml(po, items, store)}</body></html>`);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }

            viewBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const id = this.getAttribute('data-id');
                                    viewDrawer.show();

                                    content.innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-maroon" role="status"></div>
                    <p class="mt-2 small text-muted">Accessing Procurement Logs...</p>
                </div>`;

                                    fetch(`${BASE_URL}/admin/procurement/get-po-details/${id}`)
                                        .then(res => {
                                            if (!res.ok) throw new Error('Network error');
                                            return res.json();
                                        })
                                        .then(data => {
                                                if (data.error) {
                                                    content.innerHTML = `<div class="alert alert-danger m-4">${data.error}</div>`;
                                                    return;
                                                }

                                                const po = data.po;
                                                const items = data.items;
                                                const store = data.store_info;

                                                const hasStockContext = items.some(i => i.current_stock !== null);

                                                const itemsHtml = items.map(i => `
                        <tr>
                            <td><small class="fw-bold d-block">${i.name}</small><code class="extra-small">${i.sku || '—'}</code></td>
                            <td class="text-center">${i.qty_ordered}</td>
                            ${hasStockContext ? `
                                <td class="text-center text-muted">${i.current_stock ?? '—'}</td>
                                <td class="text-center text-muted">${i.reorder_level ?? '—'}</td>
                            ` : ''}
                            <td class="text-end">${peso(i.unit_cost)}</td>
                            <td class="text-end fw-bold">${peso(i.subtotal)}</td>
                        </tr>
                    `).join('');

                    const autoReorderPanel = (po.is_auto_generated == 1 && po.status === 'pending_approval') ? `
                        <div class="alert alert-primary d-flex align-items-start gap-2 mb-4" style="font-size: 11px;">
                            <i class="fas fa-robot mt-1"></i>
                            <div>
                                <b>System-Generated Recommendation</b><br>
                                This product hit its reorder level. Review the suggested quantity against current stock below, then approve or reject.
                            </div>
                        </div>
                        <div class="d-flex gap-2 mb-4">
                            <a href="${BASE_URL}/admin/procurement/approve-po/${po.po_id}" class="btn btn-success flex-grow-1" onclick="return confirm('Approve this purchase order?');">
                                <i class="fas fa-check me-2"></i>Approve
                            </a>
                            <a href="${BASE_URL}/admin/procurement/reject-po/${po.po_id}" class="btn btn-outline-danger flex-grow-1" onclick="return confirm('Reject and cancel this purchase order?');">
                                <i class="fas fa-times me-2"></i>Reject
                            </a>
                        </div>
                    ` : '';

                    const trackingHtml = `
                        <div class="tracking-timeline mt-4 p-3 bg-white border rounded-4">
                            <h6 class="fw-bold mb-3" style="font-size:11px"><i class="fas fa-map-marker-alt me-2 text-primary"></i>PO Tracking</h6>
                            <div class="timeline-item ${po.status !== 'draft' ? 'active' : ''}">
                                <div class="t-dot"></div>
                                <p class="mb-0 fw-bold">Order Created</p>
                                <small class="text-muted">${po.created_at} ${po.creator ? '• by ' + po.creator : ''}</small>
                            </div>
                            <div class="timeline-item ${['sent','partial','received'].includes(po.status) ? 'active' : ''}">
                                <div class="t-dot"></div>
                                <p class="mb-0 fw-bold">Sent to Supplier</p>
                                <small class="text-muted">${po.sname}</small>
                            </div>
                            <div class="timeline-item ${po.status === 'received' ? 'active' : ''}">
                                <div class="t-dot"></div>
                                <p class="mb-0 fw-bold">Received & Inspected</p>
                                <small class="text-muted">${po.received_date || 'Awaiting Delivery'}</small>
                            </div>
                        </div>`;

                    const notesHtml = po.notes ? `
                        <div class="mt-3 p-3 bg-light rounded-3">
                            <p class="info-label mb-1"><i class="fas fa-sticky-note me-1"></i>Notes</p>
                            <p class="mb-0" style="font-size:11px;">${po.notes}</p>
                        </div>` : '';

                    // Print is ONLY available once the order is actually complete
                    const printBtnHtml = po.status === 'received' ? `
                        <div class="mt-5">
                            <button class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow-sm" id="btnPrintReceipt">
                                <i class="fas fa-print me-2"></i>PRINT OFFICIAL RECEIPT
                            </button>
                        </div>` : `
                        <div class="mt-5 p-3 bg-light rounded-3 text-center text-muted" style="font-size:11px;">
                            <i class="fas fa-lock me-1"></i>Printing unlocks once this order is marked Received.
                        </div>`;

                    content.innerHTML = `
                        <div class="p-4">
                            <div class="text-center mb-4">
                                <span class="badge bg-maroon mb-2 px-3">PURCHASE ORDER</span>
                                <h4 class="fw-bold mb-0">${po.po_number}</h4>
                                <p class="text-muted small">Issued to: <b>${po.sname}</b></p>
                                ${po.is_auto_generated == 1 ? '<span class="badge bg-primary-subtle text-primary"><i class="fas fa-robot me-1"></i>System-Generated</span>' : ''}
                            </div>

                            ${autoReorderPanel}

                            <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                                <div class="col-6"><small class="info-label"><i class="fas fa-tag me-1"></i>Status</small><p class="mb-0 fw-bold text-uppercase" style="font-size:10px">${po.status.replace('_',' ')}</p></div>
                                <div class="col-6"><small class="info-label"><i class="fas fa-coins me-1"></i>Total</small><p class="mb-0 fw-bold text-maroon">${peso(po.total_amount)}</p></div>
                                <div class="col-6"><small class="info-label"><i class="fas fa-calendar me-1"></i>Expected</small><p class="mb-0">${po.expected_date || '—'}</p></div>
                                <div class="col-6"><small class="info-label"><i class="fas fa-user me-1"></i>By</small><p class="mb-0">${po.creator || 'System'}</p></div>
                            </div>

                            <table class="table table-sm table-borderless" style="font-size:10px">
                                <thead class="border-bottom">
                                    <tr>
                                        <th>Product</th><th class="text-center">Qty</th>
                                        ${hasStockContext ? '<th class="text-center">Stock</th><th class="text-center">Reorder At</th>' : ''}
                                        <th class="text-end">Unit Cost</th><th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>${itemsHtml}</tbody>
                            </table>

                            ${notesHtml}
                            ${trackingHtml}
                            ${printBtnHtml}
                        </div>
                    `;

                    const printBtn = document.getElementById('btnPrintReceipt');
                    if (printBtn) {
                        printBtn.addEventListener('click', () => printReceipt(po, items, store));
                    }
                })
                .catch(err => {
                    content.innerHTML = `<div class="alert alert-danger m-4">Could not load PO details.</div>`;
                    console.error(err);
                });
        });
    });
});