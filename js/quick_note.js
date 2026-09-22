// js/quick_note.js

document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.getElementById('quickNoteTextarea');
    const charCount = document.getElementById('charCount');
    const wordCount = document.getElementById('wordCount');
    const charCounter = document.getElementById('charCounter');
    const statusText = document.getElementById('statusText');
    const statusDot = document.getElementById('statusDot');
    const clearBtn = document.getElementById('clearNoteBtn');
    const saveBtn = document.getElementById('saveNoteBtn');
    const saveBtnText = document.getElementById('saveBtnText');

    let saveTimeout = null;
    let isSaving = false;
    let currentContent = '';
    let lastSavedContent = '';

    // Update counters and status
    function updateStats() {
        const content = textarea.value;
        const charCountVal = content.length;
        const wordCountVal = content.trim() === '' ? 0 : content.trim().split(/\s+/).length;

        charCount.textContent = charCountVal;
        wordCount.textContent = wordCountVal;
    }

    // Update status with dot
    function setStatus(text, state) {
        statusText.className = 'status-text';
        statusDot.className = 'status-dot';

        if (state) {
            statusText.classList.add(state);
            statusDot.classList.add(state);
        }

        statusText.innerHTML = text;
    }

    // Set saving state
    function setSaving(isSavingState) {
        isSaving = isSavingState;
        saveBtn.disabled = isSavingState;

        if (isSavingState) {
            saveBtn.classList.add('saving');
            saveBtn.classList.remove('saved');
            saveBtnText.textContent = 'Saving...';
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span id="saveBtnText">Saving...</span> <span class="keyboard-hint">(Ctrl+S)</span>';
            const hint = saveBtn.querySelector('.keyboard-hint');
            if (!hint) {
                saveBtn.innerHTML += ' <span class="keyboard-hint">(Ctrl+S)</span>';
            }
        } else {
            saveBtn.classList.remove('saving');
        }
    }

    // Set saved state (briefly)
    function setSaved() {
        saveBtn.classList.remove('saving');
        saveBtn.classList.add('saved');
        saveBtnText.textContent = 'Saved!';
        saveBtn.innerHTML = '<i class="fas fa-check"></i> <span id="saveBtnText">Saved!</span> <span class="keyboard-hint">(Ctrl+S)</span>';

        setTimeout(() => {
            if (!isSaving) {
                saveBtn.classList.remove('saved');
                saveBtnText.textContent = 'Save Now';
                saveBtn.innerHTML = '<i class="fas fa-save"></i> <span id="saveBtnText">Save Now</span> <span class="keyboard-hint">(Ctrl+S)</span>';
            }
        }, 2000);
    }

    // Save note
    async function saveNote() {
        if (isSaving) return;

        const content = textarea.value;

        // Warning: Saving same content
        if (content === lastSavedContent) {
            showToast('Note content is unchanged.', 'warning');
            return;
        }

        setSaving(true);
        setStatus('Saving...', 'saving');

        try {
            const response = await fetch('../api/quick_note/save.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: content })
            });

            const result = await response.json();

            if (result.success) {
                currentContent = result.data.content;
                lastSavedContent = content;
                const updatedAt = formatTime(result.data.updated_at);
                setStatus(`Last saved at ${updatedAt}`, 'saved');
                setSaved();

                saveBtn.classList.add('flash');
                setTimeout(() => saveBtn.classList.remove('flash'), 600);
            } else {
                setStatus('Error saving', 'error');
                showToast(result.message, 'error');
            }
        } catch (error) {
            setStatus('Error saving', 'error');
            showToast('Failed to save note', 'error');
        } finally {
            setSaving(false);
        }
    }

    // Format time
    function formatTime(dateStr) {
        if (!dateStr) return 'Now';
        const date = new Date(dateStr);
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = date.getHours() >= 12 ? 'PM' : 'AM';
        const hour12 = date.getHours() % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }

    // Debounced auto-save
    function debouncedSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            const content = textarea.value;
            if (content !== lastSavedContent) {
                saveNote();
                showToast('Note auto-saved.', 'info');
            }
        }, 1200);
    }

    // Fetch note on load
    async function fetchNote() {
        try {
            const response = await fetch('../api/quick_note/fetch.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                const content = result.data.content || '';
                textarea.value = content;
                currentContent = content;
                lastSavedContent = content;
                updateStats();

                if (content) {
                    const updatedAt = formatTime(result.data.updated_at);
                    setStatus(`Loaded — last saved at ${updatedAt}`, 'saved');
                    textarea.placeholder = 'Write something important...';
                    showToast('Your note loaded successfully.', 'info');
                } else {
                    setStatus('Ready — no note yet', '');
                    textarea.placeholder = 'Write something important...';
                }
            } else {
                showToast('Failed to load note', 'error');
                setStatus('Failed to load', 'error');
            }
        } catch (error) {
            showToast('Failed to load note', 'error');
            setStatus('Failed to load', 'error');
        }
    }

    // Clear note
    function clearNote() {
        if (!textarea.value.trim() && !currentContent.trim()) {
            showToast('Note is already empty.', 'warning');
            return;
        }

        showConfirm('Clear this note? It will be saved as empty.', () => {
            textarea.value = '';
            currentContent = '';
            // Force save the empty note
            saveNote();
            setStatus('Cleared', '');
        });
    }

    // --- Event Listeners ---

    textarea.addEventListener('input', () => {
        updateStats();
        debouncedSave();
        setStatus('Typing...', 'saving');
    });

    saveBtn.addEventListener('click', saveNote);

    clearBtn.addEventListener('click', clearNote);

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveNote();
        }
    });

    // Initial load
    fetchNote();

    if (textarea.value.trim() === '') {
        setTimeout(() => textarea.focus(), 500);
    }
});