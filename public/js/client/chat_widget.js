document.addEventListener("DOMContentLoaded", function() {
    const panel = document.getElementById('clientChatPanel');
    const messages = document.getElementById('clientChatMessages');
    const input = document.getElementById('clientChatInput');
    let historyLoaded = false;

    function addMessage(text, fromClient) {
        const bubble = document.createElement('div');
        bubble.style.cssText = fromClient ?
            'background:#0d2e4f; color:#fff; border-radius:20px 20px 4px 20px; padding:8px 14px; margin-bottom:8px; align-self:flex-end; max-width:80%;' :
            'background:#f1f3f4; color:#000; border-radius:20px 20px 20px 4px; padding:8px 14px; margin-bottom:8px; align-self:flex-start; max-width:80%;';
        bubble.textContent = text;
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
    }

    function loadHistory() {
        if (historyLoaded) return;
        historyLoaded = true;

        fetch(`${BASE_URL}/client/chatbot/history`)
            .then(res => res.json())
            .then(data => {
                if (!data.length) return; // keep the default "Ask about..." placeholder
                messages.innerHTML = '';
                data.forEach(entry => {
                    addMessage(entry.query_text, true);
                    if (entry.response_text) addMessage(entry.response_text, false);
                });
            })
            .catch(err => console.error(err));
    }

    document.getElementById('btnToggleClientChat').addEventListener('click', () => {
        const opening = panel.style.display === 'none' || panel.style.display === '';
        panel.style.display = opening ? 'block' : 'none';
        if (opening) loadHistory();
    });
    document.getElementById('btnCloseClientChat').addEventListener('click', () => {
        panel.style.display = 'none';
    });

    function sendQuery() {
        const query = input.value.trim();
        if (!query) return;
        addMessage(query, true);
        input.value = '';

        fetch(`${BASE_URL}/client/chatbot/ask`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ query })
            })
            .then(res => res.json())
            .then(data => addMessage(data.response, false))
            .catch(() => addMessage("Something went wrong. Please try again.", false));
    }

    document.getElementById('btnSendClientChat').addEventListener('click', sendQuery);
    input.addEventListener('keydown', function(e) { if (e.key === 'Enter') sendQuery(); });
});