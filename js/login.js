// js/login.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const identifier = document.getElementById('identifier');
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const errorMsg = document.getElementById('error-msg');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    // Toggle password visibility
    togglePassword.addEventListener('click', () => {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        const icon = togglePassword.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // Show error message
    function showError(message) {
        errorMsg.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        errorMsg.style.display = 'flex';
    }

    // Clear error
    function clearError() {
        errorMsg.style.display = 'none';
        errorMsg.innerHTML = '';
    }

    // Set loading state
    function setLoading(isLoading) {
        if (isLoading) {
            loginBtn.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline';
            loginBtn.classList.add('btn-loading');
        } else {
            loginBtn.disabled = false;
            btnText.style.display = 'inline';
            btnSpinner.style.display = 'none';
            loginBtn.classList.remove('btn-loading');
        }
    }

    // Set success state
    function showSuccess() {
        loginBtn.disabled = true;
        btnText.innerHTML = '<i class="fas fa-check"></i> Logged in!';
        btnText.style.display = 'inline'; 
        btnSpinner.style.display = 'none';
        loginBtn.classList.remove('btn-loading');
        loginBtn.classList.add('btn-success');
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Clear previous errors
        clearError();

        // Get values with null-safe handling
        const identifierValue = identifier.value ? identifier.value.trim() : '';
        const passwordValue = password.value ? password.value.trim() : '';

        // Frontend validation
        if (!identifierValue || !passwordValue) {
            showError('Please enter your username/email and password.');
            return;
        }

        if (passwordValue.length < 6) {
            showError('Password must be at least 6 characters.');
            return;
        }

        // Set loading state
        setLoading(true);

        try {
            const response = await fetch('../api/auth/login.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    identifier: identifierValue,
                    password: passwordValue
                })
            });

            const result = await response.json();

            if (result.success) {
                // Show success state
                showSuccess();

                // Redirect after brief delay
                setTimeout(() => {
                    if (result.data.role === 'admin') {
                        window.location.href = '../pages/admin/admin_dashboard.php';
                    } else {
                        window.location.href = '../pages/dashboard.php';
                    }
                }, 800);
            } else {
                // Show error from PHP
                showError(result.message);
                setLoading(false);
            }
        } catch (error) {
            showError('Something went wrong. Please try again later.');
            setLoading(false);
        }
    });

    // Keyboard shortcut: Escape to clear error
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            clearError();
        }
    });
});