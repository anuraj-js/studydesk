document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await apiFetch('../../api/admin/home.php');
        
        if (!res.success) {
            showToast(res.message, 'error');
            return;
        }

        const data = res.data;

        const stats = data.stats;
        const statEls = {
            statTotalUsers: stats.total_users,
            statTotalTasks: stats.total_tasks,
            statTotalExams: stats.total_exams,
            statTotalSessions: stats.total_sessions
        };

        for (const [id, value] of Object.entries(statEls)) {
            const el = document.getElementById(id);
            el.classList.remove('skeleton');
            el.textContent = value;
        }

        const signupsEl = document.getElementById('recentSignups');
        if (data.recent_signups.length === 0) {
            signupsEl.innerHTML = '<div class="empty-state">No users registered yet.</div>';
        } else {
            signupsEl.innerHTML = data.recent_signups.map(u => {
                // Convert UTC to Nepal Time
                const date = new Date(u.created_at.replace(' ', 'T') + 'Z').toLocaleDateString('en-US', { 
                    month: 'short', day: 'numeric', timeZone: 'Asia/Kathmandu' 
                });
                
                const statusBadge = u.is_active 
                    ? '<span class="badge badge-low">Active</span>' 
                    : '<span class="badge badge-high">Inactive</span>';
                
                return `
                    <div class="task-item">
                        <div class="task-content">
                            <div class="task-title">${esc(u.full_name)} <span style="font-weight: 400; color: var(--text-muted);">(${esc(u.username)})</span></div>
                            <div class="task-due"><i class="fa-solid fa-envelope"></i> ${esc(u.email)} &middot; Joined ${date}</div>
                        </div>
                        ${statusBadge}
                    </div>
                `;
            }).join('');
        }

        const actionsEl = document.getElementById('recentActions');
        if (data.recent_actions.length === 0) {
            actionsEl.innerHTML = '<div class="empty-state">No admin actions logged yet.</div>';
        } else {
            actionsEl.innerHTML = data.recent_actions.map(a => {
                // Convert UTC to Nepal Time
                const date = new Date(a.created_at.replace(' ', 'T') + 'Z').toLocaleString('en-US', { 
                    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Kathmandu'
                });
                
                return `
                    <div class="task-item">
                        <div class="task-content">
                            <div class="task-title">${esc(a.admin_name)} <span style="font-weight: 400;">${esc(a.action)}</span></div>
                            <div class="task-due"><i class="fa-solid fa-clock"></i> ${date}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

    } catch (err) {
        showToast('Failed to load dashboard data.', 'error');
    }
});