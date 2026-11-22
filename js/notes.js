let allNotes = [];
let activeNoteId = null;
let searchQuery = '';
let saveTimeout;
let saveSeq = 0;

function renderNotesList() {
    const listEl = document.getElementById('notesList');
    let list = allNotes;
    if (searchQuery) {
        const q = searchQuery.toLowerCase();
        list = list.filter(n => 
            (n.title || '').toLowerCase().includes(q) || 
            (n.body || '').toLowerCase().includes(q)
        );
    }

    if (!list.length) {
        const msg = searchQuery ? 'No notes match your search.' : 'No notes yet.<br>Create one to get started.';
        listEl.innerHTML = `<div class="empty-state" style="padding:2rem 1rem;">${msg}</div>`;
        return;
    }

    listEl.innerHTML = list.map(n => {
        // Convert UTC DB string to Local Nepal Time
        const date = new Date(n.updated_at.replace(' ', 'T') + 'Z').toLocaleDateString('en-US', { 
            month: 'short', day: 'numeric', timeZone: 'Asia/Kathmandu'
        });
        
        const preview = n.body
            ? n.body.substring(0, 40).replace(/\n/g, ' ') + (n.body.length > 40 ? '…' : '')
            : 'Empty note';
        
        const isActive = n.id === activeNoteId ? 'active-note' : '';
        const highlightedTitle = highlightText(n.title || 'Untitled Note', searchQuery);

        return `
            <div class="note-list-item ${isActive}" data-id="${n.id}">
                <div class="note-list-title">${highlightedTitle}</div>
                <div class="note-list-preview">${esc(preview)}</div>
                <div class="note-list-date">${date}</div>
            </div>
        `;
    }).join('');

    listEl.querySelectorAll('.note-list-item').forEach(item => {
        item.addEventListener('click', () => openNote(item.dataset.id));
    });
}

function openNote(id) {
    const note = allNotes.find(n => n.id == id);
    if (!note) return;

    activeNoteId = id;

    document.getElementById('notesSidebar').classList.add('hidden-view');
    document.getElementById('notesEditor').classList.add('active-view');
    document.getElementById('emptyEditor').style.display = 'none';
    document.getElementById('noteEditor').style.display = 'flex';

    document.getElementById('editNoteTitle').value = note.title || '';
    document.getElementById('editNoteBody').value = note.body || '';

    updateSaveStatus(note.updated_at || new Date().toISOString());
    renderNotesList();
}

function closeNote() {
    activeNoteId = null;
    document.getElementById('notesSidebar').classList.remove('hidden-view');
    document.getElementById('notesEditor').classList.remove('active-view');
    document.getElementById('emptyEditor').style.display = 'flex';
    document.getElementById('noteEditor').style.display = 'none';
    renderNotesList();
}

function updateSaveStatus(dateStr) {
    const safeDateStr = dateStr.includes('T') ? dateStr : dateStr.replace(' ', 'T') + 'Z';
    const date = new Date(safeDateStr);
    
    const today = new Date();
    
    // Force Nepal Timezone for display
    const isToday = date.toLocaleDateString('en-US', { timeZone: 'Asia/Kathmandu' }) === today.toLocaleDateString('en-US', { timeZone: 'Asia/Kathmandu' });

    const timeString = date.toLocaleTimeString('en-US', {
        hour: '2-digit', minute: '2-digit', hour12: true, timeZone: 'Asia/Kathmandu'
    });

    if (isToday) {
        document.getElementById('saveStatus').textContent = `Saved at ${timeString}`;
    } else {
        const dateString = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', timeZone: 'Asia/Kathmandu' });
        document.getElementById('saveStatus').textContent = `Saved at ${dateString}, ${timeString}`;
    }
}

function triggerAutoSave() {
    if (!activeNoteId) return;

    document.getElementById('saveStatus').textContent = 'Saving…';
    
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(async () => {
        const myReq = ++saveSeq;
        const title = document.getElementById('editNoteTitle').value.trim() || 'Untitled Note';
        const body = document.getElementById('editNoteBody').value;

        const note = allNotes.find(n => n.id == activeNoteId);
        if (note) {
            note.title = title;
            note.body = body;
            // FIX: Generate clean UTC string
            note.updated_at = new Date().toISOString().slice(0, 19).replace('T', ' ');
        }

        try {
            const res = await apiFetch('../api/notes.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'update', id: activeNoteId, title, body })
            });

            if (myReq !== saveSeq) return;

            if (res.success) {
                // FIX: Pass clean UTC string
                updateSaveStatus(new Date().toISOString().slice(0, 19).replace('T', ' '));
                renderNotesList();
            } else {
                document.getElementById('saveStatus').textContent = 'Save failed';
                showToast(res.message, 'error');
            }
        } catch (err) {
            if (myReq !== saveSeq) return;
            document.getElementById('saveStatus').textContent = 'Save failed';
            showToast('Failed to save note.', 'error');
        }
    }, 600);
}

document.addEventListener('DOMContentLoaded', () => {
    async function loadNotes() {
        try {
            const res = await apiFetch('../api/notes.php');
            if (res.success) {
                allNotes = res.data;
                renderNotesList();
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to load notes.', 'error');
        }
    }
    loadNotes();

    window.addEventListener('beforeunload', (e) => {
        if (saveTimeout) {
            clearTimeout(saveTimeout);
            if (activeNoteId) {
                // Get the CSRF token from the meta tag
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                const payload = JSON.stringify({ 
                    action: 'update', 
                    id: activeNoteId, 
                    title: document.getElementById('editNoteTitle').value.trim() || 'Untitled Note', 
                    body: document.getElementById('editNoteBody').value,
                    _csrf: csrfToken // <-- Include the token in the body
                });
                navigator.sendBeacon('../api/notes.php', payload);
            }
        }
    });

    document.getElementById('searchNotes').addEventListener('input', e => {
        searchQuery = e.target.value;
        renderNotesList();
    });

    document.getElementById('newNoteBtn').addEventListener('click', async () => {
        try {
            const res = await apiFetch('../api/notes.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'create' })
            });
            if (res.success) {
                allNotes.unshift(res.data);
                openNote(res.data.id);
                setTimeout(() => document.getElementById('editNoteTitle').focus(), 100);
            } else {
                showToast(res.message, 'error');
            }
        } catch (err) {
            showToast('Failed to create note.', 'error');
        }
    });

    document.getElementById('deleteNoteBtn').addEventListener('click', () => {
        if (!activeNoteId) return;
        showDialog({
            title: 'Delete Note',
            message: 'Delete this note permanently?',
            confirmText: 'Delete',
            onConfirm: async () => {
                try {
                    const res = await apiFetch('../api/notes.php', {
                        method: 'POST',
                        body: JSON.stringify({ action: 'delete', id: activeNoteId })
                    });
                    if (res.success) {
                        allNotes = allNotes.filter(n => n.id != activeNoteId);
                        closeNote();
                        showToast('Note deleted.', 'success');
                    } else {
                        showToast(res.message, 'error');
                    }
                } catch (err) {
                    showToast('Failed to delete note.', 'error');
                }
            }
        });
    });

    document.getElementById('backToListBtn').addEventListener('click', closeNote);

    document.getElementById('editNoteTitle').addEventListener('input', triggerAutoSave);
    document.getElementById('editNoteBody').addEventListener('input', triggerAutoSave);

    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            if (!activeNoteId) return;

            clearTimeout(saveTimeout);
            const title = document.getElementById('editNoteTitle').value.trim() || 'Untitled Note';
            const body = document.getElementById('editNoteBody').value;
            
            const myReq = ++saveSeq;
            apiFetch('../api/notes.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'update', id: activeNoteId, title, body })
            }).then(res => {
                if (myReq !== saveSeq) return;
                if (res.success) {
                    const note = allNotes.find(n => n.id == activeNoteId);
                    if (note) {
                        note.title = title;
                        note.body = body;
                        // FIX: Generate clean UTC string
                        note.updated_at = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    }
                    // FIX: Pass clean UTC string
                    updateSaveStatus(new Date().toISOString().slice(0, 19).replace('T', ' '));
                    renderNotesList();
                    showToast('Note saved.', 'success');
                }
            }).catch(() => showToast('Failed to save note.', 'error'));
        }
    });

    document.getElementById('editNoteBody').addEventListener('paste', e => {
        const titleEl = document.getElementById('editNoteTitle');
        if (titleEl.value.trim() && titleEl.value.trim() !== 'Untitled Note') return;

        setTimeout(() => {
            const body = document.getElementById('editNoteBody').value;
            const lines = body.split('\n');
            if (lines.length > 1 && lines[0].trim()) {
                titleEl.value = lines[0].trim();
                document.getElementById('editNoteBody').value = lines.slice(1).join('\n').trimStart();
                triggerAutoSave();
            }
        }, 0);
    });
});