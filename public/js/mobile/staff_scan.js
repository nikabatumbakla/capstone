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
            const cameraError = document.getElementById('cameraError');
            const btnToggleCamera = document.getElementById('btnToggleCamera');

            let currentOrderItems = [];
            let html5QrCode = null;
            let cameraOn = false;

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
                            const done = mode === 'grr' ? i.qty_received : 0;
                            const pct = ordered > 0 ? Math.round((done / ordered) * 100) : 0;
                            return `
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <p class="mb-0 fw-bold" style="font-size:11px;">${i.name}</p>
                        <small class="text-muted">${ordered} ${i.unit} ordered${mode==='grr' ? ` · ${done} received` : ''}</small>
                    </div>
                    ${mode === 'grr' ? `<span class="badge bg-light text-dark border">${pct}%</span>` : ''}
                </div>`;
        }).join('');
        orderItemsTable.innerHTML = rows;
        orderItemsCard.style.display = 'block';
    }

    poSelect.addEventListener('change', function() {
        if (!this.value) { orderItemsCard.style.display = 'none'; return; }
        fetch(`${BASE_URL}m/staff/scan/po-items/${this.value}`)
            .then(res => res.json())
            .then(items => { currentOrderItems = items; renderItemsTable(items, 'grr'); });
    });

    soSelect.addEventListener('change', function() {
        if (!this.value) { orderItemsCard.style.display = 'none'; return; }
        fetch(`${BASE_URL}m/staff/scan/so-items/${this.value}`)
            .then(res => res.json())
            .then(items => { currentOrderItems = items; renderItemsTable(items, 'outbound'); });
    });

    btnToggleCamera.addEventListener('click', function() {
        if (typeof Html5Qrcode === 'undefined') {
            cameraError.innerHTML = `<div class="alert alert-danger small">Camera library failed to load. Check your internet connection.</div>`;
            return;
        }
        if (!cameraOn) {
            cameraWrap.style.display = 'block';
            cameraError.innerHTML = '';
            html5QrCode = new Html5Qrcode("camera-reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 220 },
                (decodedText) => {
                    barcodeInput.value = decodedText;
                    doLookup();
                    stopCamera();
                },
                () => {} // ignore per-frame scan failures, expected while aiming
            ).then(() => {
                cameraOn = true;
                btnToggleCamera.innerHTML = '<i class="fas fa-stop me-1"></i> Stop Camera';
            }).catch(err => {
                cameraError.innerHTML = `<div class="alert alert-warning small">Camera unavailable (${err}). Please allow camera permission or use manual entry below.</div>`;
                cameraWrap.style.display = 'none';
            });
        } else {
            stopCamera();
        }
    });

    function stopCamera() {
        if (html5QrCode && cameraOn) {
            html5QrCode.stop().then(() => { cameraWrap.style.display = 'none'; }).catch(() => {});
        }
        btnToggleCamera.innerHTML = '<i class="fas fa-camera me-1"></i> Start Camera Scan';
        cameraOn = false;
    }

    function doLookup() {
        const barcode = barcodeInput.value.trim();
        const mode = modeSelect.value;
        if (!barcode) return;

        if (mode === 'grr' && !poSelect.value) {
            resultDiv.innerHTML = `<div class="alert alert-warning small">Please select a Purchase Order first.</div>`;
            return;
        }
        if (mode === 'outbound' && !soSelect.value) {
            resultDiv.innerHTML = `<div class="alert alert-warning small">Please select a Sales Order first.</div>`;
            return;
        }

        resultDiv.innerHTML = `<div class="text-center p-4"><div class="spinner-border spinner-border-sm"></div></div>`;

        fetch(`${BASE_URL}m/staff/scan/lookup`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ barcode })
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
                body: new URLSearchParams({ name, category_id: categoryId, unit })
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
                    <form action="${BASE_URL}m/staff/scan/submit-grr" method="POST">
                        <input type="hidden" name="product_id" value="${match.product_id}">
                        <input type="hidden" name="po_id" value="${poSelect.value}">
                        <label class="fw-bold small mb-1 d-block">QUANTITY RECEIVED NOW *</label>
                        <input type="number" name="qty" class="form-control mb-3" min="1" max="${remaining}" value="${remaining}" required>
                        <button type="submit" class="btn w-100 text-white py-2" style="background:#7b1113;">Confirm Receipt</button>
                    </form>
                </div>`;
        } else if (mode === 'outbound') {
            resultDiv.innerHTML = `
                <div class="m-card">
                    <p class="fw-bold mb-1">${match.name}</p>
                    <small class="text-muted d-block mb-2">Ordered: ${match.qty_ordered} ${match.unit}</small>
                    <form action="${BASE_URL}m/staff/scan/submit-outbound" method="POST">
                        <input type="hidden" name="product_id" value="${match.product_id}">
                        <input type="hidden" name="order_id" value="${soSelect.value}">
                        <label class="fw-bold small mb-1 d-block">QUANTITY VERIFIED *</label>
                        <input type="number" name="qty" class="form-control mb-3" min="1" max="${match.qty_ordered}" value="${match.qty_ordered}" required>
                        <button type="submit" class="btn w-100 text-white py-2" style="background:#7b1113;">Confirm & Dispatch</button>
                    </form>
                </div>`;
        }
    }

    document.getElementById('btnLookup').addEventListener('click', doLookup);
    barcodeInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); doLookup(); } });
});