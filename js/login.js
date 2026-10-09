// js/login.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const roleSelect = document.getElementById('role');
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
        clearError();

        const role = roleSelect.value;
        const identifierValue = identifier.value ? identifier.value.trim() : '';
        const passwordValue = password.value || '';

        // Validate role
        if (!role) {
            showError('Please select whether you are signing in as Student or Admin.');
            return;
        }

        // Validate identifier and password
        if (Validator.isEmpty(identifierValue) || Validator.isEmpty(passwordValue)) {
            showError('Please enter your username/email and password.');
            return;
        }

        // Validate reCAPTCHA
        if (!grecaptcha.getResponse()) {
            showError('Please complete the CAPTCHA verification.');
            return;
        }

        setLoading(true);

        try {
            const response = await fetch('../api/auth/login.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    role: role,
                    identifier: identifierValue,
                    password: passwordValue,
                    'g-recaptcha-response': grecaptcha.getResponse()
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess();
                setTimeout(() => {
                    if (result.data.role === 'admin') {
                        window.location.href = '../pages/admin/admin_dashboard.php';
                    } else {
                        window.location.href = '../pages/dashboard.php';
                    }
                }, 800);
            } else {
                showError(result.message);
                grecaptcha.reset();
                setLoading(false);
            }
        } catch (error) {
            showError('Something went wrong. Please try again later.');
            grecaptcha.reset();
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