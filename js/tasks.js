let activeFilter = 'all';
let allTasks = [];
let searchQuery = '';

function isOverdue(task) {
    if (task.completed || !task.due_date) return false;
    const today = new Date(); today.setHours(0,0,0,0);
    return new Date(task.due_date + 'T00:00:00') < today;
}

function renderTasks() {
    // 1. Apply Status Filter
    let list;
    if      (activeFilter === 'active')    list = allTasks.filter(t => !t.completed);
    else if (activeFilter === 'completed') list = allTasks.filter(t =>  t.completed);
    else if (activeFilter === 'overdue')   list = allTasks.filter(t => isOverdue(t));
    else                                   list = allTasks;

    // 2. Apply Search Query
    if (searchQuery) {
        const q = searchQuery.toLowerCase();
        list = list.filter(t => t.title.toLowerCase().includes(q));
    }

    // Update overdue tab count badge (based on ALL tasks, not filtered)
    const overdueCount = allTasks.filter(t => isOverdue(t)).length;
    const badge = document.getElementById('overdueCount');
    if (badge) {
        badge.textContent = overdueCount;
        badge.style.display = overdueCount > 0 ? 'inline-block' : 'none';
    }

    const el = document.getElementById('taskList');

    if (!list.length) {
        const emptyMsg = searchQuery 
            ? 'No tasks match your search' 
            : (activeFilter === 'overdue' ? 'No overdue tasks' : 'Nothing here');
        el.innerHTML = `<div class="empty-state">${emptyMsg}</div>`;
        return;
    }

    el.innerHTML = list.map(t => {
        const overdue = isOverdue(t);
        // Use the highlight helper instead of esc(t.title)
        const highlightedTitle = highlightText(t.title, searchQuery); 

        return `
            <div class="task-item ${t.completed ? 'completed' : ''}">
                <input type="checkbox" class="task-checkbox" data-id="${t.id}" ${t.completed ? 'checked' : ''} />
                <div class="task-content">
                    <div class="task-title">${highlightedTitle}</div>
                    ${t.due_date ? `<div class="task-due ${overdue ? 'overdue' : ''}">
                        <i class="fa-regular fa-calendar"></i> ${t.due_date}
                        ${overdue ? ' &middot; <strong>Overdue</strong>' : ''}
                    </div>` : ''}
                </div>
                <div class="task-meta">
                    <span class="badge badge-${t.priority}">${t.priority}</span>
                    <button class="btn btn-ghost btn-sm edit-btn" data-id="${t.id}">
                        <i class="fa-solid fa-pen"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm delete-btn" data-id="${t.id}">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
    }).join('');

    attachTaskEvents();
}

function attachTaskEvents() {
    const el = document.getElementById('taskList');

    // Toggle Checkboxes
    el.querySelectorAll('.task-checkbox').forEach(cb => {
        cb.addEventListener('change', async () => {
            const id = cb.dataset.id;
            try {
                const res = await apiFetch('../api/tasks.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'toggle', id: id })
                });
                if (res.success) {
                    const task = allTasks.find(t => t.id == id);
                    if (task) task.completed = res.data.completed;
                    renderTasks();
                } else {
                    showToast(res.message, 'error');
                    cb.checked = !cb.checked;
                }
            } catch (err) {
                showToast('Failed to toggle task.', 'error');
                cb.checked = !cb.checked;
            }
        });
    });

    // Delete Buttons
    el.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            showDialog({
                title: 'Delete Task',
                message: 'Are you sure you want to delete this task?',
                confirmText: 'Delete',
                onConfirm: async () => {
                    try {
                        const res = await apiFetch('../api/tasks.php', {
                            method: 'POST',
                            body: JSON.stringify({ action: 'delete', id: id })
                        });
                        if (res.success) {
                            allTasks = allTasks.filter(t => t.id != id);
                            renderTasks();
                            showToast('Task deleted.', 'success');
                        } else {
                            showToast(res.message, 'error');
                        }
                    } catch (err) {
                        showToast('Failed to delete task.', 'error');
                    }
                }
            });
        });
    });

    // Edit Buttons
    el.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            openEditTaskModal(btn.dataset.id);
        });
    });
}

// ── Edit Modal Logic ──
function openEditTaskModal(id) {
    const task = allTasks.find(t => t.id == id);
    if (!task) return;

    document.getElementById('editTaskId').value = task.id;
    document.getElementById('editTaskTitle').value = task.title;
    document.getElementById('editTaskPriority').value = task.priority || 'medium';
    document.getElementById('editTaskDue').value = task.due_date || '';

    document.getElementById('editTaskModal').style.display = 'flex';
}

function closeEditTaskModal() {
    document.getElementById('editTaskModal').style.display = 'none';
    document.getElementById('editTaskForm').reset();
}

// ── Initialization ──
document.addEventListener('DOMContentLoaded', () => {
    async function loadTasks() {
        try {
            const res = await apiFetch('../api/tasks.php');
            if (res.success) {
                allTasks = res.data;
                renderTasks();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to load tasks.', 'error');
        }
    }
    loadTasks();

    // Search Input
    document.getElementById('searchTask').addEventListener('input', (e) => {
        searchQuery = e.target.value;
        renderTasks();
    });

    // Filter Tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            activeFilter = tab.dataset.filter;
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            renderTasks();
        });
    });

    // Add Task Form (inside DOMContentLoaded)
    document.getElementById('taskForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById('addTaskBtn');
        const originalText = submitBtn.innerHTML;

        const title = document.getElementById('taskTitle').value.trim();
        const priority = document.getElementById('taskPriority').value;
        const due_date = document.getElementById('taskDue').value;

        if (!title) return;

        // New validation
        if (due_date && Validation.isPastDate(due_date)) {
            showToast('Due date cannot be in the past.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';

        try {
            const [res] = await Promise.all([
                apiFetch('../api/tasks.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'create', title, priority, due_date })
                }),
                new Promise(r => setTimeout(r, 500))
            ]);

            if (res.success) {
                document.getElementById('taskForm').reset();
                document.getElementById('searchTask').value = ''; 
                searchQuery = ''; 
                document.querySelector('.collapsible-card').removeAttribute('open');
                showToast('Task added successfully.', 'success');
                loadTasks();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to add task.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    // Edit Task Form (inside DOMContentLoaded)
    document.getElementById('editTaskForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById('saveTaskBtn');
        const originalText = submitBtn.innerHTML;

        const id = document.getElementById('editTaskId').value;
        const title = document.getElementById('editTaskTitle').value.trim();
        const priority = document.getElementById('editTaskPriority').value;
        const due_date = document.getElementById('editTaskDue').value;

        if (!title) return;

        // New validation
        if (due_date && Validation.isPastDate(due_date)) {
            showToast('Due date cannot be in the past.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const [res] = await Promise.all([
                apiFetch('../api/tasks.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'update', id, title, priority, due_date })
                }),
                new Promise(r => setTimeout(r, 500))
            ]);

            if (res.success) {
                closeEditTaskModal();
                showToast('Task updated.', 'success');
                loadTasks();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to update task.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    // Modal Close Handlers
    document.getElementById('editTaskModalClose').addEventListener('click', closeEditTaskModal);
    document.getElementById('editTaskModalCancel').addEventListener('click', closeEditTaskModal);
    document.getElementById('editTaskModal').addEventListener('click', e => {
        if (e.target === document.getElementById('editTaskModal')) closeEditTaskModal();
    });
});