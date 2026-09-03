<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Goods Receipt</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2"></i>Goods Receipt Recording (GRR)</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Verify delivered items against the PO · Flag discrepancies · Updates inventory automatically</p>
            </div>

            <div class="custom-table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0" style="font-size:13px;"><i class="fas fa-clipboard-check me-2 text-maroon"></i>Pending Inbound Shipments</h6>
                    <form id="filterForm" action="" method="GET">
                        <input type="text" name="search" id="liveSearch" class="form-control form-control-sm rounded-pill" placeholder="Search PO # or supplier..." style="width:220px;" value="<?= esc($search) ?>">
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">PO Number</th><th>Supplier</th><th>Expected Delivery</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($deliveries)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">No pending deliveries found for inspection.</td></tr>
                            <?php else: foreach($deliveries as $d): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= esc($d['po_number']) ?></td>
                                <td><?= esc($d['supplier']) ?></td>
                                <td><?= $d['expected_date'] ? date('M d, Y', strtotime($d['expected_date'])) : '—' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-dark rounded-pill px-4 btn-begin-inspection" data-id="<?= $d['po_id'] ?>">Receive Delivery</button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $q = '&search='.urlencode($search);
                    $w=3; $cb=(int)ceil($current_page/$w); $ws=(($cb-1)*$w)+1; $we=min($ws+$w-1,$total_pages);
                ?>
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted fw-bold" style="font-size:10px;">Page <?= $current_page ?> of <?= $total_pages ?></span>
                    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
                        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php for($i=$ws;$i<=$we;$i++): ?><li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li><?php endfor; ?>
                        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="grrDrawer" style="width: 740px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0">Inspect Delivery</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="grrContent"></div>
</div>

<script src="<?= base_url('public/js/staff/operations/goods_receipt.js') ?>"></script>
<?= view('partials/staff/footer') ?>