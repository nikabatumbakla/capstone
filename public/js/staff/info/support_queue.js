document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('chatDrawer'));
    const replyForm = document.getElementById('replyForm');
    const resolveBtn = document.getElementById('btnResolveEscalation');
    let currentOpenConversationId = null;

    const urlParams = new URLSearchParams(window.location.search);
    let currentStatus = urlParams.get('status') || 'escalated';
    let currentPage = parseInt(urlParams.get('page')) || 1;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : str;
        return div.innerHTML;
    }

    function renderMessages(messages) {
        const thread = document.getElementById('chatThread');
        thread.innerHTML = (messages || []).map(m => {
            const cls = m.sender === 'user' ? 'chat-left' : (m.sender === 'staff' ? 'chat-staff' : 'chat-right');
            const label = m.sender === 'staff' && m.staff_name ? `<div style="font-size:9px; color:#7b1113; font-weight:bold; margin-bottom:2px;">${escapeHtml(m.staff_name)}</div>` : '';
            return `<div>${label}<div class="chat-bubble ${cls}">${escapeHtml(m.message)}</div></div>`;
        }).join('');
        thread.scrollTop = thread.scrollHeight;
    }

    function applyResolvedState(status) {
        const isResolved = status === 'resolved';
        replyForm.style.display = isResolved ? 'none' : '';
        resolveBtn.style.display = isResolved ? 'none' : '';
        const notice = document.getElementById('resolvedNotice');
        if (notice) notice.style.display = isResolved ? 'block' : 'none';
    }

    function wireJoinButtons() {
        document.querySelectorAll('.btn-join-chat').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('chatEscalationId').value = id;
                currentOpenConversationId = id;
                document.getElementById('chatThread').innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;
                drawer.show();
                loadConversation(id);
            });
        });
    }

    function loadConversation(id) {
        fetch(`${BASE_URL}/staff/info/support-queue/get-details/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) { document.getElementById('chatThread').innerHTML = `<p class="text-danger text-center p-5">${data.error}</p>`; return; }
                document.getElementById('chatUser').innerText = data.customer || 'Guest';
                renderMessages(data.messages);
                applyResolvedState(data.status);
            })
            .catch(err => console.error(err));
    }

    document.getElementById('chatDrawer').addEventListener('hidden.bs.offcanvas', () => {
        currentOpenConversationId = null;
    });

    replyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const escalationId = document.getElementById('chatEscalationId').value;
        const messageInput = document.getElementById('replyMessage');
        const message = messageInput.value.trim();
        if (!message) return;

        fetch(`${BASE_URL}/staff/info/support-queue/reply`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ escalation_id: escalationId, message: message })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    if (data.error.toLowerCase().includes('resolved')) applyResolvedState('resolved');
                    return;
                }
                renderMessages(data.messages);
                messageInput.value = '';
            })
            .catch(err => console.error(err));
    });

    resolveBtn.addEventListener('click', function() {
        const escalationId = document.getElementById('chatEscalationId').value;
        if (!escalationId) return;
        if (!confirm('Mark this conversation as resolved?')) return;

        fetch(`${BASE_URL}/staff/info/support-queue/resolve/${escalationId}`)
            .then(() => {
                drawer.hide();
                reloadTable();
            });
    });

    // ============ LIVE TABLE REFRESH (no full page reload) ============
    function reloadTable() {
        fetch(`${BASE_URL}/staff/info/support-queue?status=${currentStatus}&page=${currentPage}&ajax=1`)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTbody = doc.querySelector('tbody');
                if (newTbody) document.querySelector('tbody').innerHTML = newTbody.innerHTML;
                wireJoinButtons();
            })
            .catch(() => {});
    }

    wireJoinButtons();

    setInterval(() => {
        reloadTable();
    }, 15000);

    setInterval(() => {
        if (!currentOpenConversationId) return;
        loadConversation(currentOpenConversationId);
    }, 5000);
});