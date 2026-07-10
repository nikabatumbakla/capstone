<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 mb-4" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>

            <div class="dashboard-banner mb-4 p-3 text-white" style="background: linear-gradient(90deg, #1a2a6c, #2a4858);">
                <h5 class="fw-bold mb-1"><i class="fas fa-file-invoice-dollar me-2"></i> Sales Order Intelligence</h5>
                <p class="small mb-0 opacity-75">Outbound Logistics • Billing • Delivery Status Tracking</p>
            </div>

            <!-- Status Tabs -->
            <div class="bg-light p-1 rounded-pill d-inline-flex mb-4 border">
                <a href="<?= base_url('admin/sales/sales-orders') ?>" class="btn btn-xs rounded-pill px-4 <?= !isset($_GET['status']) ? 'btn-dark shadow-sm' : 'text-muted' ?>">All Orders</a>
                <a href="?status=pending" class="btn btn-xs rounded-pill px-4 <?= @$_GET['status'] == 'pending' ? 'btn-dark shadow-sm' : 'text-muted' ?>">Pending (<?= $count_pending ?>)</a>
                <a href="?status=processing" class="btn btn-xs rounded-pill px-4 <?= @$_GET['status'] == 'processing' ? 'btn-dark shadow-sm' : 'text-muted' ?>">Processing (<?= $count_processing ?>)</a>
                <a href="?status=shipped" class="btn btn-xs rounded-pill px-4 <?= @$_GET['status'] == 'shipped' ? 'btn-dark shadow-sm' : 'text-muted' ?>">Shipped</a>
            </div>

            <div class="custom-table-container">
                <h6 class="fw-bold mb-3"><i class="fas fa-list me-2 text-maroon"></i>Distribution Queue</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:11px">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">SO #</th>
                                <th>Institution</th>
                                <th>Grand Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= $o['order_number'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= $o['client_name'] ?></div>
                                    <small class="text-muted"><?= strtoupper($o['client_type']) ?></small>
                                </td>
                                <td class="fw-bold text-dark">₱<?= number_format($o['total'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= ($o['payment_status'] == 'paid') ? 'success' : 'light text-dark border' ?> px-3">
                                        <?= strtoupper($o['payment_status']) ?>
                                    </span>
                                </td>
                                <td><span class="badge rounded-pill bg-soft-maroon text-maroon px-3"><?= ucwords($o['status']) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-dark rounded-pill px-3 btn-view-so" data-id="<?= $o['order_id'] ?>">Manage Order</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: SO INTELLIGENCE -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="soDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold">Sales Order Intelligence</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="soDrawerContent"></div>
</div>

<script src="<?= base_url('public/js/admin/sales_orders.js') ?>"></script>
<?= view('partials/admin/footer') ?>