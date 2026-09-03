document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.querySelector('select[name="reason"]').addEventListener('change', () => filterForm.submit());
        let typingTimer;
        document.getElementById('liveSearch').addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => filterForm.submit(), 600);
        });
    }
});