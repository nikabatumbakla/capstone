document.addEventListener("DOMContentLoaded", function() {
    const searchForm = document.getElementById('searchForm');
    const liveSearch = document.getElementById('liveSearch');

    // 1. AUTOMATIC INSTANT SEARCH
    let typingTimer;
    if (liveSearch) {
        liveSearch.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => { searchForm.submit(); }, 600);
        });
    }

    // 2. VIEW ACTION DRAWER LOGIC
    const viewReturnBtns = document.querySelectorAll('.btn-view-return');
    const viewReturnDrawer = new bootstrap.Offcanvas(document.getElementById('viewReturnDrawer'));
    const viewContent = document.getElementById('viewReturnContent');

    viewReturnBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            viewReturnDrawer.show();
            viewContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            // FIX: Endpoint changed from get-items to get-details
            fetch(`${BASE_URL}/admin/sales/get-return-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    viewContent.innerHTML = `
                        <div class="p-4 bg-light rounded-4 text-center mb-4 border">
                            <i class="fas fa-undo-alt fs-1 text-maroon opacity-25 mb-3"></i>
                            <h5 class="fw-bold mb-1">Return Intelligence</h5>
                            <span class="badge bg-dark">ID: RTN-0${data.return_id}</span>
                        </div>
                        <div class="row g-3 px-2 text-start">
                            <div class="col-12"><label class="info-label">Entity / Client</label><p class="info-value">${data.organization}</p></div>
                            <div class="col-6"><label class="info-label">Original Order</label><p class="info-value text-primary">${data.order_number}</p></div>
                            <div class="col-6"><label class="info-label">Returned Product</label><p class="info-value">${data.name}</p></div>
                            <div class="col-6"><label class="info-label">Quantity</label><p class="info-value fw-bold fs-5">${data.quantity}</p></div>
                            <div class="col-6"><label class="info-label">Current Status</label><p class="info-value text-uppercase">${data.status}</p></div>
                            <div class="col-12 mt-4 pt-3 border-top"><label class="info-label">Reason Stated</label><p class="info-value fw-normal text-muted" style="line-height:1.6">${data.reason}</p></div>
                        </div>
                    `;
                });
        });
    });

    // 3. FORM AUTO-FILL (CLIENT RETURN)
    const orderSelect = document.getElementById('returnOrderSelect');
    const clientInput = document.getElementById('returnClientAuto');
    const productSelect = document.getElementById('returnProductSelect');

    if (orderSelect) {
        orderSelect.addEventListener('change', function() {
            const orderId = this.value;
            fetch(`${BASE_URL}/admin/sales/get-return-order-items/${orderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length > 0) {
                        clientInput.value = data[0].organization;
                        productSelect.innerHTML = '<option value="">Select product..</option>';
                        data.forEach(item => {
                            productSelect.innerHTML += `<option value="${item.product_id}">${item.name} (${item.quantity} purchased)</option>`;
                        });
                    }
                });
        });
    }
});