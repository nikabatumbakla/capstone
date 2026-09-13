<?= $this->extend('mobile/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-3">
    <a href="javascript:history.back()" class="m-page-back"><i class="fas fa-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">PharBot Support</h5>
</div>

<div class="chat-frame">
    <div id="chatMessages" class="chat-scroll">
        <div class="text-muted text-center mt-4" style="font-size:11px;" id="chatEmptyState">
            <i class="fas fa-comment-dots fs-2 mb-2 d-block" style="color:#ccc;"></i>
            Ask about products, stock, or store hours.
        </div>
    </div>

    <div class="chat-input-bar">
        <input type="text" id="chatInput" class="form-control rounded-pill" placeholder="Type your question...">
        <button id="btnSendChat" class="btn rounded-circle text-white flex-shrink-0" style="width:42px; height:42px; background:#7b1113;"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>

<script src="<?= base_url('public/js/mobile/customer_chatbot.js') ?>"></script>
<?= $this->endSection() ?>