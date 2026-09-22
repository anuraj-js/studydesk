// js/exams.js

document.addEventListener('DOMContentLoaded', () => {
    const examList = document.getElementById('examList');
    const examPanel = document.getElementById('examPanel');
    const examOverlay = document.getElementById('examPanelOverlay');
    const openExamBtn = document.getElementById('openExamPanelBtn');
    const closeExamBtn = document.getElementById('closeExamPanelBtn');
    const examForm = document.getElementById('examForm');
    const examIdInput = document.getElementById('examId');
    const examNameInput = document.getElementById('examName');
    const examSubjectInput = document.getElementById('examSubject');
    const examDateInput = document.getElementById('examDate');
    const examSubmitBtn = document.getElementById('examSubmitBtn');
    const examPanelTitle = document.getElementById('examPanelTitle');
    const searchInput = document.getElementById('examSearchInput');
    const clearSearchBtn = document.getElementById('clearExamSearchBtn');
    const searchInfo = document.getElementById('examSearchInfo');
    const sortSelect = document.getElementById('sortBy');

    let currentExams = [];
    let searchQuery = '';
    let searchTimeout = null;
    let currentSort = 'exam_date_asc';

    // Sort select change listener — re-fetch with new sort
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            currentSort = this.value;
            fetchExams();

            // Info toast: Show which sort is active
            const sortNames = {
                'exam_date_asc': 'Sorted by Date (Earliest first)',
                'exam_date_desc': 'Sorted by Date (Latest first)',
                'exam_name': 'Sorted by Name (A → Z)',
                'subject': 'Sorted by Subject (A → Z)',
                'progress': 'Sorted by Progress (Most complete)'
            };
            showToast(sortNames[currentSort] || 'Sorted', 'info');
        });
    }

    // Search filter with debouncing
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value;

            if (clearSearchBtn) {
                clearSearchBtn.style.display = query ? 'block' : 'none';
            }

            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            searchTimeout = setTimeout(() => {
                searchQuery = query.toLowerCase().trim();
                renderExams();
                searchTimeout = null;
            }, 300);
        });

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }

            if (e.key === 'Escape' && document.activeElement === searchInput) {
                e.preventDefault();
                searchInput.value = '';
                searchQuery = '';
                if (clearSearchBtn) {
                    clearSearchBtn.style.display = 'none';
                }
                renderExams();
                searchInput.blur();
            }
        });
    }

    // Clear search button
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchQuery = '';
            this.style.display = 'none';
            renderExams();
            searchInput.focus();
            if (searchInfo) {
                searchInfo.style.display = 'none';
            }
        });
    }

    // Exam Panel
    function openExamPanel(exam = null) {
        if (exam) {
            examPanelTitle.textContent = 'Edit Exam';
            examIdInput.value = exam.id;
            examNameInput.value = exam.exam_name;
            examSubjectInput.value = exam.subject;
            examDateInput.value = exam.exam_date;
            examSubmitBtn.textContent = 'Update Exam';
        } else {
            examPanelTitle.textContent = 'Add New Exam';
            examIdInput.value = '';
            examForm.reset();
            examSubmitBtn.textContent = 'Create exam plan';
        }
        examPanel.classList.add('open');
        examOverlay.classList.add('open');
    }

    function closeExamPanel() {
        examPanel.classList.remove('open');
        examOverlay.classList.remove('open');
        examForm.reset();
        examIdInput.value = '';
    }

    openExamBtn.addEventListener('click', () => openExamPanel());
    closeExamBtn.addEventListener('click', closeExamPanel);
    examOverlay.addEventListener('click', closeExamPanel);

    // Show skeleton loading for exam cards
    function showExamLoading() {
        const skeletons = `
            <div class="skeleton-exam-card">
                <div class="skeleton-header">
                    <div>
                        <div class="skeleton-title"></div>
                        <div class="skeleton-subject"></div>
                    </div>
                    <div class="skeleton-actions">
                        <div class="skeleton-btn"></div>
                        <div class="skeleton-btn"></div>
                    </div>
                </div>
                <div class="skeleton-countdown"></div>
                <div class="skeleton-progress">
                    <div class="skeleton-bar"></div>
                    <div class="skeleton-text"></div>
                </div>
            </div>
        `;
        examList.innerHTML = skeletons.repeat(2);
    }

    // Check if topic name already exists in the exam
    function topicNameExists(examId, topicName) {
        const exam = currentExams.find(e => e.id === examId);
        if (!exam) return false;

        // We need to check against existing topics for this exam
        // Since we don't have topics in currentExams, we'll check the DOM
        const topicItems = document.querySelectorAll(`#topics-${examId} .topic-item`);
        let exists = false;
        topicItems.forEach(item => {
            const titleEl = item.querySelector('.topic-title');
            if (titleEl && titleEl.textContent.trim().toLowerCase() === topicName.toLowerCase()) {
                exists = true;
            }
        });
        return exists;
    }

    // Check exam date warnings (only after create/update)
    function checkExamDateWarnings(examDate) {
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];

        if (examDate === todayStr) {
            showToast('Reminder: This exam is today!', 'warning');
        } else if (examDate === tomorrowStr) {
            showToast('Reminder: This exam is tomorrow!', 'warning');
        }
    }

    // Fetch Exams with sort parameter
    async function fetchExams() {
        showExamLoading();

        try {
            const response = await fetch(`../api/exams/exams.php?sort=${currentSort}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                currentExams = result.data.exams || [];
                renderExams();
            } else {
                showToast('Failed to load exams: ' + result.message, 'error');
                showErrorState('Failed to load exams. Please refresh the page.');
            }
        } catch (error) {
            showToast('Network error loading exams', 'error');
            showErrorState('Network error. Please check your connection.');
        }
    }

    function showErrorState(message) {
        examList.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <span class="error-title">Something went wrong</span>
                <span class="error-sub">${message}</span>
            </div>
        `;
    }

    // Enhanced filter function with AND logic
    function filterExams(exams, query) {
        if (!query) return exams;

        const terms = query.split(/\s+/);

        return exams.filter(exam => {
            return terms.every(term => {
                const searchableText = [
                    exam.exam_name,
                    exam.subject
                ].join(' ').toLowerCase();
                return searchableText.includes(term);
            });
        });
    }

    // Highlight matching text
    function highlightText(text, query) {
        if (!query || !text) return text;
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return text.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    // Render Exams
    function renderExams() {
        let filteredExams = filterExams(currentExams, searchQuery);

        if (searchInfo) {
            if (searchQuery && filteredExams.length > 0 && filteredExams.length !== currentExams.length) {
                searchInfo.innerHTML = `<i class="fas fa-search"></i> Found ${filteredExams.length} of ${currentExams.length} exams matching "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info has-results';
            } else if (searchQuery && filteredExams.length === 0) {
                searchInfo.innerHTML = `<i class="fas fa-exclamation-circle"></i> No exams found matching "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info no-results';
            } else if (searchQuery && filteredExams.length === currentExams.length) {
                searchInfo.innerHTML = `<i class="fas fa-search"></i> All ${filteredExams.length} exams match "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info has-results';
            } else {
                searchInfo.style.display = 'none';
                searchInfo.className = 'search-info';
            }
        }

        if (filteredExams.length === 0) {
            const icon = 'fa-calendar-alt';
            const title = 'No exams yet';
            const sub = searchQuery ? 'No exams match your search' : 'Create one to start preparing!';
            examList.innerHTML = `
                <div class="empty-state">
                    <i class="fas ${icon}"></i>
                    <span class="empty-title">${title}</span>
                    <span class="empty-sub">${sub}</span>
                    ${!searchQuery ? `<button class="empty-action" id="emptyActionBtn"><i class="fas fa-plus"></i> Create Exam</button>` : ''}
                </div>
            `;
            const actionBtn = document.getElementById('emptyActionBtn');
            if (actionBtn) {
                actionBtn.addEventListener('click', () => openExamPanel());
            }
            return;
        }

        examList.innerHTML = filteredExams.map((exam, index) => {
            const percentage = exam.total_topics > 0
                ? Math.round((exam.completed_topics / exam.total_topics) * 100)
                : 0;
            const countdown = getCountdown(exam.exam_date);
            const isComplete = exam.completed_topics === exam.total_topics && exam.total_topics > 0;
            const isOverdue = new Date(exam.exam_date) < new Date();
            const countdownClass = isOverdue ? 'exam-countdown-overdue' : 'exam-countdown-display';

            return `
                <div class="exam-card" data-id="${exam.id}" style="animation-delay: ${(index * 0.05) + 0.05}s">
                    <div class="exam-card-header">
                        <div>
                            <div class="exam-name">${highlightText(escapeHtml(exam.exam_name), searchQuery)}</div>
                            <div class="exam-subject">${highlightText(escapeHtml(exam.subject), searchQuery)}</div>
                        </div>
                        <div class="exam-actions">
                            <button class="edit-exam-btn" data-exam-id="${exam.id}">Edit</button>
                            <button class="delete-exam-btn" data-exam-id="${exam.id}">Delete</button>
                        </div>
                    </div>

                    <div class="${countdownClass}">${countdown}</div>

                    <div class="exam-progress-section">
                        <div class="exam-progress-text">${percentage}% topics done · ${exam.completed_topics} of ${exam.total_topics} completed</div>
                        <div class="exam-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${percentage}%"></div>
                            </div>
                            <span class="progress-text">${percentage}%</span>
                        </div>
                        ${isComplete ? '<span class="exam-complete-badge"><i class="fas fa-check-circle"></i> Complete</span>' : ''}
                    </div>

                    <div class="exam-topics" id="topics-${exam.id}">
                        <div class="topics-loading">Loading topics...</div>
                    </div>

                    <div class="topic-form-inline" data-exam-id="${exam.id}">
                        <input type="text" class="topic-input" placeholder="Add topic" data-exam-id="${exam.id}">
                        <button class="btn btn-primary btn-sm add-topic-btn" data-exam-id="${exam.id}">Add</button>
                    </div>

                    ${isComplete ? `
                        <button class="complete-exam-btn" data-exam-id="${exam.id}"><i class="fas fa-flag-checkered"></i> Complete Exam</button>
                    ` : ''}
                </div>
            `;
        }).join('');

        // Attach event listeners
        document.querySelectorAll('.edit-exam-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const examId = parseInt(this.dataset.examId);
                const exam = currentExams.find(e => e.id === examId);
                if (exam) openExamPanel(exam);
            });
        });

        document.querySelectorAll('.delete-exam-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const examId = parseInt(this.dataset.examId);
                showConfirm('Delete this exam and all its topics?', () => {
                    deleteExam(examId);
                });
            });
        });

        document.querySelectorAll('.add-topic-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const examId = parseInt(this.dataset.examId);
                const input = document.querySelector(`.topic-input[data-exam-id="${examId}"]`);
                const title = input.value.trim();
                if (!title) {
                    showToast('Topic name is required', 'error');
                    return;
                }

                // Check if topic already exists in this exam
                if (topicNameExists(examId, title)) {
                    showToast('A topic with this name already exists in this exam.', 'warning');
                    return;
                }

                createTopic({ exam_id: examId, title: title });
            });
        });

        document.querySelectorAll('.topic-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const examId = parseInt(this.dataset.examId);
                    const title = this.value.trim();
                    if (!title) {
                        showToast('Topic name is required', 'error');
                        return;
                    }

                    // Check if topic already exists in this exam
                    if (topicNameExists(examId, title)) {
                        showToast('A topic with this name already exists in this exam.', 'warning');
                        return;
                    }

                    createTopic({ exam_id: examId, title: title });
                }
            });
        });

        document.querySelectorAll('.complete-exam-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const examId = parseInt(this.dataset.examId);
                showConfirm('Complete this exam? All topics will be archived.', () => {
                    completeExam(examId);
                });
            });
        });

        // Load topics for each exam
        filteredExams.forEach(exam => {
            fetchTopicsForExam(exam.id);
        });
    }

    // Fetch Topics for a specific exam
    async function fetchTopicsForExam(examId) {
        try {
            const response = await fetch(`../api/exams/topics.php?exam_id=${examId}`, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                const topics = result.data.topics || [];
                renderTopics(examId, topics);
            }
        } catch (error) {
            const container = document.querySelector(`#topics-${examId}`);
            if (container) {
                container.innerHTML = '<div class="topics-error">Failed to load topics</div>';
            }
        }
    }

    // Render Topics inside exam card
    function renderTopics(examId, topics) {
        const container = document.querySelector(`#topics-${examId}`);
        if (!container) return;

        if (topics.length === 0) {
            container.innerHTML = '<div class="topics-empty">No topics yet. Add one above.</div>';
            return;
        }

        container.innerHTML = topics.map(topic => `
            <div class="topic-item" data-id="${topic.id}">
                <input type="checkbox" class="topic-checkbox" data-topic-id="${topic.id}" data-exam-id="${examId}">
                <span class="topic-title">${escapeHtml(topic.title)}</span>
                <button class="delete-topic-btn" data-topic-id="${topic.id}" data-exam-id="${examId}"><i class="fas fa-times"></i></button>
            </div>
        `).join('');

        container.querySelectorAll('.topic-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    showConfirm('Mark this topic as complete?', () => {
                        const topicId = parseInt(this.dataset.topicId);
                        completeTopic(topicId);
                    }, () => {
                        this.checked = false;
                    });
                }
            });
        });

        container.querySelectorAll('.delete-topic-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const topicId = parseInt(this.dataset.topicId);
                showConfirm('Delete this topic?', () => {
                    deleteTopic(topicId);
                });
            });
        });
    }

    // CRUD: Exams
    async function createExam(data) {
        try {
            const response = await fetch('../api/exams/exams.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                closeExamPanel();
                await fetchExams();
                showToast('Exam created successfully', 'success');

                // Check exam date warnings after creation
                checkExamDateWarnings(data.exam_date);
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error creating exam', 'error');
        }
    }

    async function updateExam(id, data) {
        try {
            const response = await fetch(`../api/exams/exams.php?id=${id}`, {
                method: 'PUT',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                closeExamPanel();
                await fetchExams();
                showToast('Exam updated successfully', 'success');

                // Check exam date warnings after update
                checkExamDateWarnings(data.exam_date);
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error updating exam', 'error');
        }
    }

    async function deleteExam(id) {
        try {
            const response = await fetch(`../api/exams/exams.php?id=${id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                await fetchExams();
                showToast('Exam deleted successfully', 'success');
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error deleting exam', 'error');
        }
    }

    async function completeExam(id) {
        try {
            const response = await fetch(`../api/exams/complete.php?id=${id}`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                await fetchExams();
                showToast('Exam completed successfully', 'success');
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error completing exam', 'error');
        }
    }

    // CRUD: Topics
    async function createTopic(data) {
        try {
            const response = await fetch('../api/exams/topics.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                const input = document.querySelector(`.topic-input[data-exam-id="${data.exam_id}"]`);
                if (input) input.value = '';
                await fetchTopicsForExam(data.exam_id);
                await fetchExams();
                showToast('Topic created successfully', 'success');
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error creating topic', 'error');
        }
    }

    async function deleteTopic(id) {
        try {
            const response = await fetch(`../api/exams/topics.php?id=${id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                const topicElement = document.querySelector(`.topic-item[data-id="${id}"]`);
                if (topicElement) {
                    const examId = parseInt(topicElement.closest('.exam-card').dataset.id);
                    await fetchTopicsForExam(examId);
                    await fetchExams();
                }
                showToast('Topic deleted successfully', 'success');
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error deleting topic', 'error');
        }
    }

    async function completeTopic(id) {
        try {
            const response = await fetch(`../api/exams/topics_complete.php?id=${id}`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                const examId = result.data.exam_id;
                await fetchTopicsForExam(examId);
                await fetchExams();
                showToast('Topic completed successfully', 'success');
            } else {
                showToast('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showToast('Network error completing topic', 'error');
        }
    }

    // Form Submits
    examForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = examIdInput.value;
        const data = {
            exam_name: examNameInput.value.trim(),
            subject: examSubjectInput.value.trim(),
            exam_date: examDateInput.value
        };

        if (!data.exam_name) {
            showToast('Exam name is required', 'error');
            return;
        }

        if (!data.subject) {
            showToast('Subject is required', 'error');
            return;
        }

        if (id) {
            updateExam(parseInt(id), data);
        } else {
            createExam(data);
        }
    });

    // Helpers
    function getCountdown(dateStr) {
        const now = new Date();
        const examDate = new Date(dateStr);
        const diff = examDate - now;

        const daysOverdue = Math.ceil((now - examDate) / (1000 * 60 * 60 * 24));

        if (diff <= 0) {
            if (daysOverdue === 1) {
                return 'Overdue by 1 day';
            }
            return `Overdue by ${daysOverdue} days`;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        if (days > 0) {
            return `${days}d ${hours}h ${minutes}m ${seconds}s left`;
        }
        return `${hours}h ${minutes}m ${seconds}s left`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initial Load
    fetchExams();

    // Live countdown update every second
    setInterval(() => {
        if (currentExams.length > 0) {
            document.querySelectorAll('.exam-countdown-display, .exam-countdown-overdue').forEach((el, index) => {
                if (currentExams[index]) {
                    const isOverdue = new Date(currentExams[index].exam_date) < new Date();
                    el.textContent = getCountdown(currentExams[index].exam_date);
                    el.className = isOverdue ? 'exam-countdown-overdue' : 'exam-countdown-display';
                }
            });
        }
    }, 1000);
});