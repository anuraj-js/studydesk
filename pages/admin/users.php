<?php
require_once __DIR__ . '/../../includes/admin_layout.php';

render_admin_header('User Management', 'users');
?>

<div class="card">
    <div class="row" style="margin-bottom: 1rem;">
        <h2 class="section-title" style="margin: 0;">All Users</h2>
    </div>

    <!-- Search Bar -->
    <div class="form-group" style="margin-bottom: 1rem;">
        <input type="text" id="searchUser" placeholder="Search by name, username, or email..." style="border-radius: 20px; padding-left: 1rem;" />
    </div>

    <!-- User Table -->
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 0.85rem;">
                    <th style="padding: 0.75rem 0.5rem;">Name</th>
                    <th style="padding: 0.75rem 0.5rem;">Username</th>
                    <th class="hide-mobile" style="padding: 0.75rem 0.5rem;">Email</th>
                    <th style="padding: 0.75rem 0.5rem;">Joined</th>
                    <th style="padding: 0.75rem 0.5rem;">Status</th>
                    <th style="padding: 0.75rem 0.5rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <!-- Skeleton row -->
                <tr><td colspan="6"><div class="skeleton" style="height: 40px; margin: 0.5rem 0;"></div></td></tr>
                <tr><td colspan="6"><div class="skeleton" style="height: 40px; margin: 0.5rem 0;"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php 
render_admin_footer('users.js'); 
?>