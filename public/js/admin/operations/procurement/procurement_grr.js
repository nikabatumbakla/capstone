document.addEventListener("DOMContentLoaded", function() {
    const recordBtns = document.querySelectorAll('.btn-record-grr');
    const grrDrawer = new bootstrap.Offcanvas(document.getElementById('grrDrawer'));
    const content = document.getElementById('grrContent');

    recordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const poId = this.getAttribute('data-id');
            grrDrawer.show();
            content.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>';

            fetch(`${window.location.origin}/PharMediSync/admin/procurement/get-po-details/${poId}`)
                .then(res => res.json())
                .then(data => {
                    let itemsTable = data.items.map(i => `
                        <tr>
                            <td>${i.name}</td>
                            <td class="text-center">${i.qty_ordered}</td>
                            <td>
                                <input type="hidden" name="product_ids[]" value="${i.product_id}">
                                <input type="hidden" name="qty_expected[]" value="${i.qty_ordered}">
                                <input type="number" name="qty_received[]" class="form-control form-control-sm text-center fw-bold border-maroon" value="${i.qty_ordered}" style="width:80px">
                            </td>
                        </tr>
                    `).join('');

                    content.innerHTML = `
                        <form action="${window.location.origin}/PharMediSync/admin/procurement/save-grr" method="POST">
                            <input type="hidden" name="po_id" value="${data.po.po_id}">
                            <div class="p-3 bg-light rounded-4 mb-4">
                                <p class="info-label mb-1">Verify Delivery for:</p>
                                <h6 class="fw-bold mb-0">${data.po.po_number}</h6>
                                <small class="text-muted">Supplier: ${data.po.sname}</small>
                            </div>

                            <table class="table table-sm" style="font-size:11px">
                                <thead><tr class="table-dark"><th>Product</th><th class="text-center">Ordered</th><th>Received</th></tr></thead>
                                <tbody>${itemsTable}</tbody>
                            </table>

                            <div class="alert alert-warning border-0 small mt-4" style="font-size:10px">
                                <i class="fas fa-info-circle me-2"></i> <b>Discrepancy Note:</b> If the received quantity is different from ordered, inventory will only be updated with the "Received" amount.
                            </div>

                            <button type="submit" class="btn btn-maroon w-100 py-3 mt-4 fw-bold shadow">UPDATE INVENTORY & CLOSE PO</button>
                        </form>
                    `;
                });
        });
    });
});