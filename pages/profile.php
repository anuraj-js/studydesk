<?php
require_once __DIR__ . '/../includes/user_layout.php';

render_header('Profile', 'profile');
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

        <div class="grid-2">
            <div class="form-group">
                <label for="profAcademicLevel">Academic Level <span class="required">*</span></label>
                <select id="profAcademicLevel" required>
                    <option value="school">School</option>
                    <option value="plus_two">Plus Two</option>
                    <option value="bachelor">Bachelor</option>
                    <option value="master">Master</option>
                    <option value="others">Others</option>
                </select>
            </div>
            <!-- REMOVED DOB Input from the grid -->
        </div>

        <div class="gender-group" style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
            <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 500;">Gender <span class="required">*</span></label>
            <div style="display: flex; gap: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                    <input type="radio" name="profGender" value="male" required> Male
                </label>
                <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                    <input type="radio" name="profGender" value="female"> Female
                </label>
                <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                    <input type="radio" name="profGender" value="other"> Others
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" id="saveProfileBtn" style="width: 100%; transition: all 0.3s;">
            <i class="fa-solid fa-floppy-disk"></i> <span id="saveProfileBtnText">Save Changes</span>
        </button>
    </form>
</div>

<!-- Password Update Section remains unchanged -->
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
render_footer('profile.js'); 
?>