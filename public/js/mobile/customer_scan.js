document.addEventListener("DOMContentLoaded", function() {
    const barcodeInput = document.getElementById('barcodeInput');
    const cameraWrap = document.getElementById('cameraWrap');
    const scanPlaceholder = document.getElementById('scanPlaceholder');
    const scanStatus = document.getElementById('scanStatus');
    const scanHint = document.getElementById('scanHint');
    const btnToggleCamera = document.getElementById('btnToggleCamera');
    const btnToggleTorch = document.getElementById('btnToggleTorch');
    const videoEl = document.getElementById('scanVideo');
    const recentCard = document.getElementById('recentScansCard');
    const recentList = document.getElementById('recentScansList');

    let codeReader = null;
    let cameraOn = false;
    let hasHandled = false;
    let hintTimer = null;
    let torchOn = false;
    let mediaStream = null;

    // ===== Recent scans, kept in sessionStorage (per-tab, clears on close) =====
    function getRecentScans() {
        try { return JSON.parse(sessionStorage.getItem('recentScans') || '[]'); } catch { return []; }
    }

    function saveRecentScan(name, productId) {
        let list = getRecentScans().filter(i => i.id !== productId);
        list.unshift({ id: productId, name: name });
        list = list.slice(0, 5);
        sessionStorage.setItem('recentScans', JSON.stringify(list));
        renderRecentScans();
    }

    function renderRecentScans() {
        const list = getRecentScans();
        if (!list.length) { recentCard.style.display = 'none'; return; }
        recentCard.style.display = 'block';
        recentList.innerHTML = list.map(i => `
            <a href="${BASE_URL}m/customer/product/${i.id}" class="text-decoration-none d-block py-2 border-bottom text-dark" style="font-size:11px;">
                <i class="fas fa-history text-muted me-2"></i>${i.name}
            </a>`).join('');
    }
    renderRecentScans();

    function doLookup(barcode) {
        scanStatus.innerHTML = `<div class="text-center py-2"><div class="spinner-border spinner-border-sm"></div></div>`;

        fetch(`${BASE_URL}m/customer/scan/lookup`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ barcode })
            })
            .then(res => res.json())
            .then(data => {
                if (data.found) {
                    if (data.product_name) saveRecentScan(data.product_name, data.product_id);
                    window.location.href = `${BASE_URL}m/customer/product/${data.product_id}`;
                } else {
                    scanStatus.innerHTML = `<div class="alert alert-warning small mb-0">No product found for barcode: ${barcode}</div>`;
                }
            })
            .catch(() => {
                scanStatus.innerHTML = `<div class="alert alert-danger small mb-0">Lookup failed. Please try again.</div>`;
            });
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
                    doLookup(result.getText());
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

            // Show a helpful hint if nothing gets scanned within 5 seconds
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

    document.getElementById('btnLookup').addEventListener('click', function() {
        const v = barcodeInput.value.trim();
        if (v) doLookup(v);
    });
    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btnLookup').click();
        }
    });
});