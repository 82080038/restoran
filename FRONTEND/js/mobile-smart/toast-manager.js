/**
 * Toast Notification Manager
 * Manages toast notifications for user feedback
 */
class ToastManager {
    constructor() {
        this.container = this.createContainer();
        this.queue = [];
        this.isShowing = false;
        this.defaultDuration = 3000;
        this.init();
    }

    init() {
        document.body.appendChild(this.container);
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        container.id = 'toastContainer';
        return container;
    }

    /**
     * Show toast notification
     * @param {string} message - Toast message
     * @param {string} type - Toast type (success, error, warning, info)
     * @param {number} duration - Duration in milliseconds
     * @param {Object} options - Additional options
     */
    show(message, type = 'info', duration = this.defaultDuration, options = {}) {
        const toast = this.createToast(message, type, options);
        
        if (this.isShowing) {
            this.queue.push({ toast, duration });
            return;
        }

        this.showToast(toast, duration);
    }

    /**
     * Create toast element
     * @param {string} message - Toast message
     * @param {string} type - Toast type
     * @param {Object} options - Additional options
     */
    createToast(message, type, options) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icon = this.getIcon(type);
        const action = options.action ? this.createAction(options.action) : '';
        const dismissible = options.dismissible !== false;

        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <p class="toast-message">${message}</p>
                ${options.subtitle ? `<p class="toast-subtitle">${options.subtitle}</p>` : ''}
            </div>
            ${action}
            ${dismissible ? '<button class="toast-close">&times;</button>' : ''}
        `;

        // Add close button handler
        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.dismiss(toast));
        }

        // Add action handler
        if (options.action) {
            const actionBtn = toast.querySelector('.toast-action');
            if (actionBtn) {
                actionBtn.addEventListener('click', () => {
                    options.action.callback();
                    this.dismiss(toast);
                });
            }
        }

        return toast;
    }

    /**
     * Get icon based on type
     * @param {string} type - Toast type
     */
    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    /**
     * Create action button
     * @param {Object} action - Action configuration
     */
    createAction(action) {
        return `<button class="toast-action">${action.label}</button>`;
    }

    /**
     * Show toast with animation
     * @param {HTMLElement} toast - Toast element
     * @param {number} duration - Duration
     */
    showToast(toast, duration) {
        this.isShowing = true;
        this.container.appendChild(toast);

        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => {
                this.dismiss(toast);
            }, duration);
        }
    }

    /**
     * Dismiss toast
     * @param {HTMLElement} toast - Toast element
     */
    dismiss(toast) {
        toast.classList.remove('show');
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.isShowing = false;
            this.processQueue();
        }, 300);
    }

    /**
     * Process queued toasts
     */
    processQueue() {
        if (this.queue.length > 0) {
            const { toast, duration } = this.queue.shift();
            this.showToast(toast, duration);
        }
    }

    /**
     * Show success toast
     * @param {string} message - Message
     * @param {Object} options - Options
     */
    success(message, options = {}) {
        this.show(message, 'success', options.duration || this.defaultDuration, options);
    }

    /**
     * Show error toast
     * @param {string} message - Message
     * @param {Object} options - Options
     */
    error(message, options = {}) {
        this.show(message, 'error', options.duration || 5000, options);
    }

    /**
     * Show warning toast
     * @param {string} message - Message
     * @param {Object} options - Options
     */
    warning(message, options = {}) {
        this.show(message, 'warning', options.duration || 4000, options);
    }

    /**
     * Show info toast
     * @param {string} message - Message
     * @param {Object} options - Options
     */
    info(message, options = {}) {
        this.show(message, 'info', options.duration || this.defaultDuration, options);
    }

    /**
     * Clear all toasts
     */
    clear() {
        this.queue = [];
        const toasts = this.container.querySelectorAll('.toast');
        toasts.forEach(toast => this.dismiss(toast));
    }

    /**
     * Show loading toast
     * @param {string} message - Message
     */
    loading(message = 'Loading...') {
        const toast = this.createToast(message, 'info', { dismissible: false });
        toast.classList.add('toast-loading');
        this.showToast(toast, 0); // No auto dismiss
        return toast;
    }

    /**
     * Update loading toast
     * @param {HTMLElement} toast - Toast element
     * @param {string} message - New message
     */
    updateLoading(toast, message) {
        const messageEl = toast.querySelector('.toast-message');
        if (messageEl) {
            messageEl.textContent = message;
        }
    }

    /**
     * Dismiss loading toast
     * @param {HTMLElement} toast - Toast element
     */
    dismissLoading(toast) {
        this.dismiss(toast);
    }
}

// Initialize toast manager
document.addEventListener('DOMContentLoaded', () => {
    window.toastManager = new ToastManager();
});
