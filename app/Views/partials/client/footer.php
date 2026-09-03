<!-- jQuery and Bootstrap JS -->
<div id="clientChatWidget" style="position:fixed; bottom:20px; right:20px; z-index:1050;">
    <button id="btnToggleClientChat" class="btn btn-maroon rounded-circle shadow-lg" style="width:55px; height:55px;">
        <i class="fas fa-robot"></i>
    </button>
    <div id="clientChatPanel" class="bg-white rounded-4 shadow-lg border" style="display:none; width:340px; height:440px; position:absolute; bottom:65px; right:0;">
        <div class="p-3 bg-dark text-white rounded-top-4 d-flex justify-content-between align-items-center">
            <span class="fw-bold" style="font-size:12px;"><i class="fas fa-robot me-2"></i>PharBot</span>
            <i class="fas fa-times" id="btnCloseClientChat" style="cursor:pointer;"></i>
        </div>
        <div id="clientChatMessages" class="p-3 d-flex flex-column" style="height:310px; overflow-y:auto; font-size:12px;">
            <div class="text-muted text-center mt-4 mb-4">Ask about stocks, order status, or general questions.</div>
        </div>
        <div class="p-2 border-top d-flex gap-2">
            <input type="text" id="clientChatInput" class="form-control form-control-sm rounded-pill" placeholder="Type your question...">
            <button id="btnSendClientChat" class="btn btn-sm btn-dark rounded-circle" style="width:34px; height:34px;"><i class="fas fa-paper-plane"></i></button>
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
