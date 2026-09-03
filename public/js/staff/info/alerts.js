document.addEventListener("DOMContentLoaded", function() {
    document.querySelector('#filterForm select[name="type"]').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});