let allExams = [];
let searchQuery = '';

// ── Countdown helpers ──
function getCountdown(dateStr, timeStr) {
    const now = new Date();
    const examEnd = new Date(dateStr + 'T' + (timeStr || '23:59'));
    const diff = examEnd - now;

    if (diff <= 0) return { text: 'Exam time has passed.', cls: 'past' };

    const totalSecs = Math.floor(diff / 1000);
    const days  = Math.floor(totalSecs / 86400);
    const hours = Math.floor((totalSecs % 86400) / 3600);
    const mins  = Math.floor((totalSecs % 3600) / 60);
    const secs  = totalSecs % 60;

    const cls  = days <= 3 ? 'urgent' : '';
    const text = `${days}d ${hours}h ${mins}m ${secs}s left`;
    return { text, cls };
}

// ── Tick: update only countdown spans ──
function tickCountdowns() {
    allExams.forEach(e => {
        const el = document.getElementById('countdown-' + e.id);
        if (!el) return;
        const { text, cls } = getCountdown(e.date, e.time);
        el.textContent = text;
        el.className = 'exam-countdown-live ' + cls;
    });
}

// ── Render topics ──
function renderTopicsHtml(exam) {
    const topics = exam.topics || [];
    const done = topics.filter(t => t.completed).length;
    const total = topics.length;
    const pct = total ? Math.round((done / total) * 100) : 0;

    const topicsHtml = topics.length
        ? topics.map(t => `
            <div class="topic-item" data-topic-id="${t.id}">
                <input type="checkbox" class="task-checkbox topic-check"
                    data-exam-id="${exam.id}" data-topic-id="${t.id}"
                    ${t.completed ? 'checked' : ''} />
                <span class="topic-text ${t.completed ? 'done' : ''}">${esc(t.text)}</span>
                <button class="topic-delete" data-exam-id="${exam.id}" data-topic-id="${t.id}" title="Remove">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `).join('')
        : '<div class="empty-state" style="padding:.5rem 0;text-align:left;font-size:.82rem">No topics yet. Add one below.</div>';

    return { topicsHtml, pct, done, total };
}

// ── Render all exam cards ──
function renderExams() {
    const el = document.getElementById('examList');

    let list = allExams;
    if (searchQuery) {
        const q = searchQuery.toLowerCase();
        list = list.filter(e => 
            e.subject.toLowerCase().includes(q) || 
            e.name.toLowerCase().includes(q)
        );
    }

    if (!list.length) {
        el.innerHTML = `<div class="empty-state">${searchQuery ? 'No exams match your search' : 'No exams added yet'}</div>`;
        return;
    }

    el.innerHTML = list.map(exam => {
        const { text, cls } = getCountdown(exam.date, exam.time);
        const { topicsHtml, pct, done, total } = renderTopicsHtml(exam);
        
        const dateFormatted = new Date(exam.date + 'T00:00:00').toLocaleDateString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
        });

        const timeFormatted = exam.time 
            ? new Date(exam.date + 'T' + exam.time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })
            : '';

        const highlightedSubject = highlightText(exam.subject, searchQuery);
        const highlightedName = highlightText(exam.name, searchQuery);

        return `
            <div class="card exam-card" data-exam-id="${exam.id}">
                <div class="exam-card-header">
                    <div>
                        <div class="exam-subject-title">${highlightedSubject}</div>
                        <div class="exam-name-label">${highlightedName} &middot; ${dateFormatted} ${timeFormatted ? 'at ' + timeFormatted : ''}</div>
                    </div>
                    <div class="exam-actions">
                        <button class="btn btn-ghost btn-sm edit-btn" data-id="${exam.id}">
                            <i class="fa-solid fa-pen"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${exam.id}">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </div>
                </div>

                <div class="exam-countdown-live ${cls}" id="countdown-${exam.id}">${text}</div>

                <div class="topics-meta">${pct}% topics done &middot; ${done} of ${total} completed</div>
                <div class="topic-bar-row">
                    <div class="topic-bar-wrap">
                        <div class="topic-bar" style="width:${pct}%"></div>
                    </div>
                    <span class="topic-bar-pct">${pct}%</span>
                </div>

                <hr class="topics-divider" />

                <div class="topics-list" id="topics-${exam.id}">${topicsHtml}</div>

                <div class="topic-input-row">
                    <input type="text" class="topic-input" data-exam-id="${exam.id}" placeholder="Add topic" />
                    <button class="btn btn-primary btn-sm add-topic-btn" data-exam-id="${exam.id}">Add</button>
                </div>
            </div>
        `;
    }).join('');
}

function refreshTopics(examId) {
    const exam = allExams.find(e => e.id == examId);
    if (!exam) return;
    const { topicsHtml, pct, done, total } = renderTopicsHtml(exam);

    const topicsEl = document.getElementById('topics-' + examId);
    if (topicsEl) topicsEl.innerHTML = topicsHtml;

    const card = document.querySelector(`[data-exam-id="${examId}"]`);
    if (card) {
        const meta = card.querySelector('.topics-meta');
        const bar = card.querySelector('.topic-bar');
        const pctEl = card.querySelector('.topic-bar-pct');
        if (meta) meta.textContent = `${pct}% topics done · ${done} of ${total} completed`;
        if (bar) bar.style.width = pct + '%';
        if (pctEl) pctEl.textContent = pct + '%';
    }
}

async function addTopicFromInput(examId) {
    const input = document.querySelector(`.topic-input[data-exam-id="${examId}"]`);
    if (!input) return;
    const text = input.value.trim();
    if (!text) return;

    try {
        const res = await apiFetch('../api/topics.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'add', exam_id: examId, text })
        });

        if (res.success) {
            const exam = allExams.find(e => e.id == examId);
            if (exam) {
                exam.topics.push({ id: res.data.id, text, completed: false });
            }
            input.value = '';
            refreshTopics(examId);
        } else {
            showToast(res.message, 'error');
        }
    } catch (err) {
        showToast('Failed to add topic.', 'error');
    }
}

function openEditModal(id) {
    const exam = allExams.find(e => e.id == id);
    if (!exam) return;
    document.getElementById('editExamId').value = exam.id;
    document.getElementById('editExamName').value = exam.name;
    document.getElementById('editExamSubject').value = exam.subject;
    document.getElementById('editExamDate').value = exam.date;
    document.getElementById('editExamTime').value = exam.time || '09:00';
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editExamForm').reset();
}

document.addEventListener('DOMContentLoaded', () => {
    async function loadExams() {
        try {
            const res = await apiFetch('../api/exams.php');
            if (res.success) {
                allExams = res.data;
                renderExams();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to load exams.', 'error');
        }
    }
    loadExams();

    document.getElementById('examForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById('addExamBtn');
        const originalText = submitBtn.innerHTML;
        
        const name = document.getElementById('examName').value.trim();
        const subject = document.getElementById('examSubject').value.trim();
        const date = document.getElementById('examDate').value;
        const time = document.getElementById('examTime').value;

        // New validation
        if (Validation.isPastDateTime(date, time)) {
            showToast('Exam date and time cannot be in the past.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';

        try {
            const [res] = await Promise.all([
                apiFetch('../api/exams.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'create', name, subject, date, time })
                }),
                new Promise(r => setTimeout(r, 500))
            ]);

            if (res.success) {
                document.getElementById('examForm').reset();
                document.getElementById('searchExam').value = ''; 
                searchQuery = '';
                document.querySelector('.collapsible-card').removeAttribute('open');
                showToast('Exam created.', 'success');
                loadExams();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to create exam.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    document.getElementById('searchExam').addEventListener('input', (e) => {
        searchQuery = e.target.value;
        renderExams();
    });

    document.getElementById('editExamForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById('saveExamBtn');
        const originalText = submitBtn.innerHTML;

        const id = document.getElementById('editExamId').value;
        const name = document.getElementById('editExamName').value.trim();
        const subject = document.getElementById('editExamSubject').value.trim();
        const date = document.getElementById('editExamDate').value;
        const time = document.getElementById('editExamTime').value;

        // New validation
        if (Validation.isPastDateTime(date, time)) {
            showToast('Exam date and time cannot be in the past.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const [res] = await Promise.all([
                apiFetch('../api/exams.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'update', id, name, subject, date, time })
                }),
                new Promise(r => setTimeout(r, 500))
            ]);

            if (res.success) {
                closeEditModal();
                showToast('Exam updated.', 'success');
                loadExams();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to update exam.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    document.getElementById('modalClose').addEventListener('click', closeEditModal);
    document.getElementById('modalCancel').addEventListener('click', closeEditModal);
    document.getElementById('editModal').addEventListener('click', e => {
        if (e.target === document.getElementById('editModal')) closeEditModal();
    });

    document.getElementById('examList').addEventListener('click', async (e) => {
        const deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            showDialog({
                title: 'Delete Exam',
                message: 'Delete this exam and all its topics?',
                confirmText: 'Delete',
                onConfirm: async () => {
                    try {
                        const res = await apiFetch('../api/exams.php', {
                            method: 'POST',
                            body: JSON.stringify({ action: 'delete', id })
                        });
                        if (res.success) {
                            allExams = allExams.filter(e => e.id != id);
                            renderExams();
                            showToast('Exam deleted.', 'success');
                        } else {
                            showToast(res.message, 'error');
                        }
                    } catch (err) {
                        showToast('Failed to delete exam.', 'error');
                    }
                }
            });
            return;
        }

        const editBtn = e.target.closest('.edit-btn');
        if (editBtn) {
            openEditModal(editBtn.dataset.id);
            return;
        }

        const topicDeleteBtn = e.target.closest('.topic-delete');
        if (topicDeleteBtn) {
            const { examId, topicId } = topicDeleteBtn.dataset;
            showDialog({
                title: 'Remove Topic',
                message: 'Remove this topic?',
                confirmText: 'Remove',
                onConfirm: async () => {
                    try {
                        const res = await apiFetch('../api/topics.php', {
                            method: 'POST',
                            body: JSON.stringify({ action: 'delete', exam_id: examId, id: topicId })
                        });
                        if (res.success) {
                            const exam = allExams.find(e => e.id == examId);
                            if (exam) exam.topics = exam.topics.filter(t => t.id != topicId);
                            refreshTopics(examId);
                            showToast('Topic removed.', 'success');
                        } else {
                            showToast(res.message, 'error');
                        }
                    } catch (err) {
                        showToast('Failed to delete topic.', 'error');
                    }
                }
            });
            return;
        }

        const addTopicBtn = e.target.closest('.add-topic-btn');
        if (addTopicBtn) {
            addTopicFromInput(addTopicBtn.dataset.examId);
            return;
        }
    });

    document.getElementById('examList').addEventListener('change', async (e) => {
        const cb = e.target.closest('.topic-check');
        if (cb) {
            const { examId, topicId } = cb.dataset;
            try {
                const res = await apiFetch('../api/topics.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'toggle', exam_id: examId, id: topicId })
                });
                if (res.success) {
                    const exam = allExams.find(e => e.id == examId);
                    if (exam) {
                        const topic = exam.topics.find(t => t.id == topicId);
                        if (topic) topic.completed = res.data.completed;
                    }
                    refreshTopics(examId);
                } else {
                    showToast(res.message, 'error');
                    cb.checked = !cb.checked;
                }
            } catch (err) {
                showToast('Failed to toggle topic.', 'error');
                cb.checked = !cb.checked;
            }
        }
    });

    document.getElementById('examList').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const input = e.target.closest('.topic-input');
            if (input) {
                e.preventDefault();
                addTopicFromInput(input.dataset.examId);
            }
        }
    });

    setInterval(tickCountdowns, 1000);
});