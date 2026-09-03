document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('poDrawer'));
    const content = document.getElementById('poDrawerContent');

    function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, {minimumFractionDigits:2}); }

    document.querySelectorAll('.btn-view-po').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            drawer.show();

            fetch(`${BASE_URL}/supplier/orders/get-po-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }
                    const po = data.po;

                    const itemsHtml = data.items.map(i => `
                        <tr><td>${i.name}<br><small class="text-muted">${i.sku || '—'}</small></td><td class="text-center">${i.qty_ordered} ${i.unit}</td><td class="text-end">${peso(i.unit_cost)}</td></tr>
                    `).join('');

                    let actionBlock = '';
                    if (po.status === 'sent') {
                        actionBlock = `
                            <form action="${BASE_URL}/supplier/orders/process-acknowledge" method="POST" class="p-3 bg-light rounded-4 mt-3">
                                <input type="hidden" name="po_id" value="${po.po_id}">
                                <label class="formal-label">Confirm Delivery Date *</label>
                                <input type="date" name="confirmed_date" class="formal-input mb-2" required>
                                <label class="formal-label">Notes (optional)</label>
                                <textarea name="notes" class="formal-input mb-2" rows="2" placeholder="Any remarks about this order"></textarea>
                                <button type="submit" class="btn btn-success w-100">✓ Acknowledge Order</button>
                            </form>`;
                    } else {
                        actionBlock = `<div class="p-3 bg-light rounded-4 mt-3 text-muted">Status: <strong>${po.status.toUpperCase()}</strong></div>`;
                    }

                    content.innerHTML = `
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
                })
                .catch(() => { content.innerHTML = `<div class="text-danger text-center p-5">Failed to load order.</div>`; });
        });
    });
});