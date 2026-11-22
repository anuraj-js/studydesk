<?php
require_once __DIR__ . '/../includes/user_layout.php';

render_header('Help & Support', 'help');
?>

<div class="card" style="margin-bottom: 1.5rem;">
    <h2 class="section-title" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-question" style="color: var(--accent);"></i> Welcome to StudyDesk Help & Support</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
        StudyDesk is your all-in-one academic dashboard designed to keep you organized and focused. This comprehensive guide will walk you through every feature of the application, including tips and best practices to maximize your productivity.
    </p>
</div>

<div id="helpAccordion">

    <!-- Module 1: Home Dashboard -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-house" style="color: var(--accent);"></i> Home Dashboard
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>The Home page is your central hub. It provides a bird's-eye view of your academic life, updating in real-time.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Dynamic Greeting:</strong> The app greets you based on the time of day (Morning, Afternoon, Evening) and displays a random motivational study quote.</li>
                <li><strong>Live Clock:</strong> A precise clock that you can toggle between 12-hour and 24-hour formats. This preference is saved to your browser's local storage, so it persists even if you close the tab.</li>
                <li><strong>Stats Grid:</strong> See your pending tasks, completed tasks, next exam countdown, and today's focus sessions at a glance.</li>
                <li><strong>Calendar Widget:</strong> Visualize your monthly schedule. Days with due tasks or exams are highlighted with a dot. Click on any highlighted day to open a modal showing the specific tasks and exams due that day.</li>
                <li><strong>Quick Views:</strong> The bottom cards give you a sneak peek of your 4 most recent pending tasks and 4 nearest upcoming exams, with quick links to view the full lists.</li>
            </ul>
        </div>
    </div>

    <!-- Module 2: Task Manager -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-list-check" style="color: var(--warning);"></i> Task Manager
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Manage your assignments, readings, and daily to-dos with a powerful filtering system.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Adding Tasks:</strong> Click "Add Task" to enter a title, choose a priority (High, Medium, Low), and optionally set a due date.</li>
                <li><strong>Filtering:</strong> Use the tabs to quickly find what you need:
                    <ul style="margin-left: 1.5rem; margin-top: 0.3rem;">
                        <li><strong>All:</strong> Shows every task.</li>
                        <li><strong>Active:</strong> Shows only incomplete tasks.</li>
                        <li><strong>Completed:</strong> Shows only finished tasks.</li>
                        <li><strong>Overdue:</strong> Shows tasks past their due date (highlighted in red).</li>
                    </ul>
                </li>
                <li><strong>Search:</strong> Use the search bar to find tasks by title. Matching text is instantly highlighted.</li>
                <li><strong>Quick Actions:</strong> Check the box to mark a task complete, or use the Edit/Delete buttons to manage your list.</li>
                <li><strong>Form Auto-Close:</strong> When you add a task, the form closes automatically to save screen space.</li>
            </ul>
        </div>
    </div>

    <!-- Module 3: Exam Planner -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-book-open" style="color: var(--danger);"></i> Exam Planner
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Plan your study schedule leading up to your exams with granular topic tracking.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Live Countdown:</strong> Each exam card shows a live, second-by-second countdown. Exams within 3 days are highlighted in red.</li>
                <li><strong>Topic Tracking:</strong> Break down your exam preparation by adding specific topics. Check them off as you study to see your progress bar fill up in real-time.</li>
                <li><strong>Search:</strong> Filter your exams by subject or name using the search bar.</li>
                <li><strong>Editing:</strong> Use the Edit button to change the exam date, time, subject, or name. All topics are preserved when you edit an exam.</li>
                <li><strong>Cascading Delete:</strong> Deleting an exam also deletes all its associated topics (handled by the database).</li>
            </ul>
        </div>
    </div>

    <!-- Module 4: Pomodoro Timer -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-clock" style="color: var(--success);"></i> Pomodoro Timer
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Use the Pomodoro technique to maximize your focus and prevent burnout.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Timer:</strong> Start, pause, or reset the focus timer. The browser tab title will update with the remaining time so you can keep track even in other tabs.</li>
                <li><strong>Alarm Overlay:</strong> When the timer ends, a full-screen alarm will sound with vibration (on mobile). Tap "Stop Timer" to end it and move to the next mode.</li>
                <li><strong>Custom Settings:</strong> You can change the duration of Focus, Short Break, and Long Break sessions. You can also change how many focus sessions trigger a long break (default is 4).</li>
                <li><strong>Validation:</strong> The settings are strictly validated. You cannot input negative numbers or seconds above 59.</li>
                <li><strong>Reset to Default:</strong> A built-in button allows you to revert all settings back to the classic 25/5/15 Pomodoro timings.</li>
                <li><strong>Session Counter:</strong> The app automatically counts your completed focus sessions for the day and saves them to the database.</li>
            </ul>
        </div>
    </div>

    <!-- Module 5: Notes -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-note-sticky" style="color: var(--accent);"></i> Notes
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Write down lecture notes, ideas, or summaries in a seamless Master-Detail interface.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Auto-Save:</strong> Notes save automatically 600ms after you stop typing. You'll see a "Saved at..." status at the bottom, with a sequence counter preventing out-of-order saves.</li>
                <li><strong>Paste-to-Title:</strong> Paste a block of text, and the app will automatically extract the first line to use as the title.</li>
                <li><strong>Manual Save:</strong> Press <code>Ctrl+S</code> (or <code>Cmd+S</code>) to force an immediate save.</li>
                <li><strong>Search:</strong> Find notes by title or body content in the sidebar. Matches are highlighted.</li>
                <li><strong>Mobile View:</strong> On small screens, the sidebar and editor switch to a full-screen view, with a back button to return to the list.</li>
                <li><strong>Safe Deletion:</strong> Delete notes via a custom confirmation dialog to prevent accidental data loss.</li>
            </ul>
        </div>
    </div>

    <!-- Module 6: Profile & Security -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-user-shield" style="color: var(--text);"></i> Profile & Security
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Manage your personal information and account security.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Profile Details:</strong> Update your Full Name, Academic Level, and Gender. Your Username, Email, and Joined Date are strictly read-only for security.</li>
                <li><strong>Change Password:</strong> Click the "Change Password" button to expand the form. You must enter your current password to set a new one.</li>
                <li><strong>Password Rules:</strong> Passwords must be 8-64 characters long, and include at least one uppercase letter, one lowercase letter, one number, and one special character. The app enforces this on both the frontend and backend.</li>
                <li><strong>Auto-Collapse:</strong> The password form collapses automatically after a successful password change.</li>
            </ul>
        </div>
    </div>

    <!-- Module 7: General & Troubleshooting -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-gear" style="color: var(--text-muted);"></i> General & Troubleshooting
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Additional information about the app's behavior and security.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Themes:</strong> Toggle between Dark and Light mode using the switch in the navbar or mobile sidebar. Your preference is saved locally.</li>
                <li><strong>Mobile Responsiveness:</strong> On desktop, use the top navbar. On mobile, tap the hamburger icon to open the sidebar with all your navigation links and settings.</li>
                <li><strong>Session Security:</strong> If your account is deactivated by an administrator, your session is immediately revoked and you are redirected to the login page.</li>
                <li><strong>Data Privacy:</strong> All your tasks, exams, and notes are tied to your specific user account. You cannot see or access data from other users.</li>
                <li><strong>Browser Support:</strong> StudyDesk works best on modern browsers like Chrome, Firefox, Safari, and Edge.</li>
            </ul>
        </div>
    </div>

</div>

<?php 
render_footer(); 
?>