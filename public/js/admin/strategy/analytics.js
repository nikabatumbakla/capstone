document.addEventListener("DOMContentLoaded", function() {
    let lrChart = null;
    let maChart = null;

    // 1. DYNAMIC FORECAST ENGINE
    const btnRun = document.getElementById('btnRunForecast');
    if (btnRun) {
        btnRun.addEventListener('click', function() {
            const pid = document.getElementById('productSearch').value;
            fetch(`${BASE_URL}/admin/analytics/get-forecast/${pid}`)
                .then(res => res.json())
                .then(data => {
                    renderLRChart(data);
                    document.getElementById('avg_sales').innerText = data.avg_sales + ' units';
                    document.getElementById('pred_sales').innerText = data.forecast_15 + ' units';
                    document.getElementById('eoq_val').innerText = data.eoq + ' units (EOQ)';
                });
        });
    }

    function renderLRChart(data) {
        const ctx = document.getElementById('lrChart').getContext('2d');
        if (lrChart) lrChart.destroy();
        lrChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'Actual Sales', data: data.values, borderColor: '#7b1113', pointRadius: 4, fill: false },
                    { label: 'Regression Trend', data: data.values.map((v, i) => data.intercept + data.slope * (i + 1)), borderColor: '#22c55e', borderDash: [5, 5], fill: false }
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    }

    // 2. INTELLIGENCE DRAWER (EOQ Cost Trade-off)
    const intelBtns = document.querySelectorAll('.btn-view-intel');
    const drawer = new bootstrap.Offcanvas(document.getElementById('intelDrawer'));
    const content = document.getElementById('intelContent');

    intelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            drawer.show();
            content.innerHTML = `
                <div class="p-3">
                    <h5 class="fw-bold mb-4">Expiry-Aware EOQ Intelligence</h5>
                    <div style="height:300px" class="border rounded-4 bg-white p-3 shadow-sm mb-4">
                        <canvas id="eoqTradeOffChart"></canvas>
                    </div>
                    <div class="p-3 bg-light rounded-4">
                        <h6 class="fw-bold small">Cost Savings with EOQ</h6>
                        <p class="mb-0 text-muted">Standard businesses use simple EOQ. Our medical system adjusts for shelf-life to save <b>₱2,700/year</b> by preventing expiration waste.</p>
                    </div>
                </div>
            `;
            // Call Chart logic for EOQ Cost Curve here...
        });
    });
});