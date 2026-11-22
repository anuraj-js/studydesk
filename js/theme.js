(function () {
    // Immediately apply the theme to prevent FOUC (Flash of Unstyled Content)
    const t = localStorage.getItem('sdl-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', t);
})();

function initTheme() {
    // Grab both desktop and mobile toggle buttons
    const buttons = [
        document.getElementById('themeToggle'),
        document.getElementById('themeToggleMobile')
    ].filter(Boolean); // Remove nulls if a button doesn't exist on the current page

    if (!buttons.length) return;

    function syncBtns() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        buttons.forEach(btn => {
            // Only inject the knob element once
            if (!btn.querySelector('.toggle-knob')) {
                btn.innerHTML = `<span class="toggle-knob"><i class="fa-solid fa-moon"></i></span>`;
            }

            // Swap the icon class based on the current theme
            const icon = btn.querySelector('.toggle-knob i');
            icon.className = `fa-solid ${isDark ? 'fa-sun' : 'fa-moon'}`;
            
            // Update the tooltip
            btn.title = isDark ? 'Switch to light mode' : 'Switch to dark mode';
        });
    }

    // Initial sync
    syncBtns();

    // Attach click listeners to all found buttons
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('sdl-theme', next);
            syncBtns(); // Update all buttons instantly
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
});