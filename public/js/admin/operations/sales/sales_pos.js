document.addEventListener("DOMContentLoaded", function() {
            let cart = [];

            const searchInput = document.getElementById('posSearch');
            const categorySelect = document.getElementById('categorySelect');
            const tenderedInput = document.getElementById('tendered');
            const discountType = document.getElementById('discountType');
            const discountIdRow = document.getElementById('discountIdRow');
            const productGrid = document.getElementById('productGrid');
            const cashFields = document.getElementById('cashFields');
            const gcashFields = document.getElementById('gcashFields');

            function peso(n) {
                return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            setInterval(() => { document.getElementById('liveClock').textContent = new Date().toLocaleString(); }, 1000);

            // ============ PAYMENT TYPE TOGGLE ============
            document.querySelectorAll('input[name="payType"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const isGcash = this.value === 'gcash';
                    cashFields.style.display = isGcash ? 'none' : 'flex';
                    gcashFields.style.display = isGcash ? 'block' : 'none';
                    recalcTotals();
                });
            });

            // ============ PRODUCT GRID ============
            function renderProductGrid() {
                const term = searchInput.value.trim().toLowerCase();
                const catId = categorySelect.value;
                const matches = ALL_PRODUCTS.filter(p => {
                    const inCategory = !catId || p.category_id == catId;
                    const matchesSearch = !term || p.name.toLowerCase().includes(term) || (p.sku && p.sku.toLowerCase().includes(term)) || (p.barcode_value && p.barcode_value.includes(term));
                    return inCategory && matchesSearch;
                });

                productGrid.innerHTML = matches.length ? matches.map(p => `
            <div class="col-6">
                <button type="button" class="btn btn-outline-dark w-100 h-100 text-start p-2 product-tile" data-batch="${p.batch_id}" style="border-radius:8px;">
                    <span class="fw-bold d-block">${p.name}</span>
                    <span class="d-block" style="color:#888;">${p.batch_number} • Exp ${p.expires_at || 'N/A'}${p.is_vat_exempt == 1 ? ' • VAT-Exempt' : ''}</span>
                    <span class="d-flex justify-content-between mt-1">
                        <span class="text-maroon fw-bold">${peso(p.sell_price)}</span>
                        <span style="color:#888;">${p.quantity_avail} left</span>
                    </span>
                </button>
            </div>
        `).join('') : `<div class="col-12 text-center py-4" style="color:#888;">No matching products.</div>`;

                productGrid.querySelectorAll('.product-tile').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const product = ALL_PRODUCTS.find(p => p.batch_id == this.getAttribute('data-batch'));
                        if (product) addToCart(product);
                    });
                });
            }
            categorySelect.addEventListener('change', renderProductGrid);
            searchInput.addEventListener('input', renderProductGrid);

            // ============ CART ============
            function addToCart(p) {
                const existing = cart.find(i => i.batch_id === p.batch_id);
                if (existing) {
                    if (existing.qty + 1 > p.quantity_avail) { alert(`Only ${p.quantity_avail} unit(s) of ${p.name} available.`); return; }
                    existing.qty += 1;
                } else {
                    cart.push({...p, qty: 1 });
                }
                renderCart();
            }

            function renderCart() {
                const body = document.getElementById('cartTableBody');
                body.innerHTML = cart.map((item, index) => `
            <tr>
                <td><span class="fw-bold d-block">${item.name}</span><span style="color:#888;">${item.batch_number}</span></td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <button class="btn btn-outline-dark qty-btn btn-qty-minus" data-index="${index}">−</button>
                        <span class="fw-bold mx-1">${item.qty}</span>
                        <button class="btn btn-outline-dark qty-btn btn-qty-plus" data-index="${index}">+</button>
                    </div>
                </td>
                <td class="text-end fw-bold">${peso(item.sell_price * item.qty)}</td>
                <td class="text-end"><i class="fas fa-times-circle text-danger btn-remove-item" data-index="${index}" style="cursor:pointer;"></i></td>
            </tr>
        `).join('');

                body.querySelectorAll('.btn-qty-plus').forEach(btn => btn.addEventListener('click', function() {
                    const i = this.getAttribute('data-index');
                    if (cart[i].qty + 1 > cart[i].quantity_avail) { alert('No more stock available.'); return; }
                    cart[i].qty++;
                    renderCart();
                }));
                body.querySelectorAll('.btn-qty-minus').forEach(btn => btn.addEventListener('click', function() {
                    const i = this.getAttribute('data-index');
                    cart[i].qty = Math.max(1, cart[i].qty - 1);
                    renderCart();
                }));
                body.querySelectorAll('.btn-remove-item').forEach(btn => btn.addEventListener('click', function() {
                    cart.splice(this.getAttribute('data-index'), 1);
                    renderCart();
                }));

                recalcTotals();
            }

            // ============ TOTALS — VAT computed only on non-exempt items, mirrors server exactly ============
            function recalcTotals() {
                let vatableGross = 0,
                    exemptGross = 0;
                cart.forEach(i => {
                    const lineTotal = i.sell_price * i.qty;
                    if (i.is_vat_exempt == 1) exemptGross += lineTotal;
                    else vatableGross += lineTotal;
                });
                const gross = vatableGross + exemptGross;

                let discountAmount = 0,
                    netTotal, vatAmount, subtotal;

                if (discountType.value === 'pwd' || discountType.value === 'senior') {
                    const vatExclusiveBase = (vatableGross / (1 + VAT_RATE / 100)) + exemptGross;
                    discountAmount = vatExclusiveBase * 0.20;
                    netTotal = vatExclusiveBase - discountAmount;
                    vatAmount = 0;
                    subtotal = netTotal;
                } else {
                    vatAmount = vatableGross - (vatableGross / (1 + VAT_RATE / 100));
                    subtotal = (vatableGross / (1 + VAT_RATE / 100)) + exemptGross;
                    netTotal = gross;
                }

                document.getElementById('calcGross').textContent = peso(gross);
                document.getElementById('calcDiscount').textContent = '-' + peso(discountAmount);
                document.getElementById('calcSubtotal').textContent = peso(subtotal);
                document.getElementById('calcVat').textContent = peso(vatAmount);
                document.getElementById('calcTotal').textContent = peso(netTotal);

                calculateChange(netTotal);
            }

            discountType.addEventListener('change', function() {
                discountIdRow.style.display = (this.value === 'pwd' || this.value === 'senior') ? 'flex' : 'none';
                recalcTotals();
            });

            function calculateChange(total) {
                const tendered = parseFloat(tenderedInput.value) || 0;
                document.getElementById('change').value = peso(Math.max(0, tendered - total));
            }
            tenderedInput.addEventListener('input', () => recalcTotals());

            // ============ RECEIPT ============
            function buildReceiptHtml(txn, items, store) {
                const rows = items.map(i => `<tr><td>${i.name}</td><td style="text-align:center;">${i.qty}</td><td style="text-align:right;">${peso(i.price)}</td><td style="text-align:right;">${peso(i.subtotal)}</td></tr>`).join('');
                return `
            <div style="font-family:monospace; padding:20px; width:300px; font-size:11px;">
                <div style="text-align:center;">
                    <h5 style="margin:0;">${store.store_name || 'Store'}</h5>
                    <p style="margin:0;">${store.store_address || ''}</p>
                    <p style="margin:0;">TIN: ${store.store_tin || 'N/A'}</p>
                </div>
                <hr>
                <p>OR #: ${txn.or_number}<br>Date: ${txn.created_at}<br>Payment: ${txn.payment_method.toUpperCase()}${txn.gcash_ref ? ' (Ref: ' + txn.gcash_ref + ')' : ''}</p>
                <table style="width:100%;">${rows}</table>
                <hr>
                <p>
                    Subtotal: ${peso(txn.subtotal)}<br>
                    VAT: ${peso(txn.vat_amount)}<br>
                    Discount: -${peso(txn.discount)}<br>
                    <b>TOTAL: ${peso(txn.total)}</b><br>
                    ${txn.payment_method === 'cash' ? `Tendered: ${peso(txn.amount_tendered)}<br>Change: ${peso(txn.change_due)}<br>` : ''}
                </p>
                <p style="text-align:center;">Thank you for your purchase!</p>
            </div>`;
    }

    document.getElementById('btnComplete').addEventListener('click', function() {
        if (cart.length === 0) { alert('Cart is empty.'); return; }
        const payType = document.querySelector('input[name="payType"]:checked').value;
        const gcashRef = document.getElementById('gcashRef').value.trim();
        if (payType === 'gcash' && gcashRef === '') { alert('Please enter the GCash reference number.'); return; }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

        fetch(`${BASE_URL}/admin/sales/pos/process`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                items: JSON.stringify(cart.map(i => ({ product_id: i.product_id, batch_id: i.batch_id, qty: i.qty }))),
                discount_type: discountType.value,
                discount_id_number: document.getElementById('discountIdNumber').value,
                discount_holder_name: document.getElementById('discountHolderName').value,
                customer_name: document.getElementById('customerName').value,
                payment_method: payType,
                tendered: tenderedInput.value || 0,
                gcash_ref: gcashRef
            })
        })
        .then(res => res.json())
        .then(data => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check-circle me-2"></i>COMPLETE TRANSACTION';
            if (data.error) { alert(data.error); return; }

            const printWindow = window.open('', '_blank', 'width=350,height=600');
            printWindow.document.write(`<html><head><title>${data.txn.or_number}</title></head><body>${buildReceiptHtml(data.txn, data.items, STORE_INFO)}</body></html>`);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();

            location.reload();
        })
        .catch(err => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check-circle me-2"></i>COMPLETE TRANSACTION';
            alert('Transaction failed. Please try again.');
            console.error(err);
        });
    });

    renderProductGrid();
});