/**
 * F&B Management System Notification Manager
 * Handles real-time SSE notifications and displays them to the user
 */
class NotificationManager {
    constructor() {
        this.eventSource = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 5000;
        this.notifications = [];
        this.unreadCount = 0;
        this.listeners = {};
        this.isConnected = false;

        this.init();
    }

    init() {
        // Check if user is authenticated
        if (window.authManager && window.authManager.isAuthenticated()) {
            this.connect();
            this.loadUnreadCount();
        }

        // Listen for auth state changes
        window.addEventListener('authStateChanged', (e) => {
            if (e.detail.isAuthenticated) {
                this.connect();
                this.loadUnreadCount();
            } else {
                this.disconnect();
            }
        });
    }

    connect() {
        if (this.eventSource) {
            this.disconnect();
        }

        try {
            this.eventSource = window.apiClient.createNotificationStream();

            this.eventSource.addEventListener('connected', (e) => {
                console.log('Notification stream connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.emit('connected', { connected: true });
            });

            this.eventSource.addEventListener('notification', (e) => {
                try {
                    const data = JSON.parse(e.data);
                    this.handleNotification(data);
                } catch (err) {
                    console.error('Failed to parse notification:', err);
                }
            });

            this.eventSource.addEventListener('close', (e) => {
                console.log('Notification stream closed');
                this.isConnected = false;
                this.scheduleReconnect();
            });

            this.eventSource.onerror = () => {
                console.error('Notification stream error');
                this.isConnected = false;
                this.scheduleReconnect();
            };
        } catch (error) {
            console.error('Failed to connect notification stream:', error);
            this.scheduleReconnect();
        }
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
        this.isConnected = false;
    }

    scheduleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.log('Max reconnection attempts reached. Stopping.');
            return;
        }

        this.reconnectAttempts++;
        const delay = this.reconnectDelay * this.reconnectAttempts;

        setTimeout(() => {
            if (window.authManager && window.authManager.isAuthenticated()) {
                console.log(`Reconnecting notification stream (attempt ${this.reconnectAttempts})...`);
                this.connect();
            }
        }, delay);
    }

    handleNotification(data) {
        this.notifications.unshift(data);
        if (this.notifications.length > 100) {
            this.notifications = this.notifications.slice(0, 100);
        }

        this.unreadCount++;
        this.updateBadge();
        this.showToast(data);
        this.emit('notification', data);
    }

    async loadUnreadCount() {
        try {
            const response = await window.apiClient.getUnreadNotificationCount();
            if (response.success) {
                this.unreadCount = response.data.unread_count || 0;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Failed to load unread count:', error);
        }
    }

    async markAsRead(notificationId) {
        try {
            await window.apiClient.markNotificationAsRead(notificationId);
            this.unreadCount = Math.max(0, this.unreadCount - 1);
            this.updateBadge();
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    updateBadge() {
        const badges = document.querySelectorAll('.notification-badge');
        badges.forEach(badge => {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    showToast(notification) {
        const container = this.getToastContainer();
        const toast = document.createElement('div');
        toast.className = 'notification-toast';

        let icon = 'bell';
        let title = 'Notification';
        let message = '';

        switch (notification.type) {
            case 'new_order':
                icon = 'shopping-cart';
                title = 'New Order';
                message = `Order #${notification.order_number} - Rp ${this.formatCurrency(notification.total_amount)}`;
                break;
            case 'order_update':
                icon = 'refresh';
                title = 'Order Update';
                message = `Order #${notification.order_number} - ${notification.status}`;
                break;
            case 'low_stock':
                icon = 'exclamation-triangle';
                title = 'Low Stock Alert';
                message = `${notification.item_name}: ${notification.current_stock} ${notification.unit} remaining`;
                break;
            case 'new_reservation':
                icon = 'calendar';
                title = 'New Reservation';
                message = `${notification.customer_name} - ${notification.party_size} guests on ${notification.date}`;
                break;
            default:
                message = JSON.stringify(notification);
        }

        toast.innerHTML = `
            <div class="toast-icon"><i class="fas fa-${icon}"></i></div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">&times;</button>
        `;

        container.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });
    }

    getToastContainer() {
        let container = document.getElementById('notification-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;max-width:350px';
            document.body.appendChild(container);
        }
        return container;
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'decimal', minimumFractionDigits: 0 }).format(amount || 0);
    }

    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    }

    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(cb => cb(data));
        }
    }

    getNotifications() {
        return this.notifications;
    }

    getUnreadCount() {
        return this.unreadCount;
    }
}

// Initialize global notification manager
window.notificationManager = new NotificationManager();
