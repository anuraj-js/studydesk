<?php
require_once __DIR__ . '/../../includes/admin_layout.php';

render_admin_header('Help & Support', 'help');
?>

<div class="card" style="margin-bottom: 1.5rem;">
    <h2 class="section-title" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-question" style="color: var(--accent);"></i> StudyDesk Admin Guide</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
        Welcome to the Admin Control Panel. This guide will help you understand how to manage users, monitor platform activity, and maintain system integrity.
    </p>
</div>

<div id="helpAccordion">
    <!-- Module 1: Admin Dashboard -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-gauge" style="color: var(--accent);"></i> Admin Dashboard
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>The dashboard gives you a high-level overview of the platform's usage.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Global Stats:</strong> View the total number of users, tasks, exams, and focus sessions created across the entire platform.</li>
                <li><strong>Recent Signups:</strong> See the 5 most recent users who joined the app.</li>
                <li><strong>Recent Actions:</strong> See a log of the most recent actions taken by admins (e.g., deactivating a user, deleting an admin).</li>
            </ul>
        </div>
    </div>

    <!-- Module 2: User Management -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-users" style="color: var(--warning);"></i> User Management
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Monitor and moderate all registered users.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Search:</strong> Use the search bar to find users by name or email.</li>
                <li><strong>View Details:</strong> Click on a user to view their profile, tasks, exams, and notes (read-only).</li>
                <li><strong>Deactivate/Activate:</strong> Toggle a user's status. Deactivated users are immediately logged out of their active sessions and cannot log back in.</li>
                <li><strong>Delete:</strong> Permanently removes the user and all their data from the system.</li>
            </ul>
        </div>
    </div>

    <!-- Module 3: Admin Management -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-user-shield" style="color: var(--danger);"></i> Admin Management
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Manage the administrators who have access to this panel.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Create Admin:</strong> Add a new administrator to the system.</li>
                <li><strong>Delete Admin:</strong> Remove an administrator. <strong>Note:</strong> The last remaining admin cannot be deleted, and you cannot delete yourself.</li>
            </ul>
        </div>
    </div>

    <!-- Module 4: System Integrity -->
    <div class="card help-card">
        <button type="button" class="help-toggle-btn">
            <i class="fa-solid fa-shield-halved" style="color: var(--success);"></i> System Integrity & Security
            <i class="fa-solid fa-chevron-down help-chevron"></i>
        </button>
        <div class="help-content" style="display: none;">
            <p>Our system is built with strict data integrity rules.</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem; color: var(--text-muted); line-height: 1.6;">
                <li><strong>Privacy:</strong> Admins cannot edit user data (tasks, exams, notes, or passwords). This ensures user privacy.</li>
                <li><strong>Data Cleanup:</strong> Expired login attempts and used password reset tokens are automatically purged by the system to keep the database lean.</li>
                <li><strong>Audit Logs:</strong> Every destructive action you take (like deactivating or deleting a user) is recorded in the audit log for accountability. Logs are written atomically with the action to prevent inconsistencies.</li>
            </ul>
        </div>
    </div>
</div>

<?php 
render_admin_footer();
?>