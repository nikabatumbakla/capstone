// Usage: add data-auto-refresh="URL" to any container element.
// This utility fetches that URL every N seconds and swaps the element's innerHTML
// with the response — used for tables, KPI numbers, stock counts, etc.
// It NEVER touches form inputs currently being typed into, and pauses while
// the tab is not visible (saves server load when the browser tab is backgrounded).

document.addEventListener("DOMContentLoaded", function() {
    const refreshables = document.querySelectorAll('[data-auto-refresh]');
    if (!refreshables.length) return;

    refreshables.forEach(el => {
        const url = el.getAttribute('data-auto-refresh');
        const interval = parseInt(el.getAttribute('data-refresh-interval') || '8000');

        function refresh() {
            if (document.hidden) return; // don't poll a backgrounded tab
            if (el.querySelector('input:focus, textarea:focus, select:focus')) return; // never interrupt active typing

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => { el.innerHTML = html; })
                .catch(err => console.error('Auto-refresh failed for', url, err));
        }

        setInterval(refresh, interval);
    });
});