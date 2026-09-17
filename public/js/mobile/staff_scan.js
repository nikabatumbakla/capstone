document.addEventListener("DOMContentLoaded", function() {
    const modeSelect = document.getElementById('scanMode');
    const grrPicker = document.getElementById('grrPoPicker');
    const outboundPicker = document.getElementById('outboundSoPicker');
    const poSelect = document.getElementById('poSelect');
    const soSelect = document.getElementById('soSelect');
    const barcodeInput = document.getElementById('barcodeInput');
    const resultDiv = document.getElementById('scanResult');
    const orderItemsCard = document.getElementById('orderItemsCard');
    const orderItemsTable = document.getElementById('orderItemsTable');
    const cameraWrap = document.getElementById('cameraWrap');
    const scanPlaceholder = document.getElementById('scanPlaceholder');
    const scanStatus = document.getElementById('scanStatus');
    const scanHint = document.getElementById('scanHint');
    const btnToggleCamera = document.getElementById('btnToggleCamera');
    const btnToggleTorch = document.getElementById('btnToggleTorch');
    const videoEl = document.getElementById('scanVideo');

    let currentOrderItems = [];
    let codeReader = null;
    let cameraOn = false;
    let hasHandled = false;
    let hintTimer = null;
    let torchOn = false;
    let mediaStream = null;

    function toggleModeUI() {
        const mode = modeSelect.value;
        grrPicker.style.display = mode === 'grr' ? 'block' : 'none';
        outboundPicker.style.display = mode === 'outbound' ? 'block' : 'none';
        orderItemsCard.style.display = 'none';
        resultDiv.innerHTML = '';
        currentOrderItems = [];
    }
    modeSelect.addEventListener('change', toggleModeUI);
    toggleModeUI();

    function renderItemsTable(items, mode) {
        if (!items.length) {
            orderItemsTable.innerHTML = `<p class="text-muted small mb-0">No items found on this order.</p>`;
            orderItemsCard.style.display = 'block';
            return;
        }
        const rows = items.map(i => {
            const ordered = i.qty_ordered;
            const done = mode === 'grr' ? i.qty_received : (i.scanned_qty || 0);
            const pct = ordered > 0 ? Math.round((done / ordered) * 100) : 0;
            return `
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <p class="mb-0 fw-bold" style="font-size:11px;">${i.name}</p>
                        <small class="text-muted">${done}/${ordered} ${i.unit} scanned</small>
                    </div>
                    <span class="badge ${pct >= 100 ? 'bg-success' : 'bg-light text-dark border'}">${pct}%</span>
                </div>`;
        }).join('');
        orderItemsTable.innerHTML = rows;
        orderItemsCard.style.display = 'block';

        if (mode === 'outbound') checkOutboundCompletion(items);
    }

    poSelect.addEventListener('change', function() {
        if (!this.value) { orderItemsCard.style.display = 'none'; return; }
        fetch(`${BASE_URL}m/staff/scan/po-items/${this.value}`)
            .then(res => res.json())
            .then(items => {
                currentOrderItems = items;
                renderItemsTable(items, 'grr');
            });
    });

    soSelect.addEventListener('change', function() {
        if (!this.value) { orderItemsCard.style.display = 'none'; return; }
        const selectedOption = this.options[this.selectedIndex];
        window.currentSoFulfillment = selectedOption.getAttribute('data-fulfillment') || 'delivery';
        fetch(`${BASE_URL}m/staff/scan/so-items/${this.value}`)
            .then(res => res.json())
            .then(items => {
                currentOrderItems = items;
                renderItemsTable(items, 'outbound');
            });
    });

    // ============ CAMERA — ZXing, same as customer scan ============
    function stopCamera() {
        if (codeReader) codeReader.reset();
        if (hintTimer) clearTimeout(hintTimer);
        cameraWrap.style.display = 'none';
        scanPlaceholder.style.display = 'block';
        scanHint.style.display = 'none';
        btnToggleTorch.style.display = 'none';
        btnToggleCamera.innerHTML = '<i class="fas fa-camera me-1"></i> Enable Camera';
        cameraOn = false;
        torchOn = false;
    }

    btnToggleCamera.addEventListener('click', async function() {
        if (typeof ZXing === 'undefined') {
            scanStatus.innerHTML = `<div class="alert alert-danger small">Scanner library failed to load. Check your internet connection.</div>`;
            return;
        }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            scanStatus.innerHTML = `<div class="alert alert-warning small">
                Camera access requires a secure (HTTPS) connection. Please use manual barcode entry below, or access this page via an HTTPS link.
            </div>`;
            return;
        }
        if (cameraOn) { stopCamera(); return; }

        scanStatus.innerHTML = '';
        hasHandled = false;
        btnToggleCamera.disabled = true;
        btnToggleCamera.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Requesting access...';

        try {
            codeReader = new ZXing.BrowserMultiFormatReader();
            scanPlaceholder.style.display = 'none';
            cameraWrap.style.display = 'block';

            const constraints = { video: { facingMode: { ideal: "environment" } } };

            await codeReader.decodeFromConstraints(constraints, videoEl, (result) => {
                if (result && !hasHandled) {
                    hasHandled = true;
                    barcodeInput.value = result.getText();
                    doLookup();
                    stopCamera();
                }
            });

            mediaStream = videoEl.srcObject;
            const track = mediaStream ? mediaStream.getVideoTracks()[0] : null;
            if (track && track.getCapabilities && track.getCapabilities().torch) {
                btnToggleTorch.style.display = 'block';
            }

            cameraOn = true;
            btnToggleCamera.disabled = false;
            btnToggleCamera.innerHTML = '<i class="fas fa-stop me-1"></i> Stop Camera';

            hintTimer = setTimeout(() => { if (cameraOn) scanHint.style.display = 'block'; }, 5000);

        } catch (err) {
            btnToggleCamera.disabled = false;
            btnToggleCamera.innerHTML = '<i class="fas fa-camera me-1"></i> Enable Camera';
            scanPlaceholder.style.display = 'block';
            cameraWrap.style.display = 'none';

            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                scanStatus.innerHTML = `<div class="alert alert-warning small">Camera access was denied. Please allow camera permission for this site, then tap "Enable Camera" again.</div>`;
            } else if (err.name === 'NotFoundError') {
                scanStatus.innerHTML = `<div class="alert alert-warning small">No camera found on this device. Please use manual entry below.</div>`;
            } else {
                scanStatus.innerHTML = `<div class="alert alert-warning small">Camera error: ${err.message || err}. Please use manual entry below.</div>`;
            }
        }
    });

    btnToggleTorch.addEventListener('click', async function() {
        if (!mediaStream) return;
        const track = mediaStream.getVideoTracks()[0];
        torchOn = !torchOn;
        try {
            await track.applyConstraints({ advanced: [{ torch: torchOn }] });
            btnToggleTorch.classList.toggle('btn-warning', torchOn);
        } catch (e) { /* torch not supported on this device, silently ignore */ }
    });

    // ============ LOOKUP + MODE-SPECIFIC RENDERING (unchanged business logic) ============
    function doLookup() {
        const barcode = barcodeInput.value.trim();
        const mode = modeSelect.value;
        if (!barcode) return;

        if (mode === 'grr' && !poSelect.value) {
            scanStatus.innerHTML = `<div class="alert alert-warning small">Please select a Purchase Order first.</div>`;
            return;
        }
        if (mode === 'outbound' && !soSelect.value) {
            scanStatus.innerHTML = `<div class="alert alert-warning small">Please select a Sales Order first.</div>`;
            return;
        }

        resultDiv.innerHTML = `<div class="text-center p-4"><div class="spinner-border spinner-border-sm"></div></div>`;

        fetch(`${BASE_URL}m/staff/scan/lookup`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    [CSRF_TOKEN_NAME]: CSRF_HASH,
                    barcode
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.found) { renderNewProductPrompt(barcode, mode); return; }
                renderScanResult(data.product, mode, barcode);
            })
            .catch(() => { resultDiv.innerHTML = `<div class="alert alert-danger small">Lookup failed.</div>`; });
    }

    function renderNewProductPrompt(barcode, mode) {
        resultDiv.innerHTML = `
            <div class="m-card">
                <p class="fw-bold mb-2" style="font-size:12px;">No product found for barcode: ${barcode}</p>
                <label class="fw-bold small mb-1 d-block">PRODUCT NAME *</label>
                <input type="text" id="newProdName" class="form-control mb-2">
                <label class="fw-bold small mb-1 d-block">CATEGORY *</label>
                <select id="newProdCategory" class="form-select mb-2">${window.CATEGORIES_HTML}</select>
                <label class="fw-bold small mb-1 d-block">UNIT</label>
                <input type="text" id="newProdUnit" class="form-control mb-3" value="piece">
                <button id="btnCreateProduct" class="btn w-100 text-white" style="background:#1d3557;">Create Product & Continue</button>
            </div>`;

        document.getElementById('btnCreateProduct').addEventListener('click', function() {
            const name = document.getElementById('newProdName').value.trim();
            const categoryId = document.getElementById('newProdCategory').value;
            const unit = document.getElementById('newProdUnit').value.trim() || 'piece';
            if (!name) { alert('Please enter a product name.'); return; }

            fetch(`${BASE_URL}m/staff/scan/create-product`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        [CSRF_TOKEN_NAME]: CSRF_HASH,
                        name,
                        category_id: categoryId,
                        unit
                    })
                })
                .then(res => res.json())
                .then(data => renderScanResult({ product_id: data.product_id, name, unit, total_stock: 0, barcode_value: data.barcode }, mode, data.barcode));
        });
    }

    function renderScanResult(p, mode, barcode) {
        if (mode === 'inbound') {
            resultDiv.innerHTML = `
                <div class="m-card">
                    <p class="fw-bold mb-1">${p.name}</p>
                    <small class="text-muted d-block mb-2">Barcode: ${p.barcode_value || barcode}</small>
                    <form action="${BASE_URL}m/staff/scan/submit-inbound" method="POST">
                        <input type="hidden" name="${CSRF_TOKEN_NAME}" value="${CSRF_HASH}">
                        <input type="hidden" name="product_id" value="${p.product_id}">
                        <label class="fw-bold small mb-1 d-block">QUANTITY TO ADD *</label>
                        <input type="number" name="qty" class="form-control mb-2" min="1" required>
                        <label class="fw-bold small mb-1 d-block">BATCH NUMBER</label>
                        <input type="text" name="batch_number" class="form-control mb-2">
                        <label class="fw-bold small mb-1 d-block">EXPIRY DATE</label>
                        <input type="date" name="expires_at" class="form-control mb-3">
                        <button type="submit" class="btn w-100 text-white py-2" style="background:#7b1113;">Confirm Stock-In</button>
                    </form>
                </div>`;
            return;
        }

        const match = currentOrderItems.find(i => i.barcode_value === barcode || i.product_id == p.product_id);
        if (!match) {
            resultDiv.innerHTML = `<div class="alert alert-warning small">"${p.name}" is not on the selected order.</div>`;
            return;
        }

        if (mode === 'grr') {
            const remaining = match.qty_ordered - match.qty_received;
            resultDiv.innerHTML = `
                <div class="m-card">
                    <p class="fw-bold mb-1">${match.name}</p>
                    <small class="text-muted d-block mb-2">${match.qty_received}/${match.qty_ordered} ${match.unit} already received · ${remaining} remaining</small>
                    <form id="grrForm">
                        <input type="hidden" name="product_id" value="${match.product_id}">
                        <input type="hidden" name="po_id" value="${poSelect.value}">

                        <label class="fw-bold small mb-1 d-block">QUANTITY RECEIVED NOW *</label>
                        <input type="number" name="qty" id="grrQty" class="form-control mb-2" min="1" max="${remaining}" value="${remaining}" required>

                        <label class="fw-bold small mb-1 d-block">CONDITION *</label>
                        <select name="condition" id="grrCondition" class="form-select mb-2">
                            <option value="good" selected>Good — matches order</option>
                            <option value="damaged">Damaged</option>
                            <option value="wrong_item">Wrong Item</option>
                            <option value="expired">Expired on Arrival</option>
                        </select>

                        <div id="grrRejectedWrap" style="display:none;">
                            <label class="fw-bold small mb-1 d-block">QUANTITY AFFECTED *</label>
                            <input type="number" name="qty_rejected" id="grrRejected" class="form-control mb-2" min="0" value="0">
                        </div>

                        <label class="fw-bold small mb-1 d-block">NOTES (if incomplete or damaged)</label>
                        <textarea name="notes" class="form-control mb-3" rows="2" placeholder="e.g. Received 19 of 20 — 1 unit missing from box"></textarea>

                        <button type="submit" class="btn w-100 text-white py-2" style="background:#7b1113;">Confirm Receipt</button>
                    </form>
                </div>`;

            const conditionSelect = document.getElementById('grrCondition');
            const rejectedWrap = document.getElementById('grrRejectedWrap');
            const rejectedInput = document.getElementById('grrRejected');
            const qtyInput = document.getElementById('grrQty');

            conditionSelect.addEventListener('change', function() {
                if (this.value === 'good') {
                    rejectedWrap.style.display = 'none';
                    rejectedInput.value = 0;
                } else {
                    rejectedWrap.style.display = 'block';
                    rejectedInput.value = qtyInput.value;
                }
            });

            document.getElementById('grrForm').addEventListener('submit', function(e) {
                e.preventDefault();
                if (conditionSelect.value !== 'good' && parseInt(rejectedInput.value || 0) <= 0) {
                    alert('Please enter the quantity affected for this condition.');
                    return;
                }

                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';

                fetch(`${BASE_URL}m/staff/scan/submit-grr`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            [CSRF_TOKEN_NAME]: CSRF_HASH,
                            ...Object.fromEntries(new FormData(this))
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Confirm Receipt';
                        if (!data.success) { resultDiv.innerHTML = `<div class="alert alert-danger small">${data.message}</div>`; return; }
                        resultDiv.innerHTML = `<div class="alert alert-success small">${data.message}</div>`;
                        barcodeInput.value = '';
                        if (poSelect.value) poSelect.dispatchEvent(new Event('change'));
                    })
                    .catch(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Confirm Receipt';
                        resultDiv.innerHTML = `<div class="alert alert-danger small">Failed to submit.</div>`;
                    });
            });

        } else if (mode === 'outbound') {
            resultDiv.innerHTML = `
                <div class="m-card">
                    <p class="fw-bold mb-1">${match.name}</p>
                    <small class="text-muted d-block mb-2">Ordered: ${match.qty_ordered} ${match.unit} · Already scanned: ${match.scanned_qty || 0}</small>
                    <form id="outboundForm">
                        <label class="fw-bold small mb-1 d-block">QUANTITY VERIFIED *</label>
                        <input type="number" name="qty" id="outboundQty" class="form-control mb-3" min="1" max="${match.qty_ordered - (match.scanned_qty || 0)}" value="${match.qty_ordered - (match.scanned_qty || 0)}" required>
                        <button type="submit" class="btn w-100 text-white py-2" style="background:#7b1113;">Confirm Item</button>
                    </form>
                </div>`;

            document.getElementById('outboundForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const qty = document.getElementById('outboundQty').value;
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;

                fetch(`${BASE_URL}m/staff/scan/submit-outbound`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            [CSRF_TOKEN_NAME]: CSRF_HASH,
                            order_id: soSelect.value,
                            product_id: match.product_id,
                            qty
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        if (!data.success) { resultDiv.innerHTML = `<div class="alert alert-danger small">${data.message}</div>`; return; }

                        match.scanned_qty = (match.scanned_qty || 0) + parseInt(qty);
                        resultDiv.innerHTML = `<div class="alert alert-success small">"${match.name}" verified.</div>`;
                        barcodeInput.value = '';
                        renderItemsTable(currentOrderItems, 'outbound');
                    })
                    .catch(() => {
                        submitBtn.disabled = false;
                        resultDiv.innerHTML = `<div class="alert alert-danger small">Failed to record scan.</div>`;
                    });
            });
        }
    }

    function checkOutboundCompletion(items) {
        const allScanned = items.length > 0 && items.every(i => (i.scanned_qty || 0) >= i.qty_ordered);
        let dispatchCard = document.getElementById('dispatchCard');
        if (!allScanned) {
            if (dispatchCard) dispatchCard.remove();
            return;
        }
        if (dispatchCard) return;

        const isPickup = window.currentSoFulfillment === 'pickup';
        dispatchCard = document.createElement('div');
        dispatchCard.id = 'dispatchCard';
        dispatchCard.className = 'm-card';
        dispatchCard.innerHTML = `
            <div class="alert alert-success small mb-2"><i class="fas fa-check-circle me-1"></i>All items verified.</div>
            <button type="button" id="btnMarkDispatch" class="btn w-100 py-2 text-white" style="background:#1d3557;">
                <i class="fas fa-check-circle me-1"></i> ${isPickup ? 'Mark Ready for Pickup' : 'Mark Out for Delivery'}
            </button>
            <div id="dispatchMsg" class="mt-2"></div>`;
        orderItemsCard.after(dispatchCard);

        document.getElementById('btnMarkDispatch').addEventListener('click', function() {
            this.disabled = true;
            fetch(`${BASE_URL}m/staff/scan/update-so-status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        [CSRF_TOKEN_NAME]: CSRF_HASH,
                        order_id: soSelect.value
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const msg = document.getElementById('dispatchMsg');
                    if (!data.success) {
                        this.disabled = false;
                        msg.innerHTML = `<div class="alert alert-warning small mb-0">${data.message}</div>`;
                        return;
                    }
                    msg.innerHTML = `<div class="alert alert-success small mb-0">Order marked ${data.label}.</div>`;
                    setTimeout(() => location.reload(), 1200);
                })
                .catch(() => {
                    this.disabled = false;
                    document.getElementById('dispatchMsg').innerHTML = `<div class="alert alert-danger small mb-0">Failed to update status.</div>`;
                });
        });
    }

    document.getElementById('btnLookup').addEventListener('click', doLookup);
    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            doLookup();
        }
    });
});