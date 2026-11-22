document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const navBackdrop = document.getElementById('navBackdrop');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

    if (hamburgerBtn && mobileSidebar && navBackdrop) {
        function openMenu() {
            mobileSidebar.classList.add('open');
            navBackdrop.classList.add('active');
        }

        function closeMenu() {
            mobileSidebar.classList.remove('open');
            navBackdrop.classList.remove('active');
        }

        hamburgerBtn.addEventListener('click', openMenu);
        navBackdrop.addEventListener('click', closeMenu);
        
        if (sidebarCloseBtn) {
            sidebarCloseBtn.addEventListener('click', closeMenu);
        }

        // Close menu when clicking a link (except the logout button)
        mobileSidebar.querySelectorAll('a:not(#logoutBtnMobile)').forEach(link => {
            link.addEventListener('click', closeMenu);
        });
    }

    // ── Logout Logic (Global) ──
    function handleLogout() {
        showDialog({
            title: 'Confirm Logout',
            message: 'Are you sure you want to log out?',
            confirmText: 'Logout',
            onConfirm: async () => {
                try {
                    // Use the dynamic APP_ROOT variable
                    const basePath = window.APP_ROOT || '../';
                    const response = await apiFetch(basePath + 'api/auth/logout.php', { method: 'POST' });
                    
                    if (response.success) {
                        window.location.href = basePath + 'pages/login.php';
                    } else {
                        showToast(response.message, 'error');
                    }
                } catch (err) {
                    showToast('An error occurred while logging out.', 'error');
                }
            }
        });
    }

    const logoutBtnDesktop = document.getElementById('logoutBtnDesktop');
    const logoutBtnMobile = document.getElementById('logoutBtnMobile');

    if (logoutBtnDesktop) logoutBtnDesktop.addEventListener('click', handleLogout);
    if (logoutBtnMobile) logoutBtnMobile.addEventListener('click', handleLogout);
});