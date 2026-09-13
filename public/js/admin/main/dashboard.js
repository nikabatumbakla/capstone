document.addEventListener("DOMContentLoaded", function() {

    // --- 1. SIDEBAR SCROLL PERSISTENCE ---
    const sidebarNav = document.getElementById('sidebarScrollContainer');
    if (sidebarNav) {
        const scrollPos = localStorage.getItem('sidebarScrollPosition');
        if (scrollPos) sidebarNav.scrollTop = scrollPos;

        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', function() {
                localStorage.setItem('sidebarScrollPosition', sidebarNav.scrollTop);
            });
        });
    }

    // --- 2. SIDEBAR & CONTENT TOGGLE ---
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const toggle = document.getElementById('sidebarToggle');
    if (toggle) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
        });
    }

    // --- 3. ACCORDION DROPDOWN LOGIC ---
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

    // --- 4. SALES TREND CHART — animated on load, with clear peso-formatted tooltips ---
    const chartElement = document.getElementById('salesChart');
    if (chartElement) {
        const labels = JSON.parse(chartElement.getAttribute('data-labels'));
        const values = JSON.parse(chartElement.getAttribute('data-values'));

        new Chart(chartElement.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales',
                    data: values,
                    backgroundColor: '#7b1113',
                    hoverBackgroundColor: '#4a0000',
                    borderRadius: 8,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: {
                    duration: 900,
                    easing: 'easeOutQuart',
                    delay: (ctx) => ctx.dataIndex * 60
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => '₱' + ctx.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (val) => '₱' + val.toLocaleString() }
                    }
                }
            }
        });
    }
});