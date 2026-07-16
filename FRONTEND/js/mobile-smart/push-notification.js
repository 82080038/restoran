/**
 * Push Notification Manager
 * Handles push notifications for mobile devices
 */
class PushNotificationManager {
    constructor(config = {}) {
        this.permission = 'default';
        this.subscription = null;
        this.serviceWorkerRegistration = null;
        this.vapidPublicKey = config.vapidPublicKey || null;
        this.listeners = new Map();
        this.init();
    }

    async init() {
        // Check if service workers are supported
        if ('serviceWorker' in navigator) {
            try {
                this.serviceWorkerRegistration = await navigator.serviceWorker.register('/sw.js');
                console.log('Service Worker registered');
            } catch (error) {
                console.error('Service Worker registration failed:', error);
            }
        }

        // Check current permission
        this.permission = Notification.permission;

        // Listen for permission changes
        if ('permissions' in navigator) {
            navigator.permissions.query({ name: 'notifications' }).then(permissionStatus => {
                permissionStatus.onchange = () => {
                    this.permission = Notification.permission;
                    this.emit('permissionChanged', this.permission);
                };
            });
        }

        // Listen for push messages
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                this.handlePushMessage(event.data);
            });
        }
    }

    /**
     * Request notification permission
     */
    async requestPermission() {
        if (!('Notification' in window)) {
            console.error('This browser does not support notifications');
            return false;
        }

        if (this.permission === 'granted') {
            return true;
        }

        try {
            const permission = await Notification.requestPermission();
            this.permission = permission;
            
            if (permission === 'granted') {
                this.emit('permissionGranted');
                return true;
            } else {
                this.emit('permissionDenied');
                return false;
            }
        } catch (error) {
            console.error('Error requesting permission:', error);
            return false;
        }
    }

    /**
     * Subscribe to push notifications
     */
    async subscribe() {
        if (!this.serviceWorkerRegistration) {
            console.error('Service Worker not registered');
            return null;
        }

        try {
            const subscription = await this.serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
            });

            this.subscription = subscription;
            this.emit('subscribed', subscription);
            
            // Send subscription to server
            await this.sendSubscriptionToServer(subscription);
            
            return subscription;
        } catch (error) {
            console.error('Push subscription failed:', error);
            this.emit('subscriptionFailed', error);
            return null;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        if (!this.subscription) {
            return true;
        }

        try {
            await this.subscription.unsubscribe();
            this.subscription = null;
            this.emit('unsubscribed');
            return true;
        } catch (error) {
            console.error('Unsubscribe failed:', error);
            return false;
        }
    }

    /**
     * Send subscription to server
     * @param {PushSubscription} subscription - Push subscription
     */
    async sendSubscriptionToServer(subscription) {
        try {
            const subscriptionData = {
                endpoint: subscription.endpoint,
                keys: {
                    p256dh: subscription.getKey('p256dh'),
                    auth: subscription.getKey('auth')
                }
            };

            // Send to API
            if (window.apiClient) {
                await window.apiClient.post('/notifications/register-device', subscriptionData);
            }
        } catch (error) {
            console.error('Failed to send subscription to server:', error);
        }
    }

    /**
     * Show local notification
     * @param {string} title - Notification title
     * @param {Object} options - Notification options
     */
    showLocal(title, options = {}) {
        if (this.permission !== 'granted') {
            console.warn('Notification permission not granted');
            return;
        }

        const notification = new Notification(title, {
            icon: options.icon || '/icons/icon-192.png',
            badge: options.badge || '/icons/badge-72.png',
            body: options.body || '',
            tag: options.tag || 'default',
            data: options.data || {},
            requireInteraction: options.requireInteraction || false,
            silent: options.silent || false
        });

        // Handle click
        notification.onclick = () => {
            this.handleNotificationClick(notification);
        };

        // Auto-close after duration
        if (options.duration) {
            setTimeout(() => {
                notification.close();
            }, options.duration);
        }

        this.emit('notificationShown', { title, options });
    }

    /**
     * Handle notification click
     * @param {Notification} notification - Notification object
     */
    handleNotificationClick(notification) {
        notification.close();
        
        // Focus window
        window.focus();
        
        // Emit click event
        this.emit('notificationClicked', notification.data);
        
        // Handle specific actions based on data
        if (notification.data.action) {
            this.handleNotificationAction(notification.data);
        }
    }

    /**
     * Handle notification action
     * @param {Object} data - Notification data
     */
    handleNotificationAction(data) {
        switch (data.action) {
            case 'view_order':
                if (window.mobileApp) {
                    window.mobileApp.navigateTo('orders');
                }
                break;
            case 'view_table':
                if (window.mobileApp) {
                    window.mobileApp.navigateTo('tables');
                }
                break;
            case 'view_kds':
                // Navigate to KDS
                break;
            default:
                console.log('Unknown action:', data.action);
        }
    }

    /**
     * Handle push message from service worker
     * @param {Object} data - Push message data
     */
    handlePushMessage(data) {
        this.emit('pushMessage', data);
        
        // Show notification if data contains notification info
        if (data.notification) {
            this.showLocal(data.notification.title, data.notification.options);
        }
    }

    /**
     * Convert VAPID key to Uint8Array
     * @param {string} base64String - Base64 encoded string
     */
    urlBase64ToUint8Array(base64String) {
        if (!base64String) {
            return null;
        }

        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
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

    /**
     * Get permission status
     */
    getPermission() {
        return this.permission;
    }

    /**
     * Get subscription status
     */
    getSubscriptionStatus() {
        return {
            subscribed: !!this.subscription,
            endpoint: this.subscription?.endpoint,
            permission: this.permission
        };
    }
}

/**
 * Notification Helper
 * Provides convenient methods for common notifications
 */
class NotificationHelper {
    constructor() {
        this.manager = null;
        this.init();
    }

    init() {
        if (window.pushNotificationManager) {
            this.manager = window.pushNotificationManager;
        }
    }

    /**
     * Show order notification
     * @param {Object} order - Order data
     */
    orderUpdate(order) {
        if (this.manager) {
            this.manager.showLocal(`Order ${order.order_number} Updated`, {
                body: `Status: ${order.status}`,
                tag: `order-${order.order_id}`,
                data: {
                    action: 'view_order',
                    order_id: order.order_id
                },
                requireInteraction: true
            });
        }
    }

    /**
     * Show table notification
     * @param {Object} table - Table data
     */
    tableUpdate(table) {
        if (this.manager) {
            this.manager.showLocal(`Table ${table.table_number} ${table.status}`, {
                body: `Table status changed to ${table.status}`,
                tag: `table-${table.table_id}`,
                data: {
                    action: 'view_table',
                    table_id: table.table_id
                }
            });
        }
    }

    /**
     * Show KDS notification
     * @param {Object} ticket - KDS ticket data
     */
    kdsUpdate(ticket) {
        if (this.manager) {
            this.manager.showLocal(`New KDS Ticket`, {
                body: `Order ${ticket.order_number} - ${ticket.items_count} items`,
                tag: `kds-${ticket.ticket_id}`,
                data: {
                    action: 'view_kds',
                    ticket_id: ticket.ticket_id
                },
                requireInteraction: true
            });
        }
    }

    /**
     * Show waitlist notification
     * @param {Object} entry - Waitlist entry data
     */
    waitlistUpdate(entry) {
        if (this.manager) {
            this.manager.showLocal(`Waitlist Update`, {
                body: `Party of ${entry.party_size} - ${entry.customer_name}`,
                tag: `waitlist-${entry.entry_id}`,
                data: {
                    action: 'view_waitlist',
                    entry_id: entry.entry_id
                }
            });
        }
    }

    /**
     * Show peak hour notification
     * @param {Object} data - Peak hour data
     */
    peakHourAlert(data) {
        if (this.manager) {
            this.manager.showLocal(`Peak Hour Alert`, {
                body: `High volume expected: ${data.expected_orders} orders`,
                tag: 'peak-hour',
                data: {
                    action: 'view_dashboard'
                },
                requireInteraction: true
            });
        }
    }
}

// Initialize push notification manager
document.addEventListener('DOMContentLoaded', () => {
    window.pushNotificationManager = new PushNotificationManager({
        vapidPublicKey: window.config?.vapidPublicKey || null
    });
    
    window.notificationHelper = new NotificationHelper();
});
