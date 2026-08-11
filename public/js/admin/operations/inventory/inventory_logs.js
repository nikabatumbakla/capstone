document.addEventListener("DOMContentLoaded", function() {
    const viewLogBtns = document.querySelectorAll('.btn-view-log');
    const logDrawer = new bootstrap.Offcanvas(document.getElementById('logDrawer'));
    const content = document.getElementById('logDrawerContent');

    // Search function
    const searchInput = document.getElementById('logSearch');
    searchInput.addEventListener('keyup', function() {
        let filter = searchInput.value.toLowerCase();
        let rows = document.querySelectorAll('#logTableBody tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });

    viewLogBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            logDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${window.location.origin}/PharMediSync/admin/inventory/get-log-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    content.innerHTML = `
                        <div class="text-center mb-4 p-4 bg-light rounded-4">
                            <i class="fas fa-clipboard-check fs-1 text-maroon opacity-25 mb-3"></i>
                            <h6 class="fw-bold mb-1">${data.product_name}</h6>
                            <p class="text-muted small">${data.sku}</p>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-6"><p class="info-label mb-0">Adjusted By</p><p class="info-value text-primary">${data.full_name}</p></div>
                            <div class="col-6"><p class="info-label mb-0">Timestamp</p><p class="info-value">${data.adjusted_at}</p></div>
                            <div class="col-6"><p class="info-label mb-0">Batch Link</p><p class="info-value">${data.batch_number || 'Global'}</p></div>
                            <div class="col-6"><p class="info-label mb-0">Reason Category</p><p class="info-value text-maroon">${data.reason}</p></div>
                        </div>

                        <div class="p-3 rounded-4 bg-dark text-white mb-4 shadow">
                            <div class="row text-center">
                                <div class="col-4 border-end border-white-10"><small class="opacity-50">BEFORE</small><h4 class="mb-0">${data.qty_before}</h4></div>
                                <div class="col-4 border-end border-white-10"><small class="opacity-50">AFTER</small><h4 class="mb-0">${data.qty_after}</h4></div>
                                <div class="col-4"><small class="opacity-50">DELTA</small><h4 class="mb-0 text-warning">${data.qty_after - data.qty_before}</h4></div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-4 border">
                            <p class="info-label mb-2">Staff Remarks / Notes</p>
                            <p class="mb-0 text-dark" style="font-size: 12px; line-height: 1.6;">"${data.notes || 'No detailed remarks provided for this adjustment.'}"</p>
                        </div>

                        <button class="btn btn-outline-dark w-100 mt-5 py-2 fw-bold" onclick="window.print()">PRINT AUDIT RECEIPT</button>
                    `;
                });
        });
    });
});