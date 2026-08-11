<?= view('partials/admin/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">
<script>const BASE_URL = "<?= base_url() ?>";</script>

<style>
    /* Messenger Style Chat Bubbles */
    .chat-container { height: 450px; overflow-y: auto; background: #f0f2f5; padding: 20px; display: flex; flex-direction: column; }
    .chat-bubble { max-width: 80%; padding: 10px 15px; border-radius: 20px; margin-bottom: 10px; font-size: 12px; line-height: 1.4; position: relative; }
    .chat-left { background: #fff; color: #000; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .chat-right { background: #0084ff; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
    .chat-meta { font-size: 8px; color: #888; margin-bottom: 15px; text-align: center; text-transform: uppercase; }
</style>

<div class="wrapper">
    <?= view('partials/admin/sidebar') ?>
    <div id="content">
        <?= view('partials/admin/header') ?>

        
<div class="container-fluid p-4">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">Bulletin Board</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2"></i>Bulletin Board</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Internal Announcements · Public Posts</p>
            </div>

      

            <!-- KPI Tiles -->
            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius:20px"><small class="info-label">TOTAL BOT QUERIES</small><h4 class="fw-bold"><?= $count_queries ?></h4></div></div>
                <div class="col-md-4"><div class="stat-card shadow-sm p-3 bg-white border-0" style="border-radius:20px"><small class="info-label">PENDING ESCALATIONS</small><h4 class="fw-bold text-danger"><?= $count_escalations ?></h4></div></div>
                <div class="col-md-4"><div class="stat-card shadow-sm p-3 bg-dark text-white border-0" style="border-radius:20px"><small class="info-label text-white-50">ENGINE STATUS</small><h4 class="fw-bold text-success">ACTIVE</h4></div></div>
            </div>

            <div class="custom-table-container">
                <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill border" id="botTabs">
                    <li class="nav-item flex-grow-1"><button class="nav-link active rounded-pill w-100 small fw-bold" data-bs-toggle="pill" data-bs-target="#tab-intents">Response Intelligence (Intents)</button></li>
                    <li class="nav-item flex-grow-1"><button class="nav-link rounded-pill w-100 small fw-bold" data-bs-toggle="pill" data-bs-target="#tab-escalations">Staff Fallback Queue</button></li>
                </ul>

                <div class="tab-content">
                    <!-- 1. INTENTS TABLE -->
                    <div class="tab-pane fade show active" id="tab-intents">
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <h6 class="fw-bold mb-0">Keyword Mapping</h6>
                            <button class="btn btn-sm btn-maroon rounded-pill px-4" data-bs-toggle="offcanvas" data-bs-target="#intentDrawer" id="btnAddNewIntent">+ Add Intent</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr><th class="ps-4">Intent Name</th><th>Trigger Keywords</th><th>Response Preview</th><th>Status</th><th class="text-center">Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($intents as $i): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= $i['intent_name'] ?></td>
                                        <td><code><?= $i['keywords'] ?></code></td>
                                        <td><small class="text-muted"><?= substr($i['response_template'], 0, 40) ?>...</small></td>
                                        <td><span class="badge rounded-pill <?= $i['is_active'] ? 'bg-success' : 'bg-secondary' ?> px-3"><?= $i['is_active'] ? 'ACTIVE' : 'DISABLED' ?></span></td>
                                        <td class="text-center">
                                            <button class="btn btn-xs btn-outline-secondary rounded-pill btn-edit-intent" data-id="<?= $i['intent_id'] ?>"><i class="fas fa-edit"></i></button>
                                            <a href="<?= base_url('admin/management/chatbot/intent/delete/'.$i['intent_id']) ?>" class="btn btn-xs btn-outline-danger rounded-pill ms-1" onclick="return confirm('Delete this bot logic?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. ESCALATIONS TABLE -->
                    <div class="tab-pane fade" id="tab-escalations">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr><th class="ps-4">Request ID</th><th>User Query</th><th>Timestamp</th><th>Status</th><th class="text-center">Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($escalations as $e): ?>
                                    <tr>
                                        <td class="ps-4">#ESC-<?= $e['escalation_id'] ?></td>
                                        <td class="fw-bold"><?= $e['customer_name'] ?: 'Guest User' ?></td>
                                        <td><?= date('M d, h:i A', strtotime($e['created_at'])) ?></td>
                                        <td><span class="badge bg-danger px-3">AWAITING STAFF</span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-dark rounded-pill px-4 btn-join-chat" data-id="<?= $e['escalation_id'] ?>">Join Chat</button>
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
    </div>
</div>

<!-- LIVE CHAT DRAWER (MESSENGER STYLE) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="chatDrawer" style="width: 450px; border-left: 8px solid #0084ff;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold"><i class="fab fa-facebook-messenger me-2"></i>Live Support: <span id="chatUser"></span></h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <div class="chat-container flex-grow-1" id="chatThread">
            <!-- Bubbles injected by JS -->
        </div>
        <div class="p-3 border-top bg-white">
            <div class="input-group">
                <input type="text" class="form-control border-0 bg-light rounded-pill px-3" placeholder="Type a response as Staff...">
                <button class="btn btn-primary rounded-circle ms-2" style="width:40px; height:40px;"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- SLIDING DRAWER: INTENT FORM -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="intentDrawer" style="width: 500px; border-left: 8px solid #1a0505;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="fw-bold mb-0" id="drawerTitle">Bot Intent Specification</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="<?= base_url('admin/management/chatbot/intent/save') ?>" method="POST" id="intentForm">
            <input type="hidden" name="intent_id" id="form_intent_id">
            <div class="mb-3"><label class="formal-label">Intent Name (Category)</label><input type="text" name="name" id="form_name" class="formal-input" required></div>
            <div class="mb-3"><label class="formal-label">Trigger Keywords (Comma Separated) *</label><textarea name="keywords" id="form_keywords" class="formal-input" rows="3" required placeholder="price, cost, magkano"></textarea></div>
            <div class="mb-3"><label class="formal-label">Automated Bot Response *</label><textarea name="response" id="form_response" class="formal-input" rows="5" required></textarea></div>
            <div class="p-3 bg-light rounded-4 mb-4 border"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" id="form_active" checked><label class="small fw-bold">Enable this Intent</label></div></div>
            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow" id="btnSubmitIntent">✓ SAVE BOT LOGIC</button>
        </form>
    </div>
</div>

<script src="<?= base_url('public/js/admin/chatbot.js') ?>"></script>
<?= view('partials/admin/footer') ?>