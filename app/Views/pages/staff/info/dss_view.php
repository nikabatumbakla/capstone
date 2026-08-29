<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 12px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">DSS View</h5>

            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-boxes me-2"></i> Predictive Analysis — View Only</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Staff can view DSS insights but cannot modify recommendations</p>
            </div>

            <div class="custom-table-container">
                <h6 class="fw-bold mb-4" style="font-size:13px"><i class="fas fa-chart-line me-2 text-maroon"></i>Critical Restock Intelligence</h6>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Medical Product</th>
                                <th>Category</th>
                                <th class="text-center">Current Stock</th>
                                <th class="text-primary">Reorder Point</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recommendations as $r): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold d-block"><?= $r['name'] ?></span>
                                    <small class="text-muted"><?= $r['sku'] ?></small>
                                </td>
                                <td><?= $r['cat_name'] ?></td>
                                <td class="text-center fw-bold text-danger"><?= $r['quantity_avail'] ?></td>
                                <td class="fw-bold text-primary"><?= $r['reorder_level'] ?> units</td>
                                <td><span class="badge bg-soft-maroon text-maroon px-3">RESTOCK NOW</span></td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-dark rounded-pill px-3">Flag for Admin</button>
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
<?= view('partials/staff/footer') ?>