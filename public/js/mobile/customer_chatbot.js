document.addEventListener("DOMContentLoaded", function() {
    const messages = document.getElementById('chatMessages');
    const emptyState = document.getElementById('chatEmptyState');
    const input = document.getElementById('chatInput');

    function timeLabel(dateStr) {
        return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function addBubble(text, mine, time) {
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + (mine ? 'mine' : 'bot');
        bubble.textContent = text;
        messages.appendChild(bubble);

        if (time) {
            const t = document.createElement('div');
            t.className = 'chat-time' + (mine ? '' : ' bot-time');
            t.textContent = time;
            messages.appendChild(t);
        }
        messages.scrollTop = messages.scrollHeight;
    }

    function showTyping() {
        const t = document.createElement('div');
        t.className = 'chat-typing';
        t.id = 'typingIndicator';
        t.innerHTML = '<span></span><span></span><span></span>';
        messages.appendChild(t);
        messages.scrollTop = messages.scrollHeight;
    }

    function hideTyping() {
        const t = document.getElementById('typingIndicator');
        if (t) t.remove();
    }

    fetch(`${BASE_URL}m/customer/chatbot/history`)
        .then(res => res.json())
        .then(history => {
            if (!history.length) return;
            emptyState.remove();
            history.forEach(entry => {
                addBubble(entry.query_text, true, timeLabel(entry.created_at));
                if (entry.response_text) {
                    addBubble(entry.response_text, false, timeLabel(entry.created_at));
                } else {
                    addBubble("This question was forwarded to our support team.", false, timeLabel(entry.created_at));
                }
            });
        })
        .catch(err => console.error(err));

    function send() {
        const q = input.value.trim();
        if (!q) return;

        if (emptyState.parentNode) emptyState.remove();
        addBubble(q, true, timeLabel(new Date()));
        input.value = '';
        showTyping();

        fetch(`${BASE_URL}m/customer/chatbot/ask`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ query: q })
            })
            .then(r => r.json())
            .then(d => {
                hideTyping();
                addBubble(d.response, false, timeLabel(new Date()));
            })
            .catch(() => {
                hideTyping();
                addBubble("Something went wrong. Please try again.", false, timeLabel(new Date()));
            });
    }

    document.getElementById('btnSendChat').addEventListener('click', send);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') send(); });
});