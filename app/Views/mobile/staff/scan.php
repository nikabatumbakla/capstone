<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<script>
    const CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
    const CSRF_HASH = "<?= csrf_hash() ?>";
    window.CATEGORIES_HTML = `<?php foreach($categories as $c): ?><option value="<?= $c['category_id'] ?>"><?= esc($c['name']) ?></option><?php endforeach; ?>`;
</script>

<div class="d-flex align-items-center mb-1">
    <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">iScan</h5>
</div>
<p class="text-muted mb-3" style="font-size:11px;">Scan barcodes to manage inventory</p>

<div class="m-card">
    <label class="fw-bold small mb-2 d-block">SELECT MODE</label>
    <div class="m-custom-dropdown mb-3" id="modeDropdownWrap">
        <button type="button" class="m-dropdown-toggle" id="modeDropdownToggle">
            <span id="modeDropdownLabel">Goods Receipt (against a PO)</span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="m-dropdown-menu" id="modeDropdownMenu">
            <div class="m-dropdown-item selected" data-value="grr">Goods Receipt (against a PO)</div>
            <div class="m-dropdown-item" data-value="outbound">Outbound Scan (against a Sales Order)</div>
            <div class="m-dropdown-item" data-value="inbound">Inbound (no reference / manual)</div>
        </div>
    </div>
    <select id="scanMode" style="display:none;">
        <option value="grr">Goods Receipt (against a PO)</option>
        <option value="outbound">Outbound Scan (against a Sales Order)</option>
        <option value="inbound">Inbound (no reference / manual)</option>
    </select>

    <div id="grrPoPicker" class="mb-2">
    <label class="fw-bold small mb-1 d-block">SELECT PURCHASE ORDER</label>
    <div class="m-custom-dropdown" id="poDropdownWrap">
        <button type="button" class="m-dropdown-toggle" id="poDropdownToggle">
            <span id="poDropdownLabel">Choose a PO...</span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="m-dropdown-menu" id="poDropdownMenu">
            <div class="m-dropdown-item" data-value="">Choose a PO...</div>
            <?php foreach($open_pos as $po): ?>
                <div class="m-dropdown-item" data-value="<?= $po['po_id'] ?>"><?= esc($po['po_number']) ?> — <?= esc($po['supplier_name']) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <select id="poSelect" style="display:none;">
        <option value="">Choose a PO...</option>
        <?php foreach($open_pos as $po): ?>
            <option value="<?= $po['po_id'] ?>"><?= esc($po['po_number']) ?> — <?= esc($po['supplier_name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div id="outboundSoPicker" class="mb-2" style="display:none;">
    <label class="fw-bold small mb-1 d-block">SELECT SALES ORDER</label>
    <div class="m-custom-dropdown" id="soDropdownWrap">
        <button type="button" class="m-dropdown-toggle" id="soDropdownToggle">
            <span id="soDropdownLabel">Choose a Sales Order...</span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="m-dropdown-menu" id="soDropdownMenu">
            <div class="m-dropdown-item" data-value="" data-fulfillment="">Choose a Sales Order...</div>
            <?php foreach($open_sos as $so): ?>
                <div class="m-dropdown-item" data-value="<?= $so['order_id'] ?>" data-fulfillment="<?= esc($so['fulfillment_type']) ?>"><?= esc($so['order_number']) ?> — <?= esc($so['client_name']) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <select id="soSelect" style="display:none;">
        <option value="">Choose a Sales Order...</option>
        <?php foreach($open_sos as $so): ?>
            <option value="<?= $so['order_id'] ?>" data-fulfillment="<?= esc($so['fulfillment_type']) ?>"><?= esc($so['order_number']) ?> — <?= esc($so['client_name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>

<!-- Order items table — appears immediately once a PO/SO is picked -->
<div id="orderItemsCard" class="m-card" style="display:none;">
    <p class="fw-bold mb-2" style="font-size:12px;">Order Items</p>
    <div id="orderItemsTable"></div>
</div>

<!-- ============ CAMERA UI — now matches customer scan's layout exactly:
     ZXing video feed, scan-frame overlay, and a flashlight/torch button ============ -->
<div class="m-card text-center">
    <div id="cameraWrap" style="display:none; margin-bottom:12px; position:relative;">
        <video id="scanVideo" style="width:100%; border-radius:12px; background:#000;" playsinline></video>
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:70%; height:35%; border:3px solid #fff; border-radius:8px; box-shadow:0 0 0 999px rgba(0,0,0,0.35); pointer-events:none;"></div>
        <button id="btnToggleTorch" type="button" class="btn btn-sm" style="position:absolute; bottom:10px; right:10px; background:rgba(255,255,255,0.9); display:none;"><i class="fas fa-bolt"></i></button>
    </div>
    <div id="scanPlaceholder" class="py-5" style="border:2px dashed #ddd; border-radius:12px; margin-bottom:12px;">
        <i class="fas fa-camera fs-1 text-muted mb-2 d-block"></i>
        <small class="text-muted">Point camera at product barcode</small>
    </div>
    <div id="scanStatus"></div>
    <p id="scanHint" class="text-muted mb-2" style="font-size:9px; display:none;">Tip: hold steady, about 6-8 inches from the barcode</p>

    <button id="btnToggleCamera" class="btn w-100 mb-2 text-white" style="background:#7b1113;">
        <i class="fas fa-camera me-1"></i> Enable Camera
    </button>

    <label class="fw-bold small mb-1 d-block">OR ENTER BARCODE MANUALLY</label>
    <div class="d-flex gap-2">
        <input type="text" id="barcodeInput" class="form-control" placeholder="Type or paste barcode...">
        <button id="btnLookup" class="btn text-white" style="background:#1d3557;">Go</button>
    </div>
</div>

<div id="scanResult"></div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://unpkg.com/@zxing/library@0.20.0"></script>

<script>
function wireCustomDropdown(wrapId, toggleId, menuId, labelId, selectId) {
    const wrap = document.getElementById(wrapId);
    const toggle = document.getElementById(toggleId);
    const menu = document.getElementById(menuId);
    const label = document.getElementById(labelId);
    const select = document.getElementById(selectId);

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelectorAll('.m-dropdown-menu.open').forEach(m => { if (m !== menu) m.classList.remove('open'); });
        menu.classList.toggle('open');
    });

    menu.querySelectorAll('.m-dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            label.textContent = this.textContent;
            select.value = value;

            menu.querySelectorAll('.m-dropdown-item').forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            menu.classList.remove('open');

            select.dispatchEvent(new Event('change'));
        });
    });

    document.addEventListener('click', function() {
        menu.classList.remove('open');
    });
}

document.addEventListener("DOMContentLoaded", function() {
    wireCustomDropdown('modeDropdownWrap', 'modeDropdownToggle', 'modeDropdownMenu', 'modeDropdownLabel', 'scanMode');
    wireCustomDropdown('poDropdownWrap', 'poDropdownToggle', 'poDropdownMenu', 'poDropdownLabel', 'poSelect');
    wireCustomDropdown('soDropdownWrap', 'soDropdownToggle', 'soDropdownMenu', 'soDropdownLabel', 'soSelect');
});
</script>

<script src="<?= base_url('public/js/mobile/staff_scan.js') ?>"></script>
<?= $this->endSection() ?>