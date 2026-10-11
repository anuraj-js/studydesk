// js/quick_note.js

document.addEventListener('DOMContentLoaded', () => {
    // --- DOM References ---
    const textarea = document.getElementById('quickNoteTextarea');
    const charCount = document.getElementById('charCount');
    const wordCount = document.getElementById('wordCount');
    const statusText = document.getElementById('statusText');
    const statusDot = document.getElementById('statusDot');
    const clearBtn = document.getElementById('clearNoteBtn');
    const saveBtn = document.getElementById('saveNoteBtn');

    // --- Constants ---
    const AUTOSAVE_DELAY_MS = 1500;
    const SAVED_STATE_DURATION_MS = 2000;
    const FLASH_DURATION_MS = 600;
    const FOCUS_DELAY_MS = 500;
    const MAX_NOTE_LENGTH = 10000;

    // --- State ---
    let saveTimeout = null;
    let isSaving = false;
    let lastSavedContent = null; // null = not yet loaded

    // --- Save Button Rendering (single source of truth) ---
    function renderSaveBtn(state) {
        let icon, label;
        switch (state) {
            case 'saving':
                icon = '<i class="fas fa-spinner fa-spin"></i>';
                label = 'Saving...';
                break;
            case 'saved':
                icon = '<i class="fas fa-check"></i>';
                label = 'Saved!';
                break;
            default:
                icon = '<i class="fas fa-save"></i>';
                label = 'Save Now';
        }
        saveBtn.innerHTML = `${icon} <span class="save-btn-label">${label}</span> <span class="keyboard-hint">(Ctrl+S)</span>`;
    }

    // --- Counters ---
    function updateStats() {
        const content = textarea.value;
        const charCountVal = content.length;
        const wordCountVal = content.trim() === ''
            ? 0
            : content.trim().split(/\s+/).length;

        charCount.textContent = charCountVal;
        wordCount.textContent = wordCountVal;

        // Visual warning when approaching the cap
        const counter = charCount.closest('.char-counter');
        if (counter) {
            counter.classList.toggle('near-limit', charCountVal > MAX_NOTE_LENGTH * 0.9);
            counter.classList.toggle('at-limit', charCountVal >= MAX_NOTE_LENGTH);
        }
    }

    // --- Status ---
    function setStatus(text, state) {
        statusText.className = 'status-text';
        statusDot.className = 'status-dot';

        if (state) {
            statusText.classList.add(state);
            statusDot.classList.add(state);
        }
        statusText.textContent = text;
    }

    function setSaving(isSavingState) {
        isSaving = isSavingState;
        saveBtn.disabled = isSavingState;

        if (isSavingState) {
            saveBtn.classList.add('saving');
            saveBtn.classList.remove('saved');
            renderSaveBtn('saving');
        } else {
            saveBtn.classList.remove('saving');
        }
    }

    function setSaved() {
        saveBtn.classList.remove('saving');
        saveBtn.classList.add('saved');
        renderSaveBtn('saved');

        setTimeout(() => {
            if (!isSaving) {
                saveBtn.classList.remove('saved');
                renderSaveBtn('default');
            }
        }, SAVED_STATE_DURATION_MS);
    }

    // --- Time formatting (Safari-safe) ---
    function formatTime(dateStr) {
        if (!dateStr) return 'just now';
        // Normalize MySQL datetime "YYYY-MM-DD HH:MM:SS" -> ISO
        const normalized = String(dateStr).replace(' ', 'T');
        const date = new Date(normalized);
        if (isNaN(date.getTime())) return 'just now';

        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return `${hours}:${minutes} ${ampm}`;
    }

    // --- Save (returns true on success, false otherwise) ---
    async function saveNote(options = {}) {
        const { silent = false } = options;

        if (isSaving) return false;

        const content = textarea.value;

        // Nothing changed — silently skip (no toast)
        if (content === lastSavedContent) {
            return false;
        }

        // Client-side length guard
        if (content.length > MAX_NOTE_LENGTH) {
            if (!silent) {
                showToast(`Note is too long (max ${MAX_NOTE_LENGTH} characters).`, 'error');
            }
            setStatus('Too long to save', 'error');
            return false;
        }

        setSaving(true);
        setStatus('Saving...', 'saving');

        try {
            const response = await fetch('../api/quick_note/save.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content })
            });

            const result = await response.json();

            if (result.success) {
                lastSavedContent = content;
                const updatedAt = formatTime(result.data.updated_at);
                setStatus(`Last saved at ${updatedAt}`, 'saved');
                setSaved();

                saveBtn.classList.add('flash');
                setTimeout(() => saveBtn.classList.remove('flash'), FLASH_DURATION_MS);
                return true;
            } else {
                setStatus('Error saving', 'error');
                if (!silent) showToast(result.message || 'Failed to save note', 'error');
                return false;
            }
        } catch (error) {
            setStatus('Error saving', 'error');
            if (!silent) showToast('Failed to save note', 'error');
            return false;
        } finally {
            setSaving(false);
        }
    }

    // --- Debounced autosave (silent — no success toast) ---
    function debouncedSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(async () => {
            if (textarea.value !== lastSavedContent) {
                await saveNote({ silent: true });
            }
        }, AUTOSAVE_DELAY_MS);
    }

    // --- Fetch on load ---
    async function fetchNote() {
        textarea.disabled = true;
        setStatus('Loading...', 'saving');

        try {
            const response = await fetch('../api/quick_note/fetch.php', {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                const content = result.data.content || '';
                textarea.value = content;
                lastSavedContent = content;
                updateStats();

                if (content) {
                    const updatedAt = formatTime(result.data.updated_at);
                    setStatus(`Loaded — last saved at ${updatedAt}`, 'saved');
                } else {
                    setStatus('Ready — no note yet', '');
                }
            } else {
                setStatus('Failed to load', 'error');
                showToast('Failed to load note', 'error');
            }
        } catch (error) {
            setStatus('Failed to load', 'error');
            showToast('Failed to load note', 'error');
        } finally {
            textarea.disabled = false;
        }
    }

    // --- Clear ---
    function clearNote() {
        if (!textarea.value.trim() && (!lastSavedContent || !lastSavedContent.trim())) {
            showToast('Note is already empty.', 'warning');
            return;
        }

        showConfirm('Clear this note? It will be saved as empty.', async () => {
            textarea.value = '';
            updateStats();
            setStatus('Clearing...', 'saving');

            // Force-save the empty state even if lastSavedContent was non-empty
            const previous = lastSavedContent;
            lastSavedContent = null; // ensure the save goes through
            const ok = await saveNote({ silent: true });

            if (ok) {
                setStatus('Cleared', '');
                showToast('Note cleared.', 'info');
            } else {
                lastSavedContent = previous;
                setStatus('Failed to clear', 'error');
            }
        });
    }

    // --- Event Listeners ---
    textarea.addEventListener('input', () => {
        // Hard cap on input
        if (textarea.value.length > MAX_NOTE_LENGTH) {
            textarea.value = textarea.value.slice(0, MAX_NOTE_LENGTH);
        }
        updateStats();
        debouncedSave();
        setStatus('Typing...', 'saving');
    });

    saveBtn.addEventListener('click', () => saveNote({ silent: false }));
    clearBtn.addEventListener('click', clearNote);

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            saveNote({ silent: false });
        }
    });

    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', (e) => {
        if (lastSavedContent !== null && textarea.value !== lastSavedContent) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // --- Init ---
    renderSaveBtn('default');
    fetchNote().then(() => {
        if (textarea.value.trim() === '') {
            setTimeout(() => textarea.focus(), FOCUS_DELAY_MS);
        }
    });
});