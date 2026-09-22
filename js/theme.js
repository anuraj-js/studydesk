// js/theme.js

document.addEventListener('DOMContentLoaded', () => {
    const themeBtns = document.querySelectorAll('.theme-btn');
    const darkToggle = document.getElementById('darkToggle');
    const toggleThemes = document.getElementById('toggleThemes');
    const themeOptionsWrapper = document.getElementById('themeOptionsWrapper');
    const html = document.documentElement;

    // Get current theme from session (set in PHP)
    let currentTheme = html.dataset.theme || 'blue';
    let darkMode = html.dataset.dark === 'true';
    let isSaving = false;

    // Store original button HTML for restoration
    themeBtns.forEach(btn => {
        btn.dataset.originalHtml = btn.innerHTML;
    });

    // Toggle themes visibility
    if (toggleThemes && themeOptionsWrapper) {
        toggleThemes.addEventListener('click', function() {
            const isVisible = themeOptionsWrapper.style.display !== 'none';
            themeOptionsWrapper.style.display = isVisible ? 'none' : 'block';
            const icon = this.querySelector('.fa-chevron-down');
            if (icon) {
                icon.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    }

    // Apply theme to HTML
    function applyTheme(theme, dark) {
        html.setAttribute('data-theme', theme);
        html.setAttribute('data-dark', dark ? 'true' : 'false');

        // Update active button
        themeBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });

        // Update toggle
        if (darkToggle) {
            darkToggle.checked = dark;
        }

        // Update logo
        updateLogo(theme);
    }

    // Update logo based on theme
    function updateLogo(theme) {
        const logoImg = document.querySelector('.logo-img');
        if (logoImg) {
            logoImg.src = `../images/studydesk_logo_${theme}.svg`;
        }
    }

    // Set loading state on theme button
    function setThemeLoading(isLoading) {
        themeBtns.forEach(btn => {
            if (btn.dataset.theme === currentTheme) {
                if (isLoading) {
                    btn.disabled = true;
                    btn.classList.add('btn-loading');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                } else {
                    btn.disabled = false;
                    btn.classList.remove('btn-loading');
                    // Restore original HTML
                    btn.innerHTML = btn.dataset.originalHtml;
                }
            }
        });

        if (darkToggle && isLoading) {
            darkToggle.disabled = true;
        } else if (darkToggle) {
            darkToggle.disabled = false;
        }
    }

    // Save theme to database
    async function saveTheme(theme, dark) {
        if (isSaving) return;
        isSaving = true;

        // Show loading state
        setThemeLoading(true);

        try {
            const response = await fetch('../api/theme/update.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    theme: theme,
                    dark_mode: dark ? 1 : 0
                })
            });

            const result = await response.json();

            if (result.success) {
                // Show success toast
                const themeName = theme.charAt(0).toUpperCase() + theme.slice(1);
                const mode = dark ? 'Dark' : 'Light';
                showToast(`${themeName} theme with ${mode} mode applied`, 'success');
            } else {
                showToast('Error: ' + result.message, 'error');
                // Revert theme on error
                applyTheme(currentTheme, darkMode);
            }
        } catch (error) {
            showToast('Network error saving theme', 'error');
            // Revert theme on error
            applyTheme(currentTheme, darkMode);
        } finally {
            isSaving = false;
            setThemeLoading(false);
        }
    }

    // Theme button click
    themeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const theme = this.dataset.theme;
            currentTheme = theme;
            applyTheme(theme, darkMode);
            saveTheme(theme, darkMode);
        });
    });

    // Dark mode toggle
    if (darkToggle) {
        darkToggle.addEventListener('change', function() {
            darkMode = this.checked;
            applyTheme(currentTheme, darkMode);
            saveTheme(currentTheme, darkMode);
        });
    }

    // Keyboard shortcut: Escape to close theme options
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && themeOptionsWrapper && themeOptionsWrapper.style.display !== 'none') {
            themeOptionsWrapper.style.display = 'none';
            const icon = toggleThemes?.querySelector('.fa-chevron-down');
            if (icon) {
                icon.style.transform = 'rotate(0deg)';
            }
        }
    });

    // Initial apply
    applyTheme(currentTheme, darkMode);
});