// js/dashboard.js

document.addEventListener('DOMContentLoaded', () => {

    // Clock Variables
    const clockEl = document.getElementById('clock');
    const dateEl = document.getElementById('date');
    const greetingEl = document.getElementById('greeting');
    const messageEl = document.getElementById('studyMessage');
    const formatToggleBtn = document.getElementById('formatToggleBtn');

    // Load saved preference from localStorage, default to 12-hour format
    const savedFormat = localStorage.getItem('timeFormat');
    let is12hrsFormat = savedFormat !== null ? savedFormat === '12' : true;

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Dynamic messages by time period
    const studyMessages = {
        lateNight: [
            "Still awake?",
            "Night owl mode.",
            "Still grinding?",
            "Late-night session?",
            "You're still here?",
            "Midnight study mode."
        ],
        earlyMorning: [
            "Early bird mode.",
            "Up early today?",
            "Fresh day, fresh start.",
            "Morning grind begins.",
            "Coffee first?",
            "Early start. Nice."
        ],
        morning: [
            "Let's get productive.",
            "Time to lock in.",
            "Brain: online.",
            "Ready to cook?",
            "Let's make progress.",
            "Study mode: ON."
        ],
        afternoon: [
            "Still got time.",
            "Keep it rolling.",
            "Back to the grind.",
            "Don't lose momentum.",
            "Time to lock in.",
            "Let's get it done."
        ],
        evening: [
            "Evening grind?",
            "Let's finish strong.",
            "Time to lock in.",
            "One more session?",
            "Study mode: ON.",
            "Let's get this done."
        ],
        lateEvening: [
            "Still grinding?",
            "Night mode activated.",
            "One last push?",
            "Almost there.",
            "Keep going.",
            "Lock in mode."
        ],
        veryLate: [
            "Bro, it's late.",
            "Still here? Respect.",
            "Night owl spotted.",
            "Still studying?",
            "One last session?",
            "Don't forget to sleep."
        ]
    };

    // Helper: Pad string
    function stringPadding(value) {
        return value.toString().padStart(2, '0');
    }

    // Get dynamic message based on hour
    function getStudyMessage(hour) {
        let messages = [];

        if (hour >= 0 && hour < 5) {
            messages = studyMessages.lateNight;
        } else if (hour >= 5 && hour < 8) {
            messages = studyMessages.earlyMorning;
        } else if (hour >= 8 && hour < 12) {
            messages = studyMessages.morning;
        } else if (hour >= 12 && hour < 17) {
            messages = studyMessages.afternoon;
        } else if (hour >= 17 && hour < 20) {
            messages = studyMessages.evening;
        } else if (hour >= 20 && hour < 23) {
            messages = studyMessages.lateEvening;
        } else {
            messages = studyMessages.veryLate;
        }

        const randomIndex = Math.floor(Math.random() * messages.length);
        return messages[randomIndex];
    }

    // Get current time
    function getPresentTime(now) {
        let rawHours = now.getHours();
        return {
            hours: stringPadding(is12hrsFormat ? (rawHours % 12 || 12) : rawHours),
            minutes: stringPadding(now.getMinutes()),
            seconds: stringPadding(now.getSeconds()),
            ampm: is12hrsFormat ? (rawHours < 12 ? 'AM' : 'PM') : ''
        };
    }

    // Get current date
    function getPresentDate(now) {
        return {
            today: days[now.getDay()],
            dateNum: stringPadding(now.getDate()),
            month: months[now.getMonth()],
            year: now.getFullYear().toString()
        };
    }

    // Render clock
    function renderClock(timeObj, dateObj) {
        const timeString = `${timeObj.hours}:${timeObj.minutes}:${timeObj.seconds} ${timeObj.ampm}`.trim();
        const dateString = `${dateObj.today}, ${dateObj.month} ${dateObj.dateNum}, ${dateObj.year}`;
        clockEl.textContent = timeString;
        dateEl.textContent = dateString;
    }

    // Update clock (only clock and date, not greeting)
    function updateClock() {
        const now = new Date();
        const timeObj = getPresentTime(now);
        const dateObj = getPresentDate(now);
        renderClock(timeObj, dateObj);
    }

    // Update greeting with username and dynamic message
    function updateGreeting() {
        const hour = new Date().getHours();
        const greetingElement = document.getElementById('greeting');
        const messageElement = document.getElementById('studyMessage');
        const username = greetingElement.dataset.username || 'User';

        let greeting = '';
        if (hour < 12) {
            greeting = 'Good Morning';
        } else if (hour < 17) {
            greeting = 'Good Afternoon';
        } else {
            greeting = 'Good Evening';
        }

        greetingElement.textContent = `${greeting}, ${username}!`;
        messageElement.textContent = getStudyMessage(hour);
    }

    // Update toggle button text based on current format
    function updateToggleButton() {
        formatToggleBtn.innerHTML = is12hrsFormat
            ? '<i class="fas fa-clock"></i> Switch to 24-Hour Format'
            : '<i class="fas fa-clock"></i> Switch to 12-Hour Format';
    }

    // Format toggle
    formatToggleBtn.addEventListener('click', () => {
        is12hrsFormat = !is12hrsFormat;
        localStorage.setItem('timeFormat', is12hrsFormat ? '12' : '24');
        updateToggleButton();
        updateClock();
        updateGreeting();
    });

    // Show skeleton loading for activity list
    function showListLoading(container) {
        container.innerHTML = `
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
        `;
    }

    // Fetch recent activity
    async function fetchRecentActivity() {
        const container = document.getElementById('recentActivity');
        showListLoading(container);
        try {
            const response = await fetch('../api/dashboard/activity.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success && result.data.activities.length > 0) {
                container.innerHTML = result.data.activities.map(activity => {
                    const icon = getActivityIcon(activity.activity_type);
                    const details = typeof activity.additional_details === 'string'
                        ? JSON.parse(activity.additional_details)
                        : activity.additional_details;
                    const displayText = getActivityDisplay(activity.activity_type, activity.related_item_name, details);
                    return `
                        <div class="activity-item">
                            <span class="activity-text">
                                ${icon} ${escapeHtml(displayText)}
                            </span>
                            <span class="activity-time">${formatTime(activity.created_at)}</span>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <span class="empty-title">No recent activity</span>
                        <span class="empty-sub">Start using StudyDesk to see activity here</span>
                    </div>
                `;
            }
        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Failed to load activity. Please try again.</span>
                </div>
            `;
        }
    }

    // Get activity icon
    function getActivityIcon(type) {
        const icons = {
            'task_completed': '<i class="fas fa-check-circle" style="color: var(--color-success);"></i>',
            'exam_created': '<i class="fas fa-pen" style="color: var(--color-primary);"></i>',
            'topic_completed': '<i class="fas fa-check-circle" style="color: var(--color-success);"></i>',
            'exam_completed': '<i class="fas fa-flag-checkered" style="color: var(--color-primary);"></i>',
            'pomodoro_completed': '<i class="fas fa-clock" style="color: var(--color-primary);"></i>'
        };
        return icons[type] || '<i class="fas fa-circle"></i>';
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Format time for dashboard (short)
    function formatTime(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    // Get activity display text with seconds for Pomodoro
    function getActivityDisplay(type, itemName, details) {
        const labels = {
            'task_completed': 'Task Completed',
            'exam_created': 'Exam Created',
            'topic_completed': 'Topic Completed',
            'exam_completed': 'Exam Completed',
            'pomodoro_completed': 'Pomodoro Session'
        };

        let label = labels[type] || 'Activity';

        if (type === 'pomodoro_completed' && details && details.total_seconds !== undefined) {
            const totalSecs = details.total_seconds;
            const mins = Math.floor(totalSecs / 60);
            const secs = totalSecs % 60;

            let timeStr = '';
            if (mins > 0 && secs > 0) {
                timeStr = `${mins}m ${secs}s`;
            } else if (mins > 0) {
                timeStr = `${mins}m`;
            } else {
                timeStr = `${secs}s`;
            }

            return `${itemName} — ${label} (${timeStr})`;
        }

        return `${itemName} — ${label}`;
    }

    // Init
    updateGreeting();
    updateToggleButton();
    updateClock();
    fetchRecentActivity();

    // Clock update every second (only clock, greeting stays)
    setInterval(updateClock, 1000);

});