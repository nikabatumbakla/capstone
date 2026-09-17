<div id="quickLookupWidget" style="position:fixed; bottom:24px; right:24px; z-index:1050;">
    <button type="button" id="btnToggleQuickLookup" class="rounded-circle shadow-lg border-0" style="width:56px; height:56px; background:#7b1113; color:#fff;">
        <i class="fas fa-search"></i>
    </button>

    <div id="quickLookupPanel" class="bg-white rounded-4 shadow-lg border-0" style="display:none; width:340px; position:absolute; bottom:66px; right:0; box-shadow:0 10px 40px rgba(0,0,0,0.18);">
        <div class="p-3 text-white rounded-top-4 d-flex justify-content-between align-items-center" style="background:#7b1113;">
            <span class="fw-bold" style="font-size:12px;"><i class="fas fa-search me-2"></i>Quick Lookup</span>
            <i class="fas fa-times" id="btnCloseQuickLookup" style="cursor:pointer;"></i>
        </div>
        <div class="p-3">
            <input type="text" id="quickLookupInput" class="form-control form-control-sm rounded-pill mb-2" placeholder="Scan barcode or type a name..." style="font-size:12px;">
            <div id="quickLookupResults" style="max-height:280px; overflow-y:auto;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const panel = document.getElementById('quickLookupPanel');
    const input = document.getElementById('quickLookupInput');
    const results = document.getElementById('quickLookupResults');
    let isOpen = false;

    function peso(n) { return '₱' + parseFloat(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }); }

    document.getElementById('btnToggleQuickLookup').addEventListener('click', function() {
        isOpen = !isOpen;
        panel.style.display = isOpen ? 'block' : 'none';
        if (isOpen) { input.value = ''; results.innerHTML = ''; input.focus(); }
    });
    document.getElementById('btnCloseQuickLookup').addEventListener('click', function() {
        isOpen = false;
        panel.style.display = 'none';
    });

    function doSearch(term) {
        if (term.length < 2) { results.innerHTML = ''; return; }

        fetch(`${BASE_URL}/staff/operations/quick-lookup/search`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ [CSRF_TOKEN_NAME]: CSRF_HASH, term })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.length) { results.innerHTML = `<p class="text-muted text-center py-3" style="font-size:11px;">No matches found.</p>`; return; }
                results.innerHTML = data.map(p => {
                    const stock = p.total_stock || 0;
                    const badge = stock <= 0 ? '<span class="badge bg-secondary" style="font-size:9px;">Out of Stock</span>' : `<span class="badge bg-success" style="font-size:9px;">${stock} in stock</span>`;
                    return `
                        <div class="p-2 mb-2 rounded-3 border d-flex justify-content-between align-items-center">
                            <div>
                                <p class="fw-bold mb-0" style="font-size:11px;">${p.name}</p>
                                <small class="text-muted" style="font-size:9px;">${p.barcode_value || 'No barcode'}</small>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold text-maroon mb-1" style="font-size:12px;">${peso(p.sell_price)}</p>
                                ${badge}
                            </div>
                        </div>`;
                }).join('');
            })
            .catch(() => { results.innerHTML = `<p class="text-danger text-center py-3" style="font-size:11px;">Search failed.</p>`; });
    }

    let debounce;
    input.addEventListener('input', function() {
        clearTimeout(debounce);
        debounce = setTimeout(() => doSearch(this.value.trim()), 300);
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(debounce);
            doSearch(this.value.trim());
        }
    });
});
</script>