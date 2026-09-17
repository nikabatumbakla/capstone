document.addEventListener("DOMContentLoaded", function() {
            let typingTimer;
            document.getElementById('liveSearch').addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => document.getElementById('filterForm').submit(), 600);
            });

            const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('poDrawer'));
            const content = document.getElementById('poDrawerContent');

            function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

            document.querySelectorAll('.btn-view-po').forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const id = this.getAttribute('data-id');
                                    content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                                    drawer.show();

                                    fetch(`${BASE_URL}/staff/operations/po-history/details/${id}`)
                                        .then(res => res.json())
                                        .then(data => {
                                                if (data.error) { content.innerHTML = `<p class="text-danger text-center p-5">${data.error}</p>`; return; }
                                                const po = data.po;
                                                document.getElementById('poDrawerTitle').textContent = po.po_number;

                                                const rows = data.items.map(i => `
                        <tr><td>${i.name}<br><small class="text-muted">${i.barcode_value || '—'}</small></td>
                        <td class="text-center">${i.qty_ordered}</td><td class="text-center">${i.qty_received ?? '—'}</td>
                        <td class="text-end">${peso(i.unit_cost)}</td></tr>`).join('');

                                                content.innerHTML = `
                        <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                            <div class="col-6"><small class="info-label">Supplier</small><p class="mb-0 fw-bold">${po.supplier_name || '—'}</p></div>
                            <div class="col-6 text-end"><small class="info-label">Total</small><h5 class="fw-bold text-maroon mb-0">${peso(po.total_amount)}</h5></div>
                            <div class="col-6"><small class="info-label">Received</small><p class="mb-0">${po.received_date || '—'}</p></div>
                            <div class="col-6"><small class="info-label">Status</small><p class="mb-0 text-uppercase fw-bold">${po.status}</p></div>
                        </div>
                        <table class="table table-sm" style="font-size:11px"><thead><tr class="table-dark"><th>Product</th><th class="text-center">Ordered</th><th class="text-center">Received</th><th class="text-end">Unit Cost</th></tr></thead><tbody>${rows}</tbody></table>
                        ${data.grr ? `<div class="p-3 bg-light rounded-4 mt-3"><small class="text-muted">GRR Notes:</small><p class="mb-0" style="font-size:11px;">${data.grr.notes || 'None recorded'}</p></div>` : ''}
                    `;
                })
                .catch(() => { content.innerHTML = `<p class="text-danger text-center p-5">Failed to load PO details.</p>`; });
        });
    });
});