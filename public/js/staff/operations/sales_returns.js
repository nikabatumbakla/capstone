document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById('filterForm');
    let typingTimer;
    document.getElementById('liveSearch').addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => filterForm.submit(), 600);
    });

    const orderSelect = document.getElementById('returnOrderSelect');
    const clientInput = document.getElementById('returnClientAuto');
    const productSelect = document.getElementById('returnProductSelect');
    const batchIdField = document.getElementById('returnBatchId');

    orderSelect.addEventListener('change', function() {
        const orderId = this.value;
        fetch(`${BASE_URL}/staff/operations/get-return-order-items/${orderId}`)
            .then(res => res.json())
            .then(data => {
                productSelect.innerHTML = '<option value="">Select product..</option>';
                if (data.length > 0) {
                    clientInput.value = data[0].organization;
                    data.forEach(item => {
                        productSelect.innerHTML += `<option value="${item.product_id}" data-batch="${item.batch_id || ''}">${item.name} (${item.quantity} purchased)</option>`;
                    });
                }
            })
            .catch(err => console.error(err));
    });

    productSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        batchIdField.value = opt.getAttribute('data-batch') || '';
    });
});