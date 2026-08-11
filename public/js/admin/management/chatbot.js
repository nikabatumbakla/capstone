document.addEventListener("DOMContentLoaded", function() {
    const editBtns = document.querySelectorAll('.btn-edit-intent');
    const joinBtns = document.querySelectorAll('.btn-join-chat');

    const intentDrawerEl = document.getElementById('intentDrawer');
    const intentDrawer = bootstrap.Offcanvas.getOrCreateInstance(intentDrawerEl);

    const chatDrawerEl = document.getElementById('chatDrawer');
    const chatDrawer = bootstrap.Offcanvas.getOrCreateInstance(chatDrawerEl);

    // 1. EDIT INTENT LOGIC
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            document.getElementById('drawerTitle').innerText = "Edit Bot Logic Intelligence";
            document.getElementById('btnSubmitIntent').innerText = "✓ UPDATE BOT LOGIC";

            fetch(`${BASE_URL}/admin/management/chatbot/intent/edit/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('form_intent_id').value = data.intent_id;
                    document.getElementById('form_name').value = data.intent_name;
                    document.getElementById('form_keywords').value = data.keywords;
                    document.getElementById('form_response').value = data.response_template;
                    document.getElementById('form_active').checked = (parseInt(data.is_active) === 1);
                    intentDrawer.show();
                });
        });
    });

    // 2. JOIN CHAT LOGIC (Messenger Style)
    joinBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            document.getElementById('chatThread').innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;
            chatDrawer.show();

            fetch(`${BASE_URL}/admin/management/chatbot/escalation/details/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('chatUser').innerText = data.customer || 'Guest User';

                    // Parse "User: message\nBot: message" string into Messenger bubbles
                    const historyLines = data.full_chat_history.split('\n');
                    let threadHtml = `<div class="chat-meta">Chat Started: ${data.created_at}</div>`;

                    historyLines.forEach(line => {
                        if (line.trim() === '') return;

                        if (line.startsWith('User:')) {
                            const text = line.replace('User:', '').trim();
                            threadHtml += `<div class="chat-bubble chat-left shadow-sm">${text}</div>`;
                        } else if (line.startsWith('Bot:')) {
                            const text = line.replace('Bot:', '').trim();
                            threadHtml += `<div class="chat-bubble chat-right shadow-sm">${text}</div>`;
                        }
                    });

                    document.getElementById('chatThread').innerHTML = threadHtml;
                    // Auto-scroll to bottom
                    const container = document.getElementById('chatThread');
                    container.scrollTop = container.scrollHeight;
                });
        });
    });

    // 3. RESET FORM FOR NEW INTENT
    const btnAdd = document.getElementById('btnAddNewIntent');
    if (btnAdd) {
        btnAdd.addEventListener('click', () => {
            document.getElementById('intentForm').reset();
            document.getElementById('form_intent_id').value = '';
            document.getElementById('drawerTitle').innerText = "Bot Intent Specification";
            document.getElementById('btnSubmitIntent').innerText = "✓ SAVE BOT LOGIC";
        });
    }
});