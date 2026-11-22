<?php
require_once __DIR__ . '/../includes/user_layout.php';

render_header('Tasks', 'tasks');
?>

<!-- Add Task Form -->
<details class="card collapsible-card">
    <summary class="section-title">
        <i class="fa-solid fa-plus-circle"></i> Add Task
    </summary>
    <form id="taskForm">
        <div class="form-group">
            <label for="taskTitle">Task <span class="required">*</span></label>
            <input type="text" id="taskTitle" placeholder="What do you need to do?" required />
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label for="taskPriority">Priority <span class="required">*</span></label>
                <select id="taskPriority">
                    <option value="high">High</option>
                    <option value="medium" selected>Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="form-group">
                <label for="taskDue">Due Date</label>
                <input type="date" id="taskDue" />
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full" id="addTaskBtn">
            <i class="fa-solid fa-plus"></i> Add Task
        </button>
    </form>
</details>

<!-- Search Bar -->
<div class="form-group" style="margin-bottom: 0.85rem;">
    <input type="text" id="searchTask" placeholder="Search tasks..." style="border-radius: 20px; padding-left: 1rem;" />
</div>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <button class="filter-tab active" data-filter="all">All</button>
    <button class="filter-tab" data-filter="active">Active</button>
    <button class="filter-tab" data-filter="completed">Completed</button>
    <button class="filter-tab" data-filter="overdue">
        Overdue <span class="tab-count" id="overdueCount" style="display:none">0</span>
    </button>
</div>

<!-- Task List Container -->
<div id="taskList">
    <!-- Skeleton loaders -->
    <div class="task-item skeleton" style="height: 64px;"></div>
    <div class="task-item skeleton" style="height: 64px;"></div>
    <div class="task-item skeleton" style="height: 64px;"></div>
</div>

<!-- Edit Task Modal -->
<div class="modal-overlay" id="editTaskModal" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen"></i> Edit Task</h3>
            <button class="modal-close" id="editTaskModalClose"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editTaskForm">
            <input type="hidden" id="editTaskId" />
            <div class="form-group">
                <label for="editTaskTitle">Task <span class="required">*</span></label>
                <input type="text" id="editTaskTitle" required />
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label for="editTaskPriority">Priority <span class="required">*</span></label>
                    <select id="editTaskPriority">
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editTaskDue">Due Date</label>
                    <input type="date" id="editTaskDue" />
                </div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:1rem">
                <button type="button" class="btn btn-ghost" id="editTaskModalCancel">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1" id="saveTaskBtn">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
render_footer('tasks.js'); 
?>