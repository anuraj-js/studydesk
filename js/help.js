// js/help.js

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('helpSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const searchInfo = document.getElementById('searchInfo');
    const sections = document.querySelectorAll('.help-section');
    const toggles = document.querySelectorAll('.help-toggle');

    let searchQuery = '';
    let searchTimeout = null;

    // --- Accordion Toggle ---
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const section = this.closest('.help-section');
            const isOpen = section.classList.contains('open');

            // Close all sections
            sections.forEach(sec => sec.classList.remove('open'));

            // Toggle current section
            if (!isOpen) {
                section.classList.add('open');
            }
        });
    });

    // --- Open first section by default ---
    if (sections.length > 0) {
        sections[0].classList.add('open');
    }

    // --- Search Logic ---
    function filterSections(query) {
        const trimmed = query.toLowerCase().trim();
        let visibleCount = 0;

        sections.forEach(section => {
            const title = section.dataset.title || '';
            const keywords = section.dataset.keywords || '';
            const content = section.querySelector('.help-content')?.textContent || '';

            const searchableText = [title, keywords, content].join(' ').toLowerCase();

            const match = !trimmed || searchableText.includes(trimmed);

            if (match) {
                section.classList.remove('hidden');
                visibleCount++;
            } else {
                section.classList.add('hidden');
                // Close hidden sections
                section.classList.remove('open');
            }
        });

        return visibleCount;
    }

    function updateSearchInfo(visibleCount, totalCount) {
        if (!searchInfo) return;

        if (searchQuery) {
            if (visibleCount > 0 && visibleCount !== totalCount) {
                searchInfo.innerHTML = `<i class="fas fa-search"></i> Found ${visibleCount} of ${totalCount} topics matching "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info has-results';
            } else if (visibleCount === 0) {
                searchInfo.innerHTML = `<i class="fas fa-exclamation-circle"></i> No topics found matching "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info no-results';
            } else {
                searchInfo.innerHTML = `<i class="fas fa-search"></i> All ${visibleCount} topics match "${searchQuery}"`;
                searchInfo.style.display = 'block';
                searchInfo.className = 'search-info has-results';
            }
        } else {
            searchInfo.style.display = 'none';
            searchInfo.className = 'search-info';
        }
    }

    function performSearch() {
        const visibleCount = filterSections(searchQuery);
        updateSearchInfo(visibleCount, sections.length);

        // If search is active and only one section is visible, auto-expand it
        if (searchQuery && visibleCount === 1) {
            sections.forEach(section => {
                if (!section.classList.contains('hidden')) {
                    section.classList.add('open');
                }
            });
        }
    }

    // --- Search Input ---
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
                performSearch();
                searchTimeout = null;
            }, 300);
        });

        // Ctrl+F / Cmd+F to focus search
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }

            // Escape to clear search
            if (e.key === 'Escape' && document.activeElement === searchInput) {
                e.preventDefault();
                searchInput.value = '';
                searchQuery = '';
                if (clearSearchBtn) {
                    clearSearchBtn.style.display = 'none';
                }
                performSearch();
                searchInput.blur();
            }
        });
    }

    // --- Clear Search Button ---
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchQuery = '';
            this.style.display = 'none';
            performSearch();
            searchInput.focus();
            if (searchInfo) {
                searchInfo.style.display = 'none';
            }
        });
    }
});