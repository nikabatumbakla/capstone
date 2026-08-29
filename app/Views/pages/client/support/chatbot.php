<?= view('partials/client/head') ?>
<style>
    .chat-bubble-client { background: #0d2e4f; color: white; border-radius: 20px 20px 4px 20px; padding: 10px 15px; margin-bottom: 10px; align-self: flex-end; max-width: 80%; font-size: 12px; }
    .chat-bubble-bot { background: #f1f3f4; color: black; border-radius: 20px 20px 20px 4px; padding: 10px 15px; margin-bottom: 10px; align-self: flex-start; max-width: 80%; font-size: 12px; }
</style>
<div class="wrapper">
    <?= view('partials/client/sidebar') ?>
    <div id="content">
        <?= view('partials/client/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </button>
                <h5 class="fw-bold mb-0">ChatBot</h5>
            </div>
           
            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-credit-card me-2"></i>ChatBot Support</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">24/7 automated support · Complex queries forwarded to staff</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="custom-table-container p-0 overflow-hidden shadow-sm border-0" style="border-radius:25px;">
                        <div class="bg-dark p-3 text-white d-flex align-items-center">
                            <i class="fas fa-robot me-3 fs-4"></i>
                            <div><h6 class="fw-bold mb-0">PharBot Intelligence</h6><small class="opacity-50">24/7 Institutional Support</small></div>
                        </div>
                        <div class="p-4 d-flex flex-column bg-white" style="height:400px; overflow-y:auto;">
                            <?php foreach($chat_history as $chat): ?>
                                <div class="chat-bubble-client shadow-sm"><?= $chat['query_text'] ?></div>
                                <div class="chat-bubble-bot shadow-sm"><?= $chat['response_text'] ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="p-3 border-top bg-light">
                            <div class="input-group">
                                <input type="text" class="form-control rounded-pill border-0 px-4" placeholder="Ask about stocks, ROP, or order status...">
                                <button class="btn btn-primary rounded-circle ms-2" style="width:45px;height:45px;"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('partials/client/footer') ?>