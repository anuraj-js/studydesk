document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        window.location.href = 'users.php';
        return;
    }

    try {
        const res = await apiFetch(`../../api/admin/view_user.php?id=${userId}`);
        if (!res.success) {
            showToast(res.message, 'error');
            return;
        }

        const data = res.data;
        const user = data.user;

        const joinedDate = new Date(user.created_at.replace(' ', 'T') + 'Z').toLocaleDateString('en-US', { 
            month: 'long', day: 'numeric', year: 'numeric', timeZone: 'Asia/Kathmandu'
        });

        // Removed Date of Birth from the grid
        const profileHtml = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div><span style="color: var(--text-muted); font-size: 0.8rem;">Full Name</span><div>${esc(user.full_name)}</div></div>
                <div><span style="color: var(--text-muted); font-size: 0.8rem;">Username</span><div>${esc(user.username)}</div></div>
                <div><span style="color: var(--text-muted); font-size: 0.8rem;">Email</span><div>${esc(user.email)}</div></div>
                <div><span style="color: var(--text-muted); font-size: 0.8rem;">Academic Level</span><div>${esc(user.academic_level)}</div></div>
                <div><span style="color: var(--text-muted); font-size: 0.8rem;">Gender</span><div>${esc(user.gender)}</div></div>
                <div><span style="color: var(--text-muted); font-size: 0.8rem;">Joined</span><div>${joinedDate}</div></div>
                <div><span style="color: var(--text-muted); font-size: 0.8rem;">Status</span><div>${user.is_active ? '<span class="badge badge-low">Active</span>' : '<span class="badge badge-high">Inactive</span>'}</div></div>
            </div>
        `;
        document.getElementById('profileInfo').innerHTML = profileHtml;

        const tasksEl = document.getElementById('userTasks');
        if (data.tasks.length === 0) {
            tasksEl.innerHTML = '<div class="empty-state">No tasks created.</div>';
        } else {
            tasksEl.innerHTML = data.tasks.map(t => `
                <div class="task-item ${t.completed ? 'completed' : ''}" style="margin-bottom: 0.5rem;">
                    <div class="task-content">
                        <div class="task-title">${esc(t.title)}</div>
                        ${t.due_date ? `<div class="task-due"><i class="fa-regular fa-calendar"></i> ${t.due_date}</div>` : ''}
                    </div>
                    <span class="badge badge-${t.priority}">${t.priority}</span>
                </div>
            `).join('');
        }

        const examsEl = document.getElementById('userExams');
        if (data.exams.length === 0) {
            examsEl.innerHTML = '<div class="empty-state">No exams created.</div>';
        } else {
            examsEl.innerHTML = data.exams.map(e => `
                <div class="exam-item" style="margin-bottom: 0.5rem;">
                    <div>
                        <div class="exam-subject">${esc(e.subject)}</div>
                        <div class="exam-date">${esc(e.name)} &middot; ${new Date(e.date + 'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric', year: 'numeric'})}</div>
                    </div>
                </div>
            `).join('');
        }

        const notesEl = document.getElementById('userNotes');
        if (data.notes.length === 0) {
            notesEl.innerHTML = '<div class="empty-state">No notes created.</div>';
        } else {
            notesEl.innerHTML = data.notes.map(n => {
                const date = new Date(n.updated_at.replace(' ', 'T') + 'Z').toLocaleDateString('en-US', { 
                    month: 'short', day: 'numeric', year: 'numeric', timeZone: 'Asia/Kathmandu'
                });
                return `
                    <div class="task-item" style="margin-bottom: 0.5rem;">
                        <div class="task-content">
                            <div class="task-title">${esc(n.title || 'Untitled Note')}</div>
                            <div class="task-due"><i class="fa-regular fa-clock"></i> Updated ${date}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

    } catch (err) {
        showToast('Failed to load user details.', 'error');
    }
});