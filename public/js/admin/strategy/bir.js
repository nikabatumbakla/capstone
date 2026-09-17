document.addEventListener("DOMContentLoaded", function() {
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('journalDrawer'));
            const content = document.getElementById('journalContent');
            const title = document.getElementById('journalTitle');

            const urlParams = new URLSearchParams(window.location.search);
            const year = urlParams.get('year') || new Date().getFullYear();
            const month = urlParams.get('month') || (new Date().getMonth() + 1);

            function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

            document.querySelector('.btn-open-vat-book').addEventListener('click', function() {
                loadVatSalesBook(1);
            });

            function loadVatSalesBook(page) {
                title.textContent = 'VAT Sales Book';
                content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                drawer.show();

                fetch(`${BASE_URL}/admin/strategy/compliance/get-vat-sales-book?page=${page}`)
                    .then(async res => {
                        const text = await res.text();
                        try { return JSON.parse(text); } catch (e) {
                            console.error('[VAT Sales Book] Server did not return valid JSON:', text);
                            throw new Error('Server returned an unexpected response — check console.');
                        }
                    })
                    .then(result => {
                        const rows = result.data.map(s => `
                <tr>
                    <td>${new Date(s.year, s.month - 1).toLocaleString('default', { month: 'long', year: 'numeric' })}</td>
                    <td class="text-end">${peso(s.vatable_sales)}</td>
                    <td class="text-end fw-bold">${peso(s.output_vat)}</td>
                    <td class="text-end">${s.net_vat_payable < 0 ? 'Credit ' : ''}${peso(Math.abs(s.net_vat_payable))}</td>
                </tr>`).join('');

                        content.innerHTML = `
                <table class="table table-sm table-hover" style="font-size:10.5px;">
                    <thead class="table-dark"><tr><th>Period</th><th class="text-end">Vatable Sales</th><th class="text-end">Output VAT</th><th class="text-end">Net Payable</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-sm btn-outline-dark" ${result.current_page <= 1 ? 'disabled' : ''} id="vatBookPrev">Previous</button>
                    <span class="text-muted">Page ${result.current_page} of ${result.total_pages}</span>
                    <button class="btn btn-sm btn-outline-dark" ${result.current_page >= result.total_pages ? 'disabled' : ''} id="vatBookNext">Next</button>
                </div>`;

                        const prevBtn = document.getElementById('vatBookPrev');
                        const nextBtn = document.getElementById('vatBookNext');
                        if (prevBtn) prevBtn.addEventListener('click', () => loadVatSalesBook(result.current_page - 1));
                        if (nextBtn) nextBtn.addEventListener('click', () => loadVatSalesBook(result.current_page + 1));
                    })
                    .catch(err => {
                        content.innerHTML = `<p class="text-danger text-center p-5">Failed to load VAT Sales Book.</p>`;
                        console.error(err);
                    });
            }

            document.querySelectorAll('.btn-open-journal').forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const type = this.getAttribute('data-type');
                                    const titles = { sales: 'Subsidiary Sales Journal', purchases: 'Subsidiary Purchase Journal', clients: 'Subsidiary Client Journal' };
                                    title.textContent = titles[type];
                                    content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                                    drawer.show();

                                    fetch(`${BASE_URL}/admin/strategy/compliance/get-journal/${type}?year=${year}&month=${month}`)
                                        .then(async res => {
                                            const text = await res.text();
                                            try { return JSON.parse(text); } catch (e) { throw new Error(text); }
                                        })
                                        .then(data => {
                                                if (data.error) { content.innerHTML = `<p class="text-danger text-center p-5">${data.error}</p>`; return; }
                                                if (!data.length) { content.innerHTML = `<p class="text-muted text-center p-5">No entries for this period.</p>`; return; }

                                                if (type === 'sales') {
                                                    const taxTotal = data.reduce((s, r) => s + parseFloat(r.taxable_amount || 0), 0);
                                                    const vatTotal = data.reduce((s, r) => s + parseFloat(r.vat_output_tax || 0), 0);
                                                    const grandTotal = data.reduce((s, r) => s + parseFloat(r.total_invoice_amount || 0), 0);
                                                    content.innerHTML = `
                        <table class="table table-sm table-hover" style="font-size:10.5px;">
                            <thead class="table-dark"><tr><th>Date</th><th>Buyer</th><th class="text-end">Taxable (12%)</th><th class="text-end">VAT Output</th><th class="text-end">Total</th></tr></thead>
                            <tbody>${data.map(r => `<tr><td>${r.sale_date}</td><td>${r.buyer}</td><td class="text-end">${peso(r.taxable_amount)}</td><td class="text-end">${peso(r.vat_output_tax)}</td><td class="text-end fw-bold">${peso(r.total_invoice_amount)}</td></tr>`).join('')}</tbody>
                            <tfoot><tr class="fw-bold"><td colspan="2">TOTAL</td><td class="text-end">${peso(taxTotal)}</td><td class="text-end">${peso(vatTotal)}</td><td class="text-end">${peso(grandTotal)}</td></tr></tfoot>
                        </table>`;
                } else if (type === 'purchases') {
                    const vatPurchTotal = data.reduce((s,r) => s + parseFloat(r.vat_purchases_local||0), 0);
                    const inputVatTotal = data.reduce((s,r) => s + parseFloat(r.vat_input_tax||0), 0);
                    const grandTotal = data.reduce((s,r) => s + parseFloat(r.total_invoice_amount||0), 0);
                    content.innerHTML = `
                        <table class="table table-sm table-hover" style="font-size:10px;">
                            <thead class="table-dark"><tr><th>Date</th><th>Supplier</th><th>Inv. #</th><th>TIN</th><th class="text-end">VAT Purch.</th><th class="text-end">Input VAT</th><th class="text-end">Total</th></tr></thead>
                            <tbody>${data.map(r => `<tr><td>${r.purchase_date}</td><td>${r.supplier_name||'—'}</td><td>${r.invoice_no||'—'}</td><td>${r.tin||'—'}</td><td class="text-end">${peso(r.vat_purchases_local)}</td><td class="text-end">${peso(r.vat_input_tax)}</td><td class="text-end fw-bold">${peso(r.total_invoice_amount)}</td></tr>`).join('')}</tbody>
                            <tfoot><tr class="fw-bold"><td colspan="4">TOTAL</td><td class="text-end">${peso(vatPurchTotal)}</td><td class="text-end">${peso(inputVatTotal)}</td><td class="text-end">${peso(grandTotal)}</td></tr></tfoot>
                        </table>`;
                } else if (type === 'clients') {
                    const amountTotal = data.reduce((s,r) => s + parseFloat(r.amount||0), 0);
                    content.innerHTML = `
                        <table class="table table-sm table-hover" style="font-size:10px;">
                            <thead class="table-dark"><tr><th>Date</th><th>Client</th><th>Item</th><th>Qty</th><th class="text-end">Price</th><th class="text-end">Amount</th><th>Exp.</th></tr></thead>
                            <tbody>${data.map(r => `<tr><td>${r.item_date}</td><td>${r.client_name}</td><td>${r.item_name}</td><td>${r.qty||'—'}</td><td class="text-end">${peso(r.price)}</td><td class="text-end fw-bold">${peso(r.amount)}</td><td>${r.expiry||'—'}</td></tr>`).join('')}</tbody>
                            <tfoot><tr class="fw-bold"><td colspan="5">TOTAL</td><td class="text-end">${peso(amountTotal)}</td><td></td></tr></tfoot>
                        </table>`;
                }
            })
            .catch(err => { content.innerHTML = `<p class="text-danger text-center p-5">Failed to load journal.</p>`; console.error(err); });
    });
});
});