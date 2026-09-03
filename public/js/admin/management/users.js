document.addEventListener("DOMContentLoaded", function() {
    const roleSelect = document.getElementById('form_role');
    const roleFieldMap = {
        staff: 'staffFields',
        customer: 'customerFields',
        supplier: 'supplierFields',
        institutional_client: 'clientFields',
    };

    function toggleRoleFields() {
        Object.values(roleFieldMap).forEach(id => document.getElementById(id).style.display = 'none');
        if (roleFieldMap[roleSelect.value]) document.getElementById(roleFieldMap[roleSelect.value]).style.display = 'block';
    }
    roleSelect.addEventListener('change', toggleRoleFields);

    // ============ PASSWORD CONFIRMATION CHECK ============
    const createForm = document.getElementById('userForm');
    const pwInput = document.getElementById('form_password');
    const confirmInput = document.getElementById('form_confirm_password');
    const mismatchError = document.getElementById('passwordMismatchError');

    function validatePasswords() {
        const matches = pwInput.value === confirmInput.value;
        mismatchError.style.display = matches ? 'none' : 'block';
        return matches;
    }
    confirmInput.addEventListener('input', validatePasswords);
    pwInput.addEventListener('input', validatePasswords);

    createForm.addEventListener('submit', function(e) {
        if (!validatePasswords()) {
            e.preventDefault();
            confirmInput.focus();
        }
    });

    // ============ SEARCH / FILTER ============
    const filterForm = document.getElementById('filterForm');
    filterForm.querySelector('select[name="role"]').addEventListener('change', () => filterForm.submit());
    let typingTimer;
    document.getElementById('userSearch').addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => filterForm.submit(), 600);
    });

    // ============ MANAGE ACCESS DRAWER ============
    const accessDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('accessDrawer'));

    document.querySelectorAll('.btn-manage-access').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');

            fetch(`${BASE_URL}/admin/management/users/edit/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }

                    document.getElementById('access_user_id').value = data.user_id;
                    document.getElementById('access_display_name').textContent = data.full_name;
                    document.getElementById('access_display_email').textContent = data.email;
                    document.getElementById('access_active').checked = (parseInt(data.is_active) === 1);
                    document.getElementById('access_verified').checked = (parseInt(data.is_verified) === 1);
                    document.getElementById('access_notes').value = data.verification_notes || '';

                    accessDrawer.show();
                })
                .catch(err => {
                    console.error(err);
                    alert("Failed to fetch account data.");
                });
        });
    });

    // ============ NEW ACCOUNT RESET ============
    document.getElementById('btnAddNewUser').addEventListener('click', () => {
        createForm.reset();
        document.getElementById('form_user_id').value = '';
        mismatchError.style.display = 'none';
        toggleRoleFields();
    });
});