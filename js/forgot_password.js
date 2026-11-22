document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('forgotForm');
    const submitBtn = document.getElementById('forgotBtn');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = document.getElementById('email').value.trim();

        if (!Validation.isEmail(email)) {
            showToast('Please enter a valid email address.', 'error');
            return;
        }

        if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse() === '') {
            showToast('Please complete the CAPTCHA.', 'error');
            return;
        }

        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

        try {
            const payload = {
                email: email,
                recaptchaToken: typeof grecaptcha !== 'undefined' ? grecaptcha.getResponse() : ''
            };

            const [response] = await Promise.all([
                apiFetch('../api/auth/forgot_password.php', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                }),
                new Promise(resolve => setTimeout(resolve, 1000))
            ]);

            if (response.success) {
                showToast(response.message, 'success');
                form.reset();
                if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
            } else {
                showToast(response.message || 'Request failed.', 'error');
                if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
            }
        } catch (error) {
            showToast('A network error occurred. Please try again.', 'error');
            if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
});