// js/activity.js

document.addEventListener('DOMContentLoaded', () => {
    const activityList = document.getElementById('activityList');
    const searchInput = document.getElementById('activitySearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const searchInfo = document.getElementById('searchInfo');

    let allActivities = [];
    let searchQuery = '';
    let searchTimeout = null;

    // Activity icons (consistent with dashboard)
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

    // Activity display text with seconds for Pomodoro
    function getActivityDisplay(type, itemName, details) {
        const labels = {
            'task_completed': 'Task Completed',
            'exam_created': 'Exam Created',
            'topic_completed': 'Topic Completed',
            'exam_completed': 'Exam Completed',
            'pomodoro_completed': 'Pomodoro Session'
        };

        let label = labels[type] || 'Activity';

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

    // Format date
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    // Format date header
    function formatDateHeader(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Highlight matching text
    function highlightText(text, query) {
        if (!query || !text) return text;
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return text.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    // Filter activities by search query (AND logic)
    function filterActivities(activities, query) {
        if (!query) return activities;

        const terms = query.split(/\s+/);

        return activities.filter(activity => {
            const details = typeof activity.details === 'string'
                ? JSON.parse(activity.details)
                : activity.details;

            const displayText = getActivityDisplay(activity.type, activity.item_name, details);

            return terms.every(term => {
                const searchableText = [
                    activity.item_name,
                    activity.type,
                    displayText
                ].join(' ').toLowerCase();

                return searchableText.includes(term);
            });
        });
    }

    // Group activities by date
    function groupByDate(activities) {
        const groups = {};
        activities.forEach(activity => {
            const dateKey = activity.created_at.split(' ')[0];
            if (!groups[dateKey]) {
                groups[dateKey] = [];
            }
            groups[dateKey].push(activity);
        });
        return groups;
    }

    // Render activities
    function renderActivities(activities) {
        if (activities.length === 0) {
            const message = searchQuery ? 'No activities match your search' : 'No activities found';
            activityList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    ${message}
                    <div class="empty-sub">${searchQuery ? 'Try a different search term.' : 'Complete a task or start a Pomodoro session to get started.'}</div>
                </div>
            `;
            return;
        }

        const grouped = groupByDate(activities);
        const sortedDates = Object.keys(grouped).sort((a, b) => new Date(b) - new Date(a));

        let html = '';
        sortedDates.forEach(dateKey => {
            const items = grouped[dateKey];
            html += `
                <div class="date-group">
                    <div class="date-header">${formatDateHeader(dateKey)}</div>
            `;
            items.forEach(activity => {
                const details = typeof activity.details === 'string'
                    ? JSON.parse(activity.details)
                    : activity.details;

                const displayText = getActivityDisplay(activity.type, activity.item_name, details);
                const highlightedText = highlightText(escapeHtml(displayText), searchQuery);

                html += `
                    <div class="activity-item">
                        <div class="activity-left">
                            <span class="activity-icon">${getActivityIcon(activity.type)}</span>
                            <span class="activity-text">${highlightedText}</span>
                        </div>
                        <span class="activity-time">${formatDate(activity.created_at)}</span>
                    </div>
                `;
            });
            html += `</div>`;
        });

        activityList.innerHTML = html;
    }

    // Update search info
    function updateSearchInfo(filteredCount, totalCount) {
        if (!searchInfo) return;

        if (searchQuery) {
            if (filteredCount > 0 && filteredCount !== totalCount) {
                searchInfo.innerHTML = `<i class="fas fa-search"></i> Found ${filteredCount} of ${totalCount} activities matching "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info has-results';
            } else if (filteredCount === 0) {
                searchInfo.innerHTML = `<i class="fas fa-exclamation-circle"></i> No activities found matching "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info no-results';
            } else {
                searchInfo.innerHTML = `<i class="fas fa-search"></i> All ${filteredCount} activities match "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info has-results';
            }
        } else {
            searchInfo.style.display = 'none';
            searchInfo.className = 'search-info';
        }
    }

    // Process and render with current search
    function processActivities() {
        const filtered = filterActivities(allActivities, searchQuery);
        updateSearchInfo(filtered.length, allActivities.length);
        renderActivities(filtered);
    }

    // Fetch activities
    async function fetchActivities() {
        try {
            const response = await fetch('../api/activity/fetch.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                allActivities = result.data.activities || [];
                processActivities();
            } else {
                showToast('Failed to load activities: ' + result.message, 'error');
                activityList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle"></i>
                        ${result.message}
                    </div>
                `;
            }
        } catch (error) {
            showToast('Network error loading activities', 'error');
            activityList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-circle"></i>
                    Failed to load activities
                </div>
            `;
        }
    }

    // --- Search Event Listeners ---

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value;

            if (clearSearchBtn) {
                clearSearchBtn.style.display = query ? 'block' : 'none';
            }

            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            searchTimeout = setTimeout(() => {
                searchQuery = query.toLowerCase().trim();
                processActivities();
                searchTimeout = null;
            }, 300);
        });

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }

            if (e.key === 'Escape' && document.activeElement === searchInput) {
                e.preventDefault();
                searchInput.value = '';
                searchQuery = '';
                if (clearSearchBtn) {
                    clearSearchBtn.style.display = 'none';
                }
                processActivities();
                searchInput.blur();
            }
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchQuery = '';
            this.style.display = 'none';
            processActivities();
            searchInput.focus();
            if (searchInfo) {
                searchInfo.style.display = 'none';
            }
        });
    }

    // Initial load
    fetchActivities();
});