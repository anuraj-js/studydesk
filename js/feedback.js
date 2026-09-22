// js/feedback.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('feedbackForm');
    const typeSelect = document.getElementById('feedbackType');
    const subjectInput = document.getElementById('feedbackSubject');
    const messageTextarea = document.getElementById('feedbackMessage');
    const charCount = document.getElementById('charCount');
    const submitBtn = document.getElementById('submitFeedbackBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const submitBtnSpinner = document.getElementById('submitBtnSpinner');
    const successDiv = document.getElementById('feedbackSuccess');

    let isSubmitting = false;
    const MAX_CHARS = 1000;
    let warningShown = false; // Track if approaching warning has been shown
    let exceededShown = false; // Track if exceeded warning has been shown

    // Character counter
    messageTextarea.addEventListener('input', function() {
        const current = this.value.length;
        charCount.textContent = current;
        charCount.className = '';

        if (current > MAX_CHARS * 0.9) {
            charCount.classList.add('near-limit');
        }
        if (current >= MAX_CHARS) {
            charCount.classList.add('at-limit');
        }

        // Warning: Character count > 800 (approaching limit) — only once per typing session
        if (current > 800 && current < MAX_CHARS && !warningShown) {
            showToast('You\'re approaching the character limit (1000 max).', 'warning');
            warningShown = true;
        }
        // Reset approaching warning if user goes back below 800
        if (current <= 800) {
            warningShown = false;
        }

        // Warning: Character count >= 1000 (exceeded limit) — only once per typing session
        if (current >= MAX_CHARS && !exceededShown) {
            showToast('You have exceeded the character limit (1000 max).', 'warning');
            exceededShown = true;
        }
        // Reset exceeded warning if user goes back below 1000
        if (current < MAX_CHARS) {
            exceededShown = false;
        }
    });

    // Set submitting state
    function setSubmitting(isSubmittingState) {
        isSubmitting = isSubmittingState;
        submitBtn.disabled = isSubmittingState;
        if (isSubmittingState) {
            submitBtnText.style.display = 'none';
            submitBtnSpinner.style.display = 'inline';
        } else {
            submitBtnText.style.display = 'inline';
            submitBtnSpinner.style.display = 'none';
        }
    }

    // Show success message
    function showSuccess() {
        form.style.display = 'none';
        successDiv.style.display = 'flex';
        successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Form submit
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const type = typeSelect.value;
        const subject = subjectInput.value.trim();
        const message = messageTextarea.value.trim();

        // Validation
        if (!type) {
            showToast('Please select a feedback type.', 'error');
            return;
        }

        if (!subject) {
            showToast('Please enter a subject.', 'error');
            return;
        }

        if (!message) {
            showToast('Please enter a message.', 'error');
            return;
        }

        if (message.length < 10) {
            showToast('Message must be at least 10 characters.', 'error');
            return;
        }

        // Frontend character limit validation
        if (message.length > MAX_CHARS) {
            showToast(`Message cannot exceed ${MAX_CHARS} characters.`, 'error');
            return;
        }

        setSubmitting(true);

        try {
            const response = await fetch('../api/feedback/submit.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type: type,
                    subject: subject,
                    message: message
                })
            });

            const result = await response.json();

            if (result.success) {
                showToast('Thank you for your feedback!', 'success');
                showSuccess();
            } else {
                // Check for spam-related messages and show as warning instead of error
                const spamMessages = [
                    'already submitted this feedback recently',
                    'limit of 3 feedback submissions per hour',
                    'Please wait'
                ];
                const isSpamMessage = spamMessages.some(msg => result.message.includes(msg));
                
                if (isSpamMessage) {
                    showToast(result.message, 'warning');
                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            }
        } catch (error) {
            showToast('Network error. Please try again.', 'error');
        } finally {
            setSubmitting(false);
        }
    });

    // Keyboard shortcut: Ctrl+S to submit — Always prevent default browser save dialog
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();  // Always prevent default browser save dialog
            if (!isSubmitting && form.style.display !== 'none') {
                form.dispatchEvent(new Event('submit'));
            }
        }
    });
});