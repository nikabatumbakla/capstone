document.addEventListener("DOMContentLoaded", function() {
    const editBtns = document.querySelectorAll('.btn-edit-task');
    const taskDrawerEl = document.getElementById('taskDrawer');
    const taskDrawer = new bootstrap.Offcanvas(taskDrawerEl);

    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            // Fetch using the specific Admin route
            fetch(`${BASE_URL}/admin/management/alerts/edit/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('task_id').value = data.alert_id;
                    document.getElementById('task_type').value = data.alert_type;
                    document.getElementById('task_priority').value = data.notes; // Notes = Priority
                    document.getElementById('task_message').value = data.message;
                    taskDrawer.show();
                });
        });
    });
});