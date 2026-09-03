document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById('filterForm');
    let typingTimer;
    document.getElementById('liveSearch').addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => filterForm.submit(), 600);
    });

    const grrDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('grrDrawer'));
    const content = document.getElementById('grrContent');

    document.querySelectorAll('.btn-begin-inspection').forEach(btn => {
        btn.addEventListener('click', function() {
            const poId = this.getAttribute('data-id');
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            grrDrawer.show();

            fetch(`${BASE_URL}/staff/operations/get-po-items/${poId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`; return; }

                    const po = data.po;
                    const itemsTable = data.items.map((i, idx) => `
    <tr data-barcode="${i.barcode_value || ''}" data-row-index="${idx}">
        <td>
            <small class="fw-bold d-block">${i.name}</small>
            <small class="text-muted">${i.barcode_value || 'No barcode'}</small>
            <input type="hidden" name="product_ids[]" value="${i.product_id}">
            <input type="hidden" name="qty_expected[]" value="${i.qty_ordered}">
            <input type="hidden" name="unit_costs[]" value="${i.unit_cost}">
        </td>
        <td class="text-center">${i.qty_ordered} ${i.unit}</td>
        <td><input type="number" name="qty_received[]" class="form-control form-control-sm text-center fw-bold border-maroon grr-qty" data-expected="${i.qty_ordered}" data-row-index="${idx}" value="${i.qty_ordered}" min="0" style="width:75px"><small class="text-muted d-block text-center">${i.unit}</small></td>
        <td><input type="text" name="lot_numbers[]" class="form-control form-control-sm" placeholder="From package label" style="width:110px"></td>
        <td><input type="date" name="expires_ats[]" class="form-control form-control-sm" style="width:135px"></td>
        <td><input type="number" step="0.01" name="sell_prices[]" class="form-control form-control-sm" placeholder="0.00" style="width:90px" required></td>
    </tr>`).join('');

                    content.innerHTML = `
    <form action="${BASE_URL}/staff/operations/save-grr" method="POST" class="p-4">
        <input type="hidden" name="po_id" value="${po.po_id}">
        <div class="p-3 bg-light rounded-4 mb-3">
            <p class="info-label mb-1">Receiving Delivery for:</p>
            <h6 class="fw-bold mb-0">${po.po_number}</h6>
            <small class="text-muted">Supplier: ${po.sname}</small>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="info-label">Delivery Reference (DR / Invoice #)</label>
                <input type="text" name="delivery_ref" class="form-control form-control-sm" value="${po.supplier_dr_number || ''}" placeholder="e.g. DR-2026-0472">
                ${po.supplier_dr_number ? '<small class="text-success">✓ Submitted by supplier</small>' : '<small class="text-muted" style="font-size:9px">Enter from physical delivery slip if not yet provided by supplier</small>'}
            </div>
            <div class="col-6">
                <label class="info-label">Scan or Enter Barcode</label>
                <input type="text" id="grrBarcodeInput" class="form-control form-control-sm" placeholder="Scan item to jump to its row">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" id="grrProgressLabel" style="font-size:10px;">0 of ${data.items.length} items counted</span>
             </div>

        <div class="table-responsive">
            <table class="table table-sm" style="font-size:11px">
                <thead><tr class="table-dark"><th>Product</th><th class="text-center">Ordered</th><th>Received</th><th>Lot No.</th><th>Expiry</th><th>Sell Price *</th></tr></thead>
                <tbody id="grrItemsBody">${itemsTable}</tbody>
            </table>
        </div>
        <p class="helper-text mb-3"><i class="fas fa-info-circle me-1"></i>Lot number and expiry are read from the physical package on arrival. Sell price is required per item.</p>

        <div class="mb-3">
            <label class="info-label">Notes (required if any quantity differs from what was ordered)</label>
            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. 3 units damaged in transit"></textarea>
        </div>

        <div id="discrepancyWarning" class="alert alert-warning border-0 small mt-2 d-none" style="font-size:10px">
            <i class="fas fa-exclamation-triangle me-2"></i><b>Discrepancy Detected:</b> Received quantity differs from ordered for one or more items. This PO will be marked <b>Partial</b> instead of fully Received.
        </div>

        <button type="submit" class="btn btn-save-adj w-100 py-3 mt-3 fw-bold">
            <i class="fas fa-check-circle me-2"></i>CONFIRM RECEIPT & UPDATE INVENTORY
        </button>
    </form>
`;

                    const warningBox = document.getElementById('discrepancyWarning');
                    const progressLabel = document.getElementById('grrProgressLabel');
                    const qtyInputs = document.querySelectorAll('.grr-qty');

                    function updateProgress() {
                        const counted = Array.from(qtyInputs).filter(i => i.value !== '').length;
                        progressLabel.textContent = `${counted} of ${qtyInputs.length} items counted`;

                        const anyMismatch = Array.from(qtyInputs).some(i => parseInt(i.value || 0) !== parseInt(i.getAttribute('data-expected')));
                        warningBox.classList.toggle('d-none', !anyMismatch);
                    }

                    qtyInputs.forEach((input, idx) => {
                        input.addEventListener('input', updateProgress);
                        input.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const next = qtyInputs[idx + 1];
                                if (next) next.focus();
                            }
                        });
                    });


                    document.getElementById('grrBarcodeInput').addEventListener('keydown', function(e) {
                        if (e.key !== 'Enter') return;
                        e.preventDefault();
                        const scanned = this.value.trim();
                        const row = document.querySelector(`#grrItemsBody tr[data-barcode="${scanned}"]`);
                        if (row) {
                            const input = row.querySelector('.grr-qty');
                            input.focus();
                            input.select();
                            row.style.background = '#fff8e1';
                            setTimeout(() => { row.style.background = ''; }, 800);
                        } else {
                            alert('No matching item found on this PO for that barcode.');
                        }
                        this.value = '';
                    });

                    updateProgress();
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