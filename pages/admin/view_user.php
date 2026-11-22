<?php
require_once __DIR__ . '/../../includes/admin_layout.php';

render_admin_header('View User', 'users');
?>

<div style="margin-bottom: 1rem;">
    <a href="users.php" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Users</a>
</div>

<!-- User Profile Card -->
<div class="card">
    <h2 class="section-title"><i class="fa-solid fa-id-card"></i> User Profile</h2>
    <div id="profileInfo">
        <div class="skeleton" style="height: 20px; width: 50%; margin-bottom: 1rem;"></div>
        <div class="skeleton" style="height: 20px; width: 40%; margin-bottom: 1rem;"></div>
    </div>
</div>

<!-- Tasks -->
<div class="card">
    <h2 class="section-title"><i class="fa-solid fa-list-check" style="color: var(--warning);"></i> Tasks</h2>
    <div id="userTasks">
        <div class="skeleton" style="height: 50px; margin-bottom: 0.5rem;"></div>
    </div>
</div>

<!-- Exams -->
<div class="card">
    <h2 class="section-title"><i class="fa-solid fa-book-open" style="color: var(--danger);"></i> Exams</h2>
    <div id="userExams">
        <div class="skeleton" style="height: 50px; margin-bottom: 0.5rem;"></div>
    </div>
</div>

<!-- Notes -->
<div class="card">
    <h2 class="section-title"><i class="fa-solid fa-note-sticky" style="color: var(--accent);"></i> Notes</h2>
    <div id="userNotes">
        <div class="skeleton" style="height: 50px; margin-bottom: 0.5rem;"></div>
    </div>
</div>

<?php 
render_admin_footer('view_user.js'); 
?>