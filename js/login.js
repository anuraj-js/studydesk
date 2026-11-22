document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginBtn');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    // Toggle password visibility
    togglePassword.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = togglePassword.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // 1. Validate CAPTCHA
        if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse() === '') {
            showToast('Please complete the CAPTCHA.', 'error');
            return;
        }

        // 2. Set Loading State
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Logging in...';

        try {
            const formData = {
                role: document.getElementById('role').value,
                identifier: document.getElementById('identifier').value.trim(),
                password: passwordInput.value,
                recaptchaToken: typeof grecaptcha !== 'undefined' ? grecaptcha.getResponse() : ''
            };

            // Force a minimum 1-second delay alongside the API call
            const [response] = await Promise.all([
                apiFetch('../api/auth/login.php', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                }),
                new Promise(resolve => setTimeout(resolve, 1000))
            ]);

            if (response.success) {
                showToast('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = response.data.redirect;
                }, 1000);
            } else {
                showToast(response.message || 'Login failed.', 'error');
                if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            showToast('A network error occurred. Please try again.', 'error');
            if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
});