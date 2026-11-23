document.addEventListener('DOMContentLoaded', () => {
    async function loadProfile() {
        try {
            const res = await apiFetch('../api/profile.php');
            if (res.success) {
                const data = res.data;
                document.getElementById('profUsername').value = data.username;
                document.getElementById('profEmail').value = data.email;
                document.getElementById('profFullName').value = data.full_name;
                document.getElementById('profAcademicLevel').value = data.academic_level;
                
                const genderRadio = document.querySelector(`input[name="profGender"][value="${data.gender}"]`);
                if (genderRadio) genderRadio.checked = true;

                if (data.created_at) {
                    const joinedDate = new Date(data.created_at.replace(' ', 'T') + 'Z');
                    const formattedDate = joinedDate.toLocaleDateString('en-US', { 
                        month: 'long', day: 'numeric', year: 'numeric', timeZone: 'Asia/Kathmandu'
                    });
                    document.getElementById('profJoinedAt').textContent = formattedDate;
                } else {
                    document.getElementById('profJoinedAt').textContent = 'Unknown';
                }
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to load profile.', 'error');
        }
    }
    loadProfile();

    // Initialize the Unsaved Changes Tracker
    initUnsavedChangesTracker(
        ['profFullName', 'profAcademicLevel'], 
        'saveProfileBtn', 
        'saveProfileBtnText'
    );

    document.getElementById('togglePasswordForm').addEventListener('click', () => {
        const wrapper = document.getElementById('passwordFormWrapper');
        const chevron = document.getElementById('passwordChevron');
        
        if (wrapper.style.display === 'none') {
            wrapper.style.display = 'block';
            chevron.style.transform = 'rotate(180deg)';
        } else {
            wrapper.style.display = 'none';
            chevron.style.transform = 'rotate(0deg)';
        }
    });

    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', () => {
            const targetId = icon.dataset.target;
            const input = document.getElementById(targetId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('saveProfileBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        const payload = {
            action: 'update_profile',
            full_name: document.getElementById('profFullName').value.trim(),
            academic_level: document.getElementById('profAcademicLevel').value,
            gender: document.querySelector('input[name="profGender"]:checked')?.value
        };

        try {
            const [res] = await Promise.all([
                apiFetch('../api/profile.php', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                }),
                new Promise(r => setTimeout(r, 500))
            ]);

            if (res.success) {
                showToast('Profile updated successfully.', 'success');
                window.resetProfileSaveBtn(); // <-- Reset the button color
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to update profile.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    document.getElementById('passwordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (!Validation.isStrongPassword(newPassword)) {
            showToast('Password must be 8-64 chars with uppercase, lowercase, number, and special character.', 'error');
            return;
        }

        if (newPassword !== confirmPassword) {
            showToast('Passwords do not match.', 'error');
            return;
        }

        const btn = document.getElementById('updatePasswordBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

        try {
            const [res] = await Promise.all([
                apiFetch('../api/profile.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'change_password',
                        current_password: currentPassword,
                        new_password: newPassword
                    })
                }),
                new Promise(r => setTimeout(r, 500))
            ]);

            if (res && res.success) {
                showToast('Password updated successfully.', 'success');
                document.getElementById('passwordForm').reset();
                document.getElementById('passwordFormWrapper').style.display = 'none';
                document.getElementById('passwordChevron').style.transform = 'rotate(0deg)';
            } else {
                showToast(res.message || 'Failed to update password.', 'error');
            }
        } catch (err) {
            console.error('Password update error:', err);
            showToast('An error occurred while updating the password.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
});