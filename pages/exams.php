<?php
require_once __DIR__ . '/../includes/user_layout.php';

render_header('Exams', 'exams');
?>

<!-- Add Exam Form -->
<details class="card collapsible-card">
    <summary class="section-title">
        <i class="fa-solid fa-plus-circle"></i> Add Exam
    </summary>
    <form id="examForm">
        <div class="form-group">
            <label for="examName">Exam name <span class="required">*</span></label>
            <input type="text" id="examName" placeholder="e.g. Midterm, Final, Pre-Board" required />
        </div>
        <div class="form-group">
            <label for="examSubject">Subject <span class="required">*</span></label>
            <input type="text" id="examSubject" placeholder="e.g. Physics, Chemistry, Mathematics" required />
        </div>
        <div class="form-group">
            <label for="examDate">Exam date <span class="required">*</span></label>
            <input type="date" id="examDate" required />
        </div>
        <div class="form-group">
            <label for="examTime">Exam time <span class="required">*</span></label>
            <input type="time" id="examTime" required />
        </div>
        <button type="submit" class="btn btn-primary btn-full" id="addExamBtn">
            <i class="fa-solid fa-plus"></i> Create exam plan
        </button>
    </form>
</details>

<!-- Search Bar -->
<div class="form-group" style="margin-bottom: 0.85rem;">
    <input type="text" id="searchExam" placeholder="Search exams by subject or exam name..." style="border-radius: 20px; padding-left: 1rem;" />
</div>

<!-- Exam List Container -->
<div id="examList">
    <!-- Skeleton loaders -->
    <div class="card skeleton" style="height: 200px; margin-bottom: 1rem;"></div>
    <div class="card skeleton" style="height: 200px; margin-bottom: 1rem;"></div>
</div>

<!-- Edit Exam Modal -->
<div class="modal-overlay" id="editModal" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen"></i> Edit Exam</h3>
            <button class="modal-close" id="modalClose"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editExamForm">
            <input type="hidden" id="editExamId" />
            <div class="form-group">
                <label for="editExamName">Exam name <span class="required">*</span></label>
                <input type="text" id="editExamName" required />
            </div>
            <div class="form-group">
                <label for="editExamSubject">Subject <span class="required">*</span></label>
                <input type="text" id="editExamSubject" required />
            </div>
            <div class="form-group">
                <label for="editExamDate">Exam date <span class="required">*</span></label>
                <input type="date" id="editExamDate" required />
            </div>
            <div class="form-group">
                <label for="editExamTime">Exam time <span class="required">*</span></label>
                <input type="time" id="editExamTime" required />
            </div>
            <div style="display:flex;gap:.5rem">
                <button type="button" class="btn btn-ghost" id="modalCancel">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1" id="saveExamBtn">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
render_footer('exams.js'); 
?>