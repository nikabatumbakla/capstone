function switchRole(role) {
    const inputRole = document.getElementById('input-role');
    const btnAdmin = document.getElementById('btn-admin');
    const btnStaff = document.getElementById('btn-staff');
    const roleTitle = document.getElementById('role-title');
    const roleDesc = document.getElementById('role-desc');
    const forgotLink = document.getElementById('forgot-link');

    if (inputRole) inputRole.value = role;

    if (role === 'admin') {
        if (btnAdmin) btnAdmin.classList.add('active');
        if (btnStaff) btnStaff.classList.remove('active');
        if (roleTitle) roleTitle.innerText = "Admin";
        if (roleDesc) roleDesc.innerText = "Full System Control";
        if (forgotLink) forgotLink.innerText = "Forgot Password?";
    } else {
        if (btnStaff) btnStaff.classList.add('active');
        if (btnAdmin) btnAdmin.classList.remove('active');
        if (roleTitle) roleTitle.innerText = "Staff Member";
        if (roleDesc) roleDesc.innerText = "Access Based On Assigned Role";
        if (forgotLink) forgotLink.innerText = "Forgot Password? Contact Admin";
    }
}