<?= view('partials/client/head') ?>
<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Delivery Updates</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>Delivery Updates</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Update dispatch status and tracking info</p>
            </div>

            <div class="custom-table-container">
                <h6 class="fw-bold mb-3">Purchase Orders</h6>
                <table class="table table-hover align-middle" style="font-size:10px">
                    <thead class="table-dark"><tr><th class="ps-4">PO #</th><th>Expected</th><th>Current Status</th><th>Tracking Ref</th><th class="text-center">Action</th></tr></thead>
                    <tbody>
                        <?php foreach($orders as $o): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= $o['po_number'] ?></td>
                            <td><?= date('M d', strtotime($o['expected_date'])) ?></td>
                            <td><span class="badge bg-light text-dark border px-3"><?= strtoupper($o['status']) ?></span></td>
                            <td><code><?= $o['notes'] ?: '—' ?></code></td>
                            <td class="text-center"><button class="btn btn-xs btn-success rounded-pill px-3 btn-update-del" data-id="<?= $o['po_id'] ?>" data-no="<?= $o['po_number'] ?>">+ Mark In-Transit</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Update Form (Formal Style) -->
            <div class="custom-table-container mt-4 p-4 border-0 shadow-sm" style="border-radius:20px">
                <h6 class="fw-bold mb-4"><i class="fas fa-edit me-2 text-primary"></i> Update Delivery Details</h6>
                <form action="<?= base_url('supplier/orders/update-delivery') ?>" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="formal-label">PO Number</label><input type="text" id="del_po_no" class="formal-input read-only-input" readonly></div>
                        <div class="col-md-6"><label class="formal-label">Status</label><input type="text" class="formal-input read-only-input" value="Acknowledged" readonly></div>
                        <div class="col-md-6"><label class="formal-label">Dispatch Date</label><input type="date" name="dispatch_date" class="formal-input"></div>
                        <div class="col-md-6"><label class="formal-label">Tracking Reference</label><input type="text" name="tracking_ref" class="formal-input" placeholder="Courier ID / Driver Contact"></div>
                        <input type="hidden" name="po_id" id="del_po_id">
                    </div>
                    <button type="submit" class="btn btn-dark mt-4 px-5 py-2 fw-bold rounded-pill">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-update-del').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('del_po_no').value = this.getAttribute('data-no');
        document.getElementById('del_po_id').value = this.getAttribute('data-id');
    });
});
</script>
<?= view('partials/client/footer') ?>