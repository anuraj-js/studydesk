let use24 = false;

const studyMessages = {
    lateNight:    ['Still awake?','Night owl mode.','Still grinding?','Late-night session?','You\'re still here?','Midnight study mode.'],
    earlyMorning: ['Early bird mode.','Up early today?','Fresh day, fresh start.','Morning grind begins.','Coffee first?','Early start. Nice.'],
    morning:      ['Let\'s get productive.','Time to lock in.','Brain: online.','Ready to cook?','Let\'s make progress.','Study mode: ON.'],
    afternoon:    ['Still got time.','Keep it rolling.','Back to the grind.','Don\'t lose momentum.','Time to lock in.','Let\'s get it done.'],
    evening:      ['Evening grind?','Let\'s finish strong.','Time to lock in.','One more session?','Study mode: ON.','Let\'s get this done.'],
    lateEvening:  ['Still grinding?','Night mode activated.','One last push?','Almost there.','Keep going.','Lock in mode.'],
    veryLate:     ['Bro, it\'s late.','Still here? Respect.','Night owl spotted.','Still studying?','One last session?','Don\'t forget to sleep.']
};

function getStudyMessage(h) {
    let msgs;
    if      (h >= 0  && h < 5)  msgs = studyMessages.lateNight;
    else if (h >= 5  && h < 8)  msgs = studyMessages.earlyMorning;
    else if (h >= 8  && h < 12) msgs = studyMessages.morning;
    else if (h >= 12 && h < 17) msgs = studyMessages.afternoon;
    else if (h >= 17 && h < 20) msgs = studyMessages.evening;
    else if (h >= 20 && h < 23) msgs = studyMessages.lateEvening;
    else                         msgs = studyMessages.veryLate;
    return msgs[Math.floor(Math.random() * msgs.length)];
}

function getGreeting(h) {
    if (h < 12) return 'Good Morning';
    if (h < 17) return 'Good Afternoon';
    return 'Good Evening';
}

function updateGreeting() {
    const h = new Date().getHours();
    const userName = document.querySelector('.sidebar-name')?.textContent || 'Student';
    const firstName = userName.split(' ')[0];
    
    document.getElementById('greeting').textContent = `${getGreeting(h)}, ${firstName}!`;
    document.getElementById('studyMessage').textContent = getStudyMessage(h);
}

function initClock() {
    const clockEl = document.getElementById('clock');
    const dateEl = document.getElementById('date');
    const toggleBtn = document.getElementById('clockToggle');

    function formatTime(d) {
        return d.toLocaleTimeString('en-US', {
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            hour12: !use24
        });
    }

    function formatDate(d) {
        return d.toLocaleDateString('en-US', {
            weekday: 'long', month: 'short', day: 'numeric', year: 'numeric'
        });
    }

    function update() {
        const now = new Date();
        clockEl.textContent = formatTime(now);
        dateEl.textContent = formatDate(now);
    }

    function syncToggleLabel() {
        toggleBtn.innerHTML = use24
            ? '<i class="fa-regular fa-clock"></i> Switch to 12-Hour'
            : '<i class="fa-regular fa-clock"></i> Switch to 24-Hour';
    }

    syncToggleLabel();
    update();
    setInterval(update, 1000);

    toggleBtn.addEventListener('click', () => {
        use24 = !use24;
        localStorage.setItem('sdl-clock-24', use24 ? '1' : '0');
        syncToggleLabel();
        update();
    });
}

function renderTasks(tasks) {
    const el = document.getElementById('recentTasks');
    if (!tasks.length) {
        el.innerHTML = '<div class="empty-state">No pending tasks</div>';
        return;
    }

    el.innerHTML = tasks.map(t => `
        <div class="task-item">
            <input type="checkbox" class="task-checkbox" data-id="${t.id}" />
            <div class="task-content">
                <div class="task-title">${esc(t.title)}</div>
                ${t.due_date ? `<div class="task-due"><i class="fa-regular fa-calendar"></i> ${t.due_date}</div>` : ''}
            </div>
            <span class="badge badge-${t.priority}">${t.priority}</span>
        </div>
    `).join('');

    el.querySelectorAll('.task-checkbox').forEach(cb => {
        cb.addEventListener('change', async () => {
            const id = cb.dataset.id;
            try {
                const res = await apiFetch('../api/tasks.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'toggle', id: id })
                });
                if (res.success) {
                    showToast('Task completed!', 'success');
                    loadDashboardData(); // Refresh stats and list
                } else {
                    showToast(res.message, 'error');
                    cb.checked = false;
                }
            } catch (err) {
                showToast('Failed to toggle task.', 'error');
                cb.checked = false;
            }
        });
    });
}

function renderExams(exams) {
    const el = document.getElementById('upcomingExams');
    if (!exams.length) {
        el.innerHTML = '<div class="empty-state">No upcoming exams</div>';
        return;
    }

    const today = new Date(); today.setHours(0,0,0,0);

    el.innerHTML = exams.map(e => {
        const diff = Math.round((new Date(e.date + 'T00:00:00') - today) / 864e5);
        const urgent = diff <= 3;
        const label = diff === 0 ? 'Today!' : diff + ' days';
        return `
            <div class="exam-item">
                <div>
                    <div class="exam-subject">${esc(e.subject)}</div>
                    <div class="exam-date">${esc(e.name)} &middot; ${new Date(e.date + 'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric'})}</div>
                </div>
                <div class="exam-countdown ${urgent ? 'urgent' : ''}">${label}</div>
            </div>
        `;
    }).join('');
}

async function loadDashboardData() {
    try {
        const response = await apiFetch('../api/home.php');
        
        if (!response.success) {
            showToast(response.message, 'error');
            return;
        }

        const data = response.data;

        const statPending = document.getElementById('statPending');
        statPending.classList.remove('skeleton');
        statPending.textContent = data.stats.pending_tasks;

        const statCompleted = document.getElementById('statCompleted');
        statCompleted.classList.remove('skeleton');
        statCompleted.textContent = data.stats.done_tasks;

        const statSessions = document.getElementById('statSessions');
        statSessions.classList.remove('skeleton');
        statSessions.textContent = data.stats.today_sessions;

        const statExam = document.getElementById('statExam');
        statExam.classList.remove('skeleton');
        if (data.stats.next_exam_date) {
            const today = new Date(); today.setHours(0,0,0,0);
            const diff = Math.round((new Date(data.stats.next_exam_date + 'T00:00:00') - today) / 864e5);
            statExam.textContent = diff === 0 ? 'Today' : diff + 'd';
        } else {
            statExam.textContent = '—';
        }

        renderTasks(data.recent_tasks);
        renderExams(data.upcoming_exams);

        if (typeof window.initCalendar === 'function') {
            window.initCalendar(data.calendar_events);
        }

    } catch (err) {
        showToast('Failed to load dashboard data.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    use24 = localStorage.getItem('sdl-clock-24') === '1';

    updateGreeting();
    initClock();
    loadDashboardData();
});