<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Delivery Updates</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck me-2"></i>Delivery Updates</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Mark acknowledged orders as dispatched with a delivery reference</p>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;">Acknowledged Orders — Ready for Dispatch</h6>
                    <form action="" method="GET">
                        <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search PO #..." style="width:200px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">PO #</th><th>Expected</th><th>Status</th><th>DR Number</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($orders)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No orders ready for dispatch.</td></tr>
                            <?php else: foreach($orders as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= esc($o['po_number']) ?></td>
                                <td><?= $o['expected_date'] ? date('M d, Y', strtotime($o['expected_date'])) : '—' ?></td>
                                <td><span class="badge <?= $o['status']=='in_transit'?'bg-info':'bg-primary' ?> px-3"><?= strtoupper($o['status']) ?></span></td>
                                <td><code><?= esc($o['supplier_dr_number'] ?: '—') ?></code></td>
                                <td class="text-center">
                                    <?php if($o['status'] === 'acknowledged'): ?>
                                        <button class="btn btn-xs btn-success rounded-pill px-3 btn-mark-dispatch" data-id="<?= $o['po_id'] ?>" data-no="<?= esc($o['po_number']) ?>">Mark In-Transit</button>
                                    <?php else: ?>
                                        <span class="text-muted">Dispatched</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-4">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <?php for($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="dispatchDrawer" style="width:450px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Mark as In-Transit</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('supplier/orders/update-delivery') ?>" method="POST">
            <input type="hidden" name="po_id" id="dispatch_po_id">
            <div class="mb-3"><label class="formal-label">PO Number</label><input type="text" id="dispatch_po_no" class="formal-input read-only-input" readonly></div>
            <div class="mb-3"><label class="formal-label">Dispatch Date</label><input type="date" name="dispatch_date" class="formal-input" value="<?= date('Y-m-d') ?>"></div>
            <div class="mb-4"><label class="formal-label">Delivery Reference / DR Number *</label><input type="text" name="dr_number" class="formal-input" placeholder="Courier tracking # or DR #" required></div>
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-pill shadow">✓ CONFIRM DISPATCH</button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const dispatchDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('dispatchDrawer'));
    document.querySelectorAll('.btn-mark-dispatch').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('dispatch_po_id').value = this.getAttribute('data-id');
            document.getElementById('dispatch_po_no').value = this.getAttribute('data-no');
            dispatchDrawer.show();
        });
    });
});
</script>
<?= view('partials/supplier/footer') ?>