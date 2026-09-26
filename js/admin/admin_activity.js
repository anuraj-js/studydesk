// js/admin/admin_activity.js

document.addEventListener('DOMContentLoaded', () => {
    const activityTableBody = document.getElementById('activityTableBody');
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const userFilter = document.getElementById('userFilter');
    const typeFilter = document.getElementById('typeFilter');
    const paginationContainer = document.getElementById('paginationContainer');

    let searchQuery = '';
    let currentUserId = 0;
    let currentType = 'all';
    let currentPage = 1;
    let searchTimeout = null;

    // Escape HTML
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Safely parse JSON
    function parseDetails(details) {
        if (!details) return {};
        if (typeof details !== 'string') return details;
        try {
            return JSON.parse(details);
        } catch (e) {
            return {};
        }
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

    // Get activity icon
    function getActivityIcon(type) {
        const icons = {
            'task_completed': '<i class="fas fa-check-circle" style="color: var(--color-success);"></i>',
            'exam_created': '<i class="fas fa-pen" style="color: var(--color-primary);"></i>',
            'topic_completed': '<i class="fas fa-check-circle" style="color: var(--color-success);"></i>',
            'exam_completed': '<i class="fas fa-flag-checkered" style="color: var(--color-primary);"></i>',
            'pomodoro_completed': '<i class="fas fa-clock" style="color: var(--color-primary);"></i>'
        };
        return icons[type] || '<i class="fas fa-circle"></i>';
    }

    // Get activity display text
    function getActivityDisplay(type, itemName, details) {
        const labels = {
            'task_completed': 'Task Completed',
            'exam_created': 'Exam Created',
            'topic_completed': 'Topic Completed',
            'exam_completed': 'Exam Completed',
            'pomodoro_completed': 'Pomodoro Session'
        };

        const label = labels[type] || 'Activity';

        if (type === 'pomodoro_completed' && details && details.total_seconds !== undefined) {
            const totalSecs = details.total_seconds;
            const mins = Math.floor(totalSecs / 60);
            const secs = totalSecs % 60;

            let timeStr = '';
            if (mins > 0 && secs > 0) {
                timeStr = `${mins}m ${secs}s`;
            } else if (mins > 0) {
                timeStr = `${mins}m`;
            } else {
                timeStr = `${secs}s`;
            }

            return `${itemName} — ${label} (${timeStr})`;
        }

        return `${itemName} — ${label}`;
    }

    // Show loading skeleton
    function showLoading() {
        activityTableBody.innerHTML = `
            <tr class="skeleton-row"><td colspan="5"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="5"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="5"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="5"><div class="skeleton-line"></div></td></tr>
            <tr class="skeleton-row"><td colspan="5"><div class="skeleton-line"></div></td></tr>
        `;
    }

    // Render activities
    function renderActivities(activities) {
        if (!activities || activities.length === 0) {
            activityTableBody.innerHTML = `
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <span class="empty-title">No activity found</span>
                            <span class="empty-sub">Try adjusting your search or filters.</span>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        activityTableBody.innerHTML = activities.map(activity => {
            const details = parseDetails(activity.additional_details);
            const displayText = getActivityDisplay(activity.activity_type, activity.related_item_name, details);
            const icon = getActivityIcon(activity.activity_type);

            return `
                <tr>
                    <td>
                        <a href="admin_user_profile.php?id=${activity.user_id}" class="user-link">
                            ${escapeHtml(activity.username)}
                        </a>
                    </td>
                    <td>${icon} <span class="activity-type-label">${escapeHtml(activity.activity_type.replace(/_/g, ' '))}</span></td>
                    <td class="activity-text-cell">${escapeHtml(displayText)}</td>
                    <td class="date-cell">${formatDateTime(activity.created_at)}</td>
                    <td class="actions-cell">
                        <a href="admin_user_profile.php?id=${activity.user_id}" class="btn-icon" title="View User">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Render pagination
    function renderPagination(pagination) {
        if (!paginationContainer) return;

        const { current_page, total_pages, total, per_page } = pagination;

        if (total_pages <= 1) {
            paginationContainer.innerHTML = `<span class="pagination-info">Showing ${total} activit${total !== 1 ? 'ies' : 'y'}</span>`;
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
                    fetchActivities();
                }
            });
        });
    }

    // Fetch activities
    async function fetchActivities() {
        showLoading();

        try {
            const params = new URLSearchParams();
            if (searchQuery) params.append('search', searchQuery);
            if (currentUserId > 0) params.append('user_id', currentUserId);
            if (currentType !== 'all') params.append('type', currentType);
            params.append('page', currentPage);

            const response = await fetch(`../../api/admin/user_activity.php?${params.toString()}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                renderActivities(result.data.activities);
                renderPagination(result.data.pagination);
            } else {
                showToast('Failed to load activity: ' + result.message, 'error');
                activityTableBody.innerHTML = `
                    <tr>
                        <td colspan="5">
                            <div class="error-state">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>${escapeHtml(result.message)}</span>
                            </div>
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            showToast('Network error loading activity', 'error');
            activityTableBody.innerHTML = `
                <tr>
                    <td colspan="5">
                        <div class="error-state">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Network error. Please try again.</span>
                        </div>
                    </td>
                </tr>
            `;
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
                fetchActivities();
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
            fetchActivities();
            if (searchInput) searchInput.focus();
        });
    }

    // User filter
    if (userFilter) {
        userFilter.addEventListener('change', function() {
            currentUserId = parseInt(this.value) || 0;
            currentPage = 1;
            fetchActivities();
        });
    }

    // Type filter
    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            currentType = this.value;
            currentPage = 1;
            fetchActivities();
        });
    }

    // Keyboard shortcuts: Ctrl+F to focus search, Escape to clear
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }

        if (e.key === 'Escape' && document.activeElement === searchInput && searchInput.value) {
            searchInput.value = '';
            searchQuery = '';
            if (clearSearchBtn) clearSearchBtn.style.display = 'none';
            currentPage = 1;
            fetchActivities();
            searchInput.blur();
        }
    });

    // --- Initial Load ---
    // Check for user_id from URL (came from user profile page)
    const urlParams = new URLSearchParams(window.location.search);
    const urlUserId = urlParams.get('user_id');
    if (urlUserId && !isNaN(parseInt(urlUserId))) {
        currentUserId = parseInt(urlUserId);
        if (userFilter) {
            userFilter.value = urlUserId;
        }
    }

    fetchActivities();
});