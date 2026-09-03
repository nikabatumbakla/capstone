document.addEventListener("DOMContentLoaded", function() {
    const recordBtns = document.querySelectorAll('.btn-record-grr');
    const grrDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('grrDrawer'));
    const content = document.getElementById('grrContent');

    recordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const poId = this.getAttribute('data-id');
            grrDrawer.show();
            content.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>';

            fetch(`${BASE_URL}/admin/procurement/get-po-details/${poId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        content.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                        return;
                    }

                    const po = data.po;

                    const itemsTable = data.items.map(i => `
                        <tr>
                            <td>
                                <small class="fw-bold d-block">${i.name}</small>
                                <input type="hidden" name="product_ids[]" value="${i.product_id}">
                                <input type="hidden" name="qty_expected[]" value="${i.qty_ordered}">
                                <input type="hidden" name="unit_costs[]" value="${i.unit_cost}">
                            </td>
                            <td class="text-center">${i.qty_ordered}</td>
                            <td><input type="number" name="qty_received[]" class="form-control form-control-sm text-center fw-bold border-maroon grr-qty" data-expected="${i.qty_ordered}" value="${i.qty_ordered}" min="0" style="width:75px"></td>
                            <td><input type="text" name="lot_numbers[]" class="form-control form-control-sm" placeholder="From package label" style="width:110px"></td>
                            <td><input type="date" name="expires_ats[]" class="form-control form-control-sm" style="width:135px"></td>
                            <td><input type="number" step="0.01" name="sell_prices[]" class="form-control form-control-sm" placeholder="0.00" value="${i.last_sell_price ?? ''}" style="width:90px" required></td>
                        </tr>
                    `).join('');

                    const drNote = po.supplier_dr_number ?
                        `<p class="helper-text mb-1"><i class="fas fa-check-circle text-success me-1"></i>Reported by supplier via portal — confirm it matches the physical delivery slip.</p>` :
                        `<p class="helper-text mb-1"><i class="fas fa-exclamation-circle text-warning me-1"></i>Supplier hasn't submitted this yet — enter it from the physical delivery slip.</p>`;

                    content.innerHTML = `
                        <form action="${BASE_URL}/admin/procurement/save-grr" method="POST">
                            <input type="hidden" name="po_id" value="${po.po_id}">
                            <div class="p-3 bg-light rounded-4 mb-3">
                                <p class="info-label mb-1">Verifying Delivery for:</p>
                                <h6 class="fw-bold mb-0">${po.po_number}</h6>
                                <small class="text-muted">Supplier: ${po.sname}</small>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="info-label">Delivery Reference (DR / Invoice #)</label>
                                    <input type="text" name="delivery_ref" class="form-control form-control-sm" value="${po.supplier_dr_number || ''}" placeholder="e.g. DR-2026-0472">
                                    ${drNote}
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm" style="font-size:11px">
                                    <thead>
                                        <tr class="table-dark">
                                            <th>Product</th><th class="text-center">Ordered</th><th>Received</th>
                                            <th>Lot No.</th><th>Expiry</th><th>Sell Price *</th>
                                        </tr>
                                    </thead>
                                    <tbody>${itemsTable}</tbody>
                                </table>
                            </div>
                            <p class="helper-text mb-3"><i class="fas fa-info-circle me-1"></i>Lot number and expiry are read from the physical package on arrival — not from the PO. Sell price is pre-filled from this product's last stocked price where available; adjust if it's changed.</p>

                            <div class="mb-3">
                                <label class="info-label">Notes (required if any quantity differs from what was ordered)</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. 3 units of Cotton Balls damaged in transit"></textarea>
                            </div>

                            <div id="discrepancyWarning" class="alert alert-warning border-0 small mt-2 d-none" style="font-size:10px">
                                <i class="fas fa-exclamation-triangle me-2"></i> <b>Discrepancy Detected:</b> Received quantity differs from what was ordered for one or more items. This PO will be marked <b>Partial</b> instead of fully Received.
                            </div>

                            <button type="submit" class="btn btn-maroon w-100 py-3 mt-3 fw-bold shadow">
                                <i class="fas fa-check-circle me-2"></i>CONFIRM RECEIPT & UPDATE INVENTORY
                            </button>
                        </form>
                    `;

                    const warningBox = document.getElementById('discrepancyWarning');
                    document.querySelectorAll('.grr-qty').forEach(input => {
                        input.addEventListener('input', function() {
                            const anyMismatch = Array.from(document.querySelectorAll('.grr-qty'))
                                .some(i => parseInt(i.value || 0) !== parseInt(i.getAttribute('data-expected')));
                            warningBox.classList.toggle('d-none', !anyMismatch);
                        });
                    });
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-center text-danger p-5">Failed to load PO details.</div>`;
                    console.error(err);
                });
        });
    });
});