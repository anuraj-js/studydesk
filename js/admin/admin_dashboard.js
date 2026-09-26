// js/admin/admin_dashboard.js

document.addEventListener('DOMContentLoaded', () => {
    const statUsers = document.getElementById('statUsers');
    const statTasks = document.getElementById('statTasks');
    const statExams = document.getElementById('statExams');
    const statActivity = document.getElementById('statActivity');

    // Show loading state
    function showLoading() {
        if (statUsers) statUsers.textContent = '...';
        if (statTasks) statTasks.textContent = '...';
        if (statExams) statExams.textContent = '...';
        if (statActivity) statActivity.textContent = '...';
    }

    // Fetch stats
    async function fetchStats() {
        showLoading();

        try {
            const response = await fetch('../../api/admin/stats.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                if (statUsers) statUsers.textContent = result.data.users;
                if (statTasks) statTasks.textContent = result.data.tasks;
                if (statExams) statExams.textContent = result.data.exams;
                if (statActivity) statActivity.textContent = result.data.activity;
            } else {
                showToast('Failed to load stats: ' + result.message, 'error');
                if (statUsers) statUsers.textContent = '—';
                if (statTasks) statTasks.textContent = '—';
                if (statExams) statExams.textContent = '—';
                if (statActivity) statActivity.textContent = '—';
            }
        } catch (error) {
            showToast('Network error loading stats', 'error');
            if (statUsers) statUsers.textContent = '—';
            if (statTasks) statTasks.textContent = '—';
            if (statExams) statExams.textContent = '—';
            if (statActivity) statActivity.textContent = '—';
        }
    }

    // Initial load
    fetchStats();
});