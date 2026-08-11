document.addEventListener("DOMContentLoaded", function() {

    // --- 1. SIDEBAR SCROLL PERSISTENCE (THE "STAY STILL" FIX) ---
    const sidebarNav = document.getElementById('sidebarScrollContainer');

    if (sidebarNav) {
        // Restore scroll position IMMEDIATELY upon page load
        const scrollPos = localStorage.getItem('sidebarScrollPosition');
        if (scrollPos) {
            sidebarNav.scrollTop = scrollPos;
        }

        // Save scroll position whenever ANY link inside the sidebar is clicked
        const allSidebarLinks = document.querySelectorAll('#sidebar a');
        allSidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                localStorage.setItem('sidebarScrollPosition', sidebarNav.scrollTop);
            });
        });
    }

    // --- 2. SIDEBAR & CONTENT TOGGLE (Burger Menu) ---
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
    // Ensures only one folder is open at a time (Accordion behavior)
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

    // --- 4. REAL-TIME DASHBOARD CHART (Chart.js) ---
    const chartElement = document.getElementById('salesChart');
    if (chartElement) {
        const labels = JSON.parse(chartElement.getAttribute('data-labels'));
        const values = JSON.parse(chartElement.getAttribute('data-values'));

        new Chart(chartElement.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales (₱)',
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
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }
            }
        });
    }
});