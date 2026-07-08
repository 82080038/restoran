/**
 * OfflineManager - Client-side offline data management
 * 
 * Handles offline data storage using IndexedDB, sync queue management,
 * and conflict resolution for RESTAURANT_ERP
 * 
 * @package EBP\Frontend\Offline
 * @version 1.0.0
 */

class OfflineManager {
    constructor() {
        this.dbName = 'EBP_Restaurant_Offline';
        this.dbVersion = 1;
        this.db = null;
        this.isOnline = navigator.onLine;
        this.syncQueue = [];
        this.pendingChanges = new Map();
        
        this.init();
    }

    /**
     * Initialize IndexedDB database
     */
    async init() {
        try {
            this.db = await this.openDatabase();
            this.setupEventListeners();
            await this.loadSyncQueue();
            
            // Attempt sync if we're online
            if (this.isOnline) {
                await this.syncPendingChanges();
            }
        } catch (error) {
            console.error('Failed to initialize OfflineManager:', error);
        }
    }

    /**
     * Open IndexedDB database
     */
    openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Create object stores for offline data
                if (!db.objectStoreNames.contains('orders')) {
                    const orderStore = db.createObjectStore('orders', { keyPath: 'order_id' });
                    orderStore.createIndex('tenant_id', 'tenant_id', { unique: false });
                    orderStore.createIndex('branch_id', 'branch_id', { unique: false });
                    orderStore.createIndex('sync_status', 'sync_status', { unique: false });
                }

                if (!db.objectStoreNames.contains('inventory')) {
                    const inventoryStore = db.createObjectStore('inventory', { keyPath: 'inventory_id' });
                    inventoryStore.createIndex('tenant_id', 'tenant_id', { unique: false });
                    inventoryStore.createIndex('branch_id', 'branch_id', { unique: false });
                }

                if (!db.objectStoreNames.contains('menu')) {
                    const menuStore = db.createObjectStore('menu', { keyPath: 'product_id' });
                    menuStore.createIndex('tenant_id', 'tenant_id', { unique: false });
                }

                if (!db.objectStoreNames.contains('sync_queue')) {
                    const syncStore = db.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true });
                    syncStore.createIndex('status', 'status', { unique: false });
                    syncStore.createIndex('timestamp', 'timestamp', { unique: false });
                }

                if (!db.objectStoreNames.contains('conflicts')) {
                    const conflictStore = db.createObjectStore('conflicts', { keyPath: 'id', autoIncrement: true });
                    conflictStore.createIndex('resolved', 'resolved', { unique: false });
                }

                if (!db.objectStoreNames.contains('reports')) {
                    const reportStore = db.createObjectStore('reports', { keyPath: 'report_id', autoIncrement: true });
                    reportStore.createIndex('report_type', 'report_type', { unique: false });
                    reportStore.createIndex('created_at', 'created_at', { unique: false });
                }

                if (!db.objectStoreNames.contains('inventory_offline')) {
                    const invOfflineStore = db.createObjectStore('inventory_offline', { keyPath: 'inventory_id' });
                    invOfflineStore.createIndex('branch_id', 'branch_id', { unique: false });
                }

                if (!db.objectStoreNames.contains('schedules_offline')) {
                    const scheduleOfflineStore = db.createObjectStore('schedules_offline', { keyPath: 'shift_id', autoIncrement: true });
                    scheduleOfflineStore.createIndex('employee_id', 'employee_id', { unique: false });
                    scheduleOfflineStore.createIndex('shift_date', 'shift_date', { unique: false });
                }
            };
        });
    }

    /**
     * Setup event listeners for online/offline status
     */
    setupEventListeners() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.onOnline();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.onOffline();
        });
    }

    /**
     * Handle online event
     */
    async onOnline() {
        console.log('Connection restored. Syncing pending changes...');
        await this.syncPendingChanges();
        this.notifyStatusChange('online');
    }

    /**
     * Handle offline event
     */
    onOffline() {
        console.log('Connection lost. Entering offline mode...');
        this.notifyStatusChange('offline');
    }

    /**
     * Notify status change to application
     */
    notifyStatusChange(status) {
        const event = new CustomEvent('offlineStatusChange', { detail: { status } });
        window.dispatchEvent(event);
    }

    /**
     * Load sync queue from IndexedDB
     */
    async loadSyncQueue() {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['sync_queue'], 'readonly');
            const store = transaction.objectStore('sync_queue');
            const index = store.index('status');
            const request = index.getAll('pending');

            request.onsuccess = () => {
                this.syncQueue = request.result;
                resolve();
            };

            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Add item to sync queue
     */
    async addToSyncQueue(action, data) {
        const syncItem = {
            action,
            data,
            status: 'pending',
            timestamp: Date.now(),
            retry_count: 0
        };

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['sync_queue'], 'readwrite');
            const store = transaction.objectStore('sync_queue');
            const request = store.add(syncItem);

            request.onsuccess = () => {
                this.syncQueue.push(syncItem);
                resolve(request.result);
            };

            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Sync pending changes with server
     */
    async syncPendingChanges() {
        if (!this.isOnline || this.syncQueue.length === 0) {
            return;
        }

        const pendingItems = this.syncQueue.filter(item => item.status === 'pending');

        for (const item of pendingItems) {
            try {
                await this.syncItem(item);
            } catch (error) {
                console.error('Failed to sync item:', error);
                await this.handleSyncError(item, error);
            }
        }
    }

    /**
     * Sync individual item
     */
    async syncItem(item) {
        const { action, data } = item;
        let response;

        switch (action) {
            case 'create_order':
                response = await fetch('/api/v1/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                break;
            case 'update_order':
                response = await fetch(`/api/v1/orders/${data.order_id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                break;
            case 'update_inventory':
                response = await fetch(`/api/v1/inventory/${data.inventory_id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                break;
            default:
                throw new Error(`Unknown action: ${action}`);
        }

        if (!response.ok) {
            throw new Error(`Sync failed: ${response.statusText}`);
        }

        // Update sync queue item status
        await this.updateSyncQueueItem(item.id, 'synced');
    }

    /**
     * Handle sync error
     */
    async handleSyncError(item, error) {
        item.retry_count = (item.retry_count || 0) + 1;

        if (item.retry_count >= 3) {
            await this.updateSyncQueueItem(item.id, 'failed');
            await this.recordConflict(item, error);
        } else {
            await this.updateSyncQueueItem(item.id, 'pending');
        }
    }

    /**
     * Update sync queue item status
     */
    async updateSyncQueueItem(id, status) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['sync_queue'], 'readwrite');
            const store = transaction.objectStore('sync_queue');
            const request = store.get(id);

            request.onsuccess = () => {
                const item = request.result;
                item.status = status;
                item.last_attempt = Date.now();
                
                const updateRequest = store.put(item);
                updateRequest.onsuccess = () => resolve();
                updateRequest.onerror = () => reject(updateRequest.error);
            };

            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Record conflict
     */
    async recordConflict(item, error) {
        const conflict = {
            item,
            error: error.message,
            resolved: false,
            timestamp: Date.now()
        };

        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['conflicts'], 'readwrite');
            const store = transaction.objectStore('conflicts');
            const request = store.add(conflict);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Get unresolved conflicts
     */
    async getConflicts() {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['conflicts'], 'readonly');
            const store = transaction.objectStore('conflicts');
            const index = store.index('resolved');
            const request = index.getAll(false);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Resolve conflict
     */
    async resolveConflict(conflictId, resolution) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['conflicts'], 'readwrite');
            const store = transaction.objectStore('conflicts');
            const request = store.get(conflictId);

            request.onsuccess = () => {
                const conflict = request.result;
                conflict.resolved = true;
                conflict.resolution = resolution;
                conflict.resolved_at = Date.now();
                
                const updateRequest = store.put(conflict);
                updateRequest.onsuccess = () => resolve();
                updateRequest.onerror = () => reject(updateRequest.error);
            };

            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Store data offline
     */
    async storeData(storeName, data) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.put(data);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Get data from offline storage
     */
    async getData(storeName, key) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.get(key);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Get all data from offline storage
     */
    async getAllData(storeName) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Clear offline data
     */
    async clearData(storeName) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.clear();

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Check if online
     */
    isConnectionOnline() {
        return this.isOnline;
    }

    /**
     * Get sync queue status
     */
    getSyncQueueStatus() {
        const pending = this.syncQueue.filter(item => item.status === 'pending').length;
        const failed = this.syncQueue.filter(item => item.status === 'failed').length;
        const synced = this.syncQueue.filter(item => item.status === 'synced').length;
        return { pending, failed, synced, total: this.syncQueue.length };
    }

    /**
     * Generate offline report
     */
    async generateOfflineReport(reportType, filters = {}) {
        const reportId = Date.now();
        const report = {
            report_id: reportId,
            report_type: reportType,
            filters: filters,
            data: [],
            created_at: new Date().toISOString(),
            sync_status: 'pending'
        };

        switch (reportType) {
            case 'sales_summary':
                report.data = await this.generateSalesReport(filters);
                break;
            case 'inventory_summary':
                report.data = await this.generateInventoryReport(filters);
                break;
            case 'staff_summary':
                report.data = await this.generateStaffReport(filters);
                break;
            default:
                throw new Error('Unknown report type');
        }

        await this.storeData('reports', report);
        return report;
    }

    /**
     * Generate sales report from offline data
     */
    async generateSalesReport(filters) {
        const orders = await this.getAllData('orders');
        const { startDate, endDate, branchId } = filters;

        let filteredOrders = orders;
        if (startDate) {
            filteredOrders = filteredOrders.filter(o => new Date(o.order_date) >= new Date(startDate));
        }
        if (endDate) {
            filteredOrders = filteredOrders.filter(o => new Date(o.order_date) <= new Date(endDate));
        }
        if (branchId) {
            filteredOrders = filteredOrders.filter(o => o.branch_id === branchId);
        }

        const totalRevenue = filteredOrders.reduce((sum, o) => sum + (o.total_amount || 0), 0);
        const totalOrders = filteredOrders.length;
        const avgOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;

        return {
            total_revenue: totalRevenue,
            total_orders: totalOrders,
            average_order_value: avgOrderValue,
            orders: filteredOrders
        };
    }

    /**
     * Generate inventory report from offline data
     */
    async generateInventoryReport(filters) {
        const inventory = await this.getAllData('inventory_offline');
        const { branchId, category } = filters;

        let filteredInventory = inventory;
        if (branchId) {
            filteredInventory = filteredInventory.filter(i => i.branch_id === branchId);
        }
        if (category) {
            filteredInventory = filteredInventory.filter(i => i.category === category);
        }

        const totalItems = filteredInventory.length;
        const lowStockItems = filteredInventory.filter(i => i.quantity <= i.reorder_level);
        const totalValue = filteredInventory.reduce((sum, i) => sum + (i.quantity * i.unit_price || 0), 0);

        return {
            total_items: totalItems,
            low_stock_items: lowStockItems.length,
            total_value: totalValue,
            inventory: filteredInventory
        };
    }

    /**
     * Generate staff report from offline data
     */
    async generateStaffReport(filters) {
        const schedules = await this.getAllData('schedules_offline');
        const { startDate, endDate, employeeId } = filters;

        let filteredSchedules = schedules;
        if (startDate) {
            filteredSchedules = filteredSchedules.filter(s => new Date(s.shift_date) >= new Date(startDate));
        }
        if (endDate) {
            filteredSchedules = filteredSchedules.filter(s => new Date(s.shift_date) <= new Date(endDate));
        }
        if (employeeId) {
            filteredSchedules = filteredSchedules.filter(s => s.employee_id === employeeId);
        }

        const totalShifts = filteredSchedules.length;
        const totalHours = filteredSchedules.reduce((sum, s) => {
            const start = new Date(s.start_time);
            const end = new Date(s.end_time);
            return sum + ((end - start) / (1000 * 60 * 60));
        }, 0);

        return {
            total_shifts: totalShifts,
            total_hours: totalHours,
            schedules: filteredSchedules
        };
    }

    /**
     * Get offline reports
     */
    async getOfflineReports(reportType = null) {
        if (reportType) {
            const reports = await this.getAllData('reports');
            return reports.filter(r => r.report_type === reportType);
        }
        return await this.getAllData('reports');
    }

    /**
     * Store inventory data offline
     */
    async storeInventoryOffline(inventoryData) {
        if (Array.isArray(inventoryData)) {
            for (const item of inventoryData) {
                await this.storeData('inventory_offline', item);
            }
        } else {
            await this.storeData('inventory_offline', inventoryData);
        }
    }

    /**
     * Get offline inventory
     */
    async getOfflineInventory(branchId = null) {
        const inventory = await this.getAllData('inventory_offline');
        if (branchId) {
            return inventory.filter(i => i.branch_id === branchId);
        }
        return inventory;
    }

    /**
     * Store schedule data offline
     */
    async storeScheduleOffline(scheduleData) {
        if (Array.isArray(scheduleData)) {
            for (const item of scheduleData) {
                await this.storeData('schedules_offline', item);
            }
        } else {
            await this.storeData('schedules_offline', scheduleData);
        }
    }

    /**
     * Get offline schedules
     */
    async getOfflineSchedules(employeeId = null, date = null) {
        const schedules = await this.getAllData('schedules_offline');
        let filtered = schedules;
        
        if (employeeId) {
            filtered = filtered.filter(s => s.employee_id === employeeId);
        }
        if (date) {
            filtered = filtered.filter(s => s.shift_date === date);
        }
        
        return filtered;
    }
}

// Initialize offline manager
const offlineManager = new OfflineManager();
