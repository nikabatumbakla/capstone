document.addEventListener("DOMContentLoaded", function() {
    // --- 1. SIDEBAR & CONTENT TOGGLE (Burger Menu) ---
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const toggle = document.getElementById('sidebarToggle');

    if (toggle) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
        });
    }

    // --- 2. ACCORDION DROPDOWN LOGIC ---
    // Closes other open dropdowns when one is opened
    const collapseElements = document.querySelectorAll('.collapse');
    collapseElements.forEach(el => {
        el.addEventListener('show.bs.collapse', function() {
            collapseElements.forEach(otherEl => {
                if (otherEl !== el) {
                    const bsCollapse = bootstrap.Collapse.getInstance(otherEl);
                    if (bsCollapse) bsCollapse.hide();
                }
            });
        });
    });

    // --- 3. REAL-TIME DASHBOARD CHART (Chart.js) ---
    const chartElement = document.getElementById('salesChart');
    if (chartElement) {
        // Retrieve PHP data from HTML attributes
        const labels = JSON.parse(chartElement.getAttribute('data-labels'));
        const values = JSON.parse(chartElement.getAttribute('data-values'));

        new Chart(chartElement.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales (₱)',
                    data: values,
                    backgroundColor: '#7b1113', // Your professional Maroon
                    hoverBackgroundColor: '#4a0000',
                    borderRadius: 8,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a0505',
                        titleFont: { size: 12 },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                return '₱ ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '₱' + value; },
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }

    // --- 4. RESPONSIVENESS CHECK ---
    window.addEventListener('resize', () => {
        if (window.innerWidth < 992) {
            sidebar.classList.add('active');
            content.classList.add('active');
        } else {
            sidebar.classList.remove('active');
            content.classList.remove('active');
        }
    });
});