document.addEventListener("DOMContentLoaded", function() {
    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : window.location.origin + '/PharMediSync';

    const viewDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('viewReturnDrawer'));
    const content = document.getElementById('viewReturnContent');

    function peso(n) {
        return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    }

    document.querySelectorAll('.btn-view-return').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            viewDrawer.show();

            fetch(`${baseUrl}/supplier/orders/returns/get-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }

                    let actionHtml = '';
                    if (data.status === 'approved' && data.supplier_return_status === 'sent_to_supplier') {
                        actionHtml = `
                            <a href="${baseUrl}/supplier/orders/returns/confirm-received/${data.return_id}" class="btn btn-dark w-100 mt-4" onclick="return confirm('Confirm the returned item(s) are now in your hands?')">
                                <i class="fas fa-box-open me-1"></i>Confirm Received
                            </a>`;
                    } else if (data.status === 'approved' && data.supplier_return_status === 'received_by_supplier') {
                        actionHtml = `<div class="alert alert-success mt-4 mb-0 small"><i class="fas fa-check-circle me-1"></i>You confirmed receipt of this item. Robin Rose Trading will arrange the ${data.resolution_type === 'refund' ? 'refund' : 'replacement'}.</div>`;
                    } else if (data.status === 'approved') {
                        actionHtml = `<div class="alert alert-secondary mt-4 mb-0 small"><i class="fas fa-hourglass-half me-1"></i>Approved — waiting for Robin Rose Trading to ship the item(s) back to you.</div>`;
                    } else if (data.status === 'pending') {
                        actionHtml = `<div class="alert alert-secondary mt-4 mb-0 small"><i class="fas fa-hourglass-half me-1"></i>Still under review by Robin Rose Trading.</div>`;
                    }

                    content.innerHTML = `
                        <div class="p-4 bg-light rounded-4 text-center mb-4 border">
                            <i class="fas fa-truck-loading fs-1 text-maroon opacity-25 mb-3"></i>
                            <h5 class="fw-bold mb-1">Return Details</h5>
                            <span class="badge bg-dark">SRT-${String(data.return_id).padStart(4, '0')}</span>
                        </div>
                        <div class="row g-3 px-2 text-start">
                            <div class="col-12"><label class="info-label">Reference PO</label><p class="info-value text-primary">${data.po_number}</p></div>
                            <div class="col-6"><label class="info-label">Product</label><p class="info-value">${data.product_name}</p></div>
                            <div class="col-6"><label class="info-label">Barcode</label><p class="info-value">${data.barcode_value || '—'}</p></div>
                            <div class="col-6"><label class="info-label">Quantity</label><p class="info-value fw-bold fs-5">${data.quantity}</p></div>
                            <div class="col-6"><label class="info-label">Resolution</label><p class="info-value">${data.resolution_type === 'refund' ? 'Refund' : 'Exchange'}</p></div>
                            <div class="col-12 mt-3 pt-3 border-top"><label class="info-label">Reason Stated</label><p class="info-value fw-normal text-muted" style="line-height:1.6">${data.reason}</p></div>
                        </div>
                        ${actionHtml}`;
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-danger text-center p-5">Failed to load return details.</div>`;
                    console.error(err);
                });
        });
    });
});