document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('registerBtn');
    
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

        const fullName = document.getElementById('fullName').value.trim();
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const academicLevel = document.getElementById('academicLevel').value;
        
        const genderEl = document.querySelector('input[name="gender"]:checked');
        const gender = genderEl ? genderEl.value : '';

        // Removed 'dob' from validation
        if (!fullName || !username || !email || !password || !confirmPassword || !academicLevel || !gender) {
            showToast('Please fill in all required fields.', 'error');
            return;
        }

        if (!Validation.isEmail(email)) {
            showToast('Please enter a valid email address.', 'error');
            return;
        }

        if (!Validation.isStrongPassword(password)) {
            showToast('Password must be 8-64 chars with uppercase, lowercase, number, and special character.', 'error');
            return;
        }

        if (password !== confirmPassword) {
            showToast('Passwords do not match.', 'error');
            return;
        }

        if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse() === '') {
            showToast('Please complete the CAPTCHA.', 'error');
            return;
        }

        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';

        try {
            const payload = {
                full_name: fullName,
                username: username,
                email: email,
                password: password,
                academic_level: academicLevel,
                gender: gender,
                recaptchaToken: typeof grecaptcha !== 'undefined' ? grecaptcha.getResponse() : ''
                // Removed date_of_birth from payload
            };

            const [response] = await Promise.all([
                apiFetch('../api/auth/register.php', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                }),
                new Promise(resolve => setTimeout(resolve, 1000))
            ]);

            if (response.success) {
                showToast('Account created! Redirecting to Home...', 'success');
                setTimeout(() => {
                    window.location.href = 'home.php';
                }, 1500);
            } else {
                showToast(response.message || 'Registration failed.', 'error');
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