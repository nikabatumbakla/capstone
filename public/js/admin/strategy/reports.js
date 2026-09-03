document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById('filterForm');
    document.getElementById('movementFilter').addEventListener('change', () => filterForm.submit());

    let typingTimer;
    document.getElementById('liveSearch').addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => filterForm.submit(), 600);
    });

    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('movementDrawer'));
    const content = document.getElementById('movementContent');

    document.querySelectorAll('.btn-view-movement').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            drawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${BASE_URL}/admin/strategy/analytics/get-movement-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }
                    content.innerHTML = `
                        <div class="p-4">
                            <div class="row g-3">
                                <div class="col-12"><label class="info-label">Product</label><p class="info-value">${data.pname} (${data.sku || '—'})</p></div>
                                <div class="col-6"><label class="info-label">Batch</label><p class="info-value">${data.batch_number || 'N/A'}</p></div>
                                <div class="col-6"><label class="info-label">Barcode</label><p class="info-value">${data.barcode_value || 'N/A'}</p></div>
                                <div class="col-6"><label class="info-label">Movement Type</label><p class="info-value">${data.movement_type.toUpperCase()}</p></div>
                                <div class="col-6"><label class="info-label">Quantity</label><p class="info-value fw-bold">${data.quantity}</p></div>
                                <div class="col-6"><label class="info-label">Reference</label><p class="info-value">${data.reference_type || '—'} #${data.reference_id || ''}</p></div>
                                <div class="col-6"><label class="info-label">Handled By</label><p class="info-value">${data.staff || 'System'}</p></div>
                                <div class="col-12"><label class="info-label">Reason / Notes</label><p class="info-value text-muted">${data.reason || data.notes || 'None recorded'}</p></div>
                                <div class="col-12"><label class="info-label">Timestamp</label><p class="info-value">${data.moved_at}</p></div>
                            </div>
                        </div>`;
                })
                .catch(err => { content.innerHTML = `<div class="text-danger text-center p-5">Failed to load details.</div>`;
                    console.error(err); });
        });
    });
});