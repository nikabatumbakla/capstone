document.addEventListener("DOMContentLoaded", function() {
            let cart = [];
            let products = Array.isArray(ALL_PRODUCTS) ? ALL_PRODUCTS.slice() : [];

            const searchInput = document.getElementById('posSearch');
            const categorySelect = document.getElementById('categorySelect');
            const tenderedInput = document.getElementById('tendered');
            const discountType = document.getElementById('discountType');
            const discountIdRow = document.getElementById('discountIdRow');
            const productGrid = document.getElementById('productGrid');
            const cashFields = document.getElementById('cashFields');
            const gcashFields = document.getElementById('gcashFields');
            const cartCountBadge = document.getElementById('cartCountBadge');
            const cartEmptyState = document.getElementById('cartEmptyState');
            const posAlert = document.getElementById('posAlert');
            const receiptPrintArea = document.getElementById('receiptPrintArea');
            const btnComplete = document.getElementById('btnComplete');

            const LOW_STOCK_THRESHOLD = 10;
            const SUMMARY_POLL_MS = 10000;

            function peso(n) {
                return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function setLiveClock() {
                document.getElementById('liveClock').textContent = new Date().toLocaleString();
            }
            setLiveClock();
            setInterval(setLiveClock, 1000);

            function showAlert(message, type) {
                posAlert.className = 'alert py-2 px-2 mt-2 mb-0 alert-' + (type || 'danger');
                posAlert.textContent = message;
                posAlert.style.display = 'block';
                clearTimeout(showAlert._t);
                showAlert._t = setTimeout(() => { posAlert.style.display = 'none'; }, 5000);
            }

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
            function stockBadge(qty) {
                if (qty <= 0) return '<span class="stock-badge stock-out">Out of stock</span>';
                if (qty <= LOW_STOCK_THRESHOLD) return `<span class="stock-badge stock-low">${qty} left</span>`;
                return `<span class="stock-badge stock-ok">${qty} left</span>`;
            }

            function renderProductGrid() {
                const term = searchInput.value.trim().toLowerCase();
                const catId = categorySelect.value;
                const matches = products.filter(p => {
                    const inCategory = !catId || p.category_id == catId;
                    const matchesSearch = !term || p.name.toLowerCase().includes(term) || (p.barcode_value && p.barcode_value.includes(term));
                    return inCategory && matchesSearch;
                });

                const emptyState = document.getElementById('productGridEmpty');

                if (!matches.length) {
                    productGrid.innerHTML = '';
                    emptyState.style.display = 'block';
                    return;
                }
                emptyState.style.display = 'none';

                productGrid.innerHTML = matches.map(p => `
    <div class="col-6">
        <button type="button" class="btn btn-outline-dark w-100 h-100 product-tile" data-batch="${p.batch_id}" ${p.quantity_avail <= 0 ? 'disabled' : ''}>
            <span class="prod-name">${p.name}</span>
            <div class="prod-info">
                ${p.brand ? `<span>${p.brand}</span>` : ''}
                <span>Batch ${p.batch_number || 'N/A'}</span>
                <span>Exp ${p.expires_at || 'N/A'}</span>
                ${p.is_vat_exempt == 1 ? '<span>VAT-Exempt</span>' : ''}
            </div>
            <span class="d-flex justify-content-between align-items-center mt-1">
                <span class="prod-price">${peso(p.sell_price)}</span>
                ${stockBadge(p.quantity_avail)}
            </span>
        </button>
    </div>
`).join('');

    productGrid.querySelectorAll('.product-tile:not([disabled])').forEach(btn => {
        btn.addEventListener('click', function() {
            const product = products.find(p => p.batch_id == this.getAttribute('data-batch'));
            if (product) addToCart(product);
        });
    });
}
            categorySelect.addEventListener('change', renderProductGrid);
            searchInput.addEventListener('input', renderProductGrid);

            // ============ BARCODE SCANNER SUPPORT: Enter key auto-adds exact match ============
            searchInput.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();

                const term = searchInput.value.trim();
                if (term === '') return;

                // Exact barcode match takes priority (typical scanner behavior — instant, unambiguous)
                const exactMatch = products.find(p => p.barcode_value && p.barcode_value === term);
                if (exactMatch) {
                    if (exactMatch.quantity_avail <= 0) {
                        showAlert(`${exactMatch.name} is out of stock.`, 'warning');
                    } else {
                        addToCart(exactMatch);
                        showAlert(`Added: ${exactMatch.name}`, 'success');
                    }
                    searchInput.value = '';
                    renderProductGrid();
                    return;
                }

                // Fall back to the currently-filtered grid — if search narrowed it to one product, add it
                const term2 = term.toLowerCase();
                const filtered = products.filter(p =>
                    p.name.toLowerCase().includes(term2) || (p.barcode_value && p.barcode_value.includes(term))
                );
                if (filtered.length === 1) {
                    if (filtered[0].quantity_avail <= 0) {
                        showAlert(`${filtered[0].name} is out of stock.`, 'warning');
                    } else {
                        addToCart(filtered[0]);
                        searchInput.value = '';
                        renderProductGrid();
                    }
                }
            });

            // Keep focus on the search box after any product click — lets a cashier scan
            // item after item without touching the mouse or keyboard again.
            productGrid.addEventListener('click', function() {
                setTimeout(() => searchInput.focus(), 50);
            });

            // ============ CART ============
            function addToCart(p) {
                const existing = cart.find(i => i.batch_id === p.batch_id);
                if (existing) {
                    if (existing.qty + 1 > p.quantity_avail) { showAlert(`Only ${p.quantity_avail} unit(s) of ${p.name} available.`, 'warning'); return; }
                    existing.qty += 1;
                } else {
                    cart.push({...p, qty: 1 });
                }
                renderCart();
            }

            function renderCart() {
                const body = document.getElementById('cartTableBody');
                cartCountBadge.textContent = cart.reduce((sum, i) => sum + i.qty, 0) + ' item(s)';
                cartEmptyState.style.display = cart.length ? 'none' : 'block';

                body.innerHTML = cart.map((item, index) => `
            <tr>
                <td><span class="fw-bold d-block">${item.name}</span><span style="color:#888;">Batch ${item.batch_number}</span></td>
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
                    if (cart[i].qty + 1 > cart[i].quantity_avail) { showAlert('No more stock available.', 'warning'); return; }
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

            // ============ DAILY KPI + HISTORY RENDERING (shared by polling + post-sale refresh) ============
            function applyDailySummary(daily) {
                if (!daily) return;
                document.getElementById('kpiTxns').textContent = daily.total_txns;
                document.getElementById('kpiGross').textContent = peso(daily.gross_sales);
                if (daily.cash_sales !== undefined) document.getElementById('kpiCash').textContent = peso(daily.cash_sales);
                if (daily.gcash_sales !== undefined) document.getElementById('kpiGcash').textContent = peso(daily.gcash_sales);
            }

            function renderTxnHistory(history) {
                const list = document.getElementById('txnHistoryList');
                if (!history || !history.length) {
                    list.innerHTML = `<div class="empty-state" id="txnEmptyState"><i class="fas fa-clipboard-list mb-2" style="font-size:18px;"></i><div>No transactions yet today.</div></div>`;
                    return;
                }
                list.innerHTML = history.map(h => `
            <div class="d-flex justify-content-between border-bottom py-1 txn-row" style="font-size:10px;" data-txn="${h.txn_id}">
                <span>${h.or_number} (${h.item_count})</span>
                <span class="fw-bold">${peso(h.total)}</span>
            </div>
        `).join('');
                list.querySelectorAll('.txn-row').forEach(row => {
                    row.addEventListener('click', () => reprintReceipt(row.getAttribute('data-txn')));
                });
            }
            // Wire click-to-reprint on the server-rendered initial rows (before the first poll response)
            document.querySelectorAll('#txnHistoryList .txn-row').forEach(row => {
                row.addEventListener('click', () => reprintReceipt(row.getAttribute('data-txn')));
            });

            // ============ AUTO-REFRESH: poll the summary endpoint so the dashboard
            // (KPIs + today's transactions) stays live even with no sale on this
            // terminal — e.g. another cashier completing a sale elsewhere. ============
            function pollSummary() {
                fetch(`${BASE_URL}/admin/sales/pos/summary`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status !== 'success') return;
                        applyDailySummary(data.daily);
                        renderTxnHistory(data.history);
                        const synced = document.getElementById('lastSynced');
                        if (synced) synced.textContent = 'Synced ' + (data.server_time || new Date().toLocaleTimeString());
                    })
                    .catch(() => { /* silent — next poll will retry */ });
            }
            pollSummary();
            setInterval(pollSummary, SUMMARY_POLL_MS);

            // ============ RECEIPT — lengthwise (continuous-roll thermal) layout ============
            function buildReceiptHtml(txn, items, store) {
                const rows = items.map(i => `
            <div style="margin-bottom:4px;">
                <div>${i.name}</div>
                <div style="display:flex; justify-content:space-between;">
                    <span>${i.qty} x ${peso(i.price)}</span>
                    <span>${peso(i.subtotal)}</span>
                </div>
            </div>`).join('');

                const isDiscounted = parseFloat(txn.discount) > 0;

                return `
            <div style="font-family:'Courier New',monospace; padding:10px 8px; width:100%; font-size:11px; line-height:1.35;">
                <div style="text-align:center;">
                    <h5 style="margin:0; font-size:13px;">${store.store_name || 'Store'}</h5>
                    <p style="margin:0;">${store.store_address || ''}</p>
                    <p style="margin:0;">TIN: ${store.store_tin || 'N/A'}${store.store_vat_status ? ' — ' + store.store_vat_status : ''}</p>
                    ${store.store_phone_1 ? `<p style="margin:0;">Tel: ${store.store_phone_1}</p>` : ''}
                    ${store.store_bir_permit_no ? `<p style="margin:0;">BIR Permit No.: ${store.store_bir_permit_no}</p>` : ''}
                    ${store.store_min ? `<p style="margin:0;">MIN: ${store.store_min}</p>` : ''}
                </div>
                <div style="border-top:1px dashed #000; margin:6px 0;"></div>
                <p style="margin:0; text-align:center; font-weight:bold;">OFFICIAL RECEIPT</p>
                <p style="margin:0;">OR #: ${txn.or_number}<br>
                Date: ${txn.created_at}<br>
                Cashier: ${txn.cashier_name || ''}<br>
                Payment: ${txn.payment_method.toUpperCase()}${txn.gcash_ref ? ' (Ref: ' + txn.gcash_ref + ')' : ''}</p>
                <div style="border-top:1px dashed #000; margin:6px 0;"></div>
                ${rows}
                <div style="border-top:1px dashed #000; margin:6px 0;"></div>
                <p style="margin:0;">
                    VATable Sales: ${peso(txn.subtotal)}<br>
                    VAT (12%): ${peso(txn.vat_amount)}<br>
                    ${isDiscounted ? `Less Discount (${(txn.discount_type||'').toUpperCase()}): -${peso(txn.discount)}<br>` : ''}
                    <b>TOTAL AMOUNT DUE: ${peso(txn.total)}</b><br>
                    ${txn.payment_method === 'cash' ? `Tendered: ${peso(txn.amount_tendered)}<br>Change: ${peso(txn.change_due)}<br>` : ''}
                </p>
                ${isDiscounted ? `
                <div style="border-top:1px dashed #000; margin:6px 0;"></div>
                <p style="margin:0;">
                    ID Number: ${txn.discount_id_number || '________________'}<br>
                    Name: ${txn.discount_holder_name || '________________'}<br>
                    Signature: ________________
                </p>` : ''}
                <div style="border-top:1px dashed #000; margin:6px 0;"></div>
                <p style="text-align:center; margin:0; font-weight:bold;">THIS SERVES AS YOUR OFFICIAL RECEIPT</p>
                <p style="text-align:center; margin:0;">Thank you for your purchase!</p>
            </div>`;
    }

    function printReceipt(txn, items, store) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>${txn.or_number}</title>
                <style>
                    @page { size: 80mm auto; margin: 0; }
                    body { margin: 0; }
                </style>
            </head>
            <body>${buildReceiptHtml(txn, items, store)}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }

    function reprintReceipt(txnId) {
        fetch(`${BASE_URL}/admin/sales/pos/receipt/${txnId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) { showAlert(data.error, 'danger'); return; }
                printReceipt(data.txn, data.items, data.store);
            })
            .catch(() => showAlert('Could not load that receipt. Please try again.', 'danger'));
    }

    // ============ COMPLETE TRANSACTION ============
    document.getElementById('btnComplete').addEventListener('click', function () {
        if (cart.length === 0) { showAlert('Cart is empty.', 'warning'); return; }
        const payType = document.querySelector('input[name="payType"]:checked').value;
        const gcashRef = document.getElementById('gcashRef').value.trim();
        if (payType === 'gcash' && gcashRef === '') { showAlert('Please enter the GCash reference number.', 'warning'); return; }

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
                btnComplete.disabled = false;
                btnComplete.innerHTML = '<i class="fas fa-check-circle me-2"></i>COMPLETE TRANSACTION';

                if (data.error) { showAlert(data.error, 'danger'); return; }

                // Patch in-memory product stock instead of reloading the page
                (data.updated_batches || []).forEach(ub => {
                    const p = products.find(x => x.batch_id == ub.batch_id);
                    if (p) p.quantity_avail = ub.quantity_avail;
                });
                renderProductGrid();

                // Live-refresh KPIs and the transaction feed with the authoritative server totals
                applyDailySummary(data.daily);
                if (data.history) renderTxnHistory(data.history);

                printReceipt(data.txn, data.items, STORE_INFO);

                // Reset the sale for the next customer — no full page reload
                cart = [];
                document.getElementById('customerName').value = '';
                document.getElementById('discountIdNumber').value = '';
                document.getElementById('discountHolderName').value = '';
                document.getElementById('gcashRef').value = '';
                discountType.value = 'none';
                discountIdRow.style.display = 'none';
                document.getElementById('pC').checked = true;
                cashFields.style.display = 'flex';
                gcashFields.style.display = 'none';
                tenderedInput.value = '';
                renderCart();
                showAlert('Transaction completed — ' + data.txn.or_number, 'success');
                searchInput.focus();
            })
            .catch(err => {
                btnComplete.disabled = false;
                btnComplete.innerHTML = '<i class="fas fa-check-circle me-2"></i>COMPLETE TRANSACTION';
                showAlert('Transaction failed. Please try again.', 'danger');
                console.error(err);
            });
    });

    renderProductGrid();
    renderCart();
});