document.addEventListener("DOMContentLoaded", function() {
            let lrChart = null;
            let maChart = null;
            let lastForecast = null;

            function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

            function fmtMonth(ym) {
                const [y, m] = ym.split('-');
                return new Date(y, m - 1).toLocaleString('default', { month: 'short', year: '2-digit' });
            }

            // Default date range: last 12 months, admin can override either end
            const today = new Date();
            document.getElementById('toMonth').value = today.toISOString().slice(0, 7);
            const twelveAgo = new Date(today.getFullYear(), today.getMonth() - 11, 1);
            document.getElementById('fromMonth').value = twelveAgo.toISOString().slice(0, 7);

            // Category -> Product cascade
            document.getElementById('categorySelect').addEventListener('change', function() {
                const catId = this.value;
                const productSelect = document.getElementById('productSearch');
                const url = catId ?
                    `${BASE_URL}/admin/strategy/analytics/get-products-by-category/${catId}` :
                    `${BASE_URL}/admin/strategy/analytics/get-products-by-category/0`; // 0 = all
                fetch(url)
                    .then(res => res.json())
                    .then(products => {
                        productSelect.disabled = false;
                        productSelect.innerHTML = products.length ?
                            '<option value="" disabled selected>Select a product</option>' + products.map(p => `<option value="${p.product_id}">${p.name}</option>`).join('') :
                            '<option value="">No products in this category</option>';
                    });
            });

            // Overall trend chart — loads independently, not tied to product selection
            let overallChart = null;
            fetch(`${BASE_URL}/admin/strategy/analytics/get-overall-trend`)
                .then(res => res.json())
                .then(data => {
                    const ctx = document.getElementById('overallTrendChart').getContext('2d');
                    overallChart = new Chart(ctx, {
                        type: 'bar',
                        data: { labels: data.labels.map(fmtMonth), datasets: [{ label: 'Monthly Revenue', data: data.values, backgroundColor: '#7b1113' }] },
                        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } } }
                    });
                });

            const btnRun = document.getElementById('btnRunForecast');
            if (btnRun) btnRun.addEventListener('click', runForecast);

            function runForecast() {
                const pid = document.getElementById('productSearch').value;
                const from = document.getElementById('fromMonth').value;
                const to = document.getElementById('toMonth').value;
                if (!pid) { alert('Please select a category and product first.'); return; }
                if (from > to) { alert('"From" month must be before "To" month.'); return; }

                btnRun.disabled = true;
                btnRun.textContent = 'Calculating...';

                fetch(`${BASE_URL}/admin/strategy/analytics/get-forecast/${pid}?from=${from}&to=${to}`)
                    .then(res => res.json())
                    .then(data => {
                        btnRun.disabled = false;
                        btnRun.textContent = 'Run Forecast';
                        if (data.error) { alert(data.error); return; }
                        lastForecast = data;
                        renderLRChart(data);
                        renderMAChart(data);

                        document.getElementById('avg_monthly').innerText = data.avg_monthly_sales + ' units';
                        document.getElementById('forecast_month').innerText = data.forecast_next_month + ' units';
                        document.getElementById('avg_daily').innerText = data.avg_daily_usage + ' units/day';
                        document.getElementById('r2_val').innerText = data.r2;
                        document.getElementById('rop_val').innerText = data.rop + ' units';
                        document.getElementById('lead_val').innerText = data.lead_time_days + ' days';
                        document.getElementById('safety_val').innerText = data.safety_stock + ' units';
                        document.getElementById('eoq_val').innerText = data.eoq + ' units';

                        document.getElementById('regressionEquationBox').style.display = 'block';
                        document.getElementById('eqIntercept').textContent = data.intercept;
                        document.getElementById('eqSlope').textContent = (data.slope >= 0 ? '+' : '') + data.slope;
                        document.getElementById('eqTrend').textContent = data.trend_direction;
                        document.getElementById('eqR2').textContent = data.r2;

                        const stockoutVal = document.getElementById('stockout_val');
                        stockoutVal.textContent = data.days_until_stockout !== null ?
                            `${data.stockout_date} (in ${data.days_until_stockout} days)` :
                            'No recent sales — cannot project';

                        document.querySelector('.btn-view-intel[data-type="forecast"]').disabled = false;

                        document.getElementById('pendingPoBox').innerHTML = data.pending_po ?
                            `<div class="alert alert-warning p-2 mb-0" style="font-size:10px;">Pending auto-reorder PO: <b>${data.pending_po.po_number}</b>. <a href="${BASE_URL}/admin/procurement/purchase-orders?status=pending_approval">Review →</a></div>` :
                            `<div class="alert alert-light border p-2 mb-0 text-muted" style="font-size:10px;">No auto-reorder currently pending.</div>`;
                    })
                    .catch(err => {
                        btnRun.disabled = false;
                        btnRun.textContent = 'Run Forecast';
                        alert('Failed to load forecast.');
                        console.error(err);
                    });
            }

            function renderLRChart(data) {
                const ctx = document.getElementById('lrChart').getContext('2d');
                if (lrChart) lrChart.destroy();
                lrChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.monthly_labels.map(fmtMonth),
                        datasets: [
                            { label: 'Actual Monthly Sales', data: data.monthly_values, borderColor: '#7b1113', backgroundColor: '#7b1113', pointRadius: 4, fill: false, tension: 0.1 },
                            { label: 'Regression Trend', data: data.monthly_regression, borderColor: '#22c55e', borderDash: [5, 5], pointRadius: 0, fill: false }
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { labels: { font: { size: 10 } } } }, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } } }
                });
            }

            function renderMAChart(data) {
                // still uses the daily series under the hood (re-fetched isn't needed — reuse if backend also returns it)
                const canvas = document.getElementById('maChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (maChart) maChart.destroy();
                maChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.monthly_labels.map(fmtMonth),
                        datasets: [{ label: 'Monthly Units (reference)', data: data.monthly_values, borderColor: '#0d2e4f', pointRadius: 0, borderWidth: 2, fill: false }]
                    },
                    options: { responsive: true, plugins: { legend: { labels: { font: { size: 10 } } } }, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } } }
                });
            }

            // ============ INTELLIGENCE DRAWER — routes by data-type ============
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('intelDrawer'));
            const content = document.getElementById('intelContent');
            const drawerTitle = document.getElementById('intelDrawerTitle');

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-view-intel');
                if (!btn || btn.disabled) return;
                const type = btn.dataset.type;

                if (type === 'forecast') return openForecastDrawer();
                if (type === 'suppliers') return openSuppliersDrawer();
                if (type === 'lowperforming') return openLowPerformingDrawer();
            });

            function openForecastDrawer() {
                if (!lastForecast) return;
                const data = lastForecast;
                drawerTitle.textContent = `EOQ & Forecast — ${data.product_name}`;
                drawer.show();

                const rows = data.monthly_labels.map((label, i) => `
            <tr><td>${fmtMonth(label)}</td><td class="text-end">${data.monthly_values[i]}</td><td class="text-end text-muted">${data.monthly_regression[i]}</td></tr>
        `).join('');

                content.innerHTML = `
            <h6 class="fw-bold mb-1">EOQ Cost Trade-Off</h6>
            <p class="text-muted mb-3" style="font-size:10px;">Order Cost: ${peso(data.order_cost)}/order · Holding Cost: ${peso(data.holding_cost)}/unit/year · Annual Demand: ${data.annual_demand} units</p>
            <div style="height:260px" class="border rounded-4 bg-white p-3 shadow-sm mb-4">
                <canvas id="eoqTradeOffChart"></canvas>
            </div>
            <div class="p-3 bg-light rounded-4 mb-4">
                <p class="mb-0 text-muted" style="font-size:11px;">EOQ of <b>${data.eoq} units</b> is where ordering and holding costs balance out — the lowest total annual cost for this product.</p>
            </div>
            <h6 class="fw-bold mb-2 border-top pt-3">Monthly Sales vs. Trend</h6>
            <table class="table table-sm" style="font-size:10.5px;">
                <thead class="table-dark"><tr><th>Month</th><th class="text-end">Actual</th><th class="text-end">Trend</th></tr></thead>
                <tbody>${rows}</tbody>
            </table>
        `;

                const ctx = document.getElementById('eoqTradeOffChart').getContext('2d');
                const eoq = data.eoq || 1;
                const points = [];
                for (let q = Math.max(10, Math.round(eoq * 0.2)); q <= eoq * 2; q += Math.round(eoq / 10 || 1)) {
                    const orderingCost = (data.annual_demand / q) * data.order_cost;
                    const holdingCost = (q / 2) * data.holding_cost;
                    points.push({ q, total: orderingCost + holdingCost, orderingCost, holdingCost });
                }
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: points.map(p => p.q),
                        datasets: [
                            { label: 'Total Cost', data: points.map(p => p.total.toFixed(2)), borderColor: '#7b1113', pointRadius: 0, fill: false },
                            { label: 'Ordering Cost', data: points.map(p => p.orderingCost.toFixed(2)), borderColor: '#3b82f6', borderDash: [4, 4], pointRadius: 0, fill: false },
                            { label: 'Holding Cost', data: points.map(p => p.holdingCost.toFixed(2)), borderColor: '#f59e0b', borderDash: [4, 4], pointRadius: 0, fill: false }
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { labels: { font: { size: 10 } } }, title: { display: true, text: 'Order Quantity vs. Annual Cost', font: { size: 11 } } } }
                });
            }

            function openSuppliersDrawer() {
                drawerTitle.textContent = 'Full Supplier Performance';
                content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
                drawer.show();

                fetch(`${BASE_URL}/admin/strategy/analytics/get-supplier-report`)
                    .then(res => res.json())
                    .then(data => {
                            content.innerHTML = data.length ? `
                    <table class="table table-sm table-hover" style="font-size:10.5px;">
                        <thead class="table-dark"><tr><th>Supplier</th><th class="text-end">On-Time</th><th class="text-end">Accuracy</th><th class="text-end">Orders</th><th class="text-end">Lead Time</th></tr></thead>
                        <tbody>
                            ${data.map(s => `<tr><td>${s.name}</td><td class="text-end">${s.on_time_rate}%</td><td class="text-end">${s.accuracy_rate}%</td><td class="text-end">${s.total_orders}</td><td class="text-end">${s.lead_time_days}d</td></tr>`).join('')}
                        </tbody>
                    </table>
                ` : `<p class="text-muted text-center py-5">No scorecard data available yet.</p>`;
            })
            .catch(err => { content.innerHTML = `<p class="text-danger text-center py-5">Failed to load supplier data.</p>`; console.error(err); });
    }

    function openLowPerformingDrawer() {
        drawerTitle.textContent = 'Low Performing / Slow-Moving Products';
        content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
        drawer.show();

        fetch(`${BASE_URL}/admin/strategy/analytics/get-low-performing-report`)
            .then(res => res.json())
            .then(data => {
                content.innerHTML = data.length ? `
                    <table class="table table-sm table-hover" style="font-size:10.5px;">
                        <thead class="table-dark"><tr><th>Product</th><th>Category</th><th class="text-end">Stock</th><th>Last Movement</th></tr></thead>
                        <tbody>
                            ${data.map(p => `<tr><td>${p.name}</td><td class="text-muted">${p.cat_name}</td><td class="text-end">${p.stock}</td><td class="text-muted">${p.last_moved || 'Never'}</td></tr>`).join('')}
                        </tbody>
                    </table>
                ` : `<p class="text-muted text-center py-5">No slow-moving stock detected.</p>`;
            })
            .catch(err => { content.innerHTML = `<p class="text-danger text-center py-5">Failed to load report.</p>`; console.error(err); });
    }
});