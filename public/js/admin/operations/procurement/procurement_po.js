document.addEventListener("DOMContentLoaded", function() {
    const viewBtns = document.querySelectorAll('.btn-view-po');
    const viewDrawerEl = document.getElementById('poViewDrawer');
    const viewDrawer = bootstrap.Offcanvas.getOrCreateInstance(viewDrawerEl);
    const content = document.getElementById('poViewContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            viewDrawer.show();

            content.innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-maroon" role="status"></div>
                    <p class="mt-2 small text-muted">Accessing Procurement Logs...</p>
                </div>`;

            fetch(`${window.location.origin}/PharMediSync/admin/procurement/get-po-details/${id}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network error');
                    return res.json();
                })
                .then(data => {
                    const po = data.po;
                    const items = data.items;

                    // Generate Item Table
                    let itemsHtml = items.map(i => `
                        <tr>
                            <td><small class="fw-bold d-block">${i.name}</small><code class="extra-small">${i.sku}</code></td>
                            <td class="text-center">${i.qty_ordered}</td>
                            <td class="text-end">₱${parseFloat(i.unit_cost).toLocaleString()}</td>
                        </tr>
                    `).join('');

                    // Logic for the Tracking Timeline (Supplier Portal Integration)
                    let trackingHtml = `
                        <div class="tracking-timeline mt-4 p-3 bg-white border rounded-4">
                            <h6 class="fw-bold mb-3" style="font-size:11px"><i class="fas fa-map-marker-alt me-2 text-primary"></i>PO Tracking Intelligence</h6>
                            <div class="timeline-item ${po.status !== 'draft' ? 'active' : ''}">
                                <div class="t-dot"></div>
                                <p class="mb-0 fw-bold">Order Created</p>
                                <small class="text-muted">${po.created_at}</small>
                            </div>
                            <div class="timeline-item ${po.status === 'sent' || po.status === 'received' ? 'active' : ''}">
                                <div class="t-dot"></div>
                                <p class="mb-0 fw-bold">Sent to Supplier</p>
                                <small class="text-muted">${po.supplier_id ? 'Notified via Portal' : 'Pending'}</small>
                            </div>
                            <div class="timeline-item ${po.status === 'received' ? 'active' : ''}">
                                <div class="t-dot"></div>
                                <p class="mb-0 fw-bold">Received & Inspected</p>
                                <small class="text-muted">${po.received_date || 'Awaiting Delivery'}</small>
                            </div>
                        </div>
                    `;

                    content.innerHTML = `
                        <div class="p-4">
                            <div class="text-center mb-4">
                                <span class="badge bg-maroon mb-2 px-3">PURCHASE ORDER</span>
                                <h4 class="fw-bold mb-0">${po.po_number}</h4>
                                <p class="text-muted small">Issued to: <b>${po.sname}</b></p>
                            </div>

                            <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                                <div class="col-6"><small class="info-label">Status</small><p class="mb-0 fw-bold text-uppercase" style="font-size:10px">${po.status}</p></div>
                                <div class="col-6"><small class="info-label">Total Amount</small><p class="mb-0 fw-bold text-maroon">₱${parseFloat(po.total_amount).toLocaleString()}</p></div>
                            </div>

                            <table class="table table-sm table-borderless" style="font-size:10px">
                                <thead class="border-bottom"><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Cost</th></tr></thead>
                                <tbody>${itemsHtml}</tbody>
                            </table>

                            ${trackingHtml}

                            <div class="mt-5">
                                <button class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow-sm" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>PRINT OFFICIAL PO
                                </button>
                            </div>
                        </div>
                    `;
                })
                .catch(err => {
                    content.innerHTML = `<div class="alert alert-danger m-4">Could not load PO details. Verify database connection.</div>`;
                });
        });
    });
});