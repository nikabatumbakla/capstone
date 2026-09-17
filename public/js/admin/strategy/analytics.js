document.addEventListener("DOMContentLoaded", function() {
            let lrChart = null;
            let maChart = null;
            let lastForecast = null;

            function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

            function fmtMonth(ym) {
                const [y, m] = ym.split('-');
                return new Date(y, m - 1).toLocaleString('default', { month: 'short', year: '2-digit' });
            }

            const today = new Date();
            document.getElementById('toMonth').value = today.toISOString().slice(0, 7);
            const twelveAgo = new Date(today.getFullYear(), today.getMonth() - 11, 1);
            document.getElementById('fromMonth').value = twelveAgo.toISOString().slice(0, 7);

            document.getElementById('btnResetWindow').addEventListener('click', function() {
                const t = new Date();
                document.getElementById('toMonth').value = t.toISOString().slice(0, 7);
                const twelveAgoReset = new Date(t.getFullYear(), t.getMonth() - 11, 1);
                document.getElementById('fromMonth').value = twelveAgoReset.toISOString().slice(0, 7);
            });

            document.getElementById('categorySelect').addEventListener('change', function() {
                const catId = this.value;
                const productSelect = document.getElementById('productSearch');
                if (!catId) {
                    productSelect.innerHTML = '<option value="all" selected>All Products (Combined Trend)</option>';
                    return;
                }
                fetch(`${BASE_URL}/admin/strategy/analytics/get-products-by-category/${catId}`)
                    .then(res => res.json())
                    .then(products => {
                        productSelect.innerHTML = '<option value="all">All Products (Combined Trend)</option>' +
                            (products.length ? products.map(p => `<option value="${p.product_id}">${p.name}</option>`).join('') : '<option value="" disabled>No products in this category</option>');
                    });
            });

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
                if (!pid) { alert('Please select a product, or leave "All Products" selected.'); return; }
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

                        const unitLabel = data.is_revenue_based ? '' : ' units';
                        const fmt = v => data.is_revenue_based ? peso(v) : v + unitLabel;

                        const nowShowing = document.getElementById('nowShowingLabel');
                        nowShowing.style.display = 'block';
                        document.getElementById('nowShowingText').textContent =
                            `${data.product_name} — ${fmtMonth(data.monthly_labels[0])} to ${fmtMonth(data.monthly_labels[data.monthly_labels.length-1])}`;

                        document.getElementById('avg_monthly').innerText = fmt(data.avg_monthly_sales);
                        document.getElementById('forecast_month').innerText = fmt(data.forecast_next_month);
                        document.getElementById('avg_daily').innerText = data.avg_daily_usage + (data.is_revenue_based ? '/day' : ' units/day');
                        document.getElementById('r2_val').innerText = data.r2;

                        if (data.mae !== null && data.mae !== undefined) {
                            document.getElementById('forecastMonthsCard').style.display = 'block';
                            document.getElementById('mae_val').innerText = fmt(data.mae);
                            document.getElementById('forecastMonthsBody').innerHTML = (data.forecast_months || []).map(m =>
                                `<tr><td>${fmtMonth(m.label)}</td><td class="text-end fw-bold">${fmt(m.predicted)}</td></tr>`
                            ).join('');
                        } else {
                            document.getElementById('forecastMonthsCard').style.display = 'none';
                        }

                        const isAggregate = data.is_aggregate === true;
                        document.getElementById('rop_val').innerText = isAggregate ? 'N/A' : data.rop + ' units';
                        document.getElementById('lead_val').innerText = isAggregate ? 'N/A' : data.lead_time_days + ' days';
                        document.getElementById('safety_val').innerText = isAggregate ? 'N/A' : data.safety_stock + ' units';
                        document.getElementById('eoq_val').innerText = isAggregate ? 'N/A' : data.eoq + ' units';

                        document.getElementById('regressionEquationBox').style.display = 'block';
                        document.getElementById('eqIntercept').textContent = data.intercept;
                        document.getElementById('eqSlope').textContent = (data.slope >= 0 ? '+' : '') + data.slope;
                        document.getElementById('eqTrend').textContent = data.trend_direction;
                        document.getElementById('eqR2').textContent = data.r2;

                        const stockoutVal = document.getElementById('stockout_val');
                        stockoutVal.textContent = isAggregate ? 'Select a specific product for stockout projection' :
                            (data.days_until_stockout !== null ? `${data.stockout_date} (in ${data.days_until_stockout} days)` : 'No recent sales — cannot project');

                        document.querySelector('.btn-view-intel[data-type="forecast"]').disabled = isAggregate;

                        document.getElementById('pendingPoBox').innerHTML = (!isAggregate && data.pending_po) ?
                            `<div class="alert alert-warning p-2 mb-0" style="font-size:10px;">Pending auto-reorder PO: <b>${data.pending_po.po_number}</b>. <a href="${BASE_URL}/admin/procurement/purchase-orders?status=pending_approval">Review →</a></div>` :
                            (isAggregate ? `<div class="alert alert-light border p-2 mb-0 text-muted" style="font-size:10px;">Select a specific product to see reorder math.</div>` :
                                `<div class="alert alert-light border p-2 mb-0 text-muted" style="font-size:10px;">No auto-reorder currently pending.</div>`);
                    })
                    .catch(err => {
                        btnRun.disabled = false;
                        btnRun.textContent = 'Run Forecast';
                        alert('Done forecasting!');
                        console.error(err);
                    });
            }

            // ============ CHART — now extends into the 3 forward-forecast months,
            // visually distinct (blue dashed) from the historical trend line (green dashed) ============
            function renderLRChart(data) {
                const ctx = document.getElementById('lrChart').getContext('2d');
                if (lrChart) lrChart.destroy();

                const futureLabels = (data.forecast_months || []).map(m => m.label);
                const allLabels = [...data.monthly_labels, ...futureLabels];

                const actualSeries = [...data.monthly_values, ...futureLabels.map(() => null)];
                const trendSeries = [...data.monthly_regression, ...futureLabels.map(() => null)];
                const forecastSeries = [
                    ...data.monthly_values.map(() => null).slice(0, -1),
                    data.monthly_regression[data.monthly_regression.length - 1], // bridges the trend line into the forecast
                    ...(data.forecast_months || []).map(m => m.predicted)
                ];

                lrChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: allLabels.map(fmtMonth),
                        datasets: [
                            { label: 'Actual', data: actualSeries, borderColor: '#7b1113', backgroundColor: '#7b1113', pointRadius: 4, fill: false, tension: 0.1 },
                            { label: 'Trend Line', data: trendSeries, borderColor: '#22c55e', borderDash: [5, 5], pointRadius: 0, fill: false },
                            { label: 'Forecasted (future)', data: forecastSeries, borderColor: '#3b82f6', borderDash: [5, 5], pointRadius: 3, fill: false }
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 9 } } } } }
                });
            }

            function renderMAChart(data) {
                const canvas = document.getElementById('maChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (maChart) maChart.destroy();

                if (!data.daily_values || !data.daily_values.length) {
                    canvas.parentElement.innerHTML = '<p class="text-muted text-center py-5">No daily movement data for this period.</p>';
                    return;
                }

                const dailyVals = data.daily_values;
                const ma = dailyVals.map((_, i) => {
                    const window = dailyVals.slice(Math.max(0, i - 6), i + 1);
                    return +(window.reduce((s, v) => s + v, 0) / window.length).toFixed(2);
                });

                maChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.daily_labels.map(d => new Date(d).toLocaleDateString('default', { month: 'short', day: 'numeric' })),
                        datasets: [
                            { label: 'Daily', data: dailyVals, borderColor: '#cbd5e1', pointRadius: 0, borderWidth: 1, fill: false },
                            { label: '7-Day Moving Avg', data: ma, borderColor: '#0d2e4f', pointRadius: 0, borderWidth: 2, fill: false }
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { labels: { font: { size: 10 } } } }, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 9 }, maxTicksLimit: 10 } } } }
                });
            }

            // ============ INTELLIGENCE DRAWER ============
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

                const futureRows = (data.forecast_months || []).map(m => `
            <tr class="table-light"><td>${fmtMonth(m.label)}</td><td class="text-end" colspan="2"><i class="fas fa-arrow-right text-primary me-1"></i>Forecasted: <b>${m.predicted}</b></td></tr>
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
                <tbody>${rows}${futureRows}</tbody>
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
                        <tbody>${data.map(s => `<tr><td>${s.name}</td><td class="text-end">${s.on_time_rate}%</td><td class="text-end">${s.accuracy_rate}%</td><td class="text-end">${s.total_orders}</td><td class="text-end">${s.lead_time_days}d</td></tr>`).join('')}</tbody>
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
                        <tbody>${data.map(p => `<tr><td>${p.name}</td><td class="text-muted">${p.cat_name}</td><td class="text-end">${p.stock}</td><td class="text-muted">${p.last_moved || 'Never'}</td></tr>`).join('')}</tbody>
                    </table>
                ` : `<p class="text-muted text-center py-5">No slow-moving stock detected.</p>`;
            })
            .catch(err => { content.innerHTML = `<p class="text-danger text-center py-5">Failed to load report.</p>`; console.error(err); });
    }
});