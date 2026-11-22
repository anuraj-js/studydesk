/**
 * Escapes HTML characters to prevent XSS.
 */
function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Escapes text and highlights the matching search query.
 */
function highlightText(text, query) {
    if (!query) return esc(text);
    
    const escapedText = esc(text);
    const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${escapedQuery})`, 'gi');
    
    return escapedText.replace(regex, '<span class="search-highlight">$1</span>');
}

/**
 * Initializes the accordion behavior for Help pages.
 * Safe to include globally; does nothing if help elements aren't on the page.
 */
function initHelpAccordion() {
    const toggleButtons = document.querySelectorAll('.help-toggle-btn');
    if (!toggleButtons.length) return; // Exit if not on a Help page

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const content = btn.nextElementSibling;
            const chevron = btn.querySelector('.help-chevron');
            const isOpen = content.style.display === 'block';

            // Close all other open sections
            document.querySelectorAll('.help-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.help-chevron').forEach(el => el.style.transform = 'rotate(0deg)');

            // If it was closed, open it now
            if (!isOpen) {
                content.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
            }
        });
    });
}

// Automatically initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', initHelpAccordion);