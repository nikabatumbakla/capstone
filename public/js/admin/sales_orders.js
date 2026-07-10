document.addEventListener("DOMContentLoaded", function() {
    const viewBtns = document.querySelectorAll('.btn-view-so');
    const soDrawer = new bootstrap.Offcanvas(document.getElementById('soDrawer'));
    const content = document.getElementById('soDrawerContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            soDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;

            fetch(`${window.location.origin}/PharMediSync/admin/sales/get-order-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    const o = data.order;
                    let itemsHtml = data.items.map(i => `
                        <tr>
                            <td>${i.name}<br><small class="text-muted">${i.sku}</small></td>
                            <td class="text-center">${i.quantity}</td>
                            <td class="text-end">₱${parseFloat(i.unit_price).toLocaleString()}</td>
                        </tr>
                    `).join('');

                    content.innerHTML = `
                        <div class="mb-4">
                            <h4 class="fw-bold mb-1">${o.order_number}</h4>
                            <p class="text-muted small">Institution: <b>${o.organization}</b></p>
                        </div>

                        <div class="bg-light p-3 rounded-4 mb-4 border">
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <small class="info-label">TOTAL BILLING</small>
                                    <h5 class="fw-bold text-maroon mb-0">₱${parseFloat(o.total).toLocaleString()}</h5>
                                </div>
                                <div class="col-6">
                                    <small class="info-label">CURRENT STATUS</small>
                                    <h6 class="fw-bold mb-0 text-uppercase">${o.status}</h6>
                                </div>
                            </div>
                        </div>

                        <table class="table table-sm border-bottom" style="font-size:10px">
                            <thead><tr class="table-dark"><th>Product Specification</th><th class="text-center">Qty</th><th class="text-end">Unit</th></tr></thead>
                            <tbody>${itemsHtml}</tbody>
                        </table>

                        <form action="${window.location.origin}/PharMediSync/admin/sales/update-order-status" method="POST" class="mt-4">
                            <input type="hidden" name="order_id" value="${o.order_id}">
                            <label class="info-label mb-2">Logistics Action / Status Update</label>
                            <div class="d-flex gap-2">
                                <select name="status" class="form-select form-select-sm formal-input">
                                    <option value="pending" ${o.status == 'pending' ? 'selected' : ''}>Pending Preparation</option>
                                    <option value="processing" ${o.status == 'processing' ? 'selected' : ''}>Processing / Packing</option>
                                    <option value="shipped" ${o.status == 'shipped' ? 'selected' : ''}>Shipped / In Transit</option>
                                    <option value="delivered" ${o.status == 'delivered' ? 'selected' : ''}>Delivered & Closed</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-dark px-3">Update</button>
                            </div>
                        </form>

                        <div class="mt-5 pt-3 border-top">
                            <button class="btn btn-maroon w-100 py-3 fw-bold rounded-3 shadow" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>GENERATE BIR-COMPLIANT INVOICE
                            </button>
                        </div>
                    `;
                });
        });
    });
});