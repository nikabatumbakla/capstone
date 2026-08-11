<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </button>
                    <h5 class="fw-bold mb-0">Purchase Order</h5>
                    <a href="<?= base_url('admin/procurement/run-predictive') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-robot me-1"></i> Run Auto-Reorder (AI)
                </a>
                </div>
            </div>

             <div class="procurement-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i> Purchase Order Management</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">AI Approval Workflow • Inbound Tracking</p>
            </div>
            
            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="bg-light p-1 rounded-pill d-inline-flex border">
                        <a href="<?= base_url('admin/procurement/purchase-orders') ?>" class="btn btn-xs rounded-pill px-3 <?= !isset($_GET['status']) ? 'btn-dark' : 'text-muted' ?>">All POs</a>
                        <a href="?status=pending_approval" class="btn btn-xs rounded-pill px-3 <?= @$_GET['status'] == 'pending_approval' ? 'btn-dark' : 'text-muted' ?>">Pending (<?= $count_pending ?>)</a>
                        <a href="?status=sent" class="btn btn-xs rounded-pill px-3 <?= @$_GET['status'] == 'sent' ? 'btn-dark' : 'text-muted' ?>">Sent</a>
                        <a href="?status=received" class="btn btn-xs rounded-pill px-3 <?= @$_GET['status'] == 'received' ? 'btn-dark' : 'text-muted' ?>">Received</a>
                    </div>
                    <button class="btn btn-sm btn-maroon rounded-pill px-4 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#createPODrawer">+ Create PO</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 10px;">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">PO #</th><th>Supplier</th><th>Items</th><th>Total</th><th>Status</th><th>Type</th><th>Expected</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pos as $po): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= $po['po_number'] ?></td>
                                <td><?= $po['supplier_name'] ?></td>
                                <td><?= $po['item_count'] ?> items</td>
                                <td class="fw-bold text-maroon">₱<?= number_format($po['total_amount'], 2) ?></td>
                                <td><span class="badge rounded-pill bg-light text-dark border"><?= ucwords($po['status']) ?></span></td>
                                <td><?= $po['is_auto_generated'] ? '<span class="text-primary small fw-bold">AUTO</span>' : 'Manual' ?></td>
                                <td><?= date('M d', strtotime($po['expected_date'])) ?></td>
                                <td class="text-center">
                                    <?php if($po['status'] == 'pending_approval'): ?>
                                        <a href="<?= base_url('admin/procurement/approve-po/'.$po['po_id']) ?>" class="btn btn-xs btn-success px-2 py-1">Approve</a>
                                    <?php endif; ?>
                                    <button class="btn btn-xs btn-dark px-2 py-1 btn-view-po" data-id="<?= $po['po_id'] ?>">View</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Functional Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <?php 
                        $start = ($current_page - 1) * $per_page + 1;
                        $end = min($current_page * $per_page, $total_rows);
                    ?>
                    <span class="text-muted fw-bold" style="font-size: 10px;">Showing <?= $total_rows > 0 ? "$start-$end" : "0" ?> of <?= $total_rows ?> Entries</span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $current_page-1 ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i=1; $i<=$total_pages; $i++): ?>
                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $current_page+1 ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CREATE PO DRAWER -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="createPODrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="fw-bold mb-0">Create Purchase Order</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4" style="font-size: 11px;">
        <form action="<?= base_url('admin/procurement/save-po') ?>" method="POST">
            <div class="mb-4">
                <p class="text-maroon fw-bold mb-3 border-bottom pb-1">Purchase Order Details</p>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="formal-label">Supplier</label>
                        <select name="supplier_id" class="form-select formal-input">
                            <?php foreach($suppliers as $s): ?><option value="<?= $s['supplier_id'] ?>"><?= $s['name'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="formal-label">Expected Delivery</label>
                        <input type="date" name="expected_date" class="formal-input" required>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-maroon fw-bold mb-3 border-bottom pb-1">Products to Order</p>
                <div id="product-rows-container">
                    <!-- Row 1 -->
                    <div class="row g-2 mb-2 product-row">
                        <div class="col-6">
                            <label class="info-label">Select Product</label>
                            <select name="products[]" class="form-select form-select-sm">
                                <?php foreach($categories as $cat): ?>
                                    <optgroup label="<?= $cat['name'] ?>">
                                        <?php foreach($products as $p): if($p['category_id'] == $cat['category_id']): ?>
                                            <option value="<?= $p['product_id'] ?>"><?= $p['name'] ?></option>
                                        <?php endif; endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-3"><label class="info-label">Quantity</label><input type="number" name="qtys[]" class="form-control form-control-sm" value="1"></div>
                        <div class="col-3"><label class="info-label">Unit Cost</label><input type="number" step="0.01" name="costs[]" class="form-control form-control-sm" placeholder="0.00"></div>
                    </div>
                </div>
                <button type="button" id="btnAddRow" class="btn btn-xs btn-outline-dark mt-2">+ Add Item</button>
            </div>

            <button type="submit" class="btn btn-maroon w-100 py-3 mt-4 fw-bold">✓ SUBMIT PURCHASE ORDER</button>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="poViewDrawer" style="width: 500px;;"><div class="offcanvas-body" id="poViewContent"></div></div>

<script src="<?= base_url('public/js/admin/operations/procurement/procurement_po.js') ?>"></script>
<?= view('partials/admin/footer') ?>