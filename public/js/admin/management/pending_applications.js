document.addEventListener("DOMContentLoaded", function() {
    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('appDetailsDrawer'));
    const content = document.getElementById('appDrawerContent');
    const title = document.getElementById('appDrawerTitle');
    const actions = document.getElementById('appDrawerActions');

    function loadDetails(id) {
        title.textContent = 'Application Details';
        content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-maroon"></div></div>`;
        actions.innerHTML = '';
        drawer.show();

        fetch(`${BASE_URL}/admin/management/users/edit/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) { content.innerHTML = `<div class="text-danger text-center p-5">${data.error}</div>`; return; }

                title.textContent = data.full_name;
                let html = `<div class="row g-2 mb-3">
                    <div class="col-6"><label class="info-label">Email</label><p class="info-value mb-0">${data.email}</p></div>
                    <div class="col-6"><label class="info-label">Phone</label><p class="info-value mb-0">${data.phone || '—'}</p></div>
                </div>`;

                if (data.role === 'supplier' && data.supplier_profile) {
                    html += `
                        <div class="p-3 bg-light rounded-4 mb-3">
                            <p class="mb-1"><b>Company:</b> ${data.supplier_profile.name}</p>
                            <p class="mb-1"><b>Address:</b> ${data.supplier_profile.address || '—'}</p>
                            <p class="mb-0"><b>Reference:</b> ${data.supplier_profile.registration_ref || 'N/A'}</p>
                        </div>`;
                } else if (data.client_profile) {
                    const permitPath = data.client_profile.permit_path;
                    let permitBlock;
                    if (!permitPath) {
                        permitBlock = `<p class="text-muted mb-0">No document uploaded.</p>`;
                    } else if (permitPath.toLowerCase().endsWith('.pdf')) {
                        permitBlock = `
                            <iframe src="${BASE_URL}/${permitPath}" style="width:100%; height:380px; border:1px solid #ddd; border-radius:8px;"></iframe>
                            <a href="${BASE_URL}/${permitPath}" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill mt-2"><i class="fas fa-external-link-alt me-1"></i>Open in New Tab</a>`;
                    } else {
                        permitBlock = `<img src="${BASE_URL}/${permitPath}" style="max-width:100%; max-height:380px; border:1px solid #ddd; border-radius:8px;">`;
                    }

                    html += `
                        <div class="p-3 bg-light rounded-4 mb-3">
                            <p class="mb-1"><b>Organization:</b> ${data.client_profile.organization}</p>
                            <p class="mb-1"><b>Type:</b> ${data.client_profile.client_type}</p>
                            <p class="mb-1"><b>TIN:</b> ${data.client_profile.tin || 'N/A'}</p>
                            <p class="mb-2"><b>Reference:</b> ${data.client_profile.registration_ref || 'N/A'}</p>
                            <p class="mb-1"><b>Submitted Document:</b></p>
                            ${permitBlock}
                        </div>`;
                }

                content.innerHTML = html;

                actions.innerHTML = `
                    <button type="button" class="btn btn-success flex-grow-1" id="btnDoApprove">
                        <i class="fas fa-check me-2"></i>Approve
                    </button>
                    <button type="button" class="btn btn-outline-danger flex-grow-1" id="btnDoReject">
                        <i class="fas fa-times me-2"></i>Reject
                    </button>`;

                document.getElementById('btnDoApprove').addEventListener('click', function() {
                    if (!confirm('Approve this application? The applicant will be notified by email.')) return;
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Approving...';
                    window.location.href = `${BASE_URL}/admin/management/users/approve-application/${id}`;
                });

                document.getElementById('btnDoReject').addEventListener('click', function() {
                    if (!confirm('Reject this application? This cannot be undone.')) return;
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Rejecting...';
                    window.location.href = `${BASE_URL}/admin/management/users/reject-application/${id}`;
                });
            })
            .catch(err => {
                content.innerHTML = `<div class="text-danger text-center p-5">Failed to load details.</div>`;
                console.error(err);
            });
    }

    document.querySelectorAll('.btn-view-app').forEach(btn => {
        btn.addEventListener('click', function() { loadDetails(this.getAttribute('data-id')); });
    });
});