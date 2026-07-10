document.addEventListener("DOMContentLoaded", function() {
    const viewBtns = document.querySelectorAll('.btn-view-client');
    const clientDrawer = new bootstrap.Offcanvas(document.getElementById('clientDrawer'));
    const content = document.getElementById('clientDrawerContent');

    // 1. Real-time Search
    const searchInput = document.getElementById('clientSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            let filter = searchInput.value.toLowerCase();
            document.querySelectorAll('#clientTableBody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
            });
        });
    }

    // 2. View Intelligence & History
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            clientDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${window.location.origin}/PharMediSync/admin/sales/get-client-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    let ordersHtml = data.orders.map(o => `
                        <div class="p-3 border rounded-4 mb-2 bg-white shadow-sm">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-0" style="font-size:11px">${o.order_number}</h6>
                                    <small class="text-muted" style="font-size:9px">${o.created_at}</small>
                                </div>
                                <span class="badge bg-light text-dark border">${o.status.toUpperCase()}</span>
                            </div>
                            <div class="mt-2 fw-bold text-maroon">₱${parseFloat(o.total).toLocaleString()}</div>
                        </div>
                    `).join('');

                    content.innerHTML = `
                        <div class="p-4 border-bottom bg-light">
                            <h5 class="fw-bold mb-1">${data.client.organization}</h5>
                            <span class="badge bg-dark">${data.client.client_type.toUpperCase()}</span>
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-4">
                                <div class="col-6"><label class="info-label">Current Balance</label><p class="info-value text-danger">₱${parseFloat(data.client.credit_used).toLocaleString()}</p></div>
                                <div class="col-6"><label class="info-label">Credit Limit</label><p class="info-value">₱${parseFloat(data.client.credit_limit).toLocaleString()}</p></div>
                            </div>
                            
                            <h6 class="fw-bold mb-3 border-bottom pb-2">ORDER HISTORY</h6>
                            <div style="max-height: 400px; overflow-y: auto;" class="pe-2">
                                ${ordersHtml || '<p class="text-center text-muted py-4">No transaction history found.</p>'}
                            </div>

                            <div class="mt-5 pt-4 d-grid gap-2">
                                <button class="btn btn-maroon py-3 fw-bold rounded-3">CREATE NEW BULK ORDER</button>
                                <button class="btn btn-outline-dark py-2">MANAGE PAYMENTS</button>
                            </div>
                        </div>
                    `;
                });
        });
    });
});