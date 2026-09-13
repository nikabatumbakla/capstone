<nav class="navbar main-header px-3 shadow-sm bg-white">
    <div class="d-flex align-items-center">
        <!-- Sidebar Toggle (Burger) -->
        <button id="sidebarToggle" class="btn-burger me-3">
            <i class="fas fa-bars"></i>
        </button>
        <div class="breadcrumb-area">
            <span class="fw-bold small" style="color: #4a0000;">Robin Rose Trading — Medical Supply</span>
        </div>
    </div>
    
    <div class="header-icons d-flex align-items-center">
        <div class="position-relative">
            <button class="icon-btn position-relative me-3" id="notifBellBtn">
                <i class="far fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="padding: 3px; border: 2px solid white; display:none;"> </span>
            </button>
            <div class="p-0 shadow border bg-white" id="notifDropdown" style="width:320px; display:none; position:absolute; right:0; top:120%; z-index:1050; border-radius:12px;">
                <div class="p-3 border-bottom fw-bold" style="font-size:12px;">Notifications</div>
                <div id="notifList" style="max-height:320px; overflow-y:auto; font-size:11px;">
                    <div class="text-center text-muted p-4">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const bellBtn = document.getElementById('notifBellBtn');
    const dropdown = document.getElementById('notifDropdown');
    const badge = document.getElementById('notifBadge');
    const list = document.getElementById('notifList');
    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL.replace(/\/+$/, '') : window.location.origin + '/PharMediSync';

    // Fetched once when this page loads — never on a timer. Opening the bell
    // re-fetches too (that's a user action, not a background refresh).
    function loadNotifications() {
        fetch(`${baseUrl}/supplier/notifications/header-data`)
            .then(res => res.json())
            .then(data => {
                badge.style.display = data.unread_count > 0 ? 'block' : 'none';

                list.innerHTML = data.items.length ? data.items.map(n => `
                    <a href="${n.link ? baseUrl + n.link : '#'}" class="d-block p-3 border-bottom text-decoration-none text-dark ${n.is_read == 0 ? 'bg-light' : ''}">
                        ${n.message}
                        <br><small class="text-muted">${n.created_at}</small>
                    </a>
                `).join('') : `<div class="text-center text-muted p-4">No notifications yet.</div>`;
            })
            .catch(() => { list.innerHTML = `<div class="text-center text-danger p-4">Failed to load.</div>`; });
    }

    bellBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (!isOpen) {
            loadNotifications();
            fetch(`${baseUrl}/supplier/notifications/mark-read`).then(() => setTimeout(() => { badge.style.display = 'none'; }, 1500));
        }
    });

    document.addEventListener('click', function(e) {
        if (!bellBtn.contains(e.target) && !dropdown.contains(e.target)) dropdown.style.display = 'none';
    });

    loadNotifications(); // one-time, on page load only
});
</script>