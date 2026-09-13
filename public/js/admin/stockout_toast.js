document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById('stockoutToastContainer');

            function showToast(event) {
                const link = event.po_id ?
                    `${BASE_URL}/admin/procurement/purchase-orders?highlight=${event.po_id}` :
                    `${BASE_URL}/admin/sales/sales-orders`;

                const toast = document.createElement('a');
                toast.href = link;
                toast.className = 'text-decoration-none';
                toast.style.cssText = 'display:block; background:#fff; border-left:5px solid #c0392b; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.15); padding:14px 18px; width:320px; animation: slideIn 0.3s ease;';
                toast.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle text-danger me-2 mt-1"></i>
                <div>
                    <p class="mb-1 fw-bold text-dark" style="font-size:12px;">Out of Stock: ${event.product_name}</p>
                    <small class="text-muted" style="font-size:10px;">
                        ${event.po_number ? `PO ${event.po_number} is pending — click to view` : 'No active purchase order — click to check Sales Orders'}
                    </small>
                </div>
            </div>`;
        container.appendChild(toast);

        setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.4s'; setTimeout(() => toast.remove(), 400); }, 10000);
    }

    function pollStockouts() {
        fetch(`${BASE_URL}/admin/management/alerts/check-stockout`)
            .then(res => res.json())
            .then(events => events.forEach(showToast))
            .catch(err => console.error(err));
    }

    pollStockouts();
    setInterval(pollStockouts, 15000); // check every 15 seconds while admin is on any page
});