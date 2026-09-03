document.addEventListener("DOMContentLoaded", function() {
    let generated = false;

    function renderQr(containerId, text) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        if (typeof QRCode === 'undefined') {
            container.innerHTML = `<p class="text-danger small mb-0">QR library failed to load. Check your internet connection and reload the page.</p>`;
            console.error('QRCode library is undefined — the qrcode.min.js script did not load.');
            return;
        }

        const canvas = document.createElement('canvas');
        container.appendChild(canvas);

        QRCode.toCanvas(canvas, text, { width: 180, margin: 1 }, function(error) {
            if (error) {
                console.error('QR generation failed:', error);
                container.innerHTML = `<p class="text-danger small mb-0">Failed to generate QR code.</p>`;
            }
        });
    }

    const openBtn = document.getElementById('btnOpenQrDrawer');
    const drawerEl = document.getElementById('qrDrawer');

    if (openBtn && drawerEl) {
        openBtn.addEventListener('click', function() {
            if (typeof bootstrap === 'undefined') {
                alert('Bootstrap JS is not loaded on this page — the drawer cannot open. Check that the staff footer includes the Bootstrap bundle script.');
                console.error('window.bootstrap is undefined.');
                return;
            }

            const drawer = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
            drawer.show();

            if (!generated) {
                generated = true;
                renderQr('staffQrCode', STAFF_LOGIN_URL);
                renderQr('customerQrCode', CUSTOMER_INFO_URL);
            }
        });
    } else {
        console.error('QR drawer trigger or drawer element not found on page.');
    }

    // ============ DOWNLOAD ============
    document.querySelectorAll('.btn-download-qr').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = document.getElementById(this.getAttribute('data-target'));
            const canvas = container.querySelector('canvas');
            if (!canvas) { alert('QR code is not ready yet. Please open the QR drawer first.'); return; }

            const link = document.createElement('a');
            link.download = `${this.getAttribute('data-name')}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    });

    // ============ PRINT ============
    document.querySelectorAll('.btn-print-qr').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = document.getElementById(this.getAttribute('data-target'));
            const canvas = container.querySelector('canvas');
            if (!canvas) { alert('QR code is not ready yet. Please open the QR drawer first.'); return; }

            const title = this.getAttribute('data-title');
            const imgData = canvas.toDataURL('image/png');
            const printWindow = window.open('', '_blank', 'width=400,height=500');
            printWindow.document.write(`
                <html><head><title>${title}</title></head>
                <body style="text-align:center; font-family:Arial, sans-serif; padding:40px;">
                    <h2>Robin Rose Trading</h2>
                    <p>${title}</p>
                    <img src="${imgData}" width="220" height="220">
                </body></html>
            `);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => printWindow.print(), 300);
        });
    });
});