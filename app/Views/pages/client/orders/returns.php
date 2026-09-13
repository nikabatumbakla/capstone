<?= view('partials/client/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">My Returns</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-undo-alt me-2"></i>Return Requests</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Items flagged during delivery confirmation, and their review status</p>
            </div>

            <div class="custom-table-container">
                <table class="table table-hover align-middle">
                    <thead class="table-dark"><tr><th class="ps-4">Order #</th><th>Product</th><th class="text-center">Qty</th><th>Reason</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if(empty($returns)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No return requests filed.</td></tr>
                        <?php else:
                            $statusMeta = ['pending' => 'bg-warning text-dark', 'approved' => 'bg-success', 'rejected' => 'bg-secondary'];
                            foreach($returns as $r): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= esc($r['order_number']) ?></td>
                            <td><?= esc($r['product_name'] ?? 'N/A') ?><br><small class="text-muted"><?= esc($r['barcode_value'] ?? '—') ?></small></td>
                            <td class="text-center"><?= $r['quantity'] ?></td>
                            <td><?= esc($r['reason']) ?></td>
                            <td><span class="badge <?= $statusMeta[$r['status']] ?? 'bg-secondary' ?> px-3"><?= strtoupper($r['status']) ?></span></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-end mt-4">
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <?php for($i=1;$i<=$total_pages;$i++): ?>
                            <li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>