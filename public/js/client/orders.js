document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById('item-rows');
    const totalDisplay = document.getElementById('grandTotalDisplay');
    const totalHidden = document.getElementById('grandTotalHidden');

    // 1. ADD ROW
    document.getElementById('btnAddRow').addEventListener('click', function() {
        const firstRow = document.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.qty-input').value = 1;
        container.appendChild(newRow);

        newRow.querySelector('.remove-row').addEventListener('click', () => {
            newRow.remove();
            calculateTotal();
        });

        // Re-attach change listeners to new row
        newRow.querySelector('.product-select').addEventListener('change', calculateTotal);
        newRow.querySelector('.qty-input').addEventListener('input', calculateTotal);
    });

    // 2. CALCULATION LOGIC
    function calculateTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.product-select');
            const qty = row.querySelector('.qty-input').value;
            const price = select.options[select.selectedIndex].getAttribute('data-price');

            if (price && qty) {
                grandTotal += (parseFloat(price) * parseInt(qty));
            }
        });
        totalDisplay.innerText = "₱ " + grandTotal.toLocaleString(undefined, { minimumFractionDigits: 2 });
        totalHidden.value = grandTotal;
    }

    // Initial listeners
    document.querySelector('.product-select').addEventListener('change', calculateTotal);
    document.querySelector('.qty-input').addEventListener('input', calculateTotal);
});