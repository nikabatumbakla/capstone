document.addEventListener("DOMContentLoaded", function() {
    const viewLogBtns = document.querySelectorAll('.btn-view-log');
    const logDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('logDrawer'));
    const content = document.getElementById('logDrawerContent');

    function val(v, fallback = '—') {
        return (v === null || v === undefined || v === '' || v === 'null') ? fallback : v;
    }

    const searchInput = document.getElementById('logSearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const params = new URLSearchParams(window.location.search);
                params.set('page', 1);
                const term = searchInput.value.trim();
                if (term !== '') params.set('search', term);
                else params.delete('search');
                window.location.href = window.location.pathname + '?' + params.toString();
            }, 500);
        });
    }

    let currentLogData = null; // for the print function

    viewLogBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            logDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;

            fetch(`${BASE_URL}/admin/inventory/get-log-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        content.innerHTML = `<div class="text-center text-danger p-5">${data.error}</div>`;
                        return;
                    }

                    currentLogData = data;
                    const diff = data.qty_after - data.qty_before;
                    const diffFormatted = diff > 0 ? `+${diff}` : diff;

                    const formattedDate = data.adjusted_at ?
                        new Date(data.adjusted_at.replace(' ', 'T')).toLocaleString('en-US', {
                            month: 'short',
                            day: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        }) :
                        '—';

                    content.innerHTML = `
                        <div class="text-center mb-4 p-4 bg-light rounded-4">
                            <i class="fas fa-clipboard-check fs-1 text-maroon opacity-25 mb-3"></i>
                            <h6 class="fw-bold mb-1">${val(data.product_name)}</h6>
                            <p class="text-muted small">${val(data.sku, 'No SKU on record')}</p>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6"><p class="info-label mb-0">Adjusted By</p><p class="info-value text-primary">${val(data.full_name)}</p></div>
                            <div class="col-6"><p class="info-label mb-0">Timestamp</p><p class="info-value">${formattedDate}</p></div>
                            <div class="col-6"><p class="info-label mb-0">Batch Link</p><p class="info-value">${val(data.batch_number, 'Global')}</p></div>
                            <div class="col-6"><p class="info-label mb-0">Reason Category</p><p class="info-value text-dark">${val(data.reason, 'Not specified')}</p></div>
                        </div>

                        <div class="p-3 rounded-4 bg-dark text-white mb-4 shadow">
                            <div class="row text-center">
                                <div class="col-4 border-end border-white-10"><small class="opacity-50">BEFORE</small><h4 class="mb-0">${val(data.qty_before, 0)}</h4></div>
                                <div class="col-4 border-end border-white-10"><small class="opacity-50">AFTER</small><h4 class="mb-0">${val(data.qty_after, 0)}</h4></div>
                                <div class="col-4"><small class="opacity-50">DELTA</small><h4 class="mb-0 text-warning">${diffFormatted}</h4></div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-4 border">
                            <p class="info-label mb-2">Staff Remarks / Notes</p>
                            <p class="mb-0 text-dark" style="font-size: 12px; line-height: 1.6;">"${val(data.notes, 'No detailed remarks provided for this adjustment.')}"</p>
                        </div>

                        <button class="btn btn-outline-dark w-100 mt-5 py-2 fw-bold" id="btnPrintVoucher">
                            <i class="fas fa-print me-2"></i>PRINT ADJUSTMENT VOUCHER
                        </button>
                    `;

                    document.getElementById('btnPrintVoucher').addEventListener('click', function() {
                        printVoucher(data, formattedDate, diffFormatted);
                    });
                })
                .catch(err => {
                    content.innerHTML = `<div class="text-center text-danger p-5">Failed to load log details.</div>`;
                    console.error(err);
                });
        });
    });

    // ============ FORMAL PRINTABLE VOUCHER ============
    function printVoucher(data, formattedDate, diffFormatted) {
        const store = window.STORE_INFO || {};
        const printWindow = window.open('', '_blank', 'width=800,height=900');

        printWindow.document.write(`
        <html>
        <head>
            <title>Stock Adjustment Voucher — Log #${data.log_id}</title>
            <style>
                * { box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Arial, sans-serif;
                    color: #1a1a1a;
                    padding: 40px 50px;
                    font-size: 13px;
                    line-height: 1.5;
                }
                .doc-header {
                    text-align: center;
                    border-bottom: 3px solid #1a0505;
                    padding-bottom: 16px;
                    margin-bottom: 24px;
                }
                .doc-header h1 { font-size: 18px; margin: 0 0 4px; letter-spacing: 0.5px; }
                .doc-header p { margin: 2px 0; font-size: 11px; color: #444; }
                .doc-title {
                    text-align: center;
                    font-size: 15px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    margin: 20px 0;
                    padding: 8px;
                    background: #f4f4f4;
                    border: 1px solid #ddd;
                }
                table.info-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table.info-table td {
                    padding: 8px 10px;
                    border: 1px solid #ddd;
                    vertical-align: top;
                }
                table.info-table td.label {
                    background: #f9f9f9;
                    font-weight: 600;
                    width: 180px;
                    font-size: 11px;
                    text-transform: uppercase;
                    color: #555;
                }
                .qty-summary {
                    display: flex;
                    justify-content: space-around;
                    text-align: center;
                    border: 1px solid #1a0505;
                    padding: 14px 0;
                    margin-bottom: 20px;
                }
                .qty-summary div { flex: 1; border-right: 1px solid #ddd; }
                .qty-summary div:last-child { border-right: none; }
                .qty-summary small { font-size: 9px; text-transform: uppercase; color: #666; display: block; margin-bottom: 4px; }
                .qty-summary strong { font-size: 20px; }
                .remarks-box {
                    border: 1px solid #ddd;
                    padding: 12px;
                    min-height: 60px;
                    margin-bottom: 30px;
                    font-style: italic;
                    color: #333;
                }
                .signature-row {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 60px;
                }
                .signature-block { width: 45%; text-align: center; }
                .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 6px; font-size: 10px; }
                .doc-footer {
                    margin-top: 40px;
                    font-size: 9px;
                    color: #888;
                    text-align: center;
                    border-top: 1px solid #eee;
                    padding-top: 10px;
                }
                @media print {
                    body { padding: 20px 30px; }
                }
            </style>
        </head>
        <body>
            <div class="doc-header">
                <h1>${val(store.store_name, 'Business Name')}</h1>
                <p>${val(store.store_address, '')}</p>
                <p>TIN: ${val(store.store_tin, '—')} &nbsp; | &nbsp; Contact: ${val(store.store_phone_1, '—')}</p>
            </div>

            <div class="doc-title">Inventory Stock Adjustment Voucher</div>

            <table class="info-table">
                <tr>
                    <td class="label">Voucher No.</td>
                    <td>ADJ-${String(data.log_id).padStart(6, '0')}</td>
                    <td class="label">Date &amp; Time</td>
                    <td>${formattedDate}</td>
                </tr>
                <tr>
                    <td class="label">Product</td>
                    <td>${val(data.product_name)}</td>
                    <td class="label">SKU</td>
                    <td>${val(data.sku, 'No SKU on record')}</td>
                </tr>
                <tr>
                    <td class="label">Batch Reference</td>
                    <td>${val(data.batch_number, 'Global / Not batch-specific')}</td>
                    <td class="label">Reason</td>
                    <td>${val(data.reason, 'Not specified')}</td>
                </tr>
                <tr>
                    <td class="label">Adjusted By</td>
                    <td colspan="3">${val(data.full_name)}</td>
                </tr>
            </table>

            <div class="qty-summary">
                <div><small>Quantity Before</small><strong>${val(data.qty_before, 0)}</strong></div>
                <div><small>Quantity After</small><strong>${val(data.qty_after, 0)}</strong></div>
                <div><small>Net Change</small><strong>${diffFormatted}</strong></div>
            </div>

            <p style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #555; margin-bottom: 6px;">Remarks / Justification</p>
            <div class="remarks-box">${val(data.notes, 'No detailed remarks provided for this adjustment.')}</div>

            <div class="signature-row">
                <div class="signature-block">
                    <div class="signature-line">Prepared / Adjusted By</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line">Reviewed / Approved By</div>
                </div>
            </div>

            <div class="doc-footer">
                This is an internal inventory record generated by ${val(store.store_name, 'the system')} for stock control and audit purposes.
                Printed on ${new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })}.
            </div>
        </body>
        </html>
        `);

        printWindow.document.close();
        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
        };
    }
});