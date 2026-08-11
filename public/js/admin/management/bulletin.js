document.addEventListener("DOMContentLoaded", function() {
    const editBtns = document.querySelectorAll('.btn-edit-post');
    const drawerEl = document.getElementById('bulletinDrawer');
    const drawerInstance = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
    const form = document.getElementById('bulletinForm');

    // 1. EDIT BUTTON CLICK
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');

            // Show Loading State
            document.getElementById('drawerTitle').innerText = "Edit Announcement Intelligence";
            document.getElementById('btnSubmit').innerText = "✓ UPDATE ANNOUNCEMENT";

            fetch(`${BASE_URL}/admin/management/bulletin/edit/${id}`)
                .then(res => res.json())
                .then(data => {
                    // Populate IDs matching the SINGLE drawer
                    document.getElementById('form_post_id').value = data.post_id;
                    document.getElementById('form_title').value = data.title;
                    document.getElementById('form_audience').value = data.target_audience;
                    document.getElementById('form_content').value = data.content;

                    // Handle Switches
                    document.getElementById('form_pinned').checked = (parseInt(data.is_pinned) === 1);
                    document.getElementById('form_published').checked = (parseInt(data.is_published) === 1);

                    drawerInstance.show();
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    alert("Failed to load post details.");
                });
        });
    });

    // 2. ADD NEW POST RESET (Ensures form is clean)
    const btnAdd = document.getElementById('btnAddNewPost');
    if (btnAdd) {
        btnAdd.addEventListener('click', function() {
            form.reset();
            document.getElementById('form_post_id').value = ''; // Clear ID
            document.getElementById('drawerTitle').innerText = "Create New Announcement";
            document.getElementById('btnSubmit').innerText = "✓ SAVE & PUBLISH ANNOUNCEMENT";
        });
    }
});