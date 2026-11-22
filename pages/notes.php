<?php
require_once __DIR__ . '/../includes/user_layout.php';

render_header('Notes', 'notes');
?>

<div class="notes-container">
    
    <!-- View 1: The Note List -->
    <div class="notes-sidebar card" id="notesSidebar">
        <div class="notes-sidebar-header">
            <h2 class="section-title" style="margin:0">My Notes</h2>
            <button class="btn btn-primary btn-sm" id="newNoteBtn">
                <i class="fa-solid fa-plus"></i> New
            </button>
        </div>
        
        <div class="notes-search-wrap">
            <input type="text" id="searchNotes" placeholder="Search notes..." class="notes-search-input" />
        </div>

        <div id="notesList" class="notes-list">
            <!-- Skeleton loaders -->
            <div class="note-list-item skeleton" style="height: 70px;"></div>
            <div class="note-list-item skeleton" style="height: 70px;"></div>
        </div>
    </div>

    <!-- View 2: The Editor -->
    <div class="notes-editor card" id="notesEditor">
        
        <div id="emptyEditor" class="empty-editor-state">
            <i class="fa-regular fa-file-lines" style="font-size:3rem; color:var(--border); margin-bottom:1rem;"></i>
            <p>Select a note to view or edit</p>
        </div>

        <!-- The Editor Content -->
        <div id="noteEditor" style="display:none; flex-direction:column; height:100%;">
            <div class="editor-header">
                <button class="btn btn-ghost btn-sm" id="backToListBtn" title="Back to list">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                
                <input type="text" id="editNoteTitle" placeholder="Note Title" class="editor-title-input" />
                
                <button class="btn btn-danger btn-sm" id="deleteNoteBtn" title="Delete Note">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            
            <textarea id="editNoteBody" placeholder="Start typing your notes here..." class="editor-body-input"></textarea>
            
            <div class="editor-footer">
                <span id="saveStatus" class="save-status">Saved</span>
            </div>
        </div>
    </div>
</div>

<?php 
render_footer('notes.js'); 
?>