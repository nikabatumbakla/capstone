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
                <h5 class="fw-bold mb-0">PO Inbox</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>Purchase Order Inbox</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">All POs sent by Robin Rose Trading to you</p>
            </div>

            <!-- FIGMA TABS -->
            <div class="bg-light p-1 rounded-pill d-inline-flex mb-4 border w-100">
                <a href="?tab=open" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_tab == 'open') ? 'btn-white shadow-sm fw-bold' : 'text-muted' ?>">Open POs</a>
                <a href="?tab=history" class="btn btn-sm rounded-pill flex-grow-1 px-4 <?= ($active_tab == 'history') ? 'btn-white shadow-sm fw-bold' : 'text-muted' ?>">History</a>
            </div>

            <div class="custom-table-container border-0 shadow-sm" style="border-radius:20px; padding:25px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0"><i class="fas fa-file-invoice me-2 text-maroon"></i> <?= ($active_tab == 'open') ? 'Active Orders' : 'Order History' ?></h6>
                    <select class="form-select form-select-sm rounded border" style="width:120px; font-size:10px;"><option>All</option></select>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:10.5px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">PO #</th>
                                <th>Items Qty</th>
                                <th>Total Amount</th>
                                <th>Expected Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($pos)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No purchase orders found.</td></tr>
                            <?php endif; ?>

                            <?php foreach($pos as $po): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= $po['po_number'] ?></td>
                                <td><?= $po['total_qty'] ?> units</td>
                                <td class="fw-bold">₱ <?= number_format($po['total_amount'], 2) ?></td>
                                <td><?= date('M d', strtotime($po['expected_date'])) ?></td>
                                <td>
                                    <?php 
                                        $badge = ($po['status'] == 'sent') ? 'bg-warning text-dark' : 'bg-success';
                                        $text = ($po['status'] == 'sent') ? 'Needs ACK' : 'Approved';
                                        if($po['status'] == 'received') { $badge = 'bg-light text-success border'; $text = 'Delivered'; }
                                    ?>
                                    <span class="badge rounded-pill <?= $badge ?> px-3"><?= $text ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if($po['status'] == 'sent'): ?>
                                        <form action="<?= base_url('supplier/orders/acknowledge/'.$po['po_id']) ?>" method="POST" style="display:inline;">
                                            <button type="submit" class="btn btn-xs btn-success rounded-pill px-3">Acknowledge</button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn btn-xs btn-outline-dark rounded-pill px-3 ms-1">View</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Figma Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted small">Showing Intelligence 1-<?= count($pos) ?> of <?= count($pos) ?> records</span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager"><li class="page-item active"><a class="page-link" href="#">1</a></li></ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>