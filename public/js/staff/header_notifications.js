document.addEventListener("DOMContentLoaded", function() {
    const bellBtn = document.getElementById('notifBellBtn');
    const badge = document.getElementById('notifBadge');
    const list = document.getElementById('notifList');
    if (!bellBtn) return;

    const iconColorMap = {
        low_stock: '#e74c3c',
        near_expiry: '#f1c40f',
        expired: '#7b1113',
        po_approval: '#3498db',
        assigned_task: '#3498db',
        escalation: '#0084ff',
        delivery: '#8e44ad',
    };

    function timeAgo(dateStr) {
        const diff = (Date.now() - new Date(dateStr).getTime()) / 1000;
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return new Date(dateStr).toLocaleDateString();
    }

    function loadNotifications() {
        fetch(`${BASE_URL}/staff/info/alerts/header-notifications`)
            .then(res => res.json())
            .then(data => {
                if (data.count > 0) {
                    badge.textContent = data.count > 9 ? '9+' : data.count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                list.innerHTML = data.recent.length ? data.recent.map(n => `
                    <a href="${n.link}" class="d-block text-decoration-none text-dark">
                        <div class="p-3 border-bottom d-flex align-items-start">
                            <i class="fas ${n.icon} me-2 mt-1" style="color:${iconColorMap[n.type] || '#6c757d'}; font-size:11px;"></i>
                            <div>
                                <p class="mb-0" style="font-size:11px;">${n.message}</p>
                                <small class="text-muted" style="font-size:9px;">${timeAgo(n.time)}</small>
                            </div>
                        </div>
                    </a>`).join('') : `<div class="text-center text-muted p-4" style="font-size:11px;">You're all caught up.</div>`;
            })
            .catch(err => {
                list.innerHTML = `<div class="text-center text-danger p-4" style="font-size:11px;">Failed to load.</div>`;
                console.error(err);
            });
    }

    loadNotifications();
    bellBtn.addEventListener('click', loadNotifications);
});