// js/timezone.js

(async () => {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    try {
        await fetch('../api/user/timezone.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ timezone })
        });
    } catch (_) {
        // silent fail — non-critical
    }
})();