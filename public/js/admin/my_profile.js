document.addEventListener("DOMContentLoaded", function() {
    const drawerEl = document.getElementById('myProfileDrawer');
    if (!drawerEl) return;

    const content = document.getElementById('myProfileContent');
    let loaded = false;

    drawerEl.addEventListener('shown.bs.offcanvas', function() {
        if (loaded) return;
        loaded = true;

        fetch(`${BASE_URL}/admin/management/my-profile/get`)
            .then(res => res.json())
            .then(data => {
                if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }

                content.innerHTML = `
    <form action="${BASE_URL}/admin/management/my-profile/update" method="POST" enctype="multipart/form-data">
        <div class="text-center mb-4">
            <img src="${data.avatar_path ? BASE_URL + '/' + data.avatar_path : BASE_URL + '/public/images/default-avatar.png'}"
                 id="avatarPreview" class="rounded-circle mb-2" style="width:80px; height:80px; object-fit:cover; border:3px solid #eee;">
            <div>
                <label class="btn btn-xs btn-outline-dark rounded-pill px-3" style="cursor:pointer;">
                    <i class="fas fa-camera me-1"></i>Change Photo
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none;">
                </label>
            </div>
        </div>

        <div class="mb-3"><label class="formal-label">Full Name *</label>
            <input type="text" name="full_name" class="formal-input" value="${data.full_name}" required></div>
        <div class="mb-3"><label class="formal-label">Email Address *</label>
            <input type="email" name="email" class="formal-input" value="${data.email}" required></div>
        <div class="mb-4"><label class="formal-label">Phone Number</label>
            <input type="text" name="phone" class="formal-input" value="${data.phone || ''}"></div>

        <hr>
        <p class="fw-bold mb-3" style="font-size:12px;">Change Password (optional)</p>
        <div class="mb-3"><label class="formal-label">New Password</label>
            <input type="password" name="password" class="formal-input" placeholder="Leave blank to keep current" minlength="8"></div>
        <div class="mb-4"><label class="formal-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="formal-input" minlength="8"></div>

        <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow">✓ SAVE PROFILE</button>
    </form>
`;

                document.getElementById('avatarInput').addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = ev => document.getElementById('avatarPreview').src = ev.target.result;
                    reader.readAsDataURL(file);
                });
            })
            .catch(err => {
                content.innerHTML = `<div class="text-danger text-center p-5">Failed to load profile.</div>`;
                console.error(err);
            });
    });
});