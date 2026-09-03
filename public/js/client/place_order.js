document.addEventListener("DOMContentLoaded", function() {
    function recalc() {
        let total = 0;
        document.querySelectorAll('#orderItemsTable tbody tr').forEach(row => {
            const price = parseFloat(row.getAttribute('data-price'));
            const qty = parseFloat(row.querySelector('.qty-input').value || 0);
            const subtotal = price * qty;
            row.querySelector('.row-subtotal').textContent = '₱' + subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 });
            total += subtotal;
        });
        document.getElementById('estimatedTotal').textContent = '₱' + total.toLocaleString(undefined, { minimumFractionDigits: 2 });
    }

    document.querySelectorAll('.qty-input').forEach(input => input.addEventListener('input', recalc));
    recalc();

    // ============ FULFILLMENT TOGGLE ============
    const deliveryRadio = document.getElementById('fulfillDelivery');
    const pickupRadio = document.getElementById('fulfillPickup');
    const addressGroup = document.getElementById('deliveryAddressGroup');
    const addressInput = document.getElementById('deliveryAddressInput');

    function toggleFulfillment() {
        if (pickupRadio && pickupRadio.checked) {
            addressGroup.style.display = 'none';
            addressInput.removeAttribute('required');
        } else {
            addressGroup.style.display = '';
            addressInput.setAttribute('required', 'required');
        }
    }
    if (deliveryRadio && pickupRadio) {
        deliveryRadio.addEventListener('change', toggleFulfillment);
        pickupRadio.addEventListener('change', toggleFulfillment);
        toggleFulfillment();
    }

    // ============ CHEQUE CLEARANCE NOTE ============
    const paymentSelect = document.getElementById('paymentMethodSelect');
    const chequeNote = document.getElementById('chequeNote');

    function toggleChequeNote() {
        if (paymentSelect && chequeNote) {
            chequeNote.style.display = paymentSelect.value === 'check' ? 'block' : 'none';
        }
    }
    if (paymentSelect) {
        paymentSelect.addEventListener('change', toggleChequeNote);
        toggleChequeNote();
    }
});