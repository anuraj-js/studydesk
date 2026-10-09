// js/admin/admin_profile.js

document.addEventListener('DOMContentLoaded', () => {
    const profileForm        = document.getElementById('profileForm');
    const profileUsername    = document.getElementById('profileUsername');
    const profileEmail       = document.getElementById('profileEmail');
    const profileJoined      = document.getElementById('profileJoined');
    const saveProfileBtn     = document.getElementById('saveProfileBtn');
    const saveProfileText    = document.getElementById('saveProfileText');
    const saveProfileSpinner = document.getElementById('saveProfileSpinner');

    const passwordForm          = document.getElementById('passwordForm');
    const togglePasswordBtn     = document.getElementById('togglePasswordForm');
    const passwordFormWrapper   = document.getElementById('passwordFormWrapper');
    const currentPassword       = document.getElementById('currentPassword');
    const newPassword           = document.getElementById('newPassword');
    const confirmPassword       = document.getElementById('confirmPassword');
    const updatePasswordBtn     = document.getElementById('updatePasswordBtn');
    const updatePasswordText    = document.getElementById('updatePasswordText');
    const updatePasswordSpinner = document.getElementById('updatePasswordSpinner');

    let originalValues = {};

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach((btn) => {
        btn.addEventListener('click', function() {
            const input = document.getElementById(this.dataset.target);
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            }
        });
    });

    // Toggle password form
    if (togglePasswordBtn && passwordFormWrapper) {
        togglePasswordBtn.addEventListener('click', function() {
            const isVisible = passwordFormWrapper.style.display !== 'none';
            passwordFormWrapper.style.display = isVisible ? 'none' : 'block';
            this.innerHTML = isVisible
                ? '<i class="fas fa-key"></i> Change Password'
                : '<i class="fas fa-key"></i> Hide Password Form';
        });
    }

    function setProfileSaving(isSaving) {
        saveProfileBtn.disabled = isSaving;
        saveProfileText.style.display  = isSaving ? 'none'   : 'inline';
        saveProfileSpinner.style.display = isSaving ? 'inline' : 'none';
    }

    function setPasswordSaving(isSaving) {
        updatePasswordBtn.disabled = isSaving;
        updatePasswordText.style.display    = isSaving ? 'none'   : 'inline';
        updatePasswordSpinner.style.display = isSaving ? 'inline' : 'none';
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        return new Date(dateStr).toLocaleDateString('en-US', {
            month: 'short', day: '2-digit', year: 'numeric'
        });
    }

    function hasChanges() {
        return (
            profileUsername.value.trim() !== originalValues.username ||
            profileEmail.value.trim()    !== originalValues.email
        );
    }

    async function fetchProfile() {
        try {
            const response = await fetch('../../api/admin/profile.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                const data = result.data;
                if (profileJoined)   profileJoined.textContent = formatDate(data.created_at);
                if (profileUsername) profileUsername.value = data.username || '';
                if (profileEmail)    profileEmail.value    = data.email    || '';

                originalValues = {
                    username: data.username || '',
                    email:    data.email    || ''
                };
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Failed to load profile.', 'error');
        }
    }

    if (profileForm) {
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!hasChanges()) {
                showToast('No changes to save.', 'warning');
                return;
            }

            const username = profileUsername.value.trim();
            const email    = profileEmail.value.trim();

            if (!username || username.length < 3) {
                showToast('Username must be at least 3 characters.', 'error');
                return;
            }

            if (!email || !email.includes('@') || !email.includes('.')) {
                showToast('Please enter a valid email address.', 'error');
                return;
            }

            setProfileSaving(true);

            try {
                const response = await fetch('../../api/admin/profile.php', {
                    method: 'PUT',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, email })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Profile updated successfully.', 'success');
                    originalValues = {
                        username: result.data.user.username || '',
                        email:    result.data.user.email    || ''
                    };

                    const sidebarName  = document.querySelector('.sidebar-user-name');
                    const sidebarEmail = document.querySelector('.sidebar-user-email');
                    if (sidebarName)  sidebarName.textContent  = result.data.user.username;
                    if (sidebarEmail) sidebarEmail.textContent = result.data.user.email;
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Something went wrong.', 'error');
            } finally {
                setProfileSaving(false);
            }
        });
    }

    if (passwordForm) {
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const currentPass = currentPassword.value;
            const newPass     = newPassword.value;
            const confirmPass = confirmPassword.value;

            if (!currentPass || !newPass || !confirmPass) {
                showToast('All password fields are required.', 'error');
                return;
            }

            if (newPass.length < 8 || !/[a-zA-Z]/.test(newPass) || !/[0-9]/.test(newPass)) {
                showToast('New password must be at least 8 characters with letters and numbers.', 'error');
                return;
            }

            if (newPass !== confirmPass) {
                showToast('Passwords do not match.', 'error');
                return;
            }

            setPasswordSaving(true);

            try {
                const response = await fetch('../../api/admin/profile_password.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        current_password: currentPass,
                        new_password:     newPass,
                        confirm_password: confirmPass
                    })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Password changed successfully.', 'success');
                    currentPassword.value = '';
                    newPassword.value     = '';
                    confirmPassword.value = '';
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Something went wrong.', 'error');
            } finally {
                setPasswordSaving(false);
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            if (profileForm) profileForm.dispatchEvent(new Event('submit'));
        }
        if (e.key === 'Escape') {
            if (passwordFormWrapper && passwordFormWrapper.style.display !== 'none') {
                passwordFormWrapper.style.display = 'none';
                if (togglePasswordBtn) togglePasswordBtn.innerHTML = '<i class="fas fa-key"></i> Change Password';
            }
        }
    });

    fetchProfile();
});