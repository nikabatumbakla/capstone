<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-1">
    <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">iScan</h5>
</div>
<p class="text-muted mb-3" style="font-size:11px;">Scan barcodes to manage inventory</p>

<div class="m-card">
    <label class="fw-bold small mb-2 d-block">SELECT MODE</label>
    <select id="scanMode" class="form-select mb-3">
        <option value="grr">Goods Receipt (against a PO)</option>
        <option value="outbound">Outbound Scan (against a Sales Order)</option>
        <option value="inbound">Inbound (no reference / manual)</option>
    </select>

    <div id="grrPoPicker" class="mb-2">
        <label class="fw-bold small mb-1 d-block">SELECT PURCHASE ORDER</label>
        <select id="poSelect" class="form-select">
            <option value="">Choose a PO...</option>
            <?php foreach($open_pos as $po): ?>
                <option value="<?= $po['po_id'] ?>"><?= esc($po['po_number']) ?> — <?= esc($po['supplier_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="outboundSoPicker" class="mb-2" style="display:none;">
        <label class="fw-bold small mb-1 d-block">SELECT SALES ORDER</label>
        <select id="soSelect" class="form-select">
            <option value="">Choose a Sales Order...</option>
            <?php foreach($open_sos as $so): ?>
                <option value="<?= $so['order_id'] ?>"><?= esc($so['order_number']) ?> — <?= esc($so['client_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Order items table — appears immediately once a PO/SO is picked -->
<div id="orderItemsCard" class="m-card" style="display:none;">
    <p class="fw-bold mb-2" style="font-size:12px;">Order Items</p>
    <div id="orderItemsTable"></div>
</div>

<div class="m-card">
    <button id="btnToggleCamera" class="btn w-100 mb-3 text-white" style="background:#1d3557;">
        <i class="fas fa-camera me-1"></i> Start Camera Scan
    </button>
    <div id="cameraWrap" style="display:none; margin-bottom:12px;">
        <div id="camera-reader" style="width:100%; border-radius:12px; overflow:hidden;"></div>
    </div>
    <div id="cameraError"></div>

    <label class="fw-bold small mb-1 d-block">OR ENTER BARCODE MANUALLY</label>
    <div class="d-flex gap-2">
        <input type="text" id="barcodeInput" class="form-control" placeholder="Type or paste barcode...">
        <button id="btnLookup" class="btn text-white" style="background:#7b1113;">Go</button>
    </div>
</div>

<div id="scanResult"></div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    window.CATEGORIES_HTML = `<?php foreach($categories as $c): ?><option value="<?= $c['category_id'] ?>"><?= esc($c['name']) ?></option><?php endforeach; ?>`;
</script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="<?= base_url('public/js/mobile/staff_scan.js') ?>"></script>
<?= $this->endSection() ?>