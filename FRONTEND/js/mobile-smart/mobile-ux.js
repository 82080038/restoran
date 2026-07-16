/**
 * Mobile UX Enhancements
 * Handles swipe gestures, pull-to-refresh, and touch optimizations
 */
class MobileUX {
    constructor() {
        this.swipeThreshold = 50;
        this.pullThreshold = 80;
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.touchEndX = 0;
        this.touchEndY = 0;
        this.isPulling = false;
        this.pullDistance = 0;
        this.listeners = new Map();
        this.init();
    }

    init() {
        this.setupTouchEvents();
        this.setupPullToRefresh();
        this.setupHapticFeedback();
        this.optimizeTouchTargets();
    }

    /**
     * Setup touch events for swipe detection
     */
    setupTouchEvents() {
        document.addEventListener('touchstart', (e) => {
            this.touchStartX = e.changedTouches[0].screenX;
            this.touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            this.touchEndX = e.changedTouches[0].screenX;
            this.touchEndY = e.changedTouches[0].screenY;
            this.handleSwipe();
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            this.handlePullToRefresh(e);
        }, { passive: true });
    }

    /**
     * Handle swipe gesture
     */
    handleSwipe() {
        const deltaX = this.touchEndX - this.touchStartX;
        const deltaY = this.touchEndY - this.touchStartY;
        const absDeltaX = Math.abs(deltaX);
        const absDeltaY = Math.abs(deltaY);

        // Only trigger if horizontal swipe is dominant
        if (absDeltaX > absDeltaY && absDeltaX > this.swipeThreshold) {
            const direction = deltaX > 0 ? 'right' : 'left';
            this.emit('swipe', { direction, deltaX, deltaY });
            this.emit(`swipe:${direction}`, { deltaX, deltaY });
        }

        // Vertical swipe
        if (absDeltaY > absDeltaX && absDeltaY > this.swipeThreshold) {
            const direction = deltaY > 0 ? 'down' : 'up';
            this.emit('swipe', { direction, deltaX, deltaY });
            this.emit(`swipe:${direction}`, { deltaX, deltaY });
        }
    }

    /**
     * Setup pull-to-refresh functionality
     */
    setupPullToRefresh() {
        // Create pull-to-refresh indicator
        this.ptrIndicator = document.createElement('div');
        this.ptrIndicator.className = 'pull-to-refresh-indicator';
        this.ptrIndicator.innerHTML = `
            <div class="ptr-icon">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path fill="currentColor" d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                </svg>
            </div>
            <div class="ptr-text">Pull to refresh</div>
        `;
        this.ptrIndicator.style.display = 'none';
        document.body.appendChild(this.ptrIndicator);
    }

    /**
     * Handle pull-to-refresh gesture
     * @param {TouchEvent} e - Touch event
     */
    handlePullToRefresh(e) {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Only trigger when at top of page
        if (scrollTop === 0) {
            const deltaY = e.touches[0].clientY - this.touchStartY;
            
            if (deltaY > 0) {
                this.isPulling = true;
                this.pullDistance = Math.min(deltaY, 150);
                
                // Show indicator
                this.ptrIndicator.style.display = 'flex';
                this.ptrIndicator.style.transform = `translateY(${this.pullDistance - 50}px)`;
                
                // Update text based on threshold
                const ptrText = this.ptrIndicator.querySelector('.ptr-text');
                const ptrIcon = this.ptrIndicator.querySelector('.ptr-icon');
                
                if (this.pullDistance >= this.pullThreshold) {
                    ptrText.textContent = 'Release to refresh';
                    ptrIcon.style.transform = 'rotate(180deg)';
                } else {
                    ptrText.textContent = 'Pull to refresh';
                    ptrIcon.style.transform = 'rotate(0deg)';
                }
            }
        }
    }

    /**
     * Complete pull-to-refresh
     */
    completePullToRefresh() {
        if (this.isPulling && this.pullDistance >= this.pullThreshold) {
            this.emit('refresh');
            
            // Show loading state
            const ptrText = this.ptrIndicator.querySelector('.ptr-text');
            const ptrIcon = this.ptrIndicator.querySelector('.ptr-icon');
            ptrText.textContent = 'Refreshing...';
            ptrIcon.innerHTML = `
                <svg viewBox="0 0 24 24" width="24" height="24" class="spinning">
                    <path fill="currentColor" d="M12 4V2C6.48 2 2 6.48 2 12h2c0-4.41 3.59-8 8-8zm0 14c4.41 0 8-3.59 8-8h2c0 5.52-4.48 10-10 10v-2zm-4-4c0-2.21 1.79-4 4-4s4 1.79 4 4-1.79 4-4 4-4-1.79-4-4z"/>
                </svg>
            `;
            
            // Reset after delay
            setTimeout(() => {
                this.resetPullToRefresh();
            }, 2000);
        } else {
            this.resetPullToRefresh();
        }
    }

    /**
     * Reset pull-to-refresh state
     */
    resetPullToRefresh() {
        this.isPulling = false;
        this.pullDistance = 0;
        this.ptrIndicator.style.display = 'none';
        this.ptrIndicator.style.transform = 'translateY(-50px)';
        
        const ptrText = this.ptrIndicator.querySelector('.ptr-text');
        const ptrIcon = this.ptrIndicator.querySelector('.ptr-icon');
        ptrText.textContent = 'Pull to refresh';
        ptrIcon.style.transform = 'rotate(0deg)';
    }

    /**
     * Setup haptic feedback
     */
    setupHapticFeedback() {
        // Check if haptic feedback is supported
        this.hapticSupported = 'vibrate' in navigator;
    }

    /**
     * Trigger haptic feedback
     * @param {string} pattern - Vibration pattern
     */
    hapticFeedback(pattern = 'light') {
        if (!this.hapticSupported) return;

        const patterns = {
            light: [10],
            medium: [20],
            heavy: [30],
            success: [10, 50, 10],
            error: [30, 50, 30],
            warning: [20, 30, 20]
        };

        navigator.vibrate(patterns[pattern] || pattern);
    }

    /**
     * Optimize touch targets
     */
    optimizeTouchTargets() {
        // Find all clickable elements and ensure minimum touch target size
        const touchTargets = document.querySelectorAll('button, a, input[type="submit"], input[type="button"], .clickable');
        
        touchTargets.forEach(target => {
            const computedStyle = window.getComputedStyle(target);
            const width = parseInt(computedStyle.width);
            const height = parseInt(computedStyle.height);
            const minSize = 44; // WCAG recommended minimum

            if (width < minSize || height < minSize) {
                target.style.minWidth = `${minSize}px`;
                target.style.minHeight = `${minSize}px`;
                target.style.padding = '10px';
            }
        });
    }

    /**
     * Add swipe listener to element
     * @param {HTMLElement} element - Target element
     * @param {Function} callback - Callback function
     * @param {string} direction - Swipe direction (left, right, up, down)
     */
    addSwipeListener(element, callback, direction = null) {
        let startX, startY;

        element.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });

        element.addEventListener('touchend', (e) => {
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            const deltaX = endX - startX;
            const deltaY = endY - startY;
            const absDeltaX = Math.abs(deltaX);
            const absDeltaY = Math.abs(deltaY);

            if (absDeltaX > this.swipeThreshold || absDeltaY > this.swipeThreshold) {
                if (direction) {
                    const swipeDirection = this.getSwipeDirection(deltaX, deltaY);
                    if (swipeDirection === direction) {
                        callback({ deltaX, deltaY });
                        this.hapticFeedback('light');
                    }
                } else {
                    callback({ deltaX, deltaY });
                    this.hapticFeedback('light');
                }
            }
        }, { passive: true });
    }

    /**
     * Get swipe direction
     * @param {number} deltaX - X delta
     * @param {number} deltaY - Y delta
     */
    getSwipeDirection(deltaX, deltaY) {
        const absDeltaX = Math.abs(deltaX);
        const absDeltaY = Math.abs(deltaY);

        if (absDeltaX > absDeltaY) {
            return deltaX > 0 ? 'right' : 'left';
        } else {
            return deltaY > 0 ? 'down' : 'up';
        }
    }

    /**
     * Add event listener
     * @param {string} event - Event name
     * @param {Function} callback - Callback function
     */
    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }

    /**
     * Remove event listener
     * @param {string} event - Event name
     * @param {Function} callback - Callback function
     */
    off(event, callback) {
        if (!this.listeners.has(event)) return;
        
        const callbacks = this.listeners.get(event);
        const index = callbacks.indexOf(callback);
        
        if (index > -1) {
            callbacks.splice(index, 1);
        }
    }

    /**
     * Emit event
     * @param {string} event - Event name
     * @param {*} data - Event data
     */
    emit(event, data) {
        if (!this.listeners.has(event)) return;
        
        this.listeners.get(event).forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error(`Error in ${event} listener:`, error);
            }
        });
    }
}

/**
 * Bottom Sheet Manager
 * Manages bottom sheet UI pattern for mobile
 */
class BottomSheetManager {
    constructor() {
        this.activeSheet = null;
        this.init();
    }

    init() {
        // Close bottom sheet on backdrop click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('bottom-sheet-backdrop')) {
                this.close();
            }
        });
    }

    /**
     * Show bottom sheet
     * @param {Object} options - Sheet options
     */
    show(options = {}) {
        // Close existing sheet
        if (this.activeSheet) {
            this.close();
        }

        const sheet = document.createElement('div');
        sheet.className = 'bottom-sheet';
        sheet.innerHTML = `
            <div class="bottom-sheet-backdrop"></div>
            <div class="bottom-sheet-content">
                <div class="bottom-sheet-handle"></div>
                <div class="bottom-sheet-header">
                    <h3>${options.title || ''}</h3>
                    <button class="bottom-sheet-close">&times;</button>
                </div>
                <div class="bottom-sheet-body">
                    ${options.content || ''}
                </div>
                ${options.footer ? `<div class="bottom-sheet-footer">${options.footer}</div>` : ''}
            </div>
        `;

        document.body.appendChild(sheet);
        this.activeSheet = sheet;

        // Trigger animation
        requestAnimationFrame(() => {
            sheet.classList.add('show');
        });

        // Setup close button
        const closeBtn = sheet.querySelector('.bottom-sheet-close');
        closeBtn.addEventListener('click', () => this.close());

        // Setup drag handle
        this.setupDragHandle(sheet);

        // Emit show event
        this.emit('show', sheet);

        return sheet;
    }

    /**
     * Close bottom sheet
     */
    close() {
        if (!this.activeSheet) return;

        this.activeSheet.classList.remove('show');

        setTimeout(() => {
            if (this.activeSheet && this.activeSheet.parentNode) {
                this.activeSheet.parentNode.removeChild(this.activeSheet);
            }
            this.activeSheet = null;
            this.emit('close');
        }, 300);
    }

    /**
     * Setup drag handle for bottom sheet
     * @param {HTMLElement} sheet - Sheet element
     */
    setupDragHandle(sheet) {
        const handle = sheet.querySelector('.bottom-sheet-handle');
        const content = sheet.querySelector('.bottom-sheet-content');
        let startY, currentY, isDragging = false;

        handle.addEventListener('touchstart', (e) => {
            startY = e.touches[0].clientY;
            isDragging = true;
            content.style.transition = 'none';
        }, { passive: true });

        handle.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            
            currentY = e.touches[0].clientY;
            const deltaY = currentY - startY;
            
            if (deltaY > 0) {
                content.style.transform = `translateY(${deltaY}px)`;
            }
        }, { passive: true });

        handle.addEventListener('touchend', (e) => {
            if (!isDragging) return;
            isDragging = false;
            
            const deltaY = currentY - startY;
            content.style.transition = 'transform 0.3s ease';
            
            if (deltaY > 100) {
                this.close();
            } else {
                content.style.transform = 'translateY(0)';
            }
        }, { passive: true });
    }

    /**
     * Add event listener
     * @param {string} event - Event name
     * @param {Function} callback - Callback function
     */
    on(event, callback) {
        if (!this.listeners) {
            this.listeners = new Map();
        }
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }

    /**
     * Emit event
     * @param {string} event - Event name
     * @param {*} data - Event data
     */
    emit(event, data) {
        if (!this.listeners || !this.listeners.has(event)) return;
        
        this.listeners.get(event).forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error(`Error in ${event} listener:`, error);
            }
        });
    }
}

// Initialize mobile UX
document.addEventListener('DOMContentLoaded', () => {
    window.mobileUX = new MobileUX();
    window.bottomSheetManager = new BottomSheetManager();
    
    // Setup pull-to-refresh completion
    document.addEventListener('touchend', () => {
        if (window.mobileUX) {
            window.mobileUX.completePullToRefresh();
        }
    });
});
