// js/admin/admin_profile.js

document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements - Profile Form
    const profileForm = document.getElementById('profileForm');
    const profileUsername = document.getElementById('profileUsername');
    const profileEmail = document.getElementById('profileEmail');
    const profilePhone = document.getElementById('profilePhone');
    const profileAddress = document.getElementById('profileAddress');
    const profileAcademicLevel = document.getElementById('profileAcademicLevel');
    const profileGender = document.getElementById('profileGender');
    const profileDob = document.getElementById('profileDob');
    const profileJoined = document.getElementById('profileJoined');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const saveProfileText = document.getElementById('saveProfileText');
    const saveProfileSpinner = document.getElementById('saveProfileSpinner');

    // DOM Elements - Password Form
    const passwordForm = document.getElementById('passwordForm');
    const togglePasswordBtn = document.getElementById('togglePasswordForm');
    const passwordFormWrapper = document.getElementById('passwordFormWrapper');
    const currentPassword = document.getElementById('currentPassword');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const updatePasswordBtn = document.getElementById('updatePasswordBtn');
    const updatePasswordText = document.getElementById('updatePasswordText');
    const updatePasswordSpinner = document.getElementById('updatePasswordSpinner');

    // Store original values for change detection
    let originalValues = {};

    // Toggle password visibility for all toggle buttons
    document.querySelectorAll('.toggle-password').forEach((btn) => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    });

    // Toggle password form visibility
    if (togglePasswordBtn && passwordFormWrapper) {
        togglePasswordBtn.addEventListener('click', function() {
            const isVisible = passwordFormWrapper.style.display !== 'none';
            passwordFormWrapper.style.display = isVisible ? 'none' : 'block';
            this.innerHTML = isVisible
                ? '<i class="fas fa-key"></i> Change Password'
                : '<i class="fas fa-key"></i> Hide Password Form';
        });
    }

    // Set profile saving state
    function setProfileSaving(isSaving) {
        saveProfileBtn.disabled = isSaving;
        if (isSaving) {
            saveProfileText.style.display = 'none';
            saveProfileSpinner.style.display = 'inline';
        } else {
            saveProfileText.style.display = 'inline';
            saveProfileSpinner.style.display = 'none';
        }
    }

    // Set password saving state
    function setPasswordSaving(isSaving) {
        updatePasswordBtn.disabled = isSaving;
        if (isSaving) {
            updatePasswordText.style.display = 'none';
            updatePasswordSpinner.style.display = 'inline';
        } else {
            updatePasswordText.style.display = 'inline';
            updatePasswordSpinner.style.display = 'none';
        }
    }

    // Fetch profile
    async function fetchProfile() {
        try {
            const response = await fetch('../../api/admin/profile.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                const data = result.data;
                if (profileJoined) profileJoined.textContent = formatDate(data.created_at);
                if (profileUsername) profileUsername.value = data.username || '';
                if (profileEmail) profileEmail.value = data.email || '';
                if (profilePhone) profilePhone.value = data.phone || '';
                if (profileAddress) profileAddress.value = data.address || '';
                if (profileAcademicLevel) profileAcademicLevel.value = data.academic_level || '';
                if (profileGender) profileGender.value = data.gender || '';
                if (profileDob) profileDob.value = data.dob || '';

                // Store original values for change detection
                originalValues = {
                    username: data.username || '',
                    email: data.email || '',
                    phone: data.phone || '',
                    address: data.address || '',
                    academicLevel: data.academic_level || '',
                    gender: data.gender || '',
                    dob: data.dob || ''
                };
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Failed to load profile.', 'error');
        }
    }

    // Format date
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric'
        });
    }

    // Check if any changes were made
    function hasChanges() {
        const current = {
            username: profileUsername.value.trim(),
            email: profileEmail.value.trim(),
            phone: profilePhone.value.trim() || '',
            address: profileAddress.value.trim() || '',
            academicLevel: profileAcademicLevel.value,
            gender: profileGender.value,
            dob: profileDob.value
        };

        return (
            current.username !== originalValues.username ||
            current.email !== originalValues.email ||
            current.phone !== originalValues.phone ||
            current.address !== originalValues.address ||
            current.academicLevel !== originalValues.academicLevel ||
            current.gender !== originalValues.gender ||
            current.dob !== originalValues.dob
        );
    }

    // Update profile
    if (profileForm) {
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!hasChanges()) {
                showToast('No changes to save.', 'warning');
                return;
            }

            const username = profileUsername.value.trim();
            const email = profileEmail.value.trim();
            const phone = profilePhone.value.trim() || '';
            const address = profileAddress.value.trim() || '';
            const academicLevel = profileAcademicLevel.value;
            const gender = profileGender.value;
            const dob = profileDob.value;

            // Validate
            if (!username || username.length < 3) {
                showToast('Username must be at least 3 characters.', 'error');
                return;
            }

            if (!email || !email.includes('@') || !email.includes('.')) {
                showToast('Please enter a valid email address.', 'error');
                return;
            }

            if (!dob) {
                showToast('Date of birth is required.', 'error');
                return;
            }

            const data = {
                username,
                email,
                phone,
                address,
                academic_level: academicLevel,
                gender,
                dob
            };

            setProfileSaving(true);

            try {
                const response = await fetch('../../api/admin/profile.php', {
                    method: 'PUT',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (!result.success) {
                    if (result.message.includes('Username is already taken')) {
                        showToast('Username is already taken.', 'warning');
                    } else if (result.message.includes('Email is already registered')) {
                        showToast('Email is already registered.', 'warning');
                    } else {
                        showToast(result.message, 'error');
                    }
                    setProfileSaving(false);
                    return;
                }

                if (result.success) {
                    showToast('Profile updated successfully.', 'success');

                    // Update original values
                    originalValues = {
                        username: result.data.user.username || '',
                        email: result.data.user.email || '',
                        phone: result.data.user.phone || '',
                        address: result.data.user.address || '',
                        academicLevel: result.data.user.academic_level || '',
                        gender: result.data.user.gender || '',
                        dob: result.data.user.dob || ''
                    };

                    // Update sidebar username + email
                    const sidebarEmail = document.querySelector('.sidebar-user-email');
                    if (sidebarEmail) sidebarEmail.textContent = result.data.user.email;

                    const sidebarName = document.querySelector('.sidebar-user-name');
                    if (sidebarName) sidebarName.textContent = result.data.user.username;
                }
            } catch (error) {
                showToast('Something went wrong.', 'error');
            } finally {
                setProfileSaving(false);
            }
        });
    }

    // Update password
    if (passwordForm) {
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const currentPass = currentPassword.value;
            const newPass = newPassword.value;
            const confirmPass = confirmPassword.value;

            if (!currentPass || !newPass || !confirmPass) {
                showToast('All password fields are required.', 'error');
                return;
            }

            if (newPass.length < 6) {
                showToast('New password must be at least 6 characters.', 'error');
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
                        new_password: newPass,
                        confirm_password: confirmPass
                    })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Password changed successfully.', 'success');
                    currentPassword.value = '';
                    newPassword.value = '';
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

    // Keyboard shortcut: Ctrl+S to save profile
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            if (profileForm) {
                profileForm.dispatchEvent(new Event('submit'));
            }
        }

        if (e.key === 'Escape') {
            if (passwordFormWrapper && passwordFormWrapper.style.display !== 'none') {
                passwordFormWrapper.style.display = 'none';
                if (togglePasswordBtn) {
                    togglePasswordBtn.innerHTML = '<i class="fas fa-key"></i> Change Password';
                }
            }
        }
    });

    // Load profile
    fetchProfile();
});