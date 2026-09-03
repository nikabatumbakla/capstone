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
                    .then(res => res.json())
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

            document.querySelector('.btn-open-cash-journal').addEventListener('click', function() {
                title.textContent = 'Cash Receipts Journal';
                content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                drawer.show();

                fetch(`${BASE_URL}/admin/strategy/compliance/get-cash-receipts?year=${year}&month=${month}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.length) { content.innerHTML = `<p class="text-muted text-center p-5">No POS transactions recorded for this period.</p>`; return; }

                        const totalCash = data.reduce((s, r) => s + parseFloat(r.cash_total), 0);
                        const totalGcash = data.reduce((s, r) => s + parseFloat(r.gcash_total), 0);

                        const rows = data.map(r => `
                <tr>
                    <td>${r.day}</td>
                    <td class="text-end">${peso(r.cash_total)}</td>
                    <td class="text-end">${peso(r.gcash_total)}</td>
                    <td class="text-end fw-bold">${peso(r.day_total)}</td>
                    <td class="text-center text-muted">${r.txn_count}</td>
                </tr>`).join('');

                        content.innerHTML = `
                <div class="row g-2 mb-3">
                    <div class="col-6 p-2 bg-light rounded-3 text-center"><small class="text-muted d-block">Total Cash</small><b>${peso(totalCash)}</b></div>
                    <div class="col-6 p-2 bg-light rounded-3 text-center"><small class="text-muted d-block">Total GCash</small><b>${peso(totalGcash)}</b></div>
                </div>
                <table class="table table-sm table-hover" style="font-size:10.5px;">
                    <thead class="table-dark"><tr><th>Date</th><th class="text-end">Cash</th><th class="text-end">GCash</th><th class="text-end">Total</th><th class="text-center">Txns</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>`;
                    })
                    .catch(err => {
                        content.innerHTML = `<p class="text-danger text-center p-5">Failed to load Cash Receipts Journal.</p>`;
                        console.error(err);
                    });
            });

            document.querySelectorAll('.btn-open-journal').forEach(btn => {
                        btn.addEventListener('click', function() {
                                    const type = this.getAttribute('data-type');
                                    title.textContent = type === 'sales' ? 'Subsidiary Sales Journal' : 'Subsidiary Purchase Journal';
                                    content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                                    drawer.show();

                                    fetch(`${BASE_URL}/admin/strategy/compliance/get-journal/${type}?year=${year}&month=${month}`)
                                        .then(res => res.json())
                                        .then(data => {
                                                if (!data.length) { content.innerHTML = `<p class="text-muted text-center p-5">No entries for this period.</p>`; return; }

                                                if (type === 'sales') {
                                                    content.innerHTML = `
                            <table class="table table-sm table-hover" style="font-size:10.5px;">
                                <thead class="table-dark"><tr><th>Ref #</th><th>Source</th><th>Payment</th><th class="text-end">VAT</th><th class="text-end">Total</th></tr></thead>
                                <tbody>${data.map(r => `<tr><td>${r.ref_no}</td><td>${r.source}</td><td>${r.payment_method}</td><td class="text-end">${peso(r.vat_amount)}</td><td class="text-end fw-bold">${peso(r.total)}</td></tr>`).join('')}</tbody>
                            </table>`;
                    } else {
                        content.innerHTML = `
                            <table class="table table-sm table-hover" style="font-size:10.5px;">
                                <thead class="table-dark"><tr><th>PO #</th><th>Supplier</th><th>Received</th><th class="text-end">Amount</th></tr></thead>
                                <tbody>${data.map(r => `<tr><td>${r.ref_no}</td><td>${r.supplier_name}</td><td>${r.received_date}</td><td class="text-end fw-bold">${peso(r.total_amount)}</td></tr>`).join('')}</tbody>
                            </table>`;
                    }
                })
                .catch(err => { content.innerHTML = `<p class="text-danger text-center p-5">Failed to load journal.</p>`; console.error(err); });
        });
    });
});