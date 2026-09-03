document.addEventListener("DOMContentLoaded", function() {
    const editDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('editCatalogDrawer'));
    const content = document.getElementById('editCatalogContent');

    document.querySelectorAll('.btn-edit-catalog').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
            editDrawer.show();

            fetch(`${BASE_URL}/supplier/inventory/catalog/get-entry/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }

                    content.innerHTML = `
                        <form action="${BASE_URL}/supplier/inventory/catalog/update" method="POST">
                            <input type="hidden" name="catalog_id" value="${data.catalog_id}">
                            <div class="p-3 bg-light rounded-4 mb-3">
                                <p class="mb-0 fw-bold">${data.product_name}</p>
                                <small class="text-muted">Robin Rose SKU: ${data.global_sku}</small>
                            </div>
                            <div class="mb-3"><label class="formal-label">Your SKU</label><input type="text" name="supplier_sku" class="formal-input" value="${data.supplier_sku || ''}"></div>
                            <div class="row g-3 mb-3">
                                <div class="col-6"><label class="formal-label">Unit Cost (₱) *</label><input type="number" step="0.01" name="unit_cost" class="formal-input" value="${data.unit_cost}" required></div>
                                <div class="col-6"><label class="formal-label">Minimum Order Qty</label><input type="number" name="minimum_order_qty" class="formal-input" value="${data.minimum_order_qty}"></div>
                            </div>
                            <div class="mb-4"><label class="formal-label">Lead Time (days)</label><input type="number" name="lead_time_days" class="formal-input" value="${data.lead_time_days}"></div>
                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-pill shadow">✓ SAVE CHANGES</button>
                        </form>`;
                })
                .catch(() => { content.innerHTML = `<div class="text-danger text-center p-5">Failed to load entry.</div>`; });
        });
    });
});