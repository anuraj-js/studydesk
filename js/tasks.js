// js/tasks.js

document.addEventListener('DOMContentLoaded', () => {
    const todayPane = document.getElementById('pane-today');
    const upcomingPane = document.getElementById('pane-upcoming');
    const overduePane = document.getElementById('pane-overdue');
    const badgeToday = document.getElementById('badge-today');
    const badgeUpcoming = document.getElementById('badge-upcoming');
    const badgeOverdue = document.getElementById('badge-overdue');
    const panel = document.getElementById('taskPanel');
    const overlay = document.getElementById('panelOverlay');
    const openBtn = document.getElementById('openPanelBtn');
    const closeBtn = document.getElementById('closePanelBtn');
    const form = document.getElementById('taskForm');
    const taskIdInput = document.getElementById('taskId');
    const titleInput = document.getElementById('taskTitle');
    const typeInput = document.getElementById('taskType');
    const priorityInput = document.getElementById('taskPriority');
    const dueDateInput = document.getElementById('taskDueDate');
    const descInput = document.getElementById('taskDescription');
    const submitBtn = document.getElementById('submitBtn');
    const panelTitle = document.getElementById('panelTitle');
    const searchInput = document.getElementById('taskSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const searchInfo = document.getElementById('searchInfo');
    const sortSelect = document.getElementById('sortBy');

    let currentTasks = [];
    let searchQuery = '';
    let searchTimeout = null;
    let currentSort = 'due_date_asc';

    // Sort select change listener — re-fetch with new sort
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            currentSort = this.value;
            fetchTasks();

            // Info toast: Show which sort is active
            const sortNames = {
                'priority': 'Sorted by Priority (High → Low)',
                'due_date_asc': 'Sorted by Due Date (Earliest first)',
                'due_date_desc': 'Sorted by Due Date (Latest first)',
                'type': 'Sorted by Type (A → Z)'
            };
            showToast(sortNames[currentSort] || 'Sorted', 'info');
        });
    }

    // Search filter with debouncing
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
                renderTasks();
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
                renderTasks();
                searchInput.blur();
            }
        });
    }

    // Clear search button
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchQuery = '';
            this.style.display = 'none';
            renderTasks();
            searchInput.focus();
            if (searchInfo) {
                searchInfo.style.display = 'none';
            }
        });
    }

    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const tab = this.dataset.tab;
            document.querySelectorAll('.task-pane').forEach(p => p.classList.remove('active'));
            document.getElementById('pane-' + tab).classList.add('active');
            renderTasks();
        });
    });

    // Panel controls
    function openPanel(task = null) {
        if (task) {
            panelTitle.textContent = 'Edit Task';
            taskIdInput.value = task.id;
            titleInput.value = task.title;
            typeInput.value = task.type;
            priorityInput.value = task.priority || 'medium';
            dueDateInput.value = task.due_date;
            descInput.value = task.description || '';
            submitBtn.textContent = 'Update Task';
        } else {
            panelTitle.textContent = 'New Task';
            taskIdInput.value = '';
            form.reset();
            priorityInput.value = 'medium';
            submitBtn.textContent = 'Add Task';
        }
        panel.classList.add('open');
        overlay.classList.add('open');
    }

    function closePanel() {
        panel.classList.remove('open');
        overlay.classList.remove('open');
        form.reset();
        taskIdInput.value = '';
    }

    openBtn.addEventListener('click', () => openPanel());
    closeBtn.addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);

    // Show skeleton loading for task panes
    function showTaskLoading(container) {
        const skeletons = `
            <div class="skeleton-task-card">
                <div class="skeleton-checkbox"></div>
                <div class="skeleton-info">
                    <div class="skeleton-title"></div>
                    <div class="skeleton-desc"></div>
                </div>
                <div class="skeleton-meta">
                    <div class="skeleton-badge"></div>
                    <div class="skeleton-badge"></div>
                    <div class="skeleton-badge"></div>
                </div>
            </div>
        `;
        container.innerHTML = skeletons.repeat(3);
    }

    // Update badge with animation
    function updateBadge(element, count) {
        const oldCount = parseInt(element.textContent) || 0;
        element.textContent = count;
        if (oldCount !== count) {
            element.classList.add('update');
            setTimeout(() => element.classList.remove('update'), 300);
        }
    }

    // Fetch tasks with sort parameter
    async function fetchTasks() {
        showTaskLoading(todayPane);
        showTaskLoading(upcomingPane);
        showTaskLoading(overduePane);

        try {
            const response = await fetch(`../api/tasks/tasks.php?sort=${currentSort}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                currentTasks = result.data.tasks || [];
                renderTasks();
            } else {
                showToast('Failed to load tasks: ' + result.message, 'error');
                showErrorState('Failed to load tasks. Please refresh the page.', todayPane, upcomingPane, overduePane);
            }
        } catch (error) {
            showToast('Network error loading tasks', 'error');
            showErrorState('Network error. Please check your connection.', todayPane, upcomingPane, overduePane);
        }
    }

    function showErrorState(message, ...panes) {
        const errorHtml = `
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <span class="error-title">Something went wrong</span>
                <span class="error-sub">${message}</span>
            </div>
        `;
        panes.forEach(pane => {
            if (pane) pane.innerHTML = errorHtml;
        });
    }

    // Enhanced filter function with AND logic
    function filterTasks(tasks, query) {
        if (!query) return tasks;

        const terms = query.split(/\s+/);

        return tasks.filter(task => {
            return terms.every(term => {
                const searchableText = [
                    task.title,
                    task.description || '',
                    task.type,
                    task.priority || 'medium',
                    task.due_date
                ].join(' ').toLowerCase();
                return searchableText.includes(term);
            });
        });
    }

    // Highlight matching text
    function highlightText(text, query) {
        if (!query || !text) return text;
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return text.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    // Render tasks with search improvements
    function renderTasks() {
        const now = new Date();
        const today = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;

        let filteredTasks = filterTasks(currentTasks, searchQuery);

        if (searchInfo) {
            if (searchQuery && filteredTasks.length > 0 && filteredTasks.length !== currentTasks.length) {
                searchInfo.innerHTML = `<i class="fas fa-search"></i> Found ${filteredTasks.length} of ${currentTasks.length} tasks matching "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info has-results';
            } else if (searchQuery && filteredTasks.length === 0) {
                searchInfo.innerHTML = `<i class="fas fa-exclamation-circle"></i> No tasks found matching "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info no-results';
            } else if (searchQuery && filteredTasks.length === currentTasks.length) {
                searchInfo.innerHTML = `<i class="fas fa-search"></i> All ${filteredTasks.length} tasks match "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info has-results';
            } else {
                searchInfo.style.display = 'none';
                searchInfo.className = 'search-info';
            }
        }

        const todayTasks = [];
        const upcomingTasks = [];
        const overdueTasks = [];

        filteredTasks.forEach(task => {
            if (task.due_date === today) {
                todayTasks.push(task);
            } else if (task.due_date > today) {
                upcomingTasks.push(task);
            } else {
                overdueTasks.push(task);
            }
        });

        renderPane(todayPane, todayTasks, false, searchQuery);
        renderPane(upcomingPane, upcomingTasks, false, searchQuery);
        renderPane(overduePane, overdueTasks, true, searchQuery);

        updateBadge(badgeToday, todayTasks.length);
        updateBadge(badgeUpcoming, upcomingTasks.length);
        updateBadge(badgeOverdue, overdueTasks.length);
    }

    function renderPane(container, tasks, isOverdue, query) {
        if (tasks.length === 0) {
            const icon = isOverdue ? 'fa-exclamation-triangle' : 'fa-check-circle';
            const title = isOverdue ? 'All caught up!' : 'All caught up!';
            const sub = isOverdue ? 'No overdue tasks' : 'No tasks in this category';
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas ${icon}"></i>
                    <span class="empty-title">${title}</span>
                    <span class="empty-sub">${sub}</span>
                </div>
            `;
            return;
        }

        container.innerHTML = tasks.map((task, index) => {
            const priorityClass = task.priority || 'medium';
            return `
                <div class="task-card ${isOverdue ? 'overdue' : ''} priority-${priorityClass}" data-id="${task.id}" style="animation-delay: ${(index * 0.05) + 0.05}s">
                    <input type="checkbox" class="task-checkbox">
                    <div class="task-info">
                        <p class="task-title">${highlightText(escapeHtml(task.title), query)}</p>
                        <p class="task-description">${task.description ? highlightText(escapeHtml(task.description), query) : 'No description'}</p>
                    </div>
                    <div class="task-meta">
                        ${!isOverdue ? `<span class="task-due">${task.due_date}</span>` : ''}
                        <span class="task-type-badge type-${task.type}">${capitalize(task.type)}</span>
                        <span class="priority-badge priority-${priorityClass}">${capitalize(priorityClass)}</span>
                        <div class="task-actions">
                            <button class="edit-btn" data-id="${task.id}">Edit</button>
                            <button class="delete-btn" data-id="${task.id}">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Attach event listeners
        container.querySelectorAll('.task-checkbox').forEach((cb) => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    showConfirm('Mark this task as complete?', () => {
                        const taskId = this.closest('.task-card').dataset.id;
                        completeTask(taskId);
                    }, () => {
                        this.checked = false;
                    });
                }
            });
        });

        container.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const taskId = parseInt(this.dataset.id);
                const task = currentTasks.find(t => t.id === taskId);
                if (task) openPanel(task);
            });
        });

        container.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const taskId = parseInt(this.dataset.id);
                showConfirm('Delete this task?', () => {
                    deleteTask(taskId);
                });
            });
        });
    }

    // CRUD operations
    async function createTask(data) {
        try {
            const response = await fetch('../api/tasks/tasks.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                closePanel();
                await fetchTasks();
                showToast('Task created successfully', 'success');

                // Check due date warnings after creation
                checkDueDateWarnings(data.due_date);
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error creating task', 'error');
        }
    }

    async function updateTask(id, data) {
        try {
            const response = await fetch(`../api/tasks/tasks.php?id=${id}`, {
                method: 'PUT',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                closePanel();
                await fetchTasks();
                showToast('Task updated successfully', 'success');

                // Check due date warnings after update
                checkDueDateWarnings(data.due_date);
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error updating task', 'error');
        }
    }

    async function deleteTask(id) {
        try {
            const response = await fetch(`../api/tasks/tasks.php?id=${id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                await fetchTasks();
                showToast('Task deleted successfully', 'success');
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error deleting task', 'error');
        }
    }

    async function completeTask(id) {
        try {
            const response = await fetch(`../api/tasks/complete.php?id=${id}`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                await fetchTasks();
                showToast('Task completed successfully', 'success');
            } else {
                showToast('Error: ' + result.message, 'error');
                await fetchTasks();
            }
        } catch (error) {
            showToast('Network error completing task', 'error');
            await fetchTasks();
        }
    }

    // Check due date warnings (only after create/update)
    function checkDueDateWarnings(dueDate) {
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];

        if (dueDate === todayStr) {
            showToast('Reminder: This task is due today!', 'warning');
        } else if (dueDate === tomorrowStr) {
            showToast('Reminder: This task is due tomorrow!', 'warning');
        }
    }

    // Form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = taskIdInput.value;
        const data = {
            title: titleInput.value.trim(),
            type: typeInput.value,
            priority: priorityInput.value,
            due_date: dueDateInput.value,
            description: descInput.value.trim() || null
        };

        if (!data.title) {
            showToast('Task name is required', 'error');
            return;
        }

        const now = new Date();
        const today = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;

        if (dueDateInput.value < today) {
            showToast('Due date cannot be in the past', 'error');
            return;
        }

        // Warning: Due date > 1 year in the future
        const oneYearFromNow = new Date(now);
        oneYearFromNow.setFullYear(oneYearFromNow.getFullYear() + 1);
        const oneYearStr = oneYearFromNow.toISOString().split('T')[0];

        if (dueDateInput.value > oneYearStr) {
            showToast('Due date is more than a year away. Please double-check.', 'warning');
            // Allow submission but warn the user
        }

        if (id) {
            updateTask(parseInt(id), data);
        } else {
            createTask(data);
        }
    });

    // Helpers
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Initial load
    fetchTasks();
});