/**
 * Centralized fetch wrapper.
 * Handles CSRF headers, JSON parsing, and 401 redirects.
 */
async function apiFetch(url, options = {}) {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    const headers = {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken,
        ...options.headers
    };

    const config = {
        ...options,
        headers
    };

    try {
        const response = await fetch(url, config);
        
        // Handle 401 Unauthenticated globally
        if (response.status === 401) {
            window.location.href = '/studydesk/pages/login.php';
            throw new Error('Unauthenticated');
        }

        const data = await response.json();
        return data;
    } catch (error) {
        if (error.message !== 'Unauthenticated') {
            console.error('API Fetch Error:', error);
        }
        throw error;
    }
}