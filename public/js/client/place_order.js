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
    const pickupInfoBox = document.getElementById('pickupInfoBox');

    function toggleFulfillment() {
        const isPickup = pickupRadio && pickupRadio.checked;
        addressGroup.style.display = isPickup ? 'none' : '';
        pickupInfoBox.style.display = isPickup ? 'block' : 'none';
        if (isPickup) addressInput.removeAttribute('required');
        else addressInput.setAttribute('required', 'required');
        updatePrepaymentNote();
    }
    if (deliveryRadio && pickupRadio) {
        deliveryRadio.addEventListener('change', toggleFulfillment);
        pickupRadio.addEventListener('change', toggleFulfillment);
        toggleFulfillment();
    }

    // ============ CHEQUE CLEARANCE NOTE + PREPAYMENT WARNING ============
    const paymentSelect = document.getElementById('paymentMethodSelect');
    const chequeNote = document.getElementById('chequeNote');
    const prepaymentNote = document.getElementById('prepaymentNote');

    function updatePrepaymentNote() {
        if (!paymentSelect) return;
        const isDelivery = deliveryRadio && deliveryRadio.checked;
        const needsPrepay = isDelivery && ['bank_transfer', 'cheque'].includes(paymentSelect.value);
        prepaymentNote.style.display = needsPrepay ? 'block' : 'none';
    }

    function toggleChequeNote() {
        if (paymentSelect && chequeNote) {
            chequeNote.style.display = paymentSelect.value === 'cheque' ? 'block' : 'none';
        }
        updatePrepaymentNote();
    }
    if (paymentSelect) {
        paymentSelect.addEventListener('change', toggleChequeNote);
        toggleChequeNote();
    }
});