document.addEventListener("DOMContentLoaded", function() {
    console.log('[Supplier Returns] Script loaded and DOM ready.');

    if (typeof bootstrap === 'undefined') {
        console.error('[Supplier Returns] STOP: Bootstrap JS is not loaded yet. Check that partials/admin/footer.php loads bootstrap.bundle.min.js BEFORE this script tag runs.');
        return;
    }

    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : window.location.origin + '/PharMediSync';
    console.log('[Supplier Returns] Using base URL:', baseUrl);

    const searchForm = document.getElementById('searchForm');
    const liveSearch = document.getElementById('liveSearch');

    let typingTimer;
    if (liveSearch) {
        liveSearch.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => searchForm.submit(), 600);
        });
    }

    function peso(n) {
        return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    }

    const viewBtns = document.querySelectorAll('.btn-view-return');
    console.log('[Supplier Returns] Found', viewBtns.length, 'view button(s).');

    const viewDrawerEl = document.getElementById('viewReturnDrawer');
    if (!viewDrawerEl) {
        console.error('[Supplier Returns] STOP: #viewReturnDrawer element not found on the page.');
        return;
    }
    const viewDrawer = bootstrap.Offcanvas.getOrCreateInstance(viewDrawerEl);
    const viewContent = document.getElementById('viewReturnContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            console.log('[Supplier Returns] View button clicked, id =', this.getAttribute('data-id'));
            const id = this.getAttribute('data-id');
            viewContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            viewDrawer.show();

            fetch(`${baseUrl}/admin/procurement/get-supplier-return-details/${id}`)
                .then(res => {
                    console.log('[Supplier Returns] Fetch response status:', res.status);
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        viewContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                        return;
                    }

                    const resolutionBadge = data.resolution_type === 'refund' ?
                        `<span class="badge bg-light text-dark border">Refund</span>` :
                        `<span class="badge bg-info text-dark">Exchange</span>`;

                    let actionHtml = '';

                    if (data.status === 'pending') {
                        actionHtml = `
                            <div class="d-flex gap-2 mt-4">
                                <a href="${baseUrl}/admin/procurement/approve-supplier-return/${data.return_id}" class="btn btn-success flex-fill" onclick="return confirm('Approve this return request?');"><i class="fas fa-check me-1"></i>Approve</a>
                                <a href="${baseUrl}/admin/procurement/reject-supplier-return/${data.return_id}" class="btn btn-outline-danger flex-fill" onclick="return confirm('Reject this return request?');"><i class="fas fa-times me-1"></i>Reject</a>
                            </div>`;
                    } else if (data.status === 'approved') {
                        if (data.supplier_return_status === 'not_sent') {
                            actionHtml = `
                                <a href="${baseUrl}/admin/procurement/mark-return-sent/${data.return_id}" class="btn btn-dark w-100 mt-4" onclick="return confirm('Confirm you have shipped/returned the item(s) back to the supplier?');">
                                    <i class="fas fa-shipping-fast me-1"></i>Mark as Sent to Supplier
                                </a>`;
                        } else if (data.supplier_return_status === 'sent_to_supplier') {
                            actionHtml = `<div class="alert alert-secondary mt-4 mb-0 small"><i class="fas fa-hourglass-half me-1"></i>Sent back — waiting for the supplier to confirm they've received it.</div>`;
                        } else if (data.supplier_return_status === 'received_by_supplier') {
                            if (data.resolution_type === 'refund') {
                                actionHtml = `<div class="alert alert-success mt-4 mb-0 small"><i class="fas fa-check-circle me-1"></i>Supplier confirmed receipt — process the refund/credit manually and record the reference number above.</div>`;
                            } else if (data.replacement_po_number) {
                                actionHtml = `<div class="alert alert-success mt-4 mb-0 small"><i class="fas fa-check-circle me-1"></i>Replacement PO created: <b>${data.replacement_po_number}</b> (${(data.replacement_po_status || '').replace('_',' ').toUpperCase()})</div>`;
                            } else {
                                actionHtml = `
                                    <a href="${baseUrl}/admin/procurement/create-replacement-po/${data.return_id}" class="btn btn-dark w-100 mt-4" onclick="return confirm('Create a zero-cost replacement PO for this quantity with the same supplier?');">
                                        <i class="fas fa-plus me-1"></i>Create Replacement PO
                                    </a>`;
                            }
                        }
                    }

                    viewContent.innerHTML = `
                        <div class="p-4 bg-light rounded-4 text-center mb-4 border">
                            <i class="fas fa-truck-loading fs-1 text-maroon opacity-25 mb-3"></i>
                            <h5 class="fw-bold mb-1">Return Details</h5>
                            <span class="badge bg-dark">SRT-${String(data.return_id).padStart(4, '0')}</span>
                        </div>
                        <div class="row g-3 px-2 text-start">
                            <div class="col-12"><label class="info-label">Supplier</label><p class="info-value">${data.supplier_name}</p></div>
                            <div class="col-6"><label class="info-label">Reference PO</label><p class="info-value text-primary">${data.po_number}</p></div>
                            <div class="col-6"><label class="info-label">Product</label><p class="info-value">${data.name}</p></div>
                            <div class="col-6"><label class="info-label">Batch</label><p class="info-value">${data.batch_number || 'N/A'}</p></div>
                            <div class="col-6"><label class="info-label">Quantity</label><p class="info-value fw-bold fs-5">${data.quantity}</p></div>
                            <div class="col-6"><label class="info-label">Resolution</label><p class="info-value">${resolutionBadge}</p></div>
                            <div class="col-6"><label class="info-label">${data.resolution_type === 'refund' ? 'Refund Amount' : 'Value of Exchange'}</label><p class="info-value">${data.refund_amount ? peso(data.refund_amount) : 'Not recorded'}</p></div>
                            <div class="col-6"><label class="info-label">Reference #</label><p class="info-value">${data.credit_note_number || 'Not yet issued'}</p></div>
                            <div class="col-6"><label class="info-label">Status</label><p class="info-value text-uppercase">${data.status}</p></div>
                            <div class="col-6"><label class="info-label">Resolved By</label><p class="info-value">${data.resolved_by_name || '—'}</p></div>
                            <div class="col-12 mt-3 pt-3 border-top"><label class="info-label">Reason Stated</label><p class="info-value fw-normal text-muted" style="line-height:1.6">${data.reason}</p></div>
                        </div>
                        ${actionHtml}`;
                })
                .catch(err => {
                    console.error('[Supplier Returns] Fetch failed:', err);
                    viewContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load return details.</div>`;
                });
        });
    });
});