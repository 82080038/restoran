/**
 * Loading Manager
 * Manages loading states, spinners, and skeleton screens
 */
class LoadingManager {
    constructor() {
        this.activeLoaders = new Map();
        this.defaultSpinner = this.createDefaultSpinner();
        this.init();
    }

    init() {
        // Add default spinner to DOM
        document.body.appendChild(this.defaultSpinner);
    }

    createDefaultSpinner() {
        const spinner = document.createElement('div');
        spinner.className = 'loading-spinner-overlay';
        spinner.id = 'defaultSpinner';
        spinner.innerHTML = `
            <div class="spinner-container">
                <div class="spinner"></div>
                <p class="spinner-text">Loading...</p>
            </div>
        `;
        spinner.style.display = 'none';
        return spinner;
    }

    /**
     * Show loading spinner
     * @param {string} id - Unique loader ID
     * @param {string} message - Loading message
     * @param {boolean} overlay - Show as overlay
     */
    show(id, message = 'Loading...', overlay = true) {
        const loader = this.getOrCreateLoader(id, message, overlay);
        loader.style.display = 'flex';
        this.activeLoaders.set(id, loader);
    }

    /**
     * Hide loading spinner
     * @param {string} id - Loader ID
     */
    hide(id) {
        const loader = this.activeLoaders.get(id);
        if (loader) {
            loader.style.display = 'none';
            this.activeLoaders.delete(id);
        }
    }

    /**
     * Hide all loaders
     */
    hideAll() {
        this.activeLoaders.forEach((loader, id) => {
            loader.style.display = 'none';
        });
        this.activeLoaders.clear();
    }

    /**
     * Get or create loader
     * @param {string} id - Loader ID
     * @param {string} message - Loading message
     * @param {boolean} overlay - Show as overlay
     */
    getOrCreateLoader(id, message, overlay) {
        let loader = document.getElementById(`loader-${id}`);
        
        if (!loader) {
            loader = document.createElement('div');
            loader.id = `loader-${id}`;
            loader.className = overlay ? 'loading-spinner-overlay' : 'loading-spinner-inline';
            loader.innerHTML = `
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <p class="spinner-text">${message}</p>
                </div>
            `;
            document.body.appendChild(loader);
        } else {
            // Update message
            const textEl = loader.querySelector('.spinner-text');
            if (textEl) textEl.textContent = message;
        }

        return loader;
    }

    /**
     * Show skeleton screen
     * @param {string} containerId - Container element ID
     * @param {string} type - Skeleton type (card, list, detail)
     * @param {number} count - Number of skeleton items
     */
    showSkeleton(containerId, type = 'card', count = 3) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const originalContent = container.innerHTML;
        container.dataset.originalContent = originalContent;

        let skeletonHTML = '';
        for (let i = 0; i < count; i++) {
            skeletonHTML += this.getSkeletonHTML(type);
        }

        container.innerHTML = `<div class="skeleton-wrapper">${skeletonHTML}</div>`;
    }

    /**
     * Hide skeleton screen
     * @param {string} containerId - Container element ID
     */
    hideSkeleton(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const originalContent = container.dataset.originalContent;
        if (originalContent) {
            container.innerHTML = originalContent;
            delete container.dataset.originalContent;
        }
    }

    /**
     * Get skeleton HTML based on type
     * @param {string} type - Skeleton type
     */
    getSkeletonHTML(type) {
        switch (type) {
            case 'card':
                return `
                    <div class="skeleton-card">
                        <div class="skeleton-image"></div>
                        <div class="skeleton-content">
                            <div class="skeleton-title"></div>
                            <div class="skeleton-text"></div>
                            <div class="skeleton-text short"></div>
                        </div>
                    </div>
                `;
            case 'list':
                return `
                    <div class="skeleton-list-item">
                        <div class="skeleton-avatar"></div>
                        <div class="skeleton-content">
                            <div class="skeleton-title"></div>
                            <div class="skeleton-text"></div>
                        </div>
                    </div>
                `;
            case 'detail':
                return `
                    <div class="skeleton-detail">
                        <div class="skeleton-header"></div>
                        <div class="skeleton-text"></div>
                        <div class="skeleton-text"></div>
                        <div class="skeleton-text short"></div>
                    </div>
                `;
            default:
                return `<div class="skeleton-box"></div>`;
        }
    }

    /**
     * Wrap async function with loading state
     * @param {Function} fn - Async function
     * @param {string} loaderId - Loader ID
     * @param {string} message - Loading message
     */
    async withLoading(fn, loaderId = 'default', message = 'Loading...') {
        this.show(loaderId, message);
        try {
            const result = await fn();
            return result;
        } finally {
            this.hide(loaderId);
        }
    }

    /**
     * Show inline loading in button
     * @param {HTMLElement} button - Button element
     * @param {boolean} loading - Loading state
     * @param {string} originalText - Original button text
     */
    setButtonLoading(button, loading, originalText) {
        if (loading) {
            button.dataset.originalText = button.textContent;
            button.disabled = true;
            button.innerHTML = `
                <span class="btn-spinner"></span>
                <span>Loading...</span>
            `;
        } else {
            button.disabled = false;
            button.textContent = originalText || button.dataset.originalText || button.textContent;
        }
    }
}

// Initialize loading manager
document.addEventListener('DOMContentLoaded', () => {
    window.loadingManager = new LoadingManager();
});
