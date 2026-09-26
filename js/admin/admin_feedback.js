// js/admin/admin_feedback.js

document.addEventListener('DOMContentLoaded', () => {
    const feedbackTableBody = document.getElementById('feedbackTableBody');
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const statusTabs = document.getElementById('statusTabs');
    const typeFilter = document.getElementById('typeFilter');
    const paginationContainer = document.getElementById('paginationContainer');

    // Modal elements
    const feedbackModal = document.getElementById('feedbackModal');
    const modalSubject = document.getElementById('modalSubject');
    const modalUser = document.getElementById('modalUser');
    const modalEmail = document.getElementById('modalEmail');
    const modalType = document.getElementById('modalType');
    const modalDate = document.getElementById('modalDate');
    const modalBody = document.getElementById('modalBody');
    const modalStatusSelect = document.getElementById('modalStatusSelect');
    const modalSaveBtn = document.getElementById('modalSaveBtn');
    const modalDeleteBtn = document.getElementById('modalDeleteBtn');
    const modalCloseBtn = document.getElementById('modalCloseBtn');

    let searchQuery = '';
    let currentStatus = 'all';
    let currentType = 'all';
    let currentPage = 1;
    let searchTimeout = null;
    let currentFeedbackId = null;
    let currentFeedbackStatus = null;

    // Escape HTML
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Format date
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return 'N/A';
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    // Format datetime
    function formatDateTime(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return 'N/A';
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    // Format feedback type
    function formatType(type) {
        const map = {
            'bug_report': 'Bug Report',
            'feature_request': 'Feature Request',
            'general_feedback': 'General Feedback',
            'support': 'Support'
        };
        return map[type] || type;
    }

    // Truncate message
    function truncate(text, length) {
        if (!text) return '';
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    }

    // Show loading skeleton
    function showLoading() {
        feedbackTableBody.innerHTML = `
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="6"><div class="skeleton-line"></div></td></tr>
        `;
    }

    // Render status tabs with counts
    function renderStatusTabs(counts) {
        if (!statusTabs) return;

        const tabs = [
            { key: 'all', label: 'All', count: counts.all },
            { key: 'unread', label: 'Unread', count: counts.unread },
            { key: 'read', label: 'Read', count: counts.read },
            { key: 'resolved', label: 'Resolved', count: counts.resolved }
        ];

        statusTabs.innerHTML = tabs.map(tab => `
            <button class="status-tab status-tab-${tab.key} ${tab.key === currentStatus ? 'active' : ''}" data-status="${tab.key}">
                ${tab.label}
                <span class="tab-count">${tab.count}</span>
            </button>
        `).join('');

        statusTabs.querySelectorAll('.status-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                currentStatus = this.dataset.status;
                currentPage = 1;
                fetchFeedback();
            });
        });
    }

    // Render feedback table
    function renderFeedback(feedback) {
        if (!feedback || feedback.length === 0) {
            feedbackTableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-comment"></i>
                            <span class="empty-title">No feedback found</span>
                            <span class="empty-sub">Try adjusting your search or filters.</span>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        feedbackTableBody.innerHTML = feedback.map(item => `
            <tr data-id="${item.id}" class="${item.status === 'unread' ? 'unread-row' : ''}">
                <td>${item.id}</td>
                <td>
                    <a href="admin_user_profile.php?id=${item.user_id}" class="user-link">
                        ${escapeHtml(item.username)}
                    </a>
                </td>
                <td><span class="type-badge type-${item.type}">${formatType(item.type)}</span></td>
                <td class="subject-cell">${escapeHtml(truncate(item.subject, 40))}</td>
                <td><span class="status-badge status-${item.status}">${escapeHtml(item.status)}</span></td>
                <td>${formatDate(item.created_at)}</td>
                <td class="actions-cell">
                    <button class="btn-icon view-btn" data-id="${item.id}" title="View Feedback">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon delete-btn" data-id="${item.id}" title="Delete Feedback">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        // Attach event listeners
        feedbackTableBody.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                const item = feedback.find(f => f.id === id);
                if (item) openFeedbackModal(item);
            });
        });

        feedbackTableBody.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                const item = feedback.find(f => f.id === id);
                if (item) {
                    confirmDeleteFeedback(id, item.subject);
                }
            });
        });
    }

    // Render pagination
    function renderPagination(pagination) {
        if (!paginationContainer) return;

        const { current_page, total_pages, total, per_page } = pagination;

        if (total_pages <= 1) {
            paginationContainer.innerHTML = `<span class="pagination-info">Showing ${total} feedback item${total !== 1 ? 's' : ''}</span>`;
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
                    fetchFeedback();
                }
            });
        });
    }

    // Fetch feedback
    async function fetchFeedback() {
        showLoading();

        try {
            const params = new URLSearchParams();
            if (searchQuery) params.append('search', searchQuery);
            if (currentStatus !== 'all') params.append('status', currentStatus);
            if (currentType !== 'all') params.append('type', currentType);
            params.append('page', currentPage);

            const response = await fetch(`../../api/admin/feedback.php?${params.toString()}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                renderStatusTabs(result.data.counts);
                renderFeedback(result.data.feedback);
                renderPagination(result.data.pagination);
            } else {
                showToast('Failed to load feedback: ' + result.message, 'error');
                feedbackTableBody.innerHTML = `
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
            showToast('Network error loading feedback', 'error');
            feedbackTableBody.innerHTML = `
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

    // --- Feedback Modal ---

    function openFeedbackModal(item) {
        currentFeedbackId = item.id;
        currentFeedbackStatus = item.status;

        if (modalSubject) modalSubject.textContent = item.subject;
        if (modalUser) modalUser.textContent = item.username;
        if (modalEmail) modalEmail.textContent = item.email;
        if (modalType) modalType.innerHTML = `<span class="type-badge type-${item.type}">${formatType(item.type)}</span>`;
        if (modalDate) modalDate.textContent = formatDateTime(item.created_at);
        if (modalBody) modalBody.textContent = item.message;
        if (modalStatusSelect) modalStatusSelect.value = item.status;

        if (feedbackModal) feedbackModal.classList.add('open');
    }

    function closeFeedbackModal() {
        currentFeedbackId = null;
        currentFeedbackStatus = null;
        if (feedbackModal) feedbackModal.classList.remove('open');
    }

    // Update feedback status
    async function updateFeedbackStatus(id, newStatus) {
        try {
            const response = await fetch(`../../api/admin/feedback.php?id=${id}`, {
                method: 'PUT',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: newStatus })
            });
            const result = await response.json();

            if (result.success) {
                showToast('Status updated successfully', 'success');
                closeFeedbackModal();
                fetchFeedback();
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error updating status', 'error');
        }
    }

    // Delete feedback
    async function deleteFeedback(id) {
        try {
            const response = await fetch(`../../api/admin/feedback.php?id=${id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                showToast('Feedback deleted successfully', 'success');
                closeFeedbackModal();
                fetchFeedback();
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error deleting feedback', 'error');
        }
    }

    // Confirm delete
    function confirmDeleteFeedback(id, subject) {
        showConfirm(`Delete feedback "${subject}"? This action cannot be undone.`, function() {
            deleteFeedback(id);
        });
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
                fetchFeedback();
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
            fetchFeedback();
            if (searchInput) searchInput.focus();
        });
    }

    // Type filter
    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            currentType = this.value;
            currentPage = 1;
            fetchFeedback();
        });
    }

    // Modal - close button
    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', closeFeedbackModal);
    }

    // Modal - click overlay to close
    if (feedbackModal) {
        feedbackModal.addEventListener('click', function(e) {
            if (e.target === feedbackModal) {
                closeFeedbackModal();
            }
        });
    }

    // Modal - save status
    if (modalSaveBtn) {
        modalSaveBtn.addEventListener('click', function() {
            if (currentFeedbackId && modalStatusSelect) {
                const newStatus = modalStatusSelect.value;
                if (newStatus === currentFeedbackStatus) {
                    showToast('Status is unchanged.', 'warning');
                    return;
                }
                updateFeedbackStatus(currentFeedbackId, newStatus);
            }
        });
    }

    // Modal - delete
    if (modalDeleteBtn) {
        modalDeleteBtn.addEventListener('click', function() {
            if (currentFeedbackId && modalSubject) {
                confirmDeleteFeedback(currentFeedbackId, modalSubject.textContent);
            }
        });
    }

    // Keyboard shortcuts: Ctrl+F to focus search, Escape to close modal
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }

        if (e.key === 'Escape') {
            if (feedbackModal && feedbackModal.classList.contains('open')) {
                closeFeedbackModal();
            } else if (document.activeElement === searchInput && searchInput.value) {
                searchInput.value = '';
                searchQuery = '';
                if (clearSearchBtn) clearSearchBtn.style.display = 'none';
                currentPage = 1;
                fetchFeedback();
                searchInput.blur();
            }
        }
    });

    // Initial load
    fetchFeedback();
});