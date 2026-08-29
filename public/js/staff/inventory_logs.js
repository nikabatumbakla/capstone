document.addEventListener("DOMContentLoaded", function() {
    const productSelect = document.getElementById('selectProductToAdjust');
    const displayQty = document.getElementById('displayQtyBefore');
    const hiddenQty = document.getElementById('hiddenQtyBefore');

    if (productSelect) {
        productSelect.addEventListener('change', function() {
            // Get the data-qty attribute from the selected option
            const selectedOption = this.options[this.selectedIndex];
            const currentStock = selectedOption.getAttribute('data-qty');

            // Auto-fill the "Before" fields
            displayQty.value = currentStock;
            hiddenQty.value = currentStock;
        });
    }
});