// js/register.js

document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('register-form');
    const errorMsg = document.getElementById('error-msg');
    const registerBtn = document.getElementById('registerBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    // Toggle password visibility for all toggle buttons
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;
            const input = document.getElementById(targetId);
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                const icon = button.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
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
            registerBtn.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline';
            registerBtn.classList.add('btn-loading');
        } else {
            registerBtn.disabled = false;
            btnText.style.display = 'inline';
            btnSpinner.style.display = 'none';
            registerBtn.classList.remove('btn-loading');
        }
    }

    // Set success state
    function showSuccess() {
        registerBtn.disabled = true;
        btnText.innerHTML = '<i class="fas fa-check"></i> Account Created!';
        btnText.style.display = 'inline';
        btnSpinner.style.display = 'none';
        registerBtn.classList.remove('btn-loading');
        registerBtn.classList.add('btn-success');
    }

    registerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearError();

        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value || '';
        const confirmPassword = document.getElementById('confirm-password').value || '';
        const academicLevel = document.getElementById('academic-level').value;
        const dob = document.getElementById('dob').value;
        const gender = document.querySelector('.gender:checked');
        const phoneRaw = document.getElementById('phone').value.trim();
        const addressRaw = document.getElementById('address').value.trim();
        const phone = phoneRaw || null;
        const address = addressRaw || null;

        // Required fields
        if (Validator.isEmpty(username) || Validator.isEmpty(email) || !password || !academicLevel) {
            showError('Please fill in all required fields.');
            return;
        }

        // Username: 3–50 chars, letters/numbers/underscores only, cannot start with a digit
        if (!Validator.isAlphanumericUsername(username)) {
            showError('Username must be 3–50 characters, contain only letters, numbers, or underscores, and cannot start with a number.');
            return;
        }

        // Email format
        if (!Validator.isValidEmail(email)) {
            showError('Please enter a valid email address.');
            return;
        }

        // Email max length
        if (!Validator.isWithinLength(email, 100)) {
            showError('Email must be 100 characters or less.');
            return;
        }

        // Password: 8–72 chars, must have letter and number
        if (!Validator.isValidPassword(password)) {
            showError('Password must be 8–72 characters and include letters and numbers.');
            return;
        }

        // Confirm password
        if (password !== confirmPassword) {
            showError('Passwords do not match.');
            return;
        }

        // Date of birth
        if (!dob) {
            showError('Please select your date of birth.');
            return;
        }

        if (!Validator.isAtLeast13(dob)) {
            showError('You must be at least 13 years old to register.');
            return;
        }

        // Gender
        if (!gender) {
            showError('Please select your gender.');
            return;
        }

        // Phone (optional) — validate only if filled
        if (phone && !Validator.isValidPhone(phone)) {
            showError('Phone number must be exactly 10 digits.');
            return;
        }

        // Address (optional) — max length
        if (address && !Validator.isWithinLength(address, 255)) {
            showError('Address must be 255 characters or less.');
            return;
        }

        // reCAPTCHA
        if (!grecaptcha.getResponse()) {
            showError('Please complete the CAPTCHA verification.');
            return;
        }

        setLoading(true);

        try {
            const response = await fetch('../api/auth/register.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username: username,
                    email: email,
                    password: password,
                    academic_level: academicLevel,
                    dob: dob,
                    gender: gender.value,
                    phone: phone,
                    address: address,
                    'g-recaptcha-response': grecaptcha.getResponse()
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess();
                setTimeout(() => {
                    window.location.href = '../pages/dashboard.php';
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