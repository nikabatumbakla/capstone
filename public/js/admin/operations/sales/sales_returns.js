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

    const viewReturnBtns = document.querySelectorAll('.btn-view-return');
    const viewReturnDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('viewReturnDrawer'));
    const viewContent = document.getElementById('viewReturnContent');

    function peso(n) {
        return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    }

    viewReturnBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            viewReturnDrawer.show();
            viewContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${BASE_URL}/admin/sales/get-return-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        viewContent.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                        return;
                    }
                    viewContent.innerHTML = `
                        <div class="p-4 bg-light rounded-4 text-center mb-4 border">
                            <i class="fas fa-undo-alt fs-1 text-maroon opacity-25 mb-3"></i>
                            <h5 class="fw-bold mb-1">Return Details</h5>
                            <span class="badge bg-dark">RTN-${String(data.return_id).padStart(4, '0')}</span>
                        </div>
                        <div class="row g-3 px-2 text-start">
                            <div class="col-12"><label class="info-label">Client</label><p class="info-value">${data.organization}</p></div>
                            <div class="col-6"><label class="info-label">Original Order</label><p class="info-value text-primary">${data.order_number}</p></div>
                            <div class="col-6"><label class="info-label">Product</label><p class="info-value">${data.name || '—'}</p></div>
                            <div class="col-6"><label class="info-label">Batch</label><p class="info-value">${data.batch_number || 'N/A'}</p></div>
                            <div class="col-6"><label class="info-label">Quantity</label><p class="info-value fw-bold fs-5">${data.quantity}</p></div>
                            <div class="col-6"><label class="info-label">Condition</label><p class="info-value text-uppercase">${data.restock_condition}</p></div>
                            <div class="col-6"><label class="info-label">Refund Amount</label><p class="info-value">${data.refund_amount ? peso(data.refund_amount) : 'None recorded'}</p></div>
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

    const orderSelect = document.getElementById('returnOrderSelect');
    const clientInput = document.getElementById('returnClientAuto');
    const productSelect = document.getElementById('returnProductSelect');
    const batchIdField = document.getElementById('returnBatchId');
    const qtyInput = document.getElementById('returnQty');
    const refundInput = document.getElementById('returnRefund');
    const conditionSelect = document.getElementById('returnCondition');
    const conditionHint = document.getElementById('conditionHint');

    if (orderSelect) {
        orderSelect.addEventListener('change', function() {
            const orderId = this.value;
            fetch(`${BASE_URL}/admin/sales/get-return-order-items/${orderId}`)
                .then(res => res.json())
                .then(data => {
                    productSelect.innerHTML = '<option value="">Select product..</option>';
                    if (data.length > 0) {
                        clientInput.value = data[0].organization;
                        data.forEach(item => {
                            productSelect.innerHTML += `<option value="${item.product_id}" data-batch="${item.batch_id || ''}" data-price="${item.unit_price}" data-purchased="${item.quantity}">${item.name} (${item.quantity} purchased)</option>`;
                        });
                    }
                })
                .catch(err => console.error(err));
        });
    }

    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            batchIdField.value = opt.getAttribute('data-batch') || '';
            const price = parseFloat(opt.getAttribute('data-price') || 0);
            const qty = parseFloat(qtyInput.value || 1);
            refundInput.value = (price * qty).toFixed(2);
        });
    }

    if (qtyInput) {
        qtyInput.addEventListener('input', function() {
            const opt = productSelect.options[productSelect.selectedIndex];
            const price = parseFloat(opt.getAttribute('data-price') || 0);
            refundInput.value = (price * parseFloat(this.value || 0)).toFixed(2);
        });
    }

    if (conditionSelect) {
        conditionSelect.addEventListener('change', function() {
            conditionHint.textContent = this.value === 'resellable' ?
                '' :
                'This item will NOT be added back to sellable stock once approved.';
        });
    }
});