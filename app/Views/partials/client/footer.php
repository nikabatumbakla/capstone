<div id="clientChatWidget" style="position:fixed; bottom:20px; right:20px; z-index:1050;">
    <button id="btnToggleClientChat" class="rounded-circle shadow-lg position-relative border-0" style="width:58px; height:58px; background:linear-gradient(135deg,#7b1113,#4a0000); color:#fff;">
        <i class="fas fa-robot" style="font-size:20px;"></i>
        <span id="chatUnreadBadge" style="display:none; position:absolute; top:-2px; right:-2px; width:14px; height:14px; background:#e74c3c; border-radius:50%; border:2px solid #fff;"></span>
    </button>
    <div id="clientChatPanel" class="bg-white rounded-4 shadow-lg border-0 overflow-hidden" style="display:none; width:350px; height:460px; position:absolute; bottom:70px; right:0; box-shadow:0 10px 40px rgba(0,0,0,0.18);">
        <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg,#7b1113,#4a0000);">
            <span class="fw-bold d-flex align-items-center" style="font-size:13px;"><i class="fas fa-robot me-2"></i>PharBot Assistant</span>
            <i class="fas fa-times" id="btnCloseClientChat" style="cursor:pointer; opacity:0.85;"></i>
        </div>
        <div id="clientChatMessages" class="p-3 d-flex flex-column" style="height:300px; overflow-y:auto; font-size:12.5px; background:#f7f7f8;">
            <div class="text-muted text-center mt-4 mb-4 px-2" id="chatGreeting" style="font-size:12px; line-height:1.5;">
                <i class="fas fa-robot fs-3 d-block mb-2" style="color:#7b1113; opacity:0.6;"></i>
                Hi! I'm PharBot <br>Ask me about stock, order status, or anything else — I'll do my best, and our team steps in if I can't help.
            </div>
        </div>
        <div id="typingIndicator" style="display:none; padding:6px 16px; font-size:10.5px; color:#888; background:#f7f7f8;">
            <i class="fas fa-circle-notch fa-spin me-1"></i>PharBot is typing…
        </div>
        <div class="p-2 border-top d-flex gap-2 bg-white">
            <input type="text" id="clientChatInput" class="form-control form-control-sm rounded-pill border" placeholder="Type your question..." style="font-size:12px;">
            <button id="btnSendClientChat" class="btn rounded-circle border-0" style="width:36px; height:36px; background:#7b1113; color:#fff;"><i class="fas fa-paper-plane" style="font-size:12px;"></i></button>
        </div>
    </div>
</div>

<script src="<?= base_url('public/js/client/chat_widget.js') ?>"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('public/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('public/js/admin/main/dashboard.js') ?>"></script>
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>


