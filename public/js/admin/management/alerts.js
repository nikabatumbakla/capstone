document.addEventListener("DOMContentLoaded", function() {
    const taskDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('taskDrawer'));
    const drawerTitle = document.getElementById('taskDrawerTitle');
    const form = document.querySelector('#taskDrawer form');

    document.getElementById('btnNewTask').addEventListener('click', function() {
        form.reset();
        document.getElementById('task_id').value = '';
        drawerTitle.textContent = 'New Task';
    });

    document.querySelectorAll('.btn-edit-task').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            fetch(`${BASE_URL}/admin/management/alerts/edit/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }
                    document.getElementById('task_id').value = data.alert_id;
                    document.getElementById('task_priority').value = data.priority;
                    document.getElementById('task_assigned').value = data.assigned_to || '';
                    document.getElementById('task_due').value = data.due_date || '';
                    document.getElementById('task_message').value = data.message;
                    document.getElementById('task_notes').value = data.notes || '';
                    drawerTitle.textContent = 'Edit Task';
                    taskDrawer.show();
                })
                .catch(err => console.error(err));
        });
    });

    // ============ RESOLVE WITH NOTE ============
    const resolveDrawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('resolveDrawer'));
    const resolveNote = document.getElementById('resolveNote');
    let pendingResolveId = null;

    document.querySelectorAll('.btn-resolve').forEach(btn => {
        btn.addEventListener('click', function() {
            pendingResolveId = this.getAttribute('data-id');
            resolveNote.value = '';
            resolveDrawer.show();
        });
    });

    document.getElementById('btnConfirmResolve').addEventListener('click', function() {
        if (!pendingResolveId) return;
        const note = encodeURIComponent(resolveNote.value.trim());
        window.location.href = `${BASE_URL}/admin/management/alerts/resolve/${pendingResolveId}?note=${note}`;
    });
});