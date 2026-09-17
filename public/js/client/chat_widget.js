document.addEventListener("DOMContentLoaded", function() {
    const panel = document.getElementById('clientChatPanel');
    const messages = document.getElementById('clientChatMessages');
    const input = document.getElementById('clientChatInput');
    const typingIndicator = document.getElementById('typingIndicator');
    const unreadBadge = document.getElementById('chatUnreadBadge');
    const greeting = document.getElementById('chatGreeting');

    let historyLoaded = false;
    let isPanelOpen = false;
    let lastKnownCount = 0;
    const POLL_MS = 3000;

    const senderStyles = {
        user: 'background:#7b1113; color:#fff; border-radius:18px 18px 4px 18px; align-self:flex-end;',
        bot: 'background:#fff; color:#1a1a1a; border-radius:18px 18px 18px 4px; align-self:flex-start; border:1px solid #e5e5e5;',
        staff: 'background:#0d2e4f; color:#fff; border-radius:18px 18px 18px 4px; align-self:flex-start;',
    };

    function addMessage(text, sender, withLabel) {
        if (!text || !text.trim()) return; // never render an empty/white bubble

        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'display:flex; flex-direction:column; max-width:80%; margin-bottom:8px;' +
            (sender === 'user' ? 'align-self:flex-end;' : 'align-self:flex-start;');

        if (withLabel && sender === 'staff') {
            const label = document.createElement('span');
            label.textContent = 'Staff';
            label.style.cssText = 'font-size:9px; color:#0d2e4f; font-weight:bold; margin-bottom:2px; margin-left:4px;';
            wrapper.appendChild(label);
        }

        const bubble = document.createElement('div');
        bubble.style.cssText = (senderStyles[sender] || senderStyles.bot) + 'padding:8px 14px;';
        bubble.textContent = text;
        wrapper.appendChild(bubble);

        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;
    }

    function renderHistory(data) {
        messages.innerHTML = '';
        if (!data.length) { messages.appendChild(greeting); return; }
        let lastSender = null;
        data.forEach(entry => {
            const showLabel = entry.sender === 'staff' && lastSender !== entry.sender;
            addMessage(entry.text, entry.sender, showLabel && entry.staff_name);
            lastSender = entry.sender;
        });
    }

    function loadHistory(force) {
        if (historyLoaded && !force) return;
        historyLoaded = true;

        fetch(`${BASE_URL}/client/chatbot/history`)
            .then(res => res.json())
            .then(data => {
                lastKnownCount = data.length;
                renderHistory(data);
            })
            .catch(err => console.error(err));
    }

    // ============ POLLING: cheap count check, only re-renders on real change ============
    function pollForUpdates() {
        fetch(`${BASE_URL}/client/chatbot/poll-count`)
            .then(res => res.json())
            .then(data => {
                if (data.count > lastKnownCount) {
                    if (isPanelOpen) {
                        loadHistory(true); // re-fetch full thread, panel is visible
                    } else {
                        lastKnownCount = data.count;
                        unreadBadge.style.display = 'block'; // silent notify — no popup, no reload
                    }
                }
            })
            .catch(() => {});
    }
    setInterval(pollForUpdates, POLL_MS);

    document.getElementById('btnToggleClientChat').addEventListener('click', () => {
        const opening = panel.style.display === 'none' || panel.style.display === '';
        panel.style.display = opening ? 'block' : 'none';
        isPanelOpen = opening;
        if (opening) {
            unreadBadge.style.display = 'none';
            loadHistory(false);
        }
    });
    document.getElementById('btnCloseClientChat').addEventListener('click', () => {
        panel.style.display = 'none';
        isPanelOpen = false;
    });

    function sendQuery() {
        const query = input.value.trim();
        if (!query) return;
        addMessage(query, 'user');
        input.value = '';
        typingIndicator.style.display = 'block';
        messages.scrollTop = messages.scrollHeight;

        fetch(`${BASE_URL}/client/chatbot/ask`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ query })
            })
            .then(res => res.json())
            .then(d => {
                typingIndicator.style.display = 'none';
                // FIXED: only render when the bot actually has something to say.
                // Once escalated/in_progress, ask() returns an empty string on
                // purpose — the client already knows a human is handling it.
                if (d.response) {
                    addMessage(d.response, 'bot');
                    lastKnownCount++;
                }
            })
            .catch(() => {
                typingIndicator.style.display = 'none';
                addMessage("Something went wrong on my end — please try again in a bit.", 'bot');
            });
    }

    document.getElementById('btnSendClientChat').addEventListener('click', sendQuery);
    input.addEventListener('keydown', function(e) { if (e.key === 'Enter') sendQuery(); });
});