<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-3">
    <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">iScan Product</h5>
</div>

<div class="m-card text-center">
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
        <input type="text" id="barcodeInput" class="form-control" placeholder="Barcode number...">
        <button id="btnLookup" class="btn text-white" style="background:#1d3557;">Go</button>
    </div>
</div>

<div id="recentScansCard" class="m-card" style="display:none;">
    <p class="fw-bold mb-2" style="font-size:11px;">Recently Scanned</p>
    <div id="recentScansList"></div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://unpkg.com/@zxing/library@0.20.0"></script>
<script src="<?= base_url('public/js/mobile/customer_scan.js') ?>"></script>
<?= $this->endSection() ?>