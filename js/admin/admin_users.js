// js/admin/admin_users.js

document.addEventListener('DOMContentLoaded', () => {
    const usersTableBody = document.getElementById('usersTableBody');
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const roleFilter = document.getElementById('roleFilter');
    const levelFilter = document.getElementById('levelFilter');
    const paginationContainer = document.getElementById('paginationContainer');

    // Delete modal elements
    const deleteModal = document.getElementById('deleteModal');
    const deleteTargetName = document.getElementById('deleteTargetName');
    const deleteConfirmInput = document.getElementById('deleteConfirmInput');
    const deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
    const deleteCancelBtn = document.getElementById('deleteCancelBtn');

    // Delete modal - two states
    const modalDeleteState = document.getElementById('modalDeleteState');
    const modalBlockedState = document.getElementById('modalBlockedState');
    const deleteModalTitle = document.getElementById('deleteModalTitle');
    const deleteModalDataList = document.getElementById('deleteModalDataList');
    const deleteInputGroup = document.getElementById('deleteInputGroup');
    const deleteCloseBtn = document.getElementById('deleteCloseBtn');

    // Current logged-in admin's ID (injected by PHP)
    const currentUserId = parseInt(window.CURRENT_USER_ID) || 0;

    let searchQuery = '';
    let currentRole = 'all';
    let currentLevel = 'all';
    let currentPage = 1;
    let searchTimeout = null;
    let pendingDeleteId = null;
    let pendingDeleteUsername = null;

    // Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Format date
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    // Show loading skeleton
    function showLoading() {
        usersTableBody.innerHTML = `
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
        `;
    }

    // Render users table
    function renderUsers(users) {
        if (!users || users.length === 0) {
            usersTableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <span class="empty-title">No users found</span>
                            <span class="empty-sub">Try adjusting your search or filters.</span>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        usersTableBody.innerHTML = users.map(user => `
            <tr>
                <td>${user.id}</td>
                <td><span class="user-name">${escapeHtml(user.username)}</span></td>
                <td><span class="user-email">${escapeHtml(user.email)}</span></td>
                <td><span class="role-badge role-${user.role}">${escapeHtml(user.role)}</span></td>
                <td>${formatDate(user.created_at)}</td>
                <td class="actions-cell">
                    <button class="btn-icon view-btn" data-id="${user.id}" title="View Profile">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon delete-btn"
                            data-id="${user.id}"
                            data-username="${escapeHtml(user.username)}"
                            data-role="${user.role}"
                            title="Delete User">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        // Attach view listeners
        usersTableBody.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.id;
                window.location.href = `admin_user_profile.php?id=${userId}`;
            });
        });

        // Attach delete listeners
        usersTableBody.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = parseInt(this.dataset.id);
                const username = this.dataset.username;
                const role = this.dataset.role;
                openDeleteModal(userId, username, role);
            });
        });
    }

    // Render pagination
    function renderPagination(pagination) {
        if (!paginationContainer) return;

        const { current_page, total_pages, total, per_page } = pagination;

        if (total_pages <= 1) {
            paginationContainer.innerHTML = `<span class="pagination-info">Showing ${total} user${total !== 1 ? 's' : ''}</span>`;
            return;
        }

        const start = (current_page - 1) * per_page + 1;
        const end = Math.min(current_page * per_page, total);

        let pagesHtml = '';
        for (let i = 1; i <= total_pages; i++) {
            if (i === 1 || i === total_pages || (i >= current_page - 2 && i <= current_page + 2)) {
                pagesHtml += `<button class="page-btn ${i === current_page ? 'active' : ''}" data-page="${i}">${i}</button>`;
            } else if (i === current_page - 3 || i === current_page + 3) {
                pagesHtml += `<span class="page-ellipsis">...</span>`;
            }
        }

        paginationContainer.innerHTML = `
            <span class="pagination-info">Showing ${start}-${end} of ${total}</span>
            <div class="pagination-controls">
                <button class="page-btn" data-page="${current_page - 1}" ${current_page === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>
                ${pagesHtml}
                <button class="page-btn" data-page="${current_page + 1}" ${current_page === total_pages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;

        paginationContainer.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const page = parseInt(this.dataset.page);
                if (!isNaN(page) && page >= 1 && page <= total_pages) {
                    currentPage = page;
                    fetchUsers();
                }
            });
        });
    }

    // Fetch users
    async function fetchUsers() {
        showLoading();

        try {
            const params = new URLSearchParams();
            if (searchQuery) params.append('search', searchQuery);
            if (currentRole !== 'all') params.append('role', currentRole);
            if (currentLevel !== 'all') params.append('level', currentLevel);
            params.append('page', currentPage);

            const response = await fetch(`../../api/admin/users.php?${params.toString()}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                renderUsers(result.data.users);
                renderPagination(result.data.pagination);
            } else {
                showToast('Failed to load users: ' + result.message, 'error');
                usersTableBody.innerHTML = `
                    <tr>
                        <td colspan="6">
                            <div class="error-state">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>${escapeHtml(result.message)}</span>
                            </div>
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            showToast('Network error loading users', 'error');
            usersTableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="error-state">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Network error. Please try again.</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    // --- Delete Modal ---

    function openDeleteModal(userId, username, role) {
        pendingDeleteId = userId;
        pendingDeleteUsername = username;

        // Case 1: Attempting to delete self -> show blocked state
        if (userId === currentUserId) {
            if (modalDeleteState) modalDeleteState.style.display = 'none';
            if (modalBlockedState) modalBlockedState.style.display = 'block';
            if (deleteModal) deleteModal.classList.add('open');
            return;
        }

        // Case 2 & 3: Show confirm state (dynamic based on role)
        if (modalDeleteState) modalDeleteState.style.display = 'block';
        if (modalBlockedState) modalBlockedState.style.display = 'none';

        const isAdmin = role === 'admin';

        if (isAdmin) {
            // Case 2: Deleting another admin
            if (deleteModalTitle) deleteModalTitle.textContent = 'Delete Admin';
            if (deleteModalDataList) {
                deleteModalDataList.innerHTML = 'This will remove their access to the admin panel and delete their account.';
            }
            if (deleteConfirmBtn) deleteConfirmBtn.textContent = 'Delete Admin';
        } else {
            // Case 3: Deleting regular user
            if (deleteModalTitle) deleteModalTitle.textContent = 'Delete User';
            if (deleteModalDataList) {
                deleteModalDataList.innerHTML = 'Tasks, Exams, Topics, Quick Notes, Activity History, Feedback';
            }
            if (deleteConfirmBtn) deleteConfirmBtn.textContent = 'Delete User';
        }

        if (deleteTargetName) deleteTargetName.textContent = username;
        if (deleteConfirmInput) deleteConfirmInput.value = '';
        if (deleteConfirmBtn) deleteConfirmBtn.disabled = true;

        if (deleteModal) deleteModal.classList.add('open');

        setTimeout(() => {
            if (deleteConfirmInput) deleteConfirmInput.focus();
        }, 100);
    }

    function closeDeleteModal() {
        pendingDeleteId = null;
        pendingDeleteUsername = null;
        if (deleteModal) deleteModal.classList.remove('open');
        if (deleteConfirmInput) deleteConfirmInput.value = '';
        if (deleteConfirmBtn) deleteConfirmBtn.disabled = true;
    }

    // Delete user
    async function deleteUser(userId) {
        try {
            const response = await fetch(`../../api/admin/users.php?id=${userId}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                showToast('User deleted successfully.', 'success');
                closeDeleteModal();
                fetchUsers();
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error deleting user', 'error');
        }
    }

    // --- Event Listeners ---

    // Search (debounced)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value;

            if (clearSearchBtn) {
                clearSearchBtn.style.display = query ? 'flex' : 'none';
            }

            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            searchTimeout = setTimeout(() => {
                searchQuery = query.trim();
                currentPage = 1;
                fetchUsers();
                searchTimeout = null;
            }, 300);
        });
    }

    // Clear search
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            searchQuery = '';
            currentPage = 1;
            this.style.display = 'none';
            fetchUsers();
            if (searchInput) searchInput.focus();
        });
    }

    // Role filter
    if (roleFilter) {
        roleFilter.addEventListener('change', function() {
            currentRole = this.value;
            currentPage = 1;
            fetchUsers();
        });
    }

    // Level filter
    if (levelFilter) {
        levelFilter.addEventListener('change', function() {
            currentLevel = this.value;
            currentPage = 1;
            fetchUsers();
        });
    }

    // Delete modal - cancel
    if (deleteCancelBtn) {
        deleteCancelBtn.addEventListener('click', closeDeleteModal);
    }

    // Delete modal - close (blocked state)
    if (deleteCloseBtn) {
        deleteCloseBtn.addEventListener('click', closeDeleteModal);
    }

    // Delete modal - click overlay to close
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });
    }

    // Delete modal - input validation
    if (deleteConfirmInput) {
        deleteConfirmInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (deleteConfirmBtn) {
                deleteConfirmBtn.disabled = value !== pendingDeleteUsername;
            }
        });
    }

    // Delete modal - confirm
    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', function() {
            if (pendingDeleteId && deleteConfirmInput && deleteConfirmInput.value.trim() === pendingDeleteUsername) {
                deleteUser(pendingDeleteId);
            }
        });
    }

    // Keyboard shortcuts: Ctrl+F to focus search, Escape to close modal/clear search
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }

        if (e.key === 'Escape') {
            if (deleteModal && deleteModal.classList.contains('open')) {
                closeDeleteModal();
            } else if (document.activeElement === searchInput && searchInput.value) {
                searchInput.value = '';
                searchQuery = '';
                if (clearSearchBtn) clearSearchBtn.style.display = 'none';
                currentPage = 1;
                fetchUsers();
                searchInput.blur();
            }
        }
    });

    // Initial load
    fetchUsers();
});