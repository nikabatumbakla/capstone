document.addEventListener("DOMContentLoaded", function() {
    const bellBtn = document.getElementById('notifBellBtn');
    const badge = document.getElementById('notifBadge');
    const list = document.getElementById('notifList');
    if (!bellBtn) return;

    const typeMeta = {
        low_stock: { icon: 'fa-box-open', color: '#e74c3c' },
        near_expiry: { icon: 'fa-hourglass-half', color: '#f1c40f' },
        expired: { icon: 'fa-ban', color: '#7b1113' },
        po_approval: { icon: 'fa-file-alt', color: '#3498db' },
        assigned_task: { icon: 'fa-clipboard-check', color: '#3498db' },
    };

    function loadNotifications() {
        fetch(`${BASE_URL}/admin/management/alerts/header-notifications`)
            .then(res => res.json())
            .then(data => {
                if (data.count > 0) {
                    badge.textContent = data.count > 9 ? '9+' : data.count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                list.innerHTML = data.recent.length ? data.recent.map(a => {
                    const meta = typeMeta[a.alert_type] || { icon: 'fa-bell', color: '#6c757d' };
                    return `
                        <div class="p-3 border-bottom d-flex align-items-start">
                            <i class="fas ${meta.icon} me-2 mt-1" style="color:${meta.color}; font-size:11px;"></i>
                            <div>
                                <p class="mb-0" style="font-size:11px;">${a.message}</p>
                                <small class="text-muted" style="font-size:9px;">${new Date(a.created_at).toLocaleString()}</small>
                            </div>
                        </div>`;
                }).join('') : `<div class="text-center text-muted p-4" style="font-size:11px;">No open alerts.</div>`;
            })
            .catch(err => {
                list.innerHTML = `<div class="text-center text-danger p-4" style="font-size:11px;">Failed to load.</div>`;
                console.error(err);
            });
    }

    loadNotifications();
    bellBtn.addEventListener('click', loadNotifications); // refresh each time it's opened
});