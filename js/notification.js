// js/notification.js

// Toast Container (create once)
let toastContainer = null;

function getToastContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    return toastContainer;
}

// Show Toast
function showToast(message, type = 'info', duration = 3500) {
    const container = getToastContainer();

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const iconMap = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-exclamation-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };

    toast.innerHTML = `
        <span class="toast-icon">${iconMap[type] || iconMap.info}</span>
        <span class="toast-message">${message}</span>
        <button class="toast-close"><i class="fas fa-times"></i></button>
    `;

    container.appendChild(toast);

    toast.querySelector('.toast-close').addEventListener('click', () => {
        dismissToast(toast);
    });

    const timeout = setTimeout(() => {
        dismissToast(toast);
    }, duration);

    toast.dataset.timeout = timeout;

    return toast;
}

function dismissToast(toast) {
    if (toast.classList.contains('toast-exit')) return;
    toast.classList.add('toast-exit');
    clearTimeout(parseInt(toast.dataset.timeout));
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 350);
}

// Show Confirm (replaces confirm())
function showConfirm(message, onConfirm, onCancel = null) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay-custom open';

    overlay.innerHTML = `
        <div class="modal-box">
            <div class="modal-icon"><i class="fas fa-exclamation-triangle" style="color: var(--color-warning);"></i></div>
            <div class="modal-title">Confirm</div>
            <div class="modal-message">${message}</div>
            <div class="modal-actions">
                <button class="btn btn-cancel" id="modalCancelBtn">Cancel</button>
                <button class="btn btn-danger" id="modalConfirmBtn">Confirm</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    overlay.querySelector('#modalCancelBtn').addEventListener('click', () => {
        document.body.removeChild(overlay);
        if (onCancel) onCancel();
    });

    overlay.querySelector('#modalConfirmBtn').addEventListener('click', () => {
        document.body.removeChild(overlay);
        if (onConfirm) onConfirm();
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            document.body.removeChild(overlay);
            if (onCancel) onCancel();
        }
    });
}