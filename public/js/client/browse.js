document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('.stat-card').forEach(card => {
                let name = card.querySelector('h6').innerText.toLowerCase();
                card.closest('.col-xl-3').style.display = name.includes(filter) ? "" : "none";
            });
        });
    }
});