document.addEventListener("DOMContentLoaded", function() {
    const bellBtn = document.getElementById('notifBellBtn');
    const badge = document.getElementById('notifBadge');
    const list = document.getElementById('notifList');
    if (!bellBtn) return;

    const iconMap = { low_stock: 'fa-box-open', near_expiry: 'fa-hourglass-half', expired: 'fa-ban', po_approval: 'fa-file-alt', po_pending: 'fa-truck-loading', assigned_task: 'fa-clipboard-check' };
    const colorMap = { low_stock: '#e74c3c', near_expiry: '#f1c40f', expired: '#7b1113', po_approval: '#3498db', po_pending: '#8e44ad', assigned_task: '#3498db' };

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

                list.innerHTML = data.recent.length ? data.recent.map(n => `
                    <a href="${n.link}" class="d-block text-decoration-none text-dark">
                        <div class="p-3 border-bottom d-flex align-items-start">
                            <i class="fas ${iconMap[n.type] || 'fa-bell'} me-2 mt-1" style="color:${colorMap[n.type] || '#6c757d'}; font-size:11px;"></i>
                            <div>
                                <p class="mb-0" style="font-size:11px;">${n.message}</p>
                                <small class="text-muted" style="font-size:9px;">${new Date(n.time).toLocaleString()}</small>
                            </div>
                        </div>
                    </a>`).join('') : `<div class="text-center text-muted p-4" style="font-size:11px;">You're all caught up.</div>`;
            })
            .catch(err => console.error(err));
    }

    loadNotifications();
    bellBtn.addEventListener('click', loadNotifications);
});