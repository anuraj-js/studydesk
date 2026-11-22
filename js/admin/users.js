let allUsers = [];
let searchQuery = '';

function renderUsers() {
    const tbody = document.getElementById('userTableBody');
    
    let list = allUsers;
    if (searchQuery) {
        const q = searchQuery.toLowerCase();
        list = list.filter(u => 
            u.full_name.toLowerCase().includes(q) || 
            u.username.toLowerCase().includes(q) || 
            u.email.toLowerCase().includes(q)
        );
    }

    if (list.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state">No users found.</div></td></tr>';
        return;
    }

    tbody.innerHTML = list.map(u => {
        // Convert UTC to Nepal Time
        const joinedDate = new Date(u.created_at.replace(' ', 'T') + 'Z').toLocaleDateString('en-US', { 
            month: 'short', day: 'numeric', year: 'numeric', timeZone: 'Asia/Kathmandu'
        });
        
        const statusBadge = u.is_active 
            ? '<span class="badge badge-low">Active</span>' 
            : '<span class="badge badge-high">Inactive</span>';
        
        return `
            <tr style="border-bottom: 1px solid var(--border); font-size: 0.9rem;">
                <td style="padding: 0.75rem 0.5rem;">${highlightText(u.full_name, searchQuery)}</td>
                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">${highlightText(u.username, searchQuery)}</td>
                <td class="hide-mobile" style="padding: 0.75rem 0.5rem; color: var(--text-muted);">${highlightText(u.email, searchQuery)}</td>
                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">${joinedDate}</td>
                <td style="padding: 0.75rem 0.5rem;">${statusBadge}</td>
                <td style="padding: 0.75rem 0.5rem; text-align: right; white-space: nowrap;">
                    <a href="view_user.php?id=${u.id}" class="btn btn-ghost btn-sm" title="View">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <button class="btn btn-ghost btn-sm toggle-status-btn" data-id="${u.id}" title="${u.is_active ? 'Deactivate' : 'Activate'}">
                        <i class="fa-solid ${u.is_active ? 'fa-user-slash' : 'fa-user-check'}"></i>
                    </button>
                    <button class="btn btn-danger btn-sm delete-btn" data-id="${u.id}" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    attachEvents();
}

function attachEvents() {
    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            showDialog({
                title: 'Change User Status',
                message: 'Are you sure you want to change this user\'s active status?',
                confirmText: 'Confirm',
                onConfirm: async () => {
                    try {
                        const res = await apiFetch('../../api/admin/users.php', {
                            method: 'POST',
                            body: JSON.stringify({ action: 'toggle_status', user_id: id })
                        });
                        if (res.success) {
                            showToast(res.message, 'success');
                            loadUsers();
                        } else {
                            showToast(res.message, 'error');
                        }
                    } catch (err) {
                        showToast('Failed to change status.', 'error');
                    }
                }
            });
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            showDialog({
                title: 'Delete User',
                message: 'Are you sure you want to permanently delete this user and ALL their data (tasks, exams, notes)? This cannot be undone.',
                confirmText: 'Delete',
                onConfirm: async () => {
                    try {
                        const res = await apiFetch('../../api/admin/users.php', {
                            method: 'POST',
                            body: JSON.stringify({ action: 'delete', user_id: id })
                        });
                        if (res.success) {
                            showToast(res.message, 'success');
                            loadUsers();
                        } else {
                            showToast(res.message, 'error');
                        }
                    } catch (err) {
                        showToast('Failed to delete user.', 'error');
                    }
                }
            });
        });
    });
}

async function loadUsers() {
    try {
        const res = await apiFetch('../../api/admin/users.php');
        if (res.success) {
            allUsers = res.data;
            renderUsers();
        } else {
            showToast(res.message, 'error');
        }
    } catch (err) {
        showToast('Failed to load users.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();

    document.getElementById('searchUser').addEventListener('input', (e) => {
        searchQuery = e.target.value;
        renderUsers();
    });
});