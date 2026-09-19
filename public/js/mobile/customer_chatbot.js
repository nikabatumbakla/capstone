document.addEventListener("DOMContentLoaded", function() {
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';

    const bottomNav = document.querySelector('.m-bottom-nav');
    if (bottomNav) {
        document.documentElement.style.setProperty('--bottom-nav-height', bottomNav.offsetHeight + 'px');
    }

    const messages = document.getElementById('chatMessages');
    const emptyState = document.getElementById('chatEmptyState');
    const input = document.getElementById('chatInput');
    const POLL_MS = 3000;
    let lastKnownCount = 0;

    function timeLabel(dateStr) {
        return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function addBubble(text, sender, staffName, time) {
        if (!text || !text.trim()) return; // never render an empty/black bubble

        if (sender === 'staff' && staffName) {
            const label = document.createElement('div');
            label.className = 'chat-staff-label';
            label.style.cssText = 'font-size:9px; color:#0d2e4f; font-weight:bold; margin:4px 0 2px 4px;';
            label.textContent = staffName;
            messages.appendChild(label);
        }

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + (sender === 'user' ? 'mine' : (sender === 'staff' ? 'staff' : 'bot'));
        if (sender === 'staff') {
            bubble.style.background = '#0d2e4f';
            bubble.style.color = '#fff';
        }
        bubble.textContent = text;
        messages.appendChild(bubble);

        if (time) {
            const t = document.createElement('div');
            t.className = 'chat-time' + (sender === 'user' ? '' : ' bot-time');
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

    function renderHistory(history) {
        messages.innerHTML = '';
        if (!history.length) {
            messages.appendChild(emptyState);
            return;
        }
        history.forEach(entry => {
            addBubble(entry.text, entry.sender, entry.staff_name, timeLabel(entry.created_at || new Date()));
        });
        lastKnownCount = history.length;
    }

    function loadHistory() {
        fetch(`${BASE_URL}m/customer/chatbot/history`)
            .then(res => res.json())
            .then(history => renderHistory(history))
            .catch(err => console.error(err));
    }

    loadHistory();

    setInterval(() => {
        fetch(`${BASE_URL}m/customer/chatbot/poll-count`)
            .then(res => res.json())
            .then(data => {
                if (data.count > lastKnownCount) {
                    loadHistory();
                }
            })
            .catch(() => {});
    }, POLL_MS);

    function send() {
        const q = input.value.trim();
        if (!q) return;

        if (emptyState.parentNode) emptyState.remove();
        addBubble(q, 'user', null, timeLabel(new Date()));
        input.value = '';
        showTyping();
        lastKnownCount++;

        fetch(`${BASE_URL}m/customer/chatbot/ask`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ query: q })
            })
            .then(r => r.json())
            .then(d => {
                hideTyping();
                if (d.response) {
                    addBubble(d.response, 'bot', null, timeLabel(new Date()));
                    lastKnownCount++;
                }
            })
            .catch(() => {
                hideTyping();
                addBubble("Something went wrong. Please try again.", 'bot', null, timeLabel(new Date()));
            });
    }

    document.getElementById('btnSendChat').addEventListener('click', send);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') send(); });
});