document.addEventListener("DOMContentLoaded", function() {
    const editBtns = document.querySelectorAll('.btn-edit-post');
    const drawerEl = document.getElementById('bulletinDrawer');
    const drawerInstance = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
    const form = document.getElementById('bulletinForm');

    // ============ ARCHIVE DRAWER ============
    const archiveContent = document.getElementById('archiveListContent');
    const repostDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('repostDrawer'));

    function loadArchive(page = 1) {
        archiveContent.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
        fetch(`${BASE_URL}/admin/management/bulletin/get-archive?page=${page}`)
            .then(res => res.json())
            .then(result => {
                if (!result.data.length) {
                    archiveContent.innerHTML = `<p class="text-center text-muted py-5">No archived announcements yet.</p>`;
                    return;
                }
                archiveContent.innerHTML = result.data.map(a => `
    <div class="p-3 mb-2 border rounded-4">
        <div class="d-flex justify-content-between align-items-start">
            <h6 class="fw-bold mb-1" style="font-size:12px;">${a.title}</h6>
            <span class="badge bg-light text-dark border" style="font-size:9px;">${a.target_audience.toUpperCase()}</span>
        </div>
        <small class="text-muted d-block mb-2">Was live ${a.starts_at ? a.starts_at.split(' ')[0] : ''} – ${a.ends_at ? a.ends_at.split(' ')[0] : ''}</small>
        <div class="d-flex gap-2">
            <button class="btn btn-xs btn-maroon rounded-pill px-3 btn-repost-trigger" data-id="${a.archive_id}" data-title="${a.title}" data-content="${a.content.replace(/"/g, '&quot;')}">
                <i class="fas fa-redo me-1"></i>Repost
            </button>
            <button class="btn btn-xs btn-outline-danger rounded-circle btn-delete-archive" data-id="${a.archive_id}" title="Delete Permanently" style="width:28px; height:28px;">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
`).join('');

                document.querySelectorAll('.btn-repost-trigger').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.getElementById('repost_archive_id').value = this.getAttribute('data-id');
                        document.getElementById('repost_title_input').value = this.getAttribute('data-title');
                        document.getElementById('repost_content_input').value = this.getAttribute('data-content');

                        // Pre-fill sensible future dates: now → one week from now
                        const now = new Date();
                        const oneWeekLater = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);

                        function toLocalDatetimeString(date) {
                            const pad = n => String(n).padStart(2, '0');
                            return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
                        }
                        document.getElementById('repost_starts_at').value = toLocalDatetimeString(now);
                        document.getElementById('repost_ends_at').value = toLocalDatetimeString(oneWeekLater);

                        repostDrawer.show();
                    });
                });

                document.querySelectorAll('.btn-delete-archive').forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (!confirm('Permanently delete this archived announcement? This cannot be undone.')) return;
                        const id = this.getAttribute('data-id');
                        fetch(`${BASE_URL}/admin/management/bulletin/delete-archived/${id}`)
                            .then(res => res.json())
                            .then(() => loadArchive(1))
                            .catch(err => console.error(err));
                    });
                });

            })
            .catch(err => {
                archiveContent.innerHTML = `<p class="text-center text-danger py-5">Failed to load archive.</p>`;
                console.error(err);
            });
    }

    document.getElementById('btnOpenArchive').addEventListener('click', () => loadArchive(1));

    document.getElementById('filterForm').querySelectorAll('select, input').forEach(el => {
        el.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
    let typingTimer;
    document.getElementById('liveSearch').addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => document.getElementById('filterForm').submit(), 600);
    });

    function toDatetimeLocal(value) {
        if (!value) return '';
        return value.replace(' ', 'T').slice(0, 16);
    }

    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');

            document.getElementById('drawerTitle').innerText = "Edit Announcement";
            document.getElementById('btnSubmit').innerText = "✓ UPDATE ANNOUNCEMENT";

            fetch(`${BASE_URL}/admin/management/bulletin/edit/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }

                    document.getElementById('form_post_id').value = data.post_id;
                    document.getElementById('form_title').value = data.title;
                    document.getElementById('form_audience').value = data.target_audience;
                    document.getElementById('form_content').value = data.content;
                    document.getElementById('form_start').value = toDatetimeLocal(data.starts_at);
                    document.getElementById('form_end').value = toDatetimeLocal(data.ends_at);
                    document.getElementById('form_pinned').checked = (parseInt(data.is_pinned) === 1);
                    document.getElementById('form_published').checked = (parseInt(data.is_published) === 1);

                    const preview = document.getElementById('form_image_preview');
                    preview.innerHTML = data.image_path ?
                        `<img src="${BASE_URL}/${data.image_path}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;" class="border">` :
                        '';

                    drawerInstance.show();
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    alert("Failed to load post details.");
                });
        });
    });

    const btnAdd = document.getElementById('btnAddNewPost');
    if (btnAdd) {
        btnAdd.addEventListener('click', function() {
            form.reset();
            document.getElementById('form_post_id').value = '';
            document.getElementById('form_image_preview').innerHTML = '';
            document.getElementById('drawerTitle').innerText = "Create New Announcement";
            document.getElementById('btnSubmit').innerText = "✓ SAVE & PUBLISH ANNOUNCEMENT";
        });
    }
});