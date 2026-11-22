document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('resetForm');
    const submitBtn = document.getElementById('resetBtn');
    
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = btn.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const token = document.getElementById('resetToken').value;
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

        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Resetting...';

        try {
            const payload = {
                token: token,
                new_password: newPassword
            };

            const [response] = await Promise.all([
                apiFetch('../api/auth/reset_password.php', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                }),
                new Promise(resolve => setTimeout(resolve, 1000))
            ]);

            if (response.success) {
                showToast('Password updated successfully! Redirecting to login...', 'success');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
            } else {
                showToast(response.message || 'Reset failed.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            showToast('A network error occurred. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
});