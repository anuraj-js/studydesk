// js/reset.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reset-form');
    const tokenInput = document.getElementById('token');
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const errorMsg = document.getElementById('error-msg');
    const successMsg = document.getElementById('success-msg');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

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

    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    if (token) {
        tokenInput.value = token;
    } else {
        showError('Invalid or missing reset token.');
        submitBtn.disabled = true;
    }

    function showError(message) {
        errorMsg.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        errorMsg.style.display = 'flex';
        successMsg.style.display = 'none';
    }

    function showSuccess(message) {
        successMsg.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        successMsg.style.display = 'flex';
        errorMsg.style.display = 'none';
    }

    function clearMessages() {
        errorMsg.style.display = 'none';
        successMsg.style.display = 'none';
    }

    function setLoading(isLoading) {
        submitBtn.disabled = isLoading;
        if (isLoading) {
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline';
        } else {
            btnText.style.display = 'inline';
            btnSpinner.style.display = 'none';
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearMessages();

        const token = tokenInput.value;
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (!token) {
            showError('Invalid reset token.');
            return;
        }

        if (!newPassword || !confirmPassword) {
            showError('Please fill in all password fields.');
            return;
        }

        if (newPassword.length < 6) {
            showError('Password must be at least 6 characters.');
            return;
        }

        if (newPassword !== confirmPassword) {
            showError('Passwords do not match.');
            return;
        }

        setLoading(true);

        try {
            const response = await fetch('../api/auth/reset.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    token: token,
                    password: newPassword
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                form.reset();
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 3000);
            } else {
                showError(result.message);
            }
        } catch (error) {
            showError('Something went wrong. Please try again.');
        } finally {
            setLoading(false);
        }
    });

    // Keyboard shortcut: Escape to clear messages
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            clearMessages();
        }
    });
});