document.addEventListener("DOMContentLoaded", function() {
    const searchForm = document.getElementById('searchForm');
    const liveSearch = document.getElementById('liveSearch');
    const typeFilter = document.getElementById('typeFilter');

    // 1. INSTANT SEARCH & FILTER (Across all 37 clients)
    let typingTimer;
    liveSearch.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            searchForm.submit(); // Automatically submit after 500ms of typing
        }, 500);
    });

    typeFilter.addEventListener('change', function() {
        searchForm.submit(); // Automatically submit on category change
    });

    // 2. VIEW INTELLIGENCE (Connection Fix)
    const viewBtns = document.querySelectorAll('.btn-view-client');
    const clientDrawerEl = document.getElementById('clientDrawer');
    const clientDrawer = new bootstrap.Offcanvas(clientDrawerEl);
    const content = document.getElementById('clientDrawerContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            clientDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            // Standardize Fetch URL
            const url = `${BASE_URL}/admin/sales/get-client-details/${id}`;

            fetch(url)
                .then(res => {
                    if (!res.ok) throw new Error('Route not found');
                    return res.json();
                })
                .then(data => {
                    const c = data.client;
                    let ordersHtml = data.orders.map(o => `
                        <div class="p-3 border rounded-4 mb-2 bg-white shadow-sm text-start">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold mb-0" style="font-size:11px">${o.order_number}</h6>
                                <span class="badge bg-light text-dark border small">${o.status.toUpperCase()}</span>
                            </div>
                            <small class="text-muted">${o.created_at}</small>
                            <h6 class="text-maroon mt-2 mb-0 fw-bold">₱${parseFloat(o.total).toLocaleString()}</h6>
                        </div>
                    `).join('');

                    content.innerHTML = `
                        <div class="p-4 border-bottom bg-light">
                            <h5 class="fw-bold mb-1 text-start">${c.organization}</h5>
                            <span class="badge bg-dark d-inline-block">${c.client_type.toUpperCase()}</span>
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-4 text-start">
                                <div class="col-6"><label class="info-label">Current Balance</label><p class="info-value text-danger">₱${parseFloat(c.credit_used).toLocaleString()}</p></div>
                                <div class="col-6"><label class="info-label">Credit Limit</label><p class="info-value">₱${parseFloat(c.credit_limit).toLocaleString()}</p></div>
                                <div class="col-12"><label class="info-label">Address</label><p class="info-value text-muted" style="font-size:10px">${c.address || 'N/A'}</p></div>
                            </div>
                            <h6 class="fw-bold mb-3 border-bottom pb-2 text-start">ORDER HISTORY</h6>
                            <div style="max-height: 400px; overflow-y: auto;">
                                ${ordersHtml || '<p class="text-center text-muted py-4 small">No history.</p>'}
                            </div>
                        </div>
                    `;
                })
                .catch(err => {
                    content.innerHTML = `<div class="alert alert-danger m-3 small text-center">Error: Could not retrieve data. Check Routes.php</div>`;
                });
        });
    });
});