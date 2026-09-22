// js/progress.js

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('progressTracker');

    const MILESTONES = [1, 7, 14, 30, 45, 60, 75, 90];
    const MAX = 90;
    const MILESTONE_POSITIONS = {
        1:  (1  / MAX) * 100,
        7:  (7  / MAX) * 100,
        14: (14 / MAX) * 100,
        30: (30 / MAX) * 100,
        45: (45 / MAX) * 100,
        60: (60 / MAX) * 100,
        75: (75 / MAX) * 100,
        90: 100
    };

    function getStreakColor(streak) {
        if (streak >= 60) return 'gradient-fire';
        if (streak >= 30) return 'deep-red';
        if (streak >= 14) return 'red';
        if (streak >= 1)  return 'orange';
        return 'none';
    }

    function getNextMilestone(streak) {
        return MILESTONES.find(m => m > streak) || null;
    }

    function getStreakPercent(streak) {
        if (streak === 0) return 0;
        if (streak >= MAX) return 100;
        return Math.min((streak / MAX) * 100, 100);
    }

    function getMilestoneLabel(streak) {
        const next = getNextMilestone(streak);
        if (!next) return 'Maximum streak reached! Keep it going!';
        const remaining = next - streak;
        return remaining === 1
            ? '1 more day to reach ' + next + ' day streak!'
            : remaining + ' more days to reach ' + next + ' days streak!';
    }

    function showLoading() {
        container.innerHTML = `
            <div class="progress-stats">
                ${Array(5).fill(`
                    <div class="progress-stat-card loading">
                        <div class="stat-number skeleton"></div>
                        <div class="stat-label skeleton"></div>
                    </div>
                `).join('')}
            </div>
            <div class="progress-extra">
                <div class="skeleton" style="width: 100%; height: 60px;"></div>
            </div>
        `;
    }

    function renderStats(data) {
        const stats = [
            { label: 'Tasks Completed',   icon: 'tasks',           value: data.tasks_completed   || 0 },
            { label: 'Exams Created',     icon: 'exams-created',   value: data.exams_created     || 0 },
            { label: 'Topics Completed',  icon: 'topics',          value: data.topics_completed  || 0 },
            { label: 'Exams Completed',   icon: 'exams-completed', value: data.exams_completed   || 0 },
            { label: 'Pomodoro Sessions', icon: 'pomodoro',        value: data.pomodoro_sessions || 0 }
        ];

        const statCards = stats.map(stat => `
            <div class="progress-stat-card">
                <div class="stat-icon ${stat.icon}"><i class="fas ${getIconClass(stat.icon)}"></i></div>
                <div class="stat-number">${stat.value}</div>
                <div class="stat-label">${stat.label}</div>
            </div>
        `).join('');

        const streak               = data.streak || 0;
        const hasActivityToday     = data.has_activity_today || false;
        const hasActivityYesterday = data.has_activity_yesterday || false;
        const hasEverHadActivity   = data.longest_streak > 0;

        const barStreak      = (streak === 0 && hasActivityYesterday) ? (data.longest_streak || 0) : streak;
        const colorClass     = getStreakColor(barStreak);
        const streakPercent  = getStreakPercent(barStreak);
        const milestoneLabel = streak > 0 ? getMilestoneLabel(streak) : '';

        const milestoneDots = MILESTONES.map(m => {
            const passed = barStreak >= m;
            const pos    = MILESTONE_POSITIONS[m];
            return '<span class="milestone-dot ' + (passed ? 'passed' : '') + ' streak-color-' + colorClass + '" style="left: ' + pos + '%">' + m + '</span>';
        }).join('');

        let streakMessage = '';
        let streakIcon    = '';

        if (streak > 0) {
            streakMessage = streak === 1 ? '1 day' : streak + ' days';
            if (hasActivityToday) {
                streakIcon = '<i class="fas fa-check-circle streak-safe-icon"></i>';
            }
        } else if (hasActivityYesterday) {
            const atRisk  = data.longest_streak || 0;
            const dayWord = atRisk === 1 ? 'day' : 'days';
            streakIcon    = '<i class="fas fa-exclamation-triangle streak-atrisk-icon"></i>';
            streakMessage = atRisk + ' ' + dayWord + ' streak at risk! Do at least 1 activity today to maintain it.';
        } else if (hasEverHadActivity) {
            streakIcon    = '<i class="fas fa-undo"></i>';
            streakMessage = 'Streak broken. Start fresh today!';
        } else {
            streakIcon    = '<i class="fas fa-plus-circle"></i>';
            streakMessage = 'No activity yet. Start today!';
        }

        const longestStreak  = data.longest_streak || 0;
        const longestRange   = data.longest_streak_range || null;
        const longestDayWord = longestStreak === 1 ? 'day' : 'days';
        let longestText      = '';
        if (longestStreak > 0 && longestRange) {
            longestText = '<i class="fas fa-trophy"></i> Longest: ' + longestStreak + ' ' + longestDayWord + ' (' + longestRange + ')';
        } else if (longestStreak > 0) {
            longestText = '<i class="fas fa-trophy"></i> Longest: ' + longestStreak + ' ' + longestDayWord;
        }

        const productiveDay   = data.most_productive_day || null;
        const productiveText  = productiveDay ? productiveDay.day : 'No activity yet';
        const productiveCount = productiveDay ? productiveDay.count + ' activities' : '';

        const lastActiveDay  = data.last_active || null;
        const lastActiveText = lastActiveDay ? lastActiveDay.day : 'No activity yet';

        container.innerHTML = `
            <div class="progress-stats">${statCards}</div>
            <div class="progress-extra">
                <div class="streak-section">
                    <span class="streak-label"><i class="fas fa-fire"></i> Streak</span>
                    <div class="streak-bar-wrapper">
                        <div class="streak-milestones">${milestoneDots}</div>
                        <div class="streak-bar-track">
                            <div class="streak-bar-fill streak-color-${colorClass}" style="width: ${streakPercent}%;"></div>
                        </div>
                    </div>
                    <span class="streak-number ${streak > 0 ? 'streak-active' : ''}">${streakIcon} ${streakMessage}</span>
                    ${streak > 0 ? '<span class="streak-next-milestone">' + milestoneLabel + '</span>' : ''}
                    ${longestText ? '<span class="streak-longest">' + longestText + '</span>' : ''}
                    <span class="streak-based-on">Based on last 90 days</span>
                </div>
                <div class="productive-section">
                    <span class="productive-label"><i class="fas fa-chart-line"></i> Best Day</span>
                    <span class="productive-value">${productiveText}</span>
                    <span class="productive-count">${productiveCount}</span>
                    <span class="productive-label last-active-label"><i class="fas fa-calendar-check"></i> Last Active</span>
                    <span class="productive-value last-active-value">${lastActiveText}</span>
                </div>
            </div>
        `;
    }

    function getIconClass(icon) {
        const map = {
            'tasks':           'fa-check-circle',
            'exams-created':   'fa-pen',
            'topics':          'fa-list',
            'exams-completed': 'fa-flag-checkered',
            'pomodoro':        'fa-clock'
        };
        return map[icon] || 'fa-circle';
    }

    async function fetchProgress() {
        showLoading();
        try {
            const response = await fetch('../api/dashboard/progress.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                renderStats(result.data);
            } else {
                container.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Failed to load progress</span>
                    </div>
                `;
                showToast('Error loading progress: ' + result.message, 'error');
            }
        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Network error loading progress</span>
                </div>
            `;
            showToast('Network error loading progress', 'error');
        }
    }

    fetchProgress();
});