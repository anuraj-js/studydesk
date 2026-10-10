// js/forgot.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('forgot-form');
    const emailInput = document.getElementById('email');
    const errorMsg = document.getElementById('error-msg');
    const successMsg = document.getElementById('success-msg');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

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

        const email = emailInput.value.trim();

        if (Validator.isEmpty(email)) {
            showError('Please enter your email address.');
            return;
        }

        if (!Validator.isValidEmail(email)) {
            showError('Please enter a valid email address.');
            return;
        }

        if (!Validator.isWithinLength(email, 100)) {
            showError('Email must be 100 characters or less.');
            return;
        }

        // Validate reCAPTCHA
        if (!grecaptcha.getResponse()) {
            showError('Please complete the CAPTCHA verification.');
            return;
        }

        setLoading(true);

        try {
            const response = await fetch('../api/auth/forgot.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: email,
                    'g-recaptcha-response': grecaptcha.getResponse()
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                form.reset();
                grecaptcha.reset();
            } else {
                showError(result.message);
                grecaptcha.reset();
            }
        } catch (error) {
            showError('Something went wrong. Please try again.');
            grecaptcha.reset();
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