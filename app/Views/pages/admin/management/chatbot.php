<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<style>
    .chat-container { height: 420px; overflow-y: auto; background: #f0f2f5; padding: 20px; display: flex; flex-direction: column; }
    .chat-bubble { max-width: 80%; padding: 10px 15px; border-radius: 20px; margin-bottom: 10px; font-size: 12px; line-height: 1.4; }
    .chat-left { background: #fff; color: #000; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .chat-right { background: #0084ff; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
    .chat-staff { background: #7b1113; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
    .chat-meta { font-size: 8px; color: #888; margin-bottom: 15px; text-align: center; text-transform: uppercase; }
    .esc-status-btn { background: #fff; border: 1px solid #dee2e6; color: #6c757d; }
    .esc-status-btn.active { background: #1a0505; border-color: #1a0505; color: #fff; }
</style>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        <div class="container-fluid p-4" style="font-size:11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">ChatBot Management</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-robot me-2"></i>ChatBot Intelligence</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Automated Response Rules · Staff Escalation Queue</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">TOTAL BOT QUERIES</small><h3 class="fw-bold mb-0"><?= $count_queries ?></h3></div></div>
                <div class="col-md-4"><div class="inventory-kpi-card"><small class="text-muted fw-bold d-block mb-1">PENDING ESCALATIONS</small><h3 class="fw-bold mb-0 text-danger"><?= $count_escalations ?></h3></div></div>
                <div class="col-md-4"><div class="inventory-kpi-card" style="background:#212529; color:#fff;"><small class="d-block mb-1" style="color:#aaa;">ENGINE STATUS</small><h3 class="fw-bold mb-0 text-success">ACTIVE</h3></div></div>
            </div>

            <div class="custom-table-container">
                <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill border" id="botTabs">
                    <li class="nav-item flex-grow-1"><button class="nav-link active rounded-pill w-100 fw-bold" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-intents">Response Intents</button></li>
                    <li class="nav-item flex-grow-1"><button class="nav-link rounded-pill w-100 fw-bold" style="font-size:11px;" data-bs-toggle="pill" data-bs-target="#tab-escalations">Staff Fallback Queue</button></li>
                </ul>

                <div class="tab-content">
    <div class="tab-pane fade show active" id="tab-intents">
        <div class="d-flex justify-content-between mb-3 align-items-center">
            <h6 class="fw-bold mb-0">Keyword Mapping</h6>
            <div class="d-flex gap-2">
                <input type="text" id="intentSearch" class="form-control form-control-sm rounded-pill" placeholder="Search intents..." style="width:180px;">
                <button class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#intentDrawer" id="btnAddNewIntent"><i class="fas fa-plus me-1"></i>Add Intent</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark"><tr><th class="ps-4">Intent Name</th><th>Trigger Keywords</th><th>Response Preview</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                <tbody id="intentsTableBody"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted" id="intentPageInfo">Loading…</span>
            <nav><ul class="pagination pagination-sm mb-0 custom-pager" id="intentPager"></ul></nav>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-escalations">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <button class="btn btn-sm rounded-pill px-3 esc-status-btn active" data-status="open">Open</button>
                <button class="btn btn-sm rounded-pill px-3 esc-status-btn" data-status="in_progress">In Progress</button>
                <button class="btn btn-sm rounded-pill px-3 esc-status-btn" data-status="resolved">Resolved</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark"><tr><th class="ps-4">Request ID</th><th>Client</th><th>Date Received</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                <tbody id="escalationsTableBody"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted" id="escPageInfo">Loading…</span>
            <nav><ul class="pagination pagination-sm mb-0 custom-pager" id="escPager"></ul></nav>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="chatDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold"><i class="fab fa-facebook-messenger me-2"></i>Live Support: <span id="chatUser"></span></h6>
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
                <input type="text" id="replyMessage" class="form-control border-0 bg-light rounded-pill px-3" placeholder="Type a response as Staff...">
                <button type="submit" class="btn btn-primary rounded-circle ms-2" style="width:40px; height:40px;"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="intentDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0" id="drawerTitle">Bot Intent Specification</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/chatbot/intent/save') ?>" method="POST" id="intentForm">
            <input type="hidden" name="intent_id" id="form_intent_id">
            <div class="mb-3"><label class="formal-label">Intent Name (Category) *</label><input type="text" name="name" id="form_name" class="formal-input" required></div>
            <div class="mb-3"><label class="formal-label">Trigger Keywords (Comma Separated) *</label><textarea name="keywords" id="form_keywords" class="formal-input" rows="3" required placeholder="price, cost, magkano"></textarea></div>
            <div class="mb-3"><label class="formal-label">Automated Bot Response *</label><textarea name="response" id="form_response" class="formal-input" rows="5" required></textarea></div>
            <div class="p-3 bg-light rounded-4 mb-4 border"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" id="form_active" checked><label class="small fw-bold" for="form_active">Enable this Intent</label></div></div>
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow" id="btnSubmitIntent">✓ SAVE BOT LOGIC</button>
        </form>
    </div>
</div>

<script src="<?= base_url('public/js/admin/management/chatbot.js') ?>"></script>
<?= view('partials/admin/footer') ?>