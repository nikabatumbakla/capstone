document.addEventListener("DOMContentLoaded", function() {
    let cart = [];
    const searchInput = document.getElementById('posSearch');
    const tenderedInput = document.getElementById('tendered');

    // 1. INTELLIGENT SEARCH (Triggered by Enter)
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value;
            fetch(`${BASE_URL}/admin/sales/get-product-pos/${query}`)
                .then(res => res.json())
                .then(data => {
                    if (data) {
                        addToCart(data);
                        this.value = '';
                    } else {
                        alert('Product intelligence not found or zero stock.');
                    }
                });
        }
    });

    function addToCart(p) {
        const existing = cart.find(i => i.batch_id === p.batch_id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({...p, qty: 1 });
        }
        renderCart();
    }

    function renderCart() {
        const body = document.getElementById('cartTableBody');
        let total = 0;

        body.innerHTML = cart.map((item, index) => {
            const rowTotal = item.sell_price * item.qty;
            const vatAmount = rowTotal - (rowTotal / 1.12); // Standard BIR VAT math
            total += rowTotal;

            return `
                <tr>
                    <td class="ps-3">
                        <span class="fw-bold d-block">${item.name}</span>
                        <small class="text-muted">Batch: ${item.batch_number} | Exp: ${item.expires_at}</small>
                    </td>
                    <td class="text-center fw-bold">${item.qty}</td>
                    <td>₱${parseFloat(item.sell_price).toLocaleString()}</td>
                    <td class="text-muted">₱${vatAmount.toFixed(2)}</td>
                    <td class="fw-bold">₱${rowTotal.toLocaleString()}</td>
                    <td class="text-end text-danger px-3" style="cursor:pointer" onclick="removeItem(${index})"><i class="fas fa-times-circle"></i></td>
                </tr>
            `;
        }).join('');

        const vatTotal = total - (total / 1.12);
        document.getElementById('subtotal').innerText = `₱ ${(total - vatTotal).toLocaleString()}`;
        document.getElementById('vat').innerText = `₱ ${vatTotal.toLocaleString()}`;
        document.getElementById('grandTotal').innerText = `₱ ${total.toLocaleString()}`;
        calculateChange();
    }

    window.removeItem = (index) => {
        cart.splice(index, 1);
        renderCart();
    };

    function calculateChange() {
        const grandTotal = parseFloat(document.getElementById('grandTotal').innerText.replace('₱', '').replace(',', ''));
        const tendered = parseFloat(tenderedInput.value) || 0;
        if (tendered >= grandTotal) {
            document.getElementById('change').value = `₱ ${(tendered - grandTotal).toLocaleString()}`;
        } else {
            document.getElementById('change').value = `₱ 0.00`;
        }
    }

    tenderedInput.addEventListener('input', calculateChange);
});