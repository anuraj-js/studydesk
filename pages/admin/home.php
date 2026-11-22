<?php
require_once __DIR__ . '/../../includes/admin_layout.php';

render_admin_header('Admin Dashboard', 'home');
?>

<div class="card hero">
    <h1>Admin Control Panel</h1>
    <p class="subtitle">System Overview</p>
</div>

<!-- 2x2 Stats Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
    <div class="stat-card">
        <div class="stat-value skeleton" id="statTotalUsers" style="width: 50%; height: 28px; margin: 0 auto 0.5rem;"></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value skeleton" id="statTotalTasks" style="width: 50%; height: 28px; margin: 0 auto 0.5rem;"></div>
        <div class="stat-label">Total Tasks</div>
    </div>
    <div class="stat-card">
        <div class="stat-value skeleton" id="statTotalExams" style="width: 50%; height: 28px; margin: 0 auto 0.5rem;"></div>
        <div class="stat-label">Total Exams</div>
    </div>
    <div class="stat-card">
        <div class="stat-value skeleton" id="statTotalSessions" style="width: 50%; height: 28px; margin: 0 auto 0.5rem;"></div>
        <div class="stat-label">Focus Sessions</div>
    </div>
</div>

<div class="card">
    <h2 class="section-title"><i class="fa-solid fa-user-plus"></i> Recent Signups</h2>
    <div id="recentSignups">
        <div class="task-item skeleton" style="height: 50px; margin-bottom: 0.5rem;"></div>
        <div class="task-item skeleton" style="height: 50px; margin-bottom: 0.5rem;"></div>
    </div>
</div>

<div class="card">
    <h2 class="section-title"><i class="fa-solid fa-clipboard-list"></i> Recent Admin Actions</h2>
    <div id="recentActions">
        <div class="task-item skeleton" style="height: 50px; margin-bottom: 0.5rem;"></div>
        <div class="task-item skeleton" style="height: 50px; margin-bottom: 0.5rem;"></div>
    </div>
</div>

<?php 
render_admin_footer('home.js'); 
?>