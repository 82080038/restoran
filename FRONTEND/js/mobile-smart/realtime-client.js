/**
 * Real-time Client
 * Handles WebSocket and Server-Sent Events (SSE) connections
 * for real-time data updates
 */
class RealtimeClient {
    constructor(config = {}) {
        var apiBase = (typeof Config !== 'undefined' ? Config.api.baseURL : '/api/v1');
        this.wsUrl = config.wsUrl || (window.location.protocol === 'https:' ? 'wss://' : 'ws://') + window.location.host + '/ws';
        this.sseUrl = config.sseUrl || apiBase + '/realtime/events';
        this.ws = null;
        this.eventSource = null;
        this.channels = new Set();
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.isConnected = false;
        this.listeners = new Map();
        this.useSSE = config.useSSE || false;
        this.init();
    }

    init() {
        if (this.useSSE) {
            this.connectSSE();
        } else {
            this.connectWebSocket();
        }

        // Handle visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.disconnect();
            } else {
                this.reconnect();
            }
        });

        // Handle online/offline
        window.addEventListener('online', () => {
            this.reconnect();
        });

        window.addEventListener('offline', () => {
            this.disconnect();
        });
    }

    /**
     * Connect via WebSocket
     */
    connectWebSocket() {
        try {
            this.ws = new WebSocket(this.wsUrl);

            this.ws.onopen = () => {
                this.isConnected = true;
                this.reconnectAttempts = 0;
                console.log('WebSocket connected');
                this.emit('connected');
                
                // Subscribe to channels
                this.channels.forEach(channel => {
                    this.subscribe(channel);
                });
            };

            this.ws.onmessage = (event) => {
                this.handleMessage(event.data);
            };

            this.ws.onclose = () => {
                this.isConnected = false;
                console.log('WebSocket disconnected');
                this.emit('disconnected');
                this.scheduleReconnect();
            };

            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.emit('error', error);
            };

        } catch (error) {
            console.error('Failed to connect WebSocket:', error);
            this.scheduleReconnect();
        }
    }

    /**
     * Connect via Server-Sent Events
     */
    connectSSE() {
        try {
            this.eventSource = new EventSource(this.sseUrl);

            this.eventSource.onopen = () => {
                this.isConnected = true;
                this.reconnectAttempts = 0;
                console.log('SSE connected');
                this.emit('connected');
            };

            this.eventSource.onmessage = (event) => {
                this.handleMessage(event.data);
            };

            this.eventSource.onerror = (error) => {
                console.error('SSE error:', error);
                this.isConnected = false;
                this.emit('disconnected');
                this.eventSource.close();
                this.scheduleReconnect();
            };

        } catch (error) {
            console.error('Failed to connect SSE:', error);
            this.scheduleReconnect();
        }
    }

    /**
     * Handle incoming message
     * @param {string} data - Message data
     */
    handleMessage(data) {
        try {
            const message = JSON.parse(data);
            
            // Emit to specific channel listeners
            if (message.channel) {
                this.emit(`channel:${message.channel}`, message);
            }

            // Emit to type listeners
            if (message.type) {
                this.emit(`type:${message.type}`, message);
            }

            // Emit to all listeners
            this.emit('message', message);

        } catch (error) {
            console.error('Failed to parse message:', error);
        }
    }

    /**
     * Subscribe to a channel
     * @param {string} channel - Channel name
     */
    subscribe(channel) {
        this.channels.add(channel);
        
        if (this.isConnected && this.ws) {
            this.send({
                type: 'subscribe',
                channel: channel
            });
        }
    }

    /**
     * Unsubscribe from a channel
     * @param {string} channel - Channel name
     */
    unsubscribe(channel) {
        this.channels.delete(channel);
        
        if (this.isConnected && this.ws) {
            this.send({
                type: 'unsubscribe',
                channel: channel
            });
        }
    }

    /**
     * Send message via WebSocket
     * @param {Object} data - Message data
     */
    send(data) {
        if (this.ws && this.isConnected) {
            this.ws.send(JSON.stringify(data));
        }
    }

    /**
     * Schedule reconnection attempt
     */
    scheduleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.log('Max reconnection attempts reached');
            this.emit('reconnectFailed');
            return;
        }

        this.reconnectAttempts++;
        const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
        
        console.log(`Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts})`);
        
        setTimeout(() => {
            this.reconnect();
        }, delay);
    }

    /**
     * Reconnect to server
     */
    reconnect() {
        this.disconnect();
        
        if (this.useSSE) {
            this.connectSSE();
        } else {
            this.connectWebSocket();
        }
    }

    /**
     * Disconnect from server
     */
    disconnect() {
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }

        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }

        this.isConnected = false;
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
     * Get connection status
     */
    getStatus() {
        return {
            connected: this.isConnected,
            channels: Array.from(this.channels),
            reconnectAttempts: this.reconnectAttempts,
            useSSE: this.useSSE
        };
    }
}

/**
 * Real-time Manager
 * Manages real-time updates for specific data types
 */
class RealtimeManager {
    constructor() {
        this.client = null;
        this.autoRefreshIntervals = new Map();
        this.init();
    }

    init() {
        // Initialize real-time client
        var apiBase = (typeof Config !== 'undefined' ? Config.api.baseURL : '/api/v1');
        this.client = new RealtimeClient({
            wsUrl: window.config?.wsUrl || (window.location.protocol === 'https:' ? 'wss://' : 'ws://') + window.location.host + '/ws',
            sseUrl: window.config?.sseUrl || apiBase + '/realtime/events',
            useSSE: false // Use WebSocket by default
        });

        // Set up default listeners
        this.setupDefaultListeners();
    }

    setupDefaultListeners() {
        // Order updates
        this.client.on('channel:orders', (message) => {
            this.handleOrderUpdate(message);
        });

        // Table updates
        this.client.on('channel:tables', (message) => {
            this.handleTableUpdate(message);
        });

        // KDS updates
        this.client.on('channel:kds', (message) => {
            this.handleKDSUpdate(message);
        });

        // Waitlist updates
        this.client.on('channel:waitlist', (message) => {
            this.handleWaitlistUpdate(message);
        });
    }

    handleOrderUpdate(message) {
        if (window.mobileApp) {
            window.mobileApp.loadOrders();
        }
        
        if (window.toastManager) {
            window.toastManager.info(`Order ${message.data.order_number} updated`, {
                subtitle: `Status: ${message.data.status}`
            });
        }
    }

    handleTableUpdate(message) {
        if (window.mobileApp) {
            window.mobileApp.loadTables();
        }
    }

    handleKDSUpdate(message) {
        // Handle KDS ticket updates
        console.log('KDS update:', message);
    }

    handleWaitlistUpdate(message) {
        // Handle waitlist updates
        console.log('Waitlist update:', message);
    }

    /**
     * Subscribe to order updates
     */
    subscribeOrders() {
        this.client.subscribe('orders');
    }

    /**
     * Subscribe to table updates
     */
    subscribeTables() {
        this.client.subscribe('tables');
    }

    /**
     * Subscribe to KDS updates
     */
    subscribeKDS() {
        this.client.subscribe('kds');
    }

    /**
     * Subscribe to waitlist updates
     */
    subscribeWaitlist() {
        this.client.subscribe('waitlist');
    }

    /**
     * Set up auto-refresh for data
     * @param {string} key - Refresh key
     * @param {Function} callback - Refresh callback
     * @param {number} interval - Refresh interval in ms
     */
    setupAutoRefresh(key, callback, interval = 30000) {
        // Clear existing interval
        if (this.autoRefreshIntervals.has(key)) {
            clearInterval(this.autoRefreshIntervals.get(key));
        }

        // Set up new interval
        const intervalId = setInterval(() => {
            callback();
        }, interval);

        this.autoRefreshIntervals.set(key, intervalId);
    }

    /**
     * Clear auto-refresh
     * @param {string} key - Refresh key
     */
    clearAutoRefresh(key) {
        if (this.autoRefreshIntervals.has(key)) {
            clearInterval(this.autoRefreshIntervals.get(key));
            this.autoRefreshIntervals.delete(key);
        }
    }

    /**
     * Clear all auto-refresh intervals
     */
    clearAllAutoRefresh() {
        this.autoRefreshIntervals.forEach((intervalId) => {
            clearInterval(intervalId);
        });
        this.autoRefreshIntervals.clear();
    }

    /**
     * Get client status
     */
    getStatus() {
        return this.client.getStatus();
    }
}

// Initialize real-time manager
document.addEventListener('DOMContentLoaded', () => {
    window.realtimeManager = new RealtimeManager();
});
