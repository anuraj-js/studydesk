let allAdmins = [];
let searchQuery = '';

function renderAdmins() {
    const tbody = document.getElementById('adminTableBody');
    
    let list = allAdmins;
    if (searchQuery) {
        const q = searchQuery.toLowerCase();
        list = list.filter(a => 
            a.full_name.toLowerCase().includes(q) || 
            a.username.toLowerCase().includes(q) || 
            a.email.toLowerCase().includes(q)
        );
    }

    if (list.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state">No admins found.</div></td></tr>';
        return;
    }

    const currentAdminIdMeta = document.querySelector('meta[name="admin-id"]');
    const currentAdminId = currentAdminIdMeta ? parseInt(currentAdminIdMeta.getAttribute('content'), 10) : null;
    const currentAdminName = document.querySelector('.sidebar-name')?.textContent;

    tbody.innerHTML = list.map(a => {
        const joinedDate = new Date(a.created_at.replace(' ', 'T') + 'Z').toLocaleDateString('en-US', { 
            month: 'short', day: 'numeric', year: 'numeric', timeZone: 'Asia/Kathmandu'
        });
        
        const isSelf = currentAdminId ? (a.id === currentAdminId) : (a.full_name === currentAdminName || a.username === currentAdminName);
        
        return `
            <tr style="border-bottom: 1px solid var(--border); font-size: 0.9rem;">
                <td style="padding: 0.75rem 0.5rem;">${highlightText(a.full_name, searchQuery)}</td>
                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">${highlightText(a.username, searchQuery)}</td>
                <td class="hide-mobile" style="padding: 0.75rem 0.5rem; color: var(--text-muted);">${highlightText(a.email, searchQuery)}</td>
                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">${joinedDate}</td>
                <td style="padding: 0.75rem 0.5rem; text-align: right; white-space: nowrap;">
                    <button class="btn btn-danger btn-sm delete-btn" data-id="${a.id}" data-is-self="${isSelf}" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    attachEvents();
}

function attachEvents() {
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const isSelf = btn.dataset.isSelf === 'true';

            if (isSelf) {
                showToast('You cannot delete your own account. Please ask another admin to delete it.', 'error');
                return;
            }

            showDialog({
                title: 'Delete Admin',
                message: 'Are you sure you want to permanently delete this administrator?',
                confirmText: 'Delete',
                onConfirm: async () => {
                    try {
                        const res = await apiFetch('../../api/admin/admins.php', {
                            method: 'POST',
                            body: JSON.stringify({ action: 'delete', admin_id: id })
                        });
                        if (res.success) {
                            showToast(res.message, 'success');
                            loadAdmins();
                        } else {
                            showToast(res.message, 'error');
                        }
                    } catch (err) {
                        showToast('Failed to delete admin.', 'error');
                    }
                }
            });
        });
    });
}

async function loadAdmins() {
    try {
        const res = await apiFetch('../../api/admin/admins.php');
        if (res.success) {
            allAdmins = res.data;
            renderAdmins();
        } else {
            showToast(res.message, 'error');
        }
    } catch (err) {
        showToast('Failed to load admins.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadAdmins();

    document.getElementById('searchAdmin').addEventListener('input', (e) => {
        searchQuery = e.target.value;
        renderAdmins();
    });

    // Toggle Password Visibility
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', () => {
            const targetId = icon.dataset.target;
            const input = document.getElementById(targetId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    document.getElementById('createAdminForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const password = document.getElementById('adminPassword').value;
        const confirmPassword = document.getElementById('adminConfirmPassword').value;

        if (password !== confirmPassword) {
            showToast('Passwords do not match.', 'error');
            return;
        }

        const btn = document.getElementById('createAdminBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';

        const payload = {
            action: 'create',
            full_name: document.getElementById('adminFullName').value.trim(),
            username: document.getElementById('adminUsername').value.trim(),
            email: document.getElementById('adminEmail').value.trim(),
            password: password,
            confirm_password: confirmPassword
        };

        try {
            const [res] = await Promise.all([
                apiFetch('../../api/admin/admins.php', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                }),
                new Promise(r => setTimeout(r, 500))
            ]);

            if (res.success) {
                showToast('Admin created successfully.', 'success');
                document.getElementById('createAdminForm').reset();
                document.querySelector('.collapsible-card').removeAttribute('open');
                loadAdmins();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to create admin.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
});