<?php
require_once __DIR__ . '/../includes/user_layout.php';

render_header('Home', 'home');
?>

<!-- Hero Section -->
<div class="card hero">
    <h1 id="greeting">Hello!</h1>
    <p class="subtitle" id="studyMessage"></p>
    <div class="clock" id="clock">--:--:--</div>
    <div class="date-str" id="date">Loading…</div>
    <button class="clock-toggle" id="clockToggle"></button>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value skeleton" id="statPending" style="width: 50%; height: 28px; margin: 0 auto 0.5rem;"></div>
        <div class="stat-label">Pending Tasks</div>
    </div>
    <div class="stat-card">
        <div class="stat-value skeleton" id="statCompleted" style="width: 50%; height: 28px; margin: 0 auto 0.5rem;"></div>
        <div class="stat-label">Done Tasks</div>
    </div>
    <div class="stat-card">
        <div class="stat-value skeleton" id="statExam" style="width: 50%; height: 28px; margin: 0 auto 0.5rem;"></div>
        <div class="stat-label">Next Exam</div>
    </div>
    <div class="stat-card">
        <div class="stat-value skeleton" id="statSessions" style="width: 50%; height: 28px; margin: 0 auto 0.5rem;"></div>
        <div class="stat-label">Focus Sessions Today</div>
    </div>
</div>

<!-- Calendar Widget -->
<div class="card calendar-card">
    <div class="calendar-header">
        <h2 class="section-title" style="margin:0"><i class="fa-solid fa-calendar-days" style="color:var(--accent); margin-right:0.5rem;"></i> Calendar</h2>
    </div>

    <div class="calendar-controls">
        <div class="cal-month-title">
            <div id="calMonthYear" style="font-size:1.2rem; font-weight:700;">Loading...</div>
        </div>
        <div class="cal-nav">
            <button class="btn btn-ghost btn-sm" id="prevMonth"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="btn btn-primary btn-sm" id="todayBtn">Today</button>
            <button class="btn btn-ghost btn-sm" id="nextMonth"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </div>

    <div class="calendar-days-header">
        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
    </div>
    
    <div class="calendar-grid" id="calendarGrid">
        <!-- JS injects days here -->
    </div>
</div>

<!-- Pending Tasks -->
<div class="card">
    <div class="row">
        <h2 class="section-title" style="margin:0">Pending Tasks</h2>
        <a href="tasks.php" style="font-size:.82rem;color:var(--accent);text-decoration:none">View all <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div id="recentTasks">
        <div class="task-item skeleton" style="height: 64px; margin-bottom: 0.5rem;"></div>
        <div class="task-item skeleton" style="height: 64px; margin-bottom: 0.5rem;"></div>
        <div class="task-item skeleton" style="height: 64px;"></div>
    </div>
</div>

<!-- Upcoming Exams -->
<div class="card">
    <div class="row">
        <h2 class="section-title" style="margin:0">Upcoming Exams</h2>
        <a href="exams.php" style="font-size:.82rem;color:var(--accent);text-decoration:none">View all <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div id="upcomingExams">
        <div class="exam-item skeleton" style="height: 64px; margin-bottom: 0.5rem;"></div>
        <div class="exam-item skeleton" style="height: 64px;"></div>
    </div>
</div>

<!-- Day Events Modal -->
<div class="modal-overlay" id="dayModal" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3 id="dayModalTitle">Date</h3>
            <button class="modal-close" id="dayModalClose"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="dayModalBody" style="padding: 0.5rem 0; max-height: 60vh; overflow-y: auto;">
            <!-- JS injects events here -->
        </div>
    </div>
</div>

<?php 
render_footer(['calendar.js', 'home.js']); 
?>