document.addEventListener("DOMContentLoaded", function() {
    const recordBtns = document.querySelectorAll('.btn-record-grr');
    const grrDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('grrDrawer'));
    const content = document.getElementById('grrContent');

    const conditionLabels = { good: 'Good', damaged: 'Damaged', wrong_item: 'Wrong Item', expired: 'Expired on Arrival' };

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

                    const replacementBanner = po.replacement_for_return_id ? `
    <div class="alert alert-info d-flex align-items-start gap-2 mb-3" style="font-size: 11px;">
        <i class="fas fa-exchange-alt mt-1"></i>
        <div><b>Replacement Delivery</b><br>This shipment fulfills Return #SRT-${String(po.replacement_for_return_id).padStart(4,'0')}. Verify the correct replacement item(s) arrived in good condition.</div>
    </div>` : '';

                    const itemsTable = data.items.map((i, idx) => `
                        <tr>
                            <td>
                                <small class="fw-bold d-block">${i.name}</small>
                                <small class="text-muted extra-small">${i.barcode_value || '—'}</small>
                                <input type="hidden" name="product_ids[]" value="${i.product_id}">
                                <input type="hidden" name="qty_expected[]" value="${i.qty_ordered}">
                                <input type="hidden" name="unit_costs[]" value="${i.unit_cost}">
                            </td>
                            <td class="text-center">${i.qty_ordered}</td>
                            <td><input type="number" name="qty_received[]" class="form-control form-control-sm text-center fw-bold border-maroon grr-qty" data-expected="${i.qty_ordered}" data-row="${idx}" value="${i.qty_ordered}" min="0" style="width:75px"></td>
                            <td>
                                <select name="condition_status[]" class="form-select form-select-sm grr-condition" data-row="${idx}" style="width:130px">
                                    <option value="good" selected>Good</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="wrong_item">Wrong Item</option>
                                    <option value="expired">Expired on Arrival</option>
                                </select>
                                <input type="number" name="qty_rejected[]" class="form-control form-control-sm mt-1 grr-qty-rejected d-none" data-row="${idx}" min="0" max="${i.qty_ordered}" value="0" placeholder="Qty affected" style="width:100px">
                            </td>
                            <td><input type="text" name="lot_numbers[]" class="form-control form-control-sm" placeholder="From package label" style="width:110px"></td>
                            <td><input type="date" name="expires_ats[]" class="form-control form-control-sm" style="width:135px"></td>
                            <td><input type="number" step="0.01" name="sell_prices[]" class="form-control form-control-sm" placeholder="0.00" value="${i.last_sell_price ?? ''}" style="width:90px" required></td>
                        </tr>
                    `).join('');

                    const drNote = po.supplier_dr_number ?
                        `<p class="helper-text mb-1"><i class="fas fa-check-circle text-success me-1"></i>Reported by supplier via portal — confirm it matches the physical delivery slip.</p>` :
                        `<p class="helper-text mb-1"><i class="fas fa-exclamation-circle text-warning me-1"></i>Supplier hasn't submitted this yet — enter it from the physical delivery slip.</p>`;

                    content.innerHTML = `
                        <form action="${BASE_URL}/admin/procurement/save-grr" method="POST" enctype="multipart/form-data">
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
                                <div class="col-6">
                                    <label class="info-label">Photo of Delivery (optional)</label>
                                    <input type="file" name="delivery_photo" class="form-control form-control-sm" accept="image/*">
                                    <p class="helper-text mb-0">Recommended if flagging any item below as Damaged or Wrong Item — useful evidence for the supplier credit request.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm" style="font-size:11px">
                                    <thead>
                                        <tr class="table-dark">
                                            <th>Product</th><th class="text-center">Ordered</th><th>Received</th>
                                            <th>Condition</th><th>Lot No.</th><th>Expiry</th><th>Sell Price *</th>
                                        </tr>
                                    </thead>
                                    <tbody>${itemsTable}</tbody>
                                </table>
                            </div>
                            <p class="helper-text mb-3"><i class="fas fa-info-circle me-1"></i>Lot number and expiry can be left blank for non-perishable products (e.g. devices/equipment that don't carry a batch or expiry date). Sell price is pre-filled from this product's last stocked price where available; adjust if it's changed.</p>

                            <div class="mb-3">
                                <label class="info-label">Notes (required if any quantity differs from what was ordered, or an item is flagged below)</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. 3 units of Cotton Balls damaged in transit"></textarea>
                            </div>

                            <div id="discrepancyWarning" class="alert alert-warning border-0 small mt-2 d-none" style="font-size:10px">
                                <i class="fas fa-exclamation-triangle me-2"></i> <b>Discrepancy Detected:</b> Received quantity differs from what was ordered for one or more items. This PO will be marked <b>Partial</b> instead of fully Received.
                            </div>
                            <div id="returnWarning" class="alert alert-info border-0 small mt-2 d-none" style="font-size:10px">
                                <i class="fas fa-undo-alt me-2"></i> <b>Supplier Return will be filed automatically</b> for the flagged item(s) once you submit — no need to fill out a separate return form.
                            </div>

                            <button type="submit" class="btn btn-maroon w-100 py-3 mt-3 fw-bold shadow">
                                <i class="fas fa-check-circle me-2"></i>CONFIRM RECEIPT & UPDATE INVENTORY
                            </button>
                        </form>
                    `;

                    const warningBox = document.getElementById('discrepancyWarning');
                    const returnWarningBox = document.getElementById('returnWarning');
                    const form = content.querySelector('form');

                    function refreshWarnings() {
                        const anyMismatch = Array.from(document.querySelectorAll('.grr-qty'))
                            .some(i => parseInt(i.value || 0) !== parseInt(i.getAttribute('data-expected')));
                        warningBox.classList.toggle('d-none', !anyMismatch);

                        const anyFlagged = Array.from(document.querySelectorAll('.grr-condition'))
                            .some(sel => sel.value !== 'good');
                        returnWarningBox.classList.toggle('d-none', !anyFlagged);
                    }

                    document.querySelectorAll('.grr-qty').forEach(input => {
                        input.addEventListener('input', refreshWarnings);
                    });

                    document.querySelectorAll('.grr-condition').forEach(select => {
                        select.addEventListener('change', function() {
                            const row = this.getAttribute('data-row');
                            const rejectedInput = document.querySelector(`.grr-qty-rejected[data-row="${row}"]`);
                            const receivedInput = document.querySelector(`.grr-qty[data-row="${row}"]`);

                            if (this.value === 'good') {
                                rejectedInput.classList.add('d-none');
                                rejectedInput.value = 0;
                            } else {
                                rejectedInput.classList.remove('d-none');
                                if (parseInt(rejectedInput.value || 0) === 0) {
                                    rejectedInput.value = receivedInput.value; // suggest full qty, admin can lower it
                                }
                            }
                            refreshWarnings();
                        });
                    });

                    form.addEventListener('submit', function(e) {
                        let hasError = false;
                        document.querySelectorAll('.grr-condition').forEach(select => {
                            if (select.value === 'good') return;
                            const row = select.getAttribute('data-row');
                            const rejectedInput = document.querySelector(`.grr-qty-rejected[data-row="${row}"]`);
                            if (parseInt(rejectedInput.value || 0) <= 0) {
                                hasError = true;
                            }
                        });
                        if (hasError) {
                            e.preventDefault();
                            alert('Please enter the quantity affected for every item flagged as Damaged, Wrong Item, or Expired.');
                        }
                    });
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-center text-danger p-5">Failed to load PO details.</div>`;
                    console.error(err);
                });
        });
    });
});