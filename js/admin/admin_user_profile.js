// js/admin/admin_user_profile.js

document.addEventListener('DOMContentLoaded', () => {
    const profileContainer = document.getElementById('userProfileContainer');

    // Escape HTML
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Format date (e.g., Sep 8, 2026)
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

    // Format datetime (e.g., Sep 8, 2026 10:30 AM)
    function formatDateTime(dateStr) {
        if (!dateStr) return 'Never';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return 'Never';
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    // Capitalize first letter
    function capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Format academic level
    function formatLevel(level) {
        const map = {
            'school': 'School Level',
            'plus_two': '+2 Level',
            'bachelor': 'Bachelor Level',
            'master': 'Master Level',
            'others': 'Others'
        };
        return map[level] || capitalize(level);
    }

    // Show loading state
    function showLoading() {
        profileContainer.innerHTML = `
            <div class="profile-loading">
                <div class="skeleton skeleton-title"></div>
                <div class="skeleton skeleton-card"></div>
                <div class="skeleton skeleton-card"></div>
            </div>
        `;
    }

    // Show error state
    function showError(message) {
        profileContainer.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <span class="error-title">Failed to load profile</span>
                <span class="error-sub">${escapeHtml(message)}</span>
                <a href="admin_users.php" class="btn btn-secondary" style="margin-top: var(--space-3);">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        `;
    }

    // Render profile
    function renderProfile(user, summary) {
        const phone = user.phone ? escapeHtml(user.phone) : '<span class="text-muted">Not provided</span>';
        const address = user.address ? escapeHtml(user.address) : '<span class="text-muted">Not provided</span>';
        const lastActive = formatDateTime(summary.last_active);
        const hasQuickNote = summary.has_quick_note
            ? '<span class="text-success"><i class="fas fa-check-circle"></i> Yes</span>'
            : '<span class="text-muted">No</span>';

        profileContainer.innerHTML = `
            <!-- Header -->
            <div class="profile-page-header">
                <a href="admin_users.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <div class="profile-title-block">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div>
                        <h2 class="profile-username">${escapeHtml(user.username)}</h2>
                        <span class="role-badge role-${user.role}">${escapeHtml(user.role)}</span>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="profile-card">
                <h3 class="profile-card-title">
                    <i class="fas fa-info-circle"></i> Account Information
                </h3>
                <div class="profile-grid">
                    <div class="profile-row">
                        <span class="profile-label">User ID</span>
                        <span class="profile-value">${user.id}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Username</span>
                        <span class="profile-value">${escapeHtml(user.username)}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Email</span>
                        <span class="profile-value">${escapeHtml(user.email)}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Role</span>
                        <span class="profile-value"><span class="role-badge role-${user.role}">${escapeHtml(user.role)}</span></span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Academic Level</span>
                        <span class="profile-value">${formatLevel(user.academic_level)}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Gender</span>
                        <span class="profile-value">${capitalize(user.gender)}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Date of Birth</span>
                        <span class="profile-value">${formatDate(user.dob)}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Joined</span>
                        <span class="profile-value">${formatDate(user.created_at)}</span>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="profile-card">
                <h3 class="profile-card-title">
                    <i class="fas fa-address-book"></i> Contact Information
                </h3>
                <div class="profile-grid">
                    <div class="profile-row">
                        <span class="profile-label">Phone</span>
                        <span class="profile-value">${phone}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Address</span>
                        <span class="profile-value">${address}</span>
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="profile-card">
                <h3 class="profile-card-title">
                    <i class="fas fa-chart-bar"></i> Activity Summary
                </h3>
                <div class="summary-grid">
                    <div class="summary-stat">
                        <div class="summary-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="summary-number">${summary.tasks_completed}</div>
                        <div class="summary-label">Tasks Completed</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-icon"><i class="fas fa-pen"></i></div>
                        <div class="summary-number">${summary.exams_created}</div>
                        <div class="summary-label">Exams Created</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-icon"><i class="fas fa-list"></i></div>
                        <div class="summary-number">${summary.topics_completed}</div>
                        <div class="summary-label">Topics Completed</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-icon"><i class="fas fa-flag-checkered"></i></div>
                        <div class="summary-number">${summary.exams_completed}</div>
                        <div class="summary-label">Exams Completed</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-icon"><i class="fas fa-clock"></i></div>
                        <div class="summary-number">${summary.pomodoro_sessions}</div>
                        <div class="summary-label">Pomodoro Sessions</div>
                    </div>
                </div>
                <div class="profile-grid" style="margin-top: var(--space-4);">
                    <div class="profile-row">
                        <span class="profile-label">Total Activities</span>
                        <span class="profile-value">${summary.total_activities}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Active Tasks</span>
                        <span class="profile-value">${summary.active_tasks}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Active Exams</span>
                        <span class="profile-value">${summary.active_exams}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Has Quick Note</span>
                        <span class="profile-value">${hasQuickNote}</span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Last Active</span>
                        <span class="profile-value">${lastActive}</span>
                    </div>
                </div>
                <a href="admin_activity.php?user_id=${user.id}" class="btn btn-primary" style="margin-top: var(--space-4);">
                    <i class="fas fa-history"></i> View Full Activity
                </a>
            </div>
        `;
    }

    // Fetch profile
    async function fetchProfile() {
        const params = new URLSearchParams(window.location.search);
        const userId = params.get('id');

        if (!userId || isNaN(parseInt(userId))) {
            showError('Invalid user ID');
            return;
        }

        showLoading();

        try {
            const response = await fetch(`../../api/admin/user_profile.php?id=${userId}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                renderProfile(result.data.user, result.data.summary);
            } else {
                showError(result.message);
                showToast('Failed to load profile: ' + result.message, 'error');
            }
        } catch (error) {
            showError('Network error. Please try again.');
            showToast('Network error loading profile', 'error');
        }
    }

    // Initial load
    fetchProfile();
});