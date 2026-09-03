document.addEventListener("DOMContentLoaded", function() {
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
    const viewDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('viewReturnDrawer'));
    const viewContent = document.getElementById('viewReturnContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            viewDrawer.show();
            viewContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${BASE_URL}/admin/procurement/get-supplier-return-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        viewContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                        return;
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
                            <div class="col-6"><label class="info-label">Refund / Credit</label><p class="info-value">${data.refund_amount ? peso(data.refund_amount) : 'None recorded'}</p></div>
                            <div class="col-6"><label class="info-label">Credit Note #</label><p class="info-value">${data.credit_note_number || 'Not yet issued'}</p></div>
                            <div class="col-6"><label class="info-label">Status</label><p class="info-value text-uppercase">${data.status}</p></div>
                            <div class="col-6"><label class="info-label">Resolved By</label><p class="info-value">${data.resolved_by_name || '—'}</p></div>
                            <div class="col-12 mt-3 pt-3 border-top"><label class="info-label">Reason Stated</label><p class="info-value fw-normal text-muted" style="line-height:1.6">${data.reason}</p></div>
                        </div>`;
                })
                .catch(err => {
                    viewContent.innerHTML = `<div class="text-center text-danger p-5">Failed to load return details.</div>`;
                    console.error(err);
                });
        });
    });

    const poSelect = document.getElementById('returnPoSelect');
    const productSelect = document.getElementById('returnPoProductSelect');
    const batchIdField = document.getElementById('returnPoBatchId');
    const qtyInput = document.getElementById('returnPoQty');
    const refundInput = document.getElementById('returnPoRefund');

    if (poSelect) {
        poSelect.addEventListener('change', function() {
            const poId = this.value;
            fetch(`${BASE_URL}/admin/procurement/get-po-items-for-return/${poId}`)
                .then(res => res.json())
                .then(data => {
                    productSelect.innerHTML = data.length ?
                        data.map(item => `<option value="${item.product_id}" data-batch="${item.batch_id || ''}" data-cost="${item.unit_cost}">${item.name} (${item.qty_received} received)</option>`).join('') :
                        `<option value="">No received items on this PO</option>`;
                })
                .catch(err => console.error(err));
        });
    }

    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            batchIdField.value = opt.getAttribute('data-batch') || '';
            const cost = parseFloat(opt.getAttribute('data-cost') || 0);
            const qty = parseFloat(qtyInput.value || 1);
            refundInput.value = (cost * qty).toFixed(2);
        });
    }

    if (qtyInput) {
        qtyInput.addEventListener('input', function() {
            const opt = productSelect.options[productSelect.selectedIndex];
            const cost = parseFloat(opt.getAttribute('data-cost') || 0);
            refundInput.value = (cost * parseFloat(this.value || 0)).toFixed(2);
        });
    }
});