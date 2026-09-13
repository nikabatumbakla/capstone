<nav class="navbar main-header px-3 shadow-sm bg-white">
    <div class="d-flex align-items-center">
        <button id="sidebarToggle" class="btn-burger me-3">
            <i class="fas fa-bars"></i>
        </button>
        <div class="breadcrumb-area">
            <span class="fw-bold small" style="color: #4a0000;">Robin Rose Trading — Medical Supply</span>
        </div>
    </div>

    <div class="header-icons d-flex align-items-center">
        <a href="<?= base_url('staff/operations/pos') ?>" target="_blank" rel="noopener" class="icon-btn me-3" title="Open POS">
            <i class="fas fa-cash-register"></i>
        </a>

        <div class="dropdown me-3">
            <button class="icon-btn position-relative" id="notifBellBtn" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="far fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notifBadge" style="font-size:8px; padding: 3px 5px; border: 2px solid white;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg border-0" style="width:320px;" id="notifDropdown">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="fw-bold mb-0" style="font-size:12px;">My Alerts</h6>
                </div>
                <div id="notifList" style="max-height:320px; overflow-y:auto;">
                    <div class="text-center text-muted p-4" style="font-size:11px;">Loading...</div>
                </div>
                <a href="<?= base_url('staff/info/alerts') ?>" class="d-block text-center p-2 border-top text-decoration-none" style="font-size:11px;">View All Alerts</a>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL.replace(/\/+$/, '') : window.location.origin + '/PharMediSync';
    const badge = document.getElementById('notifBadge');
    const list = document.getElementById('notifList');
    const notifDropdownToggle = document.getElementById('notifBellBtn');

    function loadNotifications() {
        fetch(`${baseUrl}/staff/notifications/header-data`)
            .then(res => res.json())
            .then(data => {
                badge.classList.toggle('d-none', data.unread_count === 0);
                if (data.unread_count > 0) badge.textContent = data.unread_count;

                list.innerHTML = data.items.length ? data.items.map(a => `
                    <a href="${baseUrl}/staff/info/alerts" class="d-flex align-items-start gap-2 p-3 border-bottom text-decoration-none text-dark">
                        <i class="fas ${a.icon} mt-1" style="width:14px;"></i>
                        <span class="flex-grow-1" style="font-size:11px;">
                            ${a.message}
                            ${a.priority === 'high' ? '<span class="badge bg-danger ms-1" style="font-size:8px;">HIGH</span>' : ''}
                        </span>
                    </a>
                `).join('') : `<div class="text-center text-muted p-4" style="font-size:11px;">No open alerts — you're all caught up.</div>`;
            })
            .catch(() => { list.innerHTML = `<div class="text-center text-danger p-4" style="font-size:11px;">Failed to load.</div>`; });
    }

    loadNotifications();
    notifDropdownToggle.addEventListener('show.bs.dropdown', loadNotifications);
});
</script>