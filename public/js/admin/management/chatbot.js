document.addEventListener("DOMContentLoaded", function() {
    let intentPage = 1;
    let intentSearchTerm = '';
    let escPage = 1;
    let escStatus = 'open';

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ============ INTENTS TABLE ============
    function loadIntents() {
        fetch(`${BASE_URL}/admin/management/chatbot/intents-data?search=${encodeURIComponent(intentSearchTerm)}&page=${intentPage}`)
            .then(res => res.json())
            .then(result => {
                const body = document.getElementById('intentsTableBody');
                body.innerHTML = result.data.length ? result.data.map(i => `
                    <tr>
                        <td class="ps-4 fw-bold">${escapeHtml(i.intent_name)}</td>
                        <td><code style="font-size:10px;">${escapeHtml(i.keywords)}</code></td>
                        <td><small class="text-muted">${escapeHtml(i.response_template.substring(0, 40))}...</small></td>
                        <td><span class="badge rounded-pill ${i.is_active == 1 ? 'bg-success' : 'bg-secondary'} px-3">${i.is_active == 1 ? 'ACTIVE' : 'DISABLED'}</span></td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-outline-secondary rounded-pill btn-edit-intent" data-id="${i.intent_id}"><i class="fas fa-edit"></i></button>
                            <a href="${BASE_URL}/admin/management/chatbot/intent/delete/${i.intent_id}" class="btn btn-xs btn-outline-danger rounded-pill ms-1" onclick="return confirm('Delete this bot logic?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>`).join('') : `<tr><td colspan="5" class="text-center py-5 text-muted">No intents match this search.</td></tr>`;

                document.getElementById('intentPageInfo').textContent = `Showing page ${result.total_pages > 0 ? intentPage : 0} of ${result.total_pages} (${result.total} total)`;
                renderPager('intentPager', intentPage, result.total_pages, (p) => {
                    intentPage = p;
                    loadIntents();
                });
                wireIntentEditButtons();
            })
            .catch(err => console.error(err));
    }

    document.getElementById('intentSearch').addEventListener('input', function() {
        clearTimeout(this._debounce);
        this._debounce = setTimeout(() => {
            intentSearchTerm = this.value;
            intentPage = 1;
            loadIntents();
        }, 400);
    });

    // ============ ESCALATIONS TABLE ============
    function loadEscalations() {
        fetch(`${BASE_URL}/admin/management/chatbot/escalations-data?esc_status=${escStatus}&page=${escPage}`)
            .then(res => res.json())
            .then(result => {
                const statusLabelMap = { open: 'Awaiting Staff', in_progress: 'In Progress', resolved: 'Resolved' };
                const statusClassMap = { open: 'bg-danger', in_progress: 'bg-warning text-dark', resolved: 'bg-success' };

                const body = document.getElementById('escalationsTableBody');
                body.innerHTML = result.data.length ? result.data.map(e => `
                    <tr>
                        <td class="ps-4">#ESC-${String(e.escalation_id).padStart(4, '0')}</td>
                        <td class="fw-bold">${escapeHtml(e.customer_name || 'Guest User')}</td>
                        <td>${new Date(e.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                        <td><span class="badge ${statusClassMap[e.status]} px-3">${statusLabelMap[e.status].toUpperCase()}</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-dark rounded-pill px-4 btn-join-chat" data-id="${e.escalation_id}">${e.status === 'resolved' ? 'View' : 'Join Chat'}</button>
                        </td>
                    </tr>`).join('') : `<tr><td colspan="5" class="text-center py-5 text-muted">No escalations in this status.</td></tr>`;

                document.getElementById('escPageInfo').textContent = `Showing page ${result.total_pages > 0 ? escPage : 0} of ${result.total_pages} (${result.total} total)`;
                renderPager('escPager', escPage, result.total_pages, (p) => {
                    escPage = p;
                    loadEscalations();
                });
                wireJoinChatButtons();
            })
            .catch(err => console.error(err));
    }

    document.querySelectorAll('.esc-status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.esc-status-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            escStatus = this.getAttribute('data-status');
            escPage = 1;
            loadEscalations();
        });
    });

    // ============ SHARED PAGINATION RENDERER ============
    function renderPager(elementId, currentPage, totalPages, onPageClick) {
        const pager = document.getElementById(elementId);
        if (totalPages <= 1) { pager.innerHTML = ''; return; }

        const windowSize = 3;
        const currentBlock = Math.ceil(currentPage / windowSize);
        const windowStart = ((currentBlock - 1) * windowSize) + 1;
        const windowEnd = Math.min(windowStart + windowSize - 1, totalPages);

        let html = `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}"><i class="fas fa-chevron-left"></i></a></li>`;
        for (let i = windowStart; i <= windowEnd; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        html += `<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}"><i class="fas fa-chevron-right"></i></a></li>`;
        pager.innerHTML = html;

        pager.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages) onPageClick(page);
            });
        });
    }

    // ============ INTENT DRAWER (Add/Edit) ============
    const intentDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('intentDrawer'));

    function wireIntentEditButtons() {
        document.querySelectorAll('.btn-edit-intent').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('drawerTitle').innerText = "Edit Bot Logic";
                document.getElementById('btnSubmitIntent').innerText = "✓ UPDATE BOT LOGIC";

                fetch(`${BASE_URL}/admin/management/chatbot/intent/edit/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) { alert(data.error); return; }
                        document.getElementById('form_intent_id').value = data.intent_id;
                        document.getElementById('form_name').value = data.intent_name;
                        document.getElementById('form_keywords').value = data.keywords;
                        document.getElementById('form_response').value = data.response_template;
                        document.getElementById('form_active').checked = (parseInt(data.is_active) === 1);
                        intentDrawer.show();
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Failed to load intent.");
                    });
            });
        });
    }

    document.getElementById('btnAddNewIntent').addEventListener('click', () => {
        document.getElementById('intentForm').reset();
        document.getElementById('form_intent_id').value = '';
        document.getElementById('drawerTitle').innerText = "Bot Intent Specification";
        document.getElementById('btnSubmitIntent').innerText = "✓ SAVE BOT LOGIC";
    });

    // ============ LIVE CHAT ============
    const chatDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('chatDrawer'));

    function renderThread(historyText) {
        const lines = historyText.split('\n');
        let html = '';
        lines.forEach(line => {
            if (line.trim() === '') return;
            if (line.startsWith('User:')) {
                html += `<div class="chat-bubble chat-left">${escapeHtml(line.replace('User:', '').trim())}</div>`;
            } else if (line.startsWith('Bot:')) {
                html += `<div class="chat-bubble chat-right">${escapeHtml(line.replace('Bot:', '').trim())}</div>`;
            } else if (line.startsWith('Staff')) {
                html += `<div class="chat-bubble chat-staff">${escapeHtml(line.replace(/^Staff[^:]*:/, '').trim())}</div>`;
            }
        });
        const thread = document.getElementById('chatThread');
        thread.innerHTML = html;
        thread.scrollTop = thread.scrollHeight;
    }

    function wireJoinChatButtons() {
        document.querySelectorAll('.btn-join-chat').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('chatEscalationId').value = id;
                document.getElementById('chatThread').innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;
                chatDrawer.show();

                fetch(`${BASE_URL}/admin/management/chatbot/escalation/details/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) { document.getElementById('chatThread').innerHTML = `<p class="text-danger text-center p-5">${data.error}</p>`; return; }
                        document.getElementById('chatUser').innerText = data.customer || 'Guest User';
                        renderThread(data.full_chat_history);
                    })
                    .catch(err => console.error(err));
            });
        });
    }

    document.getElementById('replyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const escalationId = document.getElementById('chatEscalationId').value;
        const messageInput = document.getElementById('replyMessage');
        const message = messageInput.value.trim();
        if (!message) return;

        fetch(`${BASE_URL}/admin/management/chatbot/escalation/reply`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ escalation_id: escalationId, message: message })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }
                renderThread(data.full_chat_history);
                messageInput.value = '';
            })
            .catch(err => console.error(err));
    });

    document.getElementById('btnResolveEscalation').addEventListener('click', function() {
        const escalationId = document.getElementById('chatEscalationId').value;
        if (!escalationId) return;
        if (!confirm('Mark this escalation as resolved?')) return;

        fetch(`${BASE_URL}/admin/management/chatbot/escalation/resolve/${escalationId}`)
            .then(() => {
                chatDrawer.hide();
                loadEscalations(); // refresh in place, no reload, no tab jump
            });
    });

    // ============ INITIAL LOAD ============
    loadIntents();
    loadEscalations();
});