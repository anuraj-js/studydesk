<?php
require_once __DIR__ . '/../../includes/admin_layout.php';

render_admin_header('Admin Management', 'admins');
?>

<!-- Add Admin Form (Collapsible) -->
<details class="card collapsible-card" style="margin-bottom: 1rem;">
    <summary class="section-title">
        <i class="fa-solid fa-plus-circle"></i> Create New Admin
    </summary>
    <form id="createAdminForm">
        <div class="form-group">
            <label for="adminFullName">Full Name <span class="required">*</span></label>
            <input type="text" id="adminFullName" placeholder="e.g. John Doe" required />
        </div>
        <div class="form-group">
            <label for="adminUsername">Username <span class="required">*</span></label>
            <input type="text" id="adminUsername" placeholder="e.g. johndoe_admin" required />
        </div>
        <div class="form-group">
            <label for="adminEmail">Email <span class="required">*</span></label>
            <input type="email" id="adminEmail" placeholder="e.g. john@studydesk.com" required />
        </div>
        
        <!-- Password Field -->
        <div class="form-group">
            <label for="adminPassword">Password <span class="required">*</span></label>
            <div style="position: relative;">
                <input type="password" id="adminPassword" placeholder="Must be 8-64 chars with mixed case, number, and symbol" required style="padding-right: 40px;" />
                <i class="fa-solid fa-eye toggle-password" data-target="adminPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
            </div>
        </div>

        <!-- Confirm Password Field -->
        <div class="form-group">
            <label for="adminConfirmPassword">Confirm Password <span class="required">*</span></label>
            <div style="position: relative;">
                <input type="password" id="adminConfirmPassword" placeholder="Re-enter password" required style="padding-right: 40px;" />
                <i class="fa-solid fa-eye toggle-password" data-target="adminConfirmPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full" id="createAdminBtn">
            <i class="fa-solid fa-user-plus"></i> Create Admin
        </button>
    </form>
</details>

<!-- Admin Table -->
<div class="card">
    <div class="row" style="margin-bottom: 1rem;">
        <h2 class="section-title" style="margin: 0;">Administrators</h2>
    </div>

    <div class="form-group" style="margin-bottom: 1rem;">
        <input type="text" id="searchAdmin" placeholder="Search by name, username, or email..." style="border-radius: 20px; padding-left: 1rem;" />
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 0.85rem;">
                    <th style="padding: 0.75rem 0.5rem;">Name</th>
                    <th style="padding: 0.75rem 0.5rem;">Username</th>
                    <th class="hide-mobile" style="padding: 0.75rem 0.5rem;">Email</th>
                    <th style="padding: 0.75rem 0.5rem;">Joined</th>
                    <th style="padding: 0.75rem 0.5rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="adminTableBody">
                <tr><td colspan="5"><div class="skeleton" style="height: 40px; margin: 0.5rem 0;"></div></td></tr>
                <tr><td colspan="5"><div class="skeleton" style="height: 40px; margin: 0.5rem 0;"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php 
render_admin_footer('admins.js'); 
?>