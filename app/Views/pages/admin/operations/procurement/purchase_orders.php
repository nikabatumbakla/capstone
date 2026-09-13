<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">

        <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Purchase Orders</h5>
            </div>
     
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Purchase Order Management</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Auto-Reorder Recommendations • Approval Workflow • Inbound Tracking</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small" id="flashError"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" id="flashSuccess"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="inventory-kpi-card">
            <small class="text-muted fw-bold d-block mb-1">POs THIS MONTH</small>
            <h3 class="fw-bold mb-0"><?= $po_this_month ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <a href="?status=pending_approval" class="text-decoration-none">
            <div class="inventory-kpi-card <?= $status_filter == 'pending_approval' ? 'border-bottom border-3 border-warning' : '' ?>">
                <small class="text-muted fw-bold d-block mb-1">PENDING APPROVAL</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $count_pending ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="inventory-kpi-card">
            <small class="text-muted fw-bold d-block mb-1">AUTO-REORDERS THIS MONTH</small>
            <h3 class="fw-bold mb-0 text-primary"><?= $auto_reorders_this_month ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="inventory-kpi-card">
            <small class="text-muted fw-bold d-block mb-1">SPEND THIS MONTH</small>
            <h3 class="fw-bold mb-0 text-maroon">₱<?= number_format($spend_this_month, 2) ?></h3>
        </div>
    </div>
</div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="bg-light p-1 rounded-pill d-inline-flex border">
                        <a href="<?= base_url('admin/procurement/purchase-orders') ?>" class="btn btn-xs rounded-pill px-3 <?= !$status_filter ? 'btn-dark' : 'text-muted' ?>">All POs</a>
                        <a href="?status=pending_approval" class="btn btn-xs rounded-pill px-3 <?= $status_filter == 'pending_approval' ? 'btn-dark' : 'text-muted' ?>">Pending (<?= $count_pending ?>)</a>
                        <a href="?status=sent" class="btn btn-xs rounded-pill px-3 <?= $status_filter == 'sent' ? 'btn-dark' : 'text-muted' ?>">Sent to Supplier</a>
                        <a href="?status=received" class="btn btn-xs rounded-pill px-3 <?= $status_filter == 'received' ? 'btn-dark' : 'text-muted' ?>">Received</a>
                    </div>
                    <button class="btn btn-sm btn-outline-dark rounded-pill px-4 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#walkinPODrawer">
    <i class="fas fa-user-plus me-2"></i>Walk-in / No-Account PO
</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 10px;">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">PO #</th><th>Supplier</th><th>Items</th><th>Total</th><th>Status</th><th>Payment</th><th>Origin</th><th>Expected</th><th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $statusLabels = [
                                    'draft'            => 'Draft',
                                    'pending_approval' => 'Pending Approval',
                                    'approved'         => 'Approved',
                                    'sent'             => 'Sent to Supplier',
                                    'acknowledged'     => 'Acknowledged',
                                    'in_transit'       => 'In Transit',
                                    'partial'          => 'Partially Received',
                                    'received'         => 'Received',
                                    'cancelled'        => 'Cancelled',
                                ];
                                $statusIcons = [
                                    'draft' => 'fa-file', 'pending_approval' => 'fa-hourglass-half', 'approved' => 'fa-thumbs-up',
                                    'sent' => 'fa-paper-plane', 'acknowledged' => 'fa-clipboard-check', 'in_transit' => 'fa-shipping-fast',
                                    'partial' => 'fa-truck-loading', 'received' => 'fa-check-circle', 'cancelled' => 'fa-ban',
                                ];
                            ?>
                            <?php foreach($pos as $po): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= $po['po_number'] ?></td>
                                <td><?= $po['supplier_name'] ?></td>
                                <td><?= $po['item_count'] ?> items</td>
                                <td class="fw-bold text-maroon">₱<?= number_format($po['total_amount'], 2) ?></td>
                                <td><span class="badge rounded-pill bg-light text-dark border"><i class="fas <?= $statusIcons[$po['status']] ?? 'fa-circle' ?> me-1"></i><?= $statusLabels[$po['status']] ?? ucwords($po['status']) ?></span></td>
                                <td>
                                    <?php if(($po['payment_status'] ?? 'unpaid') === 'paid'): ?>
                                        <span class="badge bg-success">PAID</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">UNPAID</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(!empty($po['replacement_for_return_id'])): ?>
                                        <i class="fas fa-exchange-alt text-info" title="Replacement for Return #<?= str_pad($po['replacement_for_return_id'], 4, '0', STR_PAD_LEFT) ?>"></i>
                                    <?php elseif($po['is_auto_generated']): ?>
                                        <i class="fas fa-robot text-primary" title="System-Generated (Auto-Reorder)"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user-edit text-muted" title="Manual Entry"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= $po['expected_date'] ? date('M d, Y', strtotime($po['expected_date'])) : '—' ?></td>
                                <td class="text-center">
                                    <?php if($po['status'] == 'pending_approval'): ?>
                                        <a href="<?= base_url('admin/procurement/approve-po/'.$po['po_id']) ?>" class="btn btn-xs btn-success px-2 py-1" title="Approve" onclick="return confirm('Approve this purchase order?');">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="<?= base_url('admin/procurement/reject-po/'.$po['po_id']) ?>" class="btn btn-xs btn-danger px-2 py-1" title="Reject" onclick="return confirm('Reject and cancel this purchase order?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if(($po['payment_status'] ?? 'unpaid') === 'unpaid' && in_array($po['status'], ['acknowledged', 'in_transit', 'partial', 'received'])): ?>
                                        <button class="btn btn-xs btn-outline-success px-2 py-1 btn-mark-paid" title="Mark as Paid"
                                            data-id="<?= $po['po_id'] ?>" data-no="<?= esc($po['po_number']) ?>"
                                            data-supplier="<?= esc($po['supplier_name']) ?>" data-amount="₱<?= number_format($po['total_amount'], 2) ?>">
                                            <i class="fas fa-money-check-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-xs btn-dark px-2 py-1 btn-view-po" data-id="<?= $po['po_id'] ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($pos)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No purchase orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $rangeStart = $total_rows > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
                    $rangeEnd   = min($current_page * $per_page, $total_rows);
                    $statusQuery = $status_filter ? '&status=' . $status_filter : '';

                    $windowSize   = 3;
                    $currentBlock = (int) ceil($current_page / $windowSize);
                    $windowStart  = (($currentBlock - 1) * $windowSize) + 1;
                    $windowEnd    = min($windowStart + $windowSize - 1, $total_pages);
                ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span class="text-muted fw-bold" style="font-size: 10px;">
                        Showing <?= $rangeStart ?>-<?= $rangeEnd ?> of <?= $total_rows ?> Entries
                    </span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 custom-pager">
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= max(1, $current_page - 1) . $statusQuery ?>"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php for ($i = $windowStart; $i <= $windowEnd; $i++): ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i . $statusQuery ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $statusQuery ?>"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="poViewDrawer" style="width: 600px;"><div class="offcanvas-body" id="poViewContent"></div></div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="markPaidDrawer" style="width:400px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Record Payment</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/procurement/mark-paid') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="po_id" id="pay_po_id">
            <div class="mb-3"><label class="formal-label">PO Number</label><input type="text" id="pay_po_no" class="formal-input read-only-input" readonly></div>
            <div class="mb-3"><label class="formal-label">Supplier</label><input type="text" id="pay_supplier_name" class="formal-input read-only-input" readonly></div>
            <div class="mb-3"><label class="formal-label">Amount Due</label><input type="text" id="pay_amount" class="formal-input read-only-input" readonly></div>
            <div class="mb-3">
                <label class="formal-label">Payment Method *</label>
                <select name="payment_method" class="form-select formal-input" required>
                    <option value="" disabled selected>Select method</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                </select>
            </div>
            <div class="mb-4"><label class="formal-label">Reference / Cheque No. *</label><input type="text" name="payment_reference" class="formal-input" required></div>
            <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow">✓ MARK AS PAID</button>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="walkinPODrawer" style="width:850px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="fw-bold mb-0"><i class="fas fa-user-plus me-2"></i>Walk-in / Non-Account Purchase</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <p class="helper-text mb-3">For a supplier who doesn't have (or doesn't want) a Supplier Portal account. This records a completed purchase — items are added to inventory and marked paid immediately, since goods and payment both change hands on the spot.</p>
        <form action="<?= base_url('admin/procurement/save-walkin-po') ?>" method="POST" id="walkinPOForm">
            <?= csrf_field() ?>

            <p class="text-maroon fw-bold small border-bottom pb-1 mb-3">SUPPLIER INFORMATION</p>
            <div class="row g-3 mb-3">
                <div class="col-6"><label class="formal-label">Supplier / Business Name *</label><input type="text" name="guest_name" class="formal-input" required></div>
                <div class="col-6"><label class="formal-label">Contact Person</label><input type="text" name="guest_contact" class="formal-input"></div>
                <div class="col-6"><label class="formal-label">Phone</label><input type="text" name="guest_phone" class="formal-input"></div>
                <div class="col-6"><label class="formal-label">Email</label><input type="email" name="guest_email" class="formal-input"></div>
                <div class="col-6"><label class="formal-label">Address</label><input type="text" name="guest_address" class="formal-input"></div>
                <div class="col-6"><label class="formal-label">TIN</label><input type="text" name="guest_tin" class="formal-input"></div>
            </div>
            <p class="helper-text mb-3">If this supplier has purchased with you before under the same name, their record is reused automatically.</p>

            <div class="mb-3"><label class="formal-label">Notes</label><textarea name="notes" class="formal-input" rows="2"></textarea></div>

            <p class="text-maroon fw-bold small border-bottom pb-1 mb-3">ITEMS RECEIVED</p>
            <div id="walkinRowsContainer"></div>
            <button type="button" id="btnAddWalkinRow" class="btn btn-xs btn-outline-dark mt-2 mb-4">+ Add Item</button>

            <p class="text-maroon fw-bold small border-bottom pb-1 mb-3">PAYMENT</p>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="formal-label">Payment Method *</label>
                    <select name="payment_method" id="walkinPaymentMethod" class="form-select formal-input" required>
                        <option value="cash" selected>Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="col-6" id="walkinPaymentRefWrap" style="display:none;">
                    <label class="formal-label">Reference / Cheque No. *</label>
                    <input type="text" name="payment_reference" id="walkinPaymentRef" class="formal-input">
                </div>
            </div>

            <button type="submit" class="btn btn-maroon w-100 py-3 fw-bold">✓ RECORD COMPLETED PURCHASE</button>
        </form>
    </div>
</div>

<script>
    const WALKIN_CATEGORIES = <?= json_encode($categories) ?>;
    const WALKIN_PRODUCTS = <?= json_encode($walkin_products) ?>;

    document.addEventListener("DOMContentLoaded", function() {
        const rowsContainer = document.getElementById('walkinRowsContainer');

        function buildRow() {
            const catOptions = WALKIN_CATEGORIES.map(c => `<option value="${c.category_id}">${c.name}</option>`).join('');
            return `
                <div class="row g-2 mb-2 walkin-row align-items-end">
                    <div class="col-3">
                        <label class="info-label">Category</label>
                        <select class="form-select form-select-sm walkin-cat-select"><option value="">Select</option>${catOptions}</select>
                    </div>
                    <div class="col-3">
                        <label class="info-label">Product</label>
                        <select name="products[]" class="form-select form-select-sm walkin-product-select" disabled required><option value="">Select category first</option></select>
                    </div>
                    <div class="col-1">
                        <label class="info-label">Qty</label>
                        <input type="number" name="qtys[]" class="form-control form-control-sm" value="1" min="1">
                    </div>
                    <div class="col-1">
                        <label class="info-label">Cost</label>
                        <input type="number" step="0.01" name="costs[]" class="form-control form-control-sm" placeholder="0.00" required>
                    </div>
                    <div class="col-1">
                        <label class="info-label">Sell Price *</label>
                        <input type="number" step="0.01" name="sell_prices[]" class="form-control form-control-sm" placeholder="0.00" required>
                    </div>
                    <div class="col-1">
                        <label class="info-label">Lot No.</label>
                        <input type="text" name="lot_numbers[]" class="form-control form-control-sm" placeholder="Optional">
                    </div>
                    <div class="col-1">
                        <label class="info-label">Expiry</label>
                        <input type="date" name="expires_ats[]" class="form-control form-control-sm">
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-remove-walkin-row"><i class="fas fa-times"></i></button>
                    </div>
                </div>`;
        }

        function wireRow(row) {
            row.querySelector('.walkin-cat-select').addEventListener('change', function() {
                const prodSelect = row.querySelector('.walkin-product-select');
                const matches = WALKIN_PRODUCTS.filter(p => p.category_id == this.value);
                prodSelect.innerHTML = matches.length ?
                    matches.map(p => `<option value="${p.product_id}">${p.name} (${p.barcode_value})</option>`).join('') :
                    `<option value="">No products in this category</option>`;
                prodSelect.disabled = false;
            });
        }

        rowsContainer.insertAdjacentHTML('beforeend', buildRow());
        wireRow(rowsContainer.querySelector('.walkin-row'));

        document.getElementById('btnAddWalkinRow').addEventListener('click', function() {
            rowsContainer.insertAdjacentHTML('beforeend', buildRow());
            wireRow(rowsContainer.lastElementChild);
        });

        rowsContainer.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove-walkin-row');
            if (!btn) return;
            if (rowsContainer.querySelectorAll('.walkin-row').length <= 1) return;
            btn.closest('.walkin-row').remove();
        });

        // Payment method — reference only required for bank transfer / cheque
        const paymentMethodSelect = document.getElementById('walkinPaymentMethod');
        const refWrap = document.getElementById('walkinPaymentRefWrap');
        const refInput = document.getElementById('walkinPaymentRef');

        paymentMethodSelect.addEventListener('change', function() {
            const needsRef = ['bank_transfer', 'cheque'].includes(this.value);
            refWrap.style.display = needsRef ? 'block' : 'none';
            refInput.required = needsRef;
        });
    });
</script>

<script>
  const BASE_URL = "<?= rtrim(base_url(), '/') ?>";
</script>
<script>
    setTimeout(function() {
        ['flashError', 'flashSuccess'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }
        });
    }, 5000);
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const markPaidDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('markPaidDrawer'));
    document.querySelectorAll('.btn-mark-paid').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('pay_po_id').value = this.getAttribute('data-id');
            document.getElementById('pay_po_no').value = this.getAttribute('data-no');
            document.getElementById('pay_supplier_name').value = this.getAttribute('data-supplier');
            document.getElementById('pay_amount').value = this.getAttribute('data-amount');
            markPaidDrawer.show();
        });
    });
});
</script>
<script src="<?= base_url('public/js/admin/operations/procurement/procurement_po.js') ?>"></script>
<?= view('partials/admin/footer') ?>