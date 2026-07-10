document.addEventListener("DOMContentLoaded", function() {
    let cart = [];

    // Search/Scan Placeholder Logic
    const posSearch = document.getElementById('posSearch');
    posSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            // Mock adding "Pulse Oximeter" for demo
            addToCart({ id: 1, name: 'Pulse Oximeter', price: 850, batch: 'B2026-01' });
            this.value = '';
        }
    });

    function addToCart(item) {
        cart.push(item);
        renderCart();
    }

    function renderCart() {
        const body = document.getElementById('cartBody');
        let total = 0;
        body.innerHTML = cart.map((item, index) => {
            total += item.price;
            return `
                <tr>
                    <td class="fw-bold">${item.name}</td>
                    <td>${item.batch}</td>
                    <td>₱${item.price}</td>
                    <td><input type="number" class="form-control form-control-sm border-0" value="1"></td>
                    <td class="fw-bold">₱${item.price}</td>
                    <td class="text-end"><button class="btn btn-link text-danger p-0"><i class="fas fa-times"></i></button></td>
                </tr>
            `;
        }).join('');

        document.getElementById('displayTotal').innerText = `₱ ${total.toLocaleString()}`;
        updateChange(total);
    }

    function updateChange(total) {
        const tendered = document.getElementById('amountTendered').value;
        if (tendered > 0) {
            document.getElementById('displayChange').innerText = `₱ ${(tendered - total).toLocaleString()}`;
        }
    }

    document.getElementById('amountTendered').addEventListener('input', function() {
        const totalText = document.getElementById('displayTotal').innerText.replace('₱ ', '').replace(',', '');
        updateChange(parseFloat(totalText));
    });
});