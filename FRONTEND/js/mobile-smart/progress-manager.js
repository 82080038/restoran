/**
 * Progress Manager
 * Manages progress indicators for long-running operations
 */
class ProgressManager {
    constructor() {
        this.activeProgress = new Map();
        this.init();
    }

    init() {
        // Create progress container
        this.container = document.createElement('div');
        this.container.className = 'progress-container';
        this.container.id = 'progressContainer';
        document.body.appendChild(this.container);
    }

    /**
     * Create progress indicator
     * @param {string} id - Progress ID
     * @param {string} title - Progress title
     * @param {Object} options - Options
     */
    create(id, title, options = {}) {
        const progress = document.createElement('div');
        progress.className = 'progress-indicator';
        progress.id = `progress-${id}`;
        
        const showPercentage = options.showPercentage !== false;
        const showSteps = options.steps !== undefined;
        const currentStep = options.currentStep || 0;
        const totalSteps = options.steps || 0;

        progress.innerHTML = `
            <div class="progress-header">
                <h4 class="progress-title">${title}</h4>
                ${showPercentage ? '<span class="progress-percentage">0%</span>' : ''}
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: 0%"></div>
            </div>
            ${showSteps ? `
                <div class="progress-steps">
                    <span class="progress-step-text">Step ${currentStep} of ${totalSteps}</span>
                </div>
            ` : ''}
            <div class="progress-message"></div>
            ${options.cancellable ? '<button class="progress-cancel">Cancel</button>' : ''}
        `;

        // Add cancel handler
        if (options.cancellable && options.onCancel) {
            const cancelBtn = progress.querySelector('.progress-cancel');
            cancelBtn.addEventListener('click', () => {
                options.onCancel();
                this.remove(id);
            });
        }

        this.container.appendChild(progress);
        this.activeProgress.set(id, {
            element: progress,
            options: options,
            value: 0
        });

        return progress;
    }

    /**
     * Update progress value
     * @param {string} id - Progress ID
     * @param {number} value - Progress value (0-100)
     * @param {string} message - Progress message
     */
    update(id, value, message = '') {
        const progress = this.activeProgress.get(id);
        if (!progress) return;

        progress.value = value;
        const element = progress.element;

        // Update progress bar
        const bar = element.querySelector('.progress-bar');
        if (bar) {
            bar.style.width = `${value}%`;
        }

        // Update percentage
        const percentage = element.querySelector('.progress-percentage');
        if (percentage) {
            percentage.textContent = `${Math.round(value)}%`;
        }

        // Update message
        const messageEl = element.querySelector('.progress-message');
        if (messageEl && message) {
            messageEl.textContent = message;
        }

        // Auto-complete at 100%
        if (value >= 100) {
            setTimeout(() => {
                this.complete(id);
            }, 500);
        }
    }

    /**
     * Update step
     * @param {string} id - Progress ID
     * @param {number} step - Current step
     * @param {number} total - Total steps
     * @param {string} message - Step message
     */
    updateStep(id, step, total, message = '') {
        const progress = this.activeProgress.get(id);
        if (!progress) return;

        const element = progress.element;
        const stepText = element.querySelector('.progress-step-text');
        if (stepText) {
            stepText.textContent = `Step ${step} of ${total}`;
        }

        const value = (step / total) * 100;
        this.update(id, value, message);
    }

    /**
     * Set progress as indeterminate
     * @param {string} id - Progress ID
     * @param {string} message - Message
     */
    setIndeterminate(id, message = 'Processing...') {
        const progress = this.activeProgress.get(id);
        if (!progress) return;

        const element = progress.element;
        const bar = element.querySelector('.progress-bar');
        if (bar) {
            bar.classList.add('indeterminate');
        }

        const messageEl = element.querySelector('.progress-message');
        if (messageEl) {
            messageEl.textContent = message;
        }
    }

    /**
     * Complete progress
     * @param {string} id - Progress ID
     * @param {string} message - Completion message
     */
    complete(id, message = 'Complete!') {
        const progress = this.activeProgress.get(id);
        if (!progress) return;

        this.update(id, 100, message);
        
        const element = progress.element;
        element.classList.add('complete');

        setTimeout(() => {
            this.remove(id);
        }, 2000);
    }

    /**
     * Set progress as error
     * @param {string} id - Progress ID
     * @param {string} message - Error message
     */
    error(id, message = 'Error occurred') {
        const progress = this.activeProgress.get(id);
        if (!progress) return;

        const element = progress.element;
        element.classList.add('error');

        const messageEl = element.querySelector('.progress-message');
        if (messageEl) {
            messageEl.textContent = message;
        }

        setTimeout(() => {
            this.remove(id);
        }, 3000);
    }

    /**
     * Remove progress indicator
     * @param {string} id - Progress ID
     */
    remove(id) {
        const progress = this.activeProgress.get(id);
        if (!progress) return;

        const element = progress.element;
        element.classList.add('removing');

        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
            this.activeProgress.delete(id);
        }, 300);
    }

    /**
     * Remove all progress indicators
     */
    removeAll() {
        this.activeProgress.forEach((progress, id) => {
            this.remove(id);
        });
    }

    /**
     * Wrap async function with progress tracking
     * @param {Function} fn - Async function
     * @param {string} id - Progress ID
     * @param {string} title - Progress title
     * @param {Object} options - Options
     */
    async withProgress(fn, id, title, options = {}) {
        this.create(id, title, options);
        
        try {
            const result = await fn();
            this.complete(id);
            return result;
        } catch (error) {
            this.error(id, error.message);
            throw error;
        }
    }

    /**
     * Create inline progress bar
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Options
     */
    createInline(container, options = {}) {
        const progress = document.createElement('div');
        progress.className = 'progress-inline';
        
        progress.innerHTML = `
            <div class="progress-bar-container small">
                <div class="progress-bar" style="width: 0%"></div>
            </div>
            ${options.showText ? '<span class="progress-text">0%</span>' : ''}
        `;

        container.appendChild(progress);

        return {
            element: progress,
            update: (value) => {
                const bar = progress.querySelector('.progress-bar');
                if (bar) bar.style.width = `${value}%`;
                
                const text = progress.querySelector('.progress-text');
                if (text) text.textContent = `${Math.round(value)}%`;
            },
            remove: () => {
                if (progress.parentNode) {
                    progress.parentNode.removeChild(progress);
                }
            }
        };
    }
}

// Initialize progress manager
document.addEventListener('DOMContentLoaded', () => {
    window.progressManager = new ProgressManager();
});
