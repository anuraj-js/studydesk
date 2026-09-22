// js/calendar.js

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('calendarContainer');
    const today = new Date();
    let currentYear = today.getFullYear();
    let currentMonth = today.getMonth();
    let selectedDate = null;
    let allEvents = [];

    // Render loading state
    function showLoading() {
        container.innerHTML = `
            <div class="calendar-wrapper">
                <div class="calendar-header">
                    <div class="month-year skeleton" style="width: 40%; height: 24px;"></div>
                    <div class="calendar-nav">
                        <button disabled>◀</button>
                        <button disabled>Today</button>
                        <button disabled>▶</button>
                    </div>
                </div>
                <div class="calendar-grid">
                    ${['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].map(() => `
                        <div class="skeleton" style="height: 30px;"></div>
                    `).join('')}
                    ${Array(35).fill(`
                        <div class="skeleton" style="height: 36px;"></div>
                    `).join('')}
                </div>
                <div class="calendar-details">
                    <div class="skeleton" style="height: 20px; width: 30%; margin-bottom: 8px;"></div>
                    <div class="skeleton" style="height: 16px; width: 60%;"></div>
                    <div class="skeleton" style="height: 16px; width: 50%;"></div>
                </div>
            </div>
        `;
    }

    // Render calendar
    function renderCalendar(events) {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const todayStr = formatDateKey(today);

        // Build grid
        let gridHtml = '';
        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        // Weekday headers
        weekdays.forEach(day => {
            gridHtml += `<div class="weekday">${day}</div>`;
        });

        // Empty days before month starts
        for (let i = 0; i < firstDay; i++) {
            gridHtml += `<div class="day empty"></div>`;
        }

        // Days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const dateKey = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = dateKey === todayStr;
            const isSelected = dateKey === selectedDate;
            const dayEvents = events[dateKey] || [];
            const taskCount = dayEvents.filter(e => e.type === 'task').length;
            const examCount = dayEvents.filter(e => e.type === 'exam').length;
            const hasEvents = taskCount > 0 || examCount > 0;

            let badgeHtml = '';
            if (taskCount > 0) badgeHtml += `<span class="day-badge badge-tasks">● ${taskCount}</span>`;
            if (examCount > 0) badgeHtml += `<span class="day-badge badge-exams">● ${examCount}</span>`;

            let dayClass = 'day';
            if (isToday) dayClass += ' today';
            if (isSelected) dayClass += ' selected';

            gridHtml += `
                <div class="${dayClass}" data-date="${dateKey}">
                    ${day}
                    ${hasEvents ? `<div class="day-badge">${badgeHtml}</div>` : ''}
                </div>
            `;
        }

        // Fill remaining cells
        const totalCells = firstDay + daysInMonth;
        const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        for (let i = 0; i < remainingCells; i++) {
            gridHtml += `<div class="day empty"></div>`;
        }

        // Get selected date events
        const selectedEvents = selectedDate && events[selectedDate] ? events[selectedDate] : [];
        const detailsHtml = renderDetails(selectedEvents);

        container.innerHTML = `
            <div class="calendar-wrapper">
                <div class="calendar-layout">
                    <div class="calendar-main">
                        <div class="calendar-header">
                            <div class="month-year">${monthNames[currentMonth]} ${currentYear}</div>
                            <div class="calendar-nav">
                                <button class="prev-btn"><i class="fas fa-chevron-left"></i></button>
                                <button class="today-btn">Today</button>
                                <button class="next-btn"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="calendar-grid">${gridHtml}</div>
                    </div>
                    <div class="calendar-side">
                        <div class="calendar-details">${detailsHtml}</div>
                    </div>
                </div>
            </div>
        `;

        // Attach event listeners
        container.querySelectorAll('.day:not(.empty)').forEach(el => {
            el.addEventListener('click', () => {
                selectedDate = el.dataset.date;
                renderCalendar(events);
            });
        });

        container.querySelector('.prev-btn')?.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            selectedDate = null;
            renderCalendar(events);
        });

        container.querySelector('.next-btn')?.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            selectedDate = null;
            renderCalendar(events);
        });

        container.querySelector('.today-btn')?.addEventListener('click', () => {
            currentYear = today.getFullYear();
            currentMonth = today.getMonth();
            selectedDate = formatDateKey(today);
            renderCalendar(events);
        });
    }

    // Render day details
    function renderDetails(events) {
        if (!selectedDate) {
            return `
                <div class="details-title">Select a date to see details</div>
                <div class="details-empty">Click on any day</div>
            `;
        }

        if (events.length === 0) {
            return `
                <div class="details-title">${formatDateDisplay(selectedDate)}</div>
                <div class="details-empty">No tasks or exams on this day</div>
            `;
        }

        const itemsHtml = events.map(e => {
            const iconClass = e.type === 'task' ? 'task' : 'exam';
            const icon = e.type === 'task' ? 'fa-check-circle' : 'fa-calendar-alt';
            return `
                <div class="detail-item">
                    <span class="detail-icon ${iconClass}"><i class="fas ${icon}"></i></span>
                    <span class="detail-text">${escapeHtml(e.title)}</span>
                    <span class="detail-date">${e.type === 'task' ? 'Task' : 'Exam'}</span>
                </div>
            `;
        }).join('');

        return `
            <div class="details-title">${formatDateDisplay(selectedDate)}</div>
            ${itemsHtml}
        `;
    }

    // Format date key (YYYY-MM-DD)
    function formatDateKey(date) {
        if (typeof date === 'string') return date;
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // Format date display (Month Day, Year)
    function formatDateDisplay(dateStr) {
        const parts = dateStr.split('-');
        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Fetch calendar data
    async function fetchCalendar() {
        showLoading();

        try {
            const response = await fetch('../api/dashboard/calendar.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                allEvents = result.data.events || {};
                // If no date selected, highlight today
                if (!selectedDate) {
                    selectedDate = formatDateKey(today);
                }
                renderCalendar(allEvents);
            } else {
                container.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Failed to load calendar</span>
                    </div>
                `;
                showToast('Error loading calendar: ' + result.message, 'error');
            }
        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Network error loading calendar</span>
                </div>
            `;
            showToast('Network error loading calendar', 'error');
        }
    }

    // Initial load
    fetchCalendar();
});