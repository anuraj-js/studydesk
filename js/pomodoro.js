// js/pomodoro.js

document.addEventListener('DOMContentLoaded', () => {
    const minsInput = document.getElementById('mins-input');
    const secsInput = document.getElementById('secs-input');
    const playBtn = document.getElementById('playBtn');
    const pauseBtn = document.getElementById('pauseBtn');
    const resetBtn = document.getElementById('resetBtn');
    const taskNameInput = document.getElementById('taskName');
    const timerLabel = document.getElementById('timerLabel');
    const statusDot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');
    const modeBtns = document.querySelectorAll('.mode-btn');
    const modeContainer = document.getElementById('modeContainer');
    const pomodoroContainer = document.getElementById('pomodoroContainer');
    const progressBarFill = document.getElementById('progressBarFill');
    const progressText = document.getElementById('progressText');
    const skipConfirmCheckbox = document.getElementById('skipTaskNameConfirm');

    // Modal elements
    const modalOverlay = document.getElementById('modalOverlay');
    const modalMessage = document.getElementById('modalMessage');
    const modalBtn = document.getElementById('modalBtn');

    // Time adjustment buttons
    const timeAdjustBtns = document.querySelectorAll('.time-adjust-btn');

    const MODES = {
        'pomodoro': { label: 'Focus', default: 25, max: 60 },
        'short-break': { label: 'Short Break', default: 5, max: 15 },
        'long-break': { label: 'Long Break', default: 15, max: 30 }
    };

    let currentMode = 'pomodoro';
    let totalSeconds = MODES.pomodoro.default * 60;
    let remainingSeconds = totalSeconds;
    let intervalId = null;
    let isRunning = false;
    let isPaused = false;
    let isCompleted = false;
    let targetTime = null;
    let beepAudio = null;
    let lastReminderShown = 0;

    // --- BeforeUnload Handler — Warn when timer is running or paused ---
    window.addEventListener('beforeunload', function(e) {
        if (isRunning || isPaused) {
            const message = 'Changes you made may not be saved.';
            e.preventDefault();
            e.returnValue = message;
            return message;
        }
    });

    // --- Load checkbox state from localStorage ---
    const STORAGE_KEY = 'pomodoro_skip_task_name';
    if (skipConfirmCheckbox) {
        const savedState = localStorage.getItem(STORAGE_KEY);
        if (savedState === 'true') {
            skipConfirmCheckbox.checked = true;
        }

        skipConfirmCheckbox.addEventListener('change', function() {
            localStorage.setItem(STORAGE_KEY, this.checked ? 'true' : 'false');
            if (this.checked) {
                showToast('Proceed without task name enabled.', 'info');
            } else {
                showToast('Proceed without task name disabled.', 'info');
            }
        });
    }

    // Helper Functions
    function updateStatus(label, state) {
        timerLabel.textContent = label;
        timerLabel.className = 'timer-label' + (state ? ' ' + state : '');
        statusText.textContent = label;
        statusDot.className = 'dot' + (state ? ' ' + state : '');
    }

    function updateModeClass(mode) {
        modeContainer.className = 'mode-container mode-' + mode;
        pomodoroContainer.className = 'pomodoro-container mode-' + mode;
    }

    function setInputsEnabled(disabled) {
        minsInput.disabled = disabled;
        secsInput.disabled = disabled;
        timeAdjustBtns.forEach(btn => btn.disabled = disabled);
    }

    function setControls(playDisabled, pauseDisabled) {
        playBtn.disabled = playDisabled;
        pauseBtn.disabled = pauseDisabled;
    }

    function displayTime() {
        const mins = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        minsInput.value = String(mins).padStart(2, '0');
        secsInput.value = String(secs).padStart(2, '0');
        document.title = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')} - Pomodoro Timer`;
    }

    function getTimeFromInputs() {
        const mins = parseInt(minsInput.value) || 0;
        const secs = parseInt(secsInput.value) || 0;
        return (mins * 60) + secs;
    }

    // Update progress bar
    function updateProgress() {
        const percent = totalSeconds > 0 ? (remainingSeconds / totalSeconds) * 100 : 0;
        progressBarFill.style.width = Math.max(0, percent) + '%';
        progressText.textContent = Math.round(Math.max(0, percent)) + '%';
    }

    // Mode Switching
    modeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (isRunning) {
                showToast('Stop the timer first before switching modes.', 'warning');
                return;
            }

            modeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentMode = this.dataset.mode;

            const modeNames = {
                'pomodoro': 'Pomodoro mode',
                'short-break': 'Short Break',
                'long-break': 'Long Break'
            };
            showToast(`Switched to ${modeNames[currentMode] || currentMode}`, 'info');

            resetTimer();
        });
    });

    // Time adjustment buttons
    timeAdjustBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (isRunning) return;

            const seconds = parseInt(this.dataset.seconds);
            const max = MODES[currentMode].max * 60;
            const current = getTimeFromInputs();
            const newTotal = current + seconds;

            if (newTotal > max) {
                showToast(`Maximum time for ${currentMode.replace('-', ' ')} is ${MODES[currentMode].max} minutes.`, 'error');
                return;
            }

            totalSeconds = newTotal;
            remainingSeconds = newTotal;
            displayTime();
            updateProgress();
        });
    });

    // Timer Functions
    function resetTimer() {
        clearInterval(intervalId);
        intervalId = null;
        isRunning = false;
        isPaused = false;
        isCompleted = false;
        targetTime = null;
        stopBeep();
        closeModal();
        lastReminderShown = 0;

        const mode = MODES[currentMode];
        totalSeconds = mode.default * 60;
        remainingSeconds = totalSeconds;

        displayTime();
        updateProgress();
        setInputsEnabled(false);
        taskNameInput.disabled = false;
        taskNameInput.value = '';
        setControls(false, true);
        updateStatus('Ready', '');
        document.title = 'Pomodoro Timer - StudyDesk';
        updateModeClass(currentMode);
    }

    function validateTime() {
        const mins = parseInt(minsInput.value) || 0;
        const secs = parseInt(secsInput.value) || 0;
        const max = MODES[currentMode].max;

        if (mins > max) {
            showToast(`Maximum time for ${currentMode.replace('-', ' ')} is ${max} minutes.`, 'error');
            minsInput.value = String(max).padStart(2, '0');
            return false;
        }

        if (secs > 59) {
            showToast('Seconds cannot exceed 59.', 'error');
            secsInput.value = '59';
            return false;
        }

        return true;
    }

    // Tick Logic
    function tick() {
        if (!targetTime) return;

        const now = new Date();
        const diff = Math.floor((targetTime - now) / 1000);

        if (diff <= 0) {
            remainingSeconds = 0;
            displayTime();
            updateProgress();

            clearInterval(intervalId);
            intervalId = null;
            isRunning = false;
            isPaused = false;
            isCompleted = true;

            updateStatus('Session completed!', 'completed');
            setControls(true, true);
            setInputsEnabled(false);
            taskNameInput.disabled = false;
            lastReminderShown = 0;

            showModal('Your session is complete. Great job!');
            playBeep();

            if (currentMode === 'pomodoro') {
                logSession();
            }

            return;
        }

        // Info toast: Countdown reminders at exactly 10 min, 5 min, 1 min
        if (currentMode === 'pomodoro' && diff > 0) {
            const secondsRemaining = diff;
            if (secondsRemaining === 600 && lastReminderShown !== 600) {
                showToast('10 minutes remaining!', 'info');
                lastReminderShown = 600;
            } else if (secondsRemaining === 300 && lastReminderShown !== 300) {
                showToast('5 minutes remaining!', 'info');
                lastReminderShown = 300;
            } else if (secondsRemaining === 60 && lastReminderShown !== 60) {
                showToast('1 minute remaining!', 'info');
                lastReminderShown = 60;
            }
        }

        remainingSeconds = diff;
        displayTime();
        updateProgress();
    }

    // Start Timer
    function startTimer(total) {
        totalSeconds = total;
        remainingSeconds = total;
        targetTime = new Date(Date.now() + (total * 1000));
        lastReminderShown = 0;

        setInputsEnabled(true);
        taskNameInput.disabled = true;
        setControls(true, false);
        isRunning = true;
        isPaused = false;
        isCompleted = false;

        const modeLabel = MODES[currentMode].label;
        updateStatus(modeLabel + '...', 'running');
        updateModeClass(currentMode);
        updateProgress();

        if (intervalId) {
            clearInterval(intervalId);
        }
        intervalId = setInterval(tick, 100);
    }

    // Modal Functions
    function showModal(message) {
        modalMessage.textContent = message;
        modalOverlay.classList.add('open');
    }

    function closeModal() {
        modalOverlay.classList.remove('open');
    }

    // Beep Functions
    function playBeep() {
        try {
            beepAudio = new Audio('../audio/sound.mp3');
            beepAudio.loop = true;
            beepAudio.play().catch(function(err) {
                console.error('Beep error:', err);
            });
        } catch (error) {
            console.error('Audio error:', error);
        }
    }

    function stopBeep() {
        if (beepAudio) {
            beepAudio.pause();
            beepAudio.currentTime = 0;
            beepAudio = null;
        }
    }

    // Modal event listeners
    modalBtn.addEventListener('click', function() {
        stopBeep();
        closeModal();
    });

    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            stopBeep();
            closeModal();
        }
    });

    // Log Session
    async function logSession() {
        const taskName = taskNameInput.value.trim() || 'Untitled';

        try {
            const response = await fetch('../api/pomodoro/log.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    task_name: taskName,
                    total_seconds: totalSeconds
                })
            });
            const result = await response.json();
            if (!result.success) {
                showToast('Failed to log session.', 'error');
            }
        } catch (error) {
            showToast('Network error logging session.', 'error');
        }
    }

    // Controls — Play button
    playBtn.addEventListener('click', function() {
        if (!validateTime()) return;

        const total = getTimeFromInputs();
        if (total <= 0) {
            showToast('Set a time first', 'error');
            return;
        }

        if (total < 60) {
            showToast('Timer must be at least 1 minute.', 'error');
            return;
        }

        if (total < 300) {
            showToast('Timer is less than 5 minutes. Consider a longer session.', 'warning');
        }

        const taskName = taskNameInput.value.trim();
        if (taskName.length > 100) {
            showToast('Task name is too long (max 100 characters).', 'error');
            return;
        }

        if (!taskName && (!skipConfirmCheckbox || !skipConfirmCheckbox.checked)) {
            showConfirm('No task name entered. Continue anyway?', function() {
                startTimer(total);
            });
            return;
        }

        startTimer(total);
    });

    pauseBtn.addEventListener('click', function() {
        if (isRunning) {
            clearInterval(intervalId);
            intervalId = null;
            isRunning = false;
            isPaused = true;
            updateStatus('Paused', 'paused');
            setControls(false, true);
        }
    });

    resetBtn.addEventListener('click', function() {
        if (isRunning) {
            showConfirm('Reset timer? Current session will not be logged.', function() {
                resetTimer();
            });
            return;
        }
        resetTimer();
    });

    // Input Validation
    minsInput.addEventListener('input', function() {
        if (isRunning) return;
        let val = parseInt(this.value) || 0;
        const max = MODES[currentMode].max;
        if (val > max) {
            showToast(`Maximum time for ${currentMode.replace('-', ' ')} is ${max} minutes.`, 'error');
            this.value = max;
        }
        if (val < 0) this.value = 0;
    });

    secsInput.addEventListener('input', function() {
        if (isRunning) return;
        let val = parseInt(this.value) || 0;
        if (val > 59) {
            showToast('Seconds cannot exceed 59.', 'error');
            this.value = 59;
        }
        if (val < 0) this.value = 0;
    });

    // Input Click Clear
    minsInput.addEventListener('click', function() {
        if (!isRunning) {
            this.value = '';
        }
    });

    secsInput.addEventListener('click', function() {
        if (!isRunning) {
            this.value = '';
        }
    });

    // Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === ' ' && e.target.tagName !== 'INPUT') {
            e.preventDefault();
            if (isRunning) {
                pauseBtn.click();
            } else if (!playBtn.disabled) {
                playBtn.click();
            }
        }
        if (e.key === 'Escape' && e.target.tagName !== 'INPUT') {
            resetBtn.click();
        }
    });

    // Initial State
    resetTimer();
});