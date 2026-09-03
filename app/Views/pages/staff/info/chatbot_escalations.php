<?= view('partials/staff/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>
<style>
    .chat-container { height: 380px; overflow-y: auto; background: #f0f2f5; padding: 20px; display: flex; flex-direction: column; }
    .chat-bubble { max-width: 80%; padding: 10px 15px; border-radius: 20px; margin-bottom: 10px; font-size: 12px; line-height: 1.4; }
    .chat-left { background: #fff; color: #000; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .chat-right { background: #0084ff; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
    .chat-staff { background: #7b1113; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
</style>

<div class="wrapper">
    <?= view('partials/staff/sidebar') ?>
    <div id="content">
        <?= view('partials/staff/header') ?>

        <div class="container-fluid p-4" style="font-size: 12px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">Customer Support Queue</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-headset me-2"></i>Chatbot Escalations</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Conversations the bot couldn't handle — respond as a live agent</p>
            </div>

            <div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="?status=open" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">AWAITING RESPONSE</small>
                <h3 class="fw-bold mb-0 text-danger"><?= $count_open ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="?status=in_progress" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">IN PROGRESS</small>
                <h3 class="fw-bold mb-0 text-warning"><?= $count_in_progress ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="?status=resolved" class="text-decoration-none kpi-filter-link">
            <div class="inventory-kpi-card position-relative">
                <i class="fas fa-filter position-absolute text-muted kpi-filter-icon" style="top:10px; right:12px; font-size:10px;"></i>
                <small class="text-muted fw-bold d-block mb-1">RESOLVED</small>
                <h3 class="fw-bold mb-0 text-success"><?= $count_resolved ?></h3>
                <small class="text-muted kpi-hint" style="font-size:9px;">Click to view</small>
            </div>
        </a>
    </div>
</div>

<?php if ($status_filter): ?>
<div class="alert alert-light border d-flex justify-content-between align-items-center mb-3" style="font-size: 11px;">
    <span><strong>
        <?php
            $labels = ['open' => 'Awaiting Response', 'in_progress' => 'In Progress', 'resolved' => 'Resolved'];
            echo $labels[$status_filter] ?? strtoupper($status_filter);
        ?>
    </strong></span>
    <a href="?" class="text-danger fw-bold text-decoration-none"> ×</a>
</div>
<?php endif; ?>

            <div class="custom-table-container">
    <h6 class="fw-bold mb-4" style="font-size:13px;"><i class="fas fa-comments me-2 text-maroon"></i>Escalated Conversations</h6>


                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark"><tr><th class="ps-4">Request ID</th><th>From</th><th>Query</th><th>Timestamp</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($escalations)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No escalations in this status.</td></tr>
                            <?php else: foreach($escalations as $e): ?>
                            <tr>
                                <td class="ps-4">#ESC-<?= $e['escalation_id'] ?></td>
                                <td class="fw-bold"><?= esc($e['customer_name'] ?: 'Guest') ?> <small class="text-muted">(<?= esc($e['customer_role'] ?? 'guest') ?>)</small></td>
                                <td><small class="text-muted"><?= esc(substr($e['query_text'], 0, 40)) ?>...</small></td>
                                <td><?= date('M d, h:i A', strtotime($e['created_at'])) ?></td>
                                <td><span class="badge <?= $e['status']=='open'?'bg-danger':($e['status']=='in_progress'?'bg-warning text-dark':'bg-success') ?> px-3"><?= strtoupper(str_replace('_',' ',$e['status'])) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-dark rounded-pill px-4 btn-join-chat" data-id="<?= $e['escalation_id'] ?>">
                                        <?= $e['status']=='resolved' ? 'View' : 'Respond' ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php
    $q = '&status='.$status_filter;
    $w=3; $cb=(int)ceil($current_page/$w); $ws=(($cb-1)*$w)+1; $we=min($ws+$w-1,$total_pages);
?>
<?php if($total_pages > 1): ?>
<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
    <span class="text-muted fw-bold" style="font-size:10px;">Page <?= $current_page ?> of <?= $total_pages ?></span>
    <nav><ul class="pagination pagination-sm mb-0 custom-pager">
        <li class="page-item <?= $current_page<=1?'disabled':'' ?>"><a class="page-link" href="?page=<?= max(1,$current_page-1).$q ?>"><i class="fas fa-chevron-left"></i></a></li>
        <?php for($i=$ws;$i<=$we;$i++): ?><li class="page-item <?= $i==$current_page?'active':'' ?>"><a class="page-link" href="?page=<?= $i.$q ?>"><?= $i ?></a></li><?php endfor; ?>
        <li class="page-item <?= $current_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?page=<?= min($total_pages,$current_page+1).$q ?>"><i class="fas fa-chevron-right"></i></a></li>
    </ul></nav>
</div>
<?php endif; ?>

            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="chatDrawer" style="width: 420px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold"><i class="fab fa-facebook-messenger me-2"></i>Support: <span id="chatUser"></span></h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <div class="chat-container flex-grow-1" id="chatThread"></div>
        <div class="p-3 border-top bg-white">
            <div class="d-flex justify-content-end mb-2">
                <button class="btn btn-xs btn-outline-success rounded-pill px-3" id="btnResolveEscalation"><i class="fas fa-check me-1"></i>Mark Resolved</button>
            </div>
            <form id="replyForm" class="input-group">
                <input type="hidden" id="chatEscalationId">
                <input type="text" id="replyMessage" class="form-control border-0 bg-light rounded-pill px-3" placeholder="Type your response...">
                <button type="submit" class="btn btn-primary rounded-circle ms-2" style="width:40px; height:40px;"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('public/js/staff/info/support_queue.js') ?>"></script>
<?= view('partials/staff/footer') ?>