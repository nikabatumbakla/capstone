document.addEventListener("DOMContentLoaded", function() {
    const editBtns = document.querySelectorAll('.btn-edit-user');
    const drawerEl = document.getElementById('userDrawer');
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
    const form = document.getElementById('userForm');

    // 1. SEARCH LOGIC (Automatic as you type)
    const searchInput = document.getElementById('userSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('#userTableBody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });
    }

    // 2. EDIT USER (AJAX Population)
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            document.getElementById('drawerTitle').innerText = "Edit Identity Specifications";
            document.getElementById('btnSubmit').innerText = "✓ UPDATE IDENTITY";

            fetch(`${BASE_URL}/admin/management/users/edit/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('form_user_id').value = data.user_id;
                    document.getElementById('form_name').value = data.full_name;
                    document.getElementById('form_email').value = data.email;
                    document.getElementById('form_phone').value = data.phone;
                    document.getElementById('form_role').value = data.role;
                    document.getElementById('form_active').checked = (parseInt(data.is_active) === 1);
                    drawer.show();
                })
                .catch(err => alert("Failed to fetch user data. Check route configuration."));
        });
    });

    // 3. RESET FORM FOR NEW PROVISIONING
    const btnAdd = document.getElementById('btnAddNewUser');
    if (btnAdd) {
        btnAdd.addEventListener('click', () => {
            form.reset();
            document.getElementById('form_user_id').value = '';
            document.getElementById('drawerTitle').innerText = "Provision New Account";
            document.getElementById('btnSubmit').innerText = "✓ SAVE NEW IDENTITY";
        });
    }
});