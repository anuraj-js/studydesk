<?php
require_once __DIR__ . '/../../includes/admin_layout.php';

render_admin_header('Admin Profile', 'profile');
?>

<div class="card">
    <h2 class="section-title"><i class="fa-solid fa-id-card"></i> Profile Details</h2>

    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">
        <i class="fa-solid fa-calendar-plus"></i> Joined on <span id="profJoinedAt">Loading...</span>
    </div>
    
    <form id="profileForm">
        <div class="grid-2">
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="profUsername" disabled style="background: var(--surface2); cursor: not-allowed;" />
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="profEmail" disabled style="background: var(--surface2); cursor: not-allowed;" />
            </div>
        </div>

        <div class="form-group">
            <label for="profFullName">Full Name <span class="required">*</span></label>
            <input type="text" id="profFullName" required />
        </div>

        <button type="submit" class="btn btn-primary" id="saveProfileBtn" style="width: 100%; transition: all 0.3s;">
            <i class="fa-solid fa-floppy-disk"></i> <span id="saveProfileBtnText">Save Changes</span>
        </button>
    </form>
</div>

<!-- Password Update Section -->
<div class="card" style="margin-top: 1rem;">
    <button type="button" class="btn btn-ghost" id="togglePasswordForm" style="width: 100%; justify-content: flex-start; padding: 1rem; font-weight: 600;">
        <i class="fa-solid fa-key"></i> Change Password
        <i class="fa-solid fa-chevron-down" style="margin-left: auto;" id="passwordChevron"></i>
    </button>

    <div id="passwordFormWrapper" style="display: none; margin-top: 1.5rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
        <form id="passwordForm">
            <div class="form-group">
                <label for="currentPassword">Current Password <span class="required">*</span></label>
                <div style="position: relative;">
                    <input type="password" id="currentPassword" required style="padding-right: 40px;" />
                    <i class="fa-solid fa-eye toggle-password" data-target="currentPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="newPassword">New Password <span class="required">*</span></label>
                <div style="position: relative;">
                    <input type="password" id="newPassword" required style="padding-right: 40px;" />
                    <i class="fa-solid fa-eye toggle-password" data-target="newPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm New Password <span class="required">*</span></label>
                <div style="position: relative;">
                    <input type="password" id="confirmPassword" required style="padding-right: 40px;" />
                    <i class="fa-solid fa-eye toggle-password" data-target="confirmPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="updatePasswordBtn" style="width: 100%;">
                <i class="fa-solid fa-key"></i> Update Password
            </button>
        </form>
    </div>
</div>

<?php 
render_admin_footer('profile.js'); 
?>