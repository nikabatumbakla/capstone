document.addEventListener("DOMContentLoaded", function() {
    const viewBtns = document.querySelectorAll('.btn-view-supplier');
    const drawer = new bootstrap.Offcanvas(document.getElementById('supplierDrawer'));
    const content = document.getElementById('supplierDrawerContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            drawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${window.location.origin}/PharMediSync/admin/procurement/get-supplier-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    content.innerHTML = `
                        <div class="text-center mb-4 p-4 bg-light rounded-4">
                            <i class="fas fa-building fs-1 text-maroon opacity-25 mb-3"></i>
                            <h5 class="fw-bold mb-1">${data.name}</h5>
                            <p class="text-muted small mb-0">${data.contact_person || 'No primary contact'}</p>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-12"><label class="info-label">Address</label><p class="info-value">${data.address || 'N/A'}</p></div>
                            <div class="col-6"><label class="info-label">Email</label><p class="info-value text-primary">${data.email || 'N/A'}</p></div>
                            <div class="col-6"><label class="info-label">Phone</label><p class="info-value">${data.phone || 'N/A'}</p></div>
                        </div>
                        <div class="p-3 border rounded-3">
                            <h6 class="fw-bold small mb-2"><i class="fas fa-truck me-2"></i>Procurement Note</h6>
                            <p class="extra-small mb-0 text-muted">Standard lead time for this supplier is ${data.lead_time_days} days. Average order accuracy is recorded at 94%.</p>
                        </div>
                    `;
                });
        });
    });
});