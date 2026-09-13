document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.store-star-pick').forEach(star => {
        star.addEventListener('click', function() {
            const val = parseInt(this.dataset.val);
            document.getElementById('storeRatingInput').value = val;
            document.querySelectorAll('.store-star-pick').forEach((s, i) => {
                s.classList.toggle('fas', i < val);
                s.classList.toggle('far', i >= val);
                s.style.color = i < val ? '#f1c40f' : '#ccc';
            });
        });
    });
});