document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById('filterForm');
    const liveSearch = document.getElementById('liveSearch');
    const typeFilter = document.getElementById('typeFilter');

    // 1. INSTANT AUTOMATIC SEARCH (Across all database records)
    let typingTimer;
    liveSearch.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            filterForm.submit(); // Submit form automatically 0.6s after user stops typing
        }, 600);
    });

    typeFilter.addEventListener('change', function() {
        filterForm.submit(); // Submit form automatically on category change
    });

    // 2. VIEW ORDER INTELLIGENCE (Fixes "Not showing details")
    const viewBtns = document.querySelectorAll('.btn-view-so');
    const soDrawer = new bootstrap.Offcanvas(document.getElementById('soDrawer'));
    const content = document.getElementById('soDrawerContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            soDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            // Standardize absolute path using BASE_URL defined in PHP
            fetch(`${BASE_URL}/admin/sales/get-order-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    const o = data.order;
                    let itemsHtml = data.items.map(i => `
                        <tr>
                            <td style="border-bottom:1px solid #eee; padding:8px;"><b>${i.name}</b><br><small>${i.sku}</small></td>
                            <td style="border-bottom:1px solid #eee; padding:8px; text-align:center;">${i.quantity}</td>
                            <td style="border-bottom:1px solid #eee; padding:8px; text-align:right;">₱${parseFloat(i.unit_price).toLocaleString()}</td>
                        </tr>`).join('');

                    content.innerHTML = `
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Order Intelligence: ${o.order_number}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                        </div>
                        <div class="p-4">
                            <div class="row g-2 mb-4 bg-light p-3 rounded-4">
                                <div class="col-6"><small class="info-label">Organization</small><p class="mb-0 fw-bold">${o.organization}</p></div>
                                <div class="col-6 text-end"><small class="info-label">Total Amount</small><h5 class="fw-bold text-maroon">₱${parseFloat(o.total).toLocaleString()}</h5></div>
                            </div>
                            <table class="table table-sm border-bottom" style="font-size:11px">
                                <thead><tr class="table-dark"><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit</th></tr></thead>
                                <tbody>${itemsHtml}</tbody>
                            </table>
                            <div class="mt-5">
                                <button class="btn btn-dark w-100 py-3 fw-bold rounded-pill" onclick="window.print()">PRINT BUSINESS INVOICE</button>
                            </div>
                        </div>`;
                })
                .catch(err => {
                    content.innerHTML = `<div class="alert alert-danger m-3 small text-center">Error: Could not retrieve order data.</div>`;
                });
        });
    });

    // 3. ADD ITEM ROW LOGIC
    document.getElementById('btnAddItemRow') ? .addEventListener('click', function() {
        const container = document.getElementById('new-order-rows');
        const firstRow = document.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('input').value = 1;
        container.appendChild(newRow);
    });
});