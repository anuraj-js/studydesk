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

        // Clear previous errors
        clearError();

        // Get values with null-safe handling
        const username = document.getElementById('username').value ? document.getElementById('username').value.trim() : '';
        const email = document.getElementById('email').value ? document.getElementById('email').value.trim() : '';
        const password = document.getElementById('password').value ? document.getElementById('password').value : '';
        const confirmPassword = document.getElementById('confirm-password').value ? document.getElementById('confirm-password').value : '';
        const academicLevel = document.getElementById('academic-level').value ? document.getElementById('academic-level').value : '';
        const dob = document.getElementById('dob').value ? document.getElementById('dob').value : '';
        const gender = document.querySelector('.gender:checked');
        const phone = document.getElementById('phone').value ? document.getElementById('phone').value.trim() : null;
        const address = document.getElementById('address').value ? document.getElementById('address').value.trim() : null;

        // Frontend validation
        if (!username || !email || !password || !confirmPassword || !academicLevel) {
            showError('Please fill in all required fields.');
            return;
        }

        if (username.length < 3) {
            showError('Username must be at least 3 characters.');
            return;
        }

        if (!email.includes('@') || !email.includes('.')) {
            showError('Please enter a valid email address.');
            return;
        }

        if (password.length < 6) {
            showError('Password must be at least 6 characters.');
            return;
        }

        if (password !== confirmPassword) {
            showError('Passwords do not match.');
            return;
        }

        if (!dob) {
            showError('Please select your date of birth.');
            return;
        }

        // Validate date of birth (at least 13 years old)
        const dobDate = new Date(dob);
        const today = new Date();
        const cutoff = new Date(dobDate.getFullYear() + 13, dobDate.getMonth(), dobDate.getDate());
        if (today < cutoff) {
            showError('You must be at least 13 years old to register.');
            return;
        }

        if (!gender) {
            showError('Please select your gender.');
            return;
        }

        // Set loading state
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
                    address: address
                })
            });

            const result = await response.json();

            if (result.success) {
                // Show success state
                showSuccess();

                // Redirect after brief delay
                setTimeout(() => {
                    window.location.href = '../pages/dashboard.php';
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