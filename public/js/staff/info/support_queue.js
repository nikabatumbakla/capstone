document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('chatDrawer'));

    function renderThread(historyText) {
        const lines = historyText.split('\n');
        let html = '';
        lines.forEach(line => {
            if (line.trim() === '') return;
            if (line.startsWith('User:')) html += `<div class="chat-bubble chat-left">${line.replace('User:', '').trim()}</div>`;
            else if (line.startsWith('Bot:')) html += `<div class="chat-bubble chat-right">${line.replace('Bot:', '').trim()}</div>`;
            else if (line.startsWith('Staff')) html += `<div class="chat-bubble chat-staff">${line.replace(/^Staff[^:]*:/, '').trim()}</div>`;
        });
        const thread = document.getElementById('chatThread');
        thread.innerHTML = html;
        thread.scrollTop = thread.scrollHeight;
    }

    document.querySelectorAll('.btn-join-chat').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            document.getElementById('chatEscalationId').value = id;
            document.getElementById('chatThread').innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;
            drawer.show();

            fetch(`${BASE_URL}/staff/info/support-queue/get-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { document.getElementById('chatThread').innerHTML = `<p class="text-danger text-center p-5">${data.error}</p>`; return; }
                    document.getElementById('chatUser').innerText = data.customer || 'Guest';
                    renderThread(data.full_chat_history);
                })
                .catch(err => console.error(err));
        });
    });

    document.getElementById('replyForm').addEventListener('submit', function(e) {
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
                if (data.error) { alert(data.error); return; }
                renderThread(data.full_chat_history);
                messageInput.value = '';
            })
            .catch(err => console.error(err));
    });

    document.getElementById('btnResolveEscalation').addEventListener('click', function() {
        const escalationId = document.getElementById('chatEscalationId').value;
        if (!escalationId) return;
        if (!confirm('Mark this conversation as resolved?')) return;
        window.location.href = `${BASE_URL}/staff/info/support-queue/resolve/${escalationId}`;
    });
});