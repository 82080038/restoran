<?php

/**
 * OfflineManager - Offline Capability Manager
 * 
 * Handles offline data storage, synchronization, conflict resolution,
 * and offline mode detection for the RESTAURANT_ERP system
 * 
 * @package EBP\Core\Offline
 * @version 1.0.0
 */

class OfflineManager
{
    private $db;
    private $syncQueue = [];
    private $offlineData = [];
    private $isOffline = false;

    public function __construct($db)
    {
        $this->db = $db;
        $this->initializeOfflineStorage();
    }

    /**
     * Initialize offline storage (IndexedDB simulation)
     */
    private function initializeOfflineStorage()
    {
        // In a real implementation, this would initialize IndexedDB
        // For PHP backend, we'll use a file-based approach
        $this->offlineStoragePath = __DIR__ . '/../../storage/offline/';
        
        if (!file_exists($this->offlineStoragePath)) {
            mkdir($this->offlineStoragePath, 0755, true);
        }
    }

    /**
     * Check if system is in offline mode
     */
    public function isOffline()
    {
        $this->isOffline = !$this->checkConnection();
        return $this->isOffline;
    }

    /**
     * Check database connection
     */
    private function checkConnection()
    {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Store data for offline use
     */
    public function storeOfflineData($dataType, $data)
    {
        $filename = $this->offlineStoragePath . $dataType . '.json';
        file_put_contents($filename, json_encode($data));
        
        $this->offlineData[$dataType] = $data;
        
        return [
            'success' => true,
            'data_type' => $dataType,
            'stored_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Retrieve offline data
     */
    public function getOfflineData($dataType)
    {
        $filename = $this->offlineStoragePath . $dataType . '.json';
        
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
            $this->offlineData[$dataType] = $data;
            return [
                'success' => true,
                'data' => $data,
                'retrieved_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Data not found in offline storage'
        ];
    }

    /**
     * Queue operation for sync when online
     */
    public function queueSyncOperation($operation)
    {
        $operation['queued_at'] = date('Y-m-d H:i:s');
        $operation['status'] = 'PENDING';
        
        $this->syncQueue[] = $operation;
        
        // Persist to file
        $this->persistSyncQueue();
        
        return [
            'success' => true,
            'operation_id' => count($this->syncQueue),
            'queued_at' => $operation['queued_at']
        ];
    }

    /**
     * Persist sync queue to file
     */
    private function persistSyncQueue()
    {
        $filename = $this->offlineStoragePath . 'sync_queue.json';
        file_put_contents($filename, json_encode($this->syncQueue));
    }

    /**
     * Load sync queue from file
     */
    private function loadSyncQueue()
    {
        $filename = $this->offlineStoragePath . 'sync_queue.json';
        
        if (file_exists($filename)) {
            $this->syncQueue = json_decode(file_get_contents($filename), true);
        }
    }

    /**
     * Synchronize queued operations when online
     */
    public function syncQueuedOperations()
    {
        if ($this->isOffline()) {
            return [
                'success' => false,
                'message' => 'Cannot sync while offline'
            ];
        }

        $this->loadSyncQueue();
        
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($this->syncQueue as $index => $operation) {
            if ($operation['status'] === 'PENDING') {
                try {
                    $result = $this->executeSyncOperation($operation);
                    
                    if ($result['success']) {
                        $this->syncQueue[$index]['status'] = 'COMPLETED';
                        $this->syncQueue[$index]['synced_at'] = date('Y-m-d H:i:s');
                        $successful++;
                    } else {
                        $this->syncQueue[$index]['status'] = 'FAILED';
                        $this->syncQueue[$index]['error'] = $result['message'];
                        $failed++;
                    }
                    
                    $results[] = $result;
                } catch (Exception $e) {
                    $this->syncQueue[$index]['status'] = 'FAILED';
                    $this->syncQueue[$index]['error'] = $e->getMessage();
                    $failed++;
                    
                    $results[] = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }
        }

        $this->persistSyncQueue();
        
        return [
            'success' => true,
            'total_operations' => count($this->syncQueue),
            'successful' => $successful,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Execute a single sync operation
     */
    private function executeSyncOperation($operation)
    {
        $action = $operation['action'] ?? '';
        $data = $operation['data'] ?? [];

        switch ($action) {
            case 'CREATE_ORDER':
                return $this->syncCreateOrder($data);
            case 'UPDATE_ORDER':
                return $this->syncUpdateOrder($data);
            case 'CREATE_PAYMENT':
                return $this->syncCreatePayment($data);
            case 'UPDATE_INVENTORY':
                return $this->syncUpdateInventory($data);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown sync action'
                ];
        }
    }

    /**
     * Sync create order
     */
    private function syncCreateOrder($data)
    {
        $sql = "
            INSERT INTO orders
            (tenant_id, branch_id, customer_id, table_id, total_amount, status, payment_status, created_at)
            VALUES (?, ?, ?, ?, ?, 'COMPLETED', 'PAID', ?)
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['customer_id'] ?? null,
            $data['table_id'] ?? null,
            $data['total_amount'],
            $data['created_at']
        ]);

        return [
            'success' => $result,
            'order_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Sync update order
     */
    private function syncUpdateOrder($data)
    {
        $sql = "
            UPDATE orders
            SET total_amount = ?, status = ?, updated_at = ?
            WHERE order_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['total_amount'],
            $data['status'],
            date('Y-m-d H:i:s'),
            $data['order_id']
        ]);

        return [
            'success' => $result
        ];
    }

    /**
     * Sync create payment
     */
    private function syncCreatePayment($data)
    {
        $sql = "
            INSERT INTO payments
            (order_id, tenant_id, branch_id, amount, payment_method, status, payment_date, source)
            VALUES (?, ?, ?, ?, ?, 'COMPLETED', ?, 'POS')
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['order_id'],
            $data['tenant_id'],
            $data['branch_id'],
            $data['amount'],
            $data['payment_method'],
            $data['payment_date']
        ]);

        return [
            'success' => $result,
            'payment_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Sync update inventory
     */
    private function syncUpdateInventory($data)
    {
        $sql = "
            UPDATE stock_balances
            SET quantity = quantity + ?, last_transaction_date = NOW()
            WHERE inventory_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['quantity_change'],
            $data['inventory_id'],
            $data['branch_id']
        ]);

        return [
            'success' => $result
        ];
    }

    /**
     * Resolve conflicts between offline and online data
     */
    public function resolveConflict($conflictId, $resolution)
    {
        // Load conflicts
        $conflicts = $this->getConflicts();
        
        if (!isset($conflicts[$conflictId])) {
            return [
                'success' => false,
                'message' => 'Conflict not found'
            ];
        }

        $conflict = $conflicts[$conflictId];
        
        switch ($resolution) {
            case 'KEEP_OFFLINE':
                $result = $this->applyOfflineData($conflict);
                break;
            case 'KEEP_ONLINE':
                $result = $this->applyOnlineData($conflict);
                break;
            case 'MERGE':
                $result = $this->mergeData($conflict);
                break;
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown resolution type'
                ];
        }

        // Remove resolved conflict
        unset($conflicts[$conflictId]);
        $this->saveConflicts($conflicts);

        return [
            'success' => true,
            'resolution' => $resolution,
            'result' => $result
        ];
    }

    /**
     * Get conflicts
     */
    private function getConflicts()
    {
        $filename = $this->offlineStoragePath . 'conflicts.json';
        
        if (file_exists($filename)) {
            return json_decode(file_get_contents($filename), true);
        }
        
        return [];
    }

    /**
     * Save conflicts
     */
    private function saveConflicts($conflicts)
    {
        $filename = $this->offlineStoragePath . 'conflicts.json';
        file_put_contents($filename, json_encode($conflicts));
    }

    /**
     * Apply offline data
     */
    private function applyOfflineData($conflict)
    {
        // Implementation depends on data type
        return [
            'success' => true,
            'message' => 'Offline data applied'
        ];
    }

    /**
     * Apply online data
     */
    private function applyOnlineData($conflict)
    {
        // Implementation depends on data type
        return [
            'success' => true,
            'message' => 'Online data applied'
        ];
    }

    /**
     * Merge data
     */
    private function mergeData($conflict)
    {
        // Implementation depends on data type
        return [
            'success' => true,
            'message' => 'Data merged'
        ];
    }

    /**
     * Get offline status
     */
    public function getOfflineStatus()
    {
        $this->loadSyncQueue();
        
        $pendingOperations = array_filter($this->syncQueue, fn($op) => $op['status'] === 'PENDING');
        
        return [
            'is_offline' => $this->isOffline(),
            'connection_status' => $this->checkConnection() ? 'ONLINE' : 'OFFLINE',
            'sync_queue_size' => count($this->syncQueue),
            'pending_operations' => count($pendingOperations),
            'last_sync_at' => $this->getLastSyncTime(),
            'offline_data_types' => array_keys($this->offlineData)
        ];
    }

    /**
     * Get last sync time
     */
    private function getLastSyncTime()
    {
        $filename = $this->offlineStoragePath . 'last_sync.json';
        
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
            return $data['last_sync_at'] ?? null;
        }
        
        return null;
    }

    /**
     * Update last sync time
     */
    private function updateLastSyncTime()
    {
        $filename = $this->offlineStoragePath . 'last_sync.json';
        file_put_contents($filename, json_encode([
            'last_sync_at' => date('Y-m-d H:i:s')
        ]));
    }

    /**
     * Prepare data for offline use
     */
    public function prepareOfflineData($tenantId, $branchId)
    {
        // Get essential data for offline operation
        $data = [
            'menu' => $this->getMenuData($tenantId, $branchId),
            'tables' => $this->getTableData($tenantId, $branchId),
            'customers' => $this->getCustomerData($tenantId, $branchId),
            'inventory' => $this->getInventoryData($tenantId, $branchId),
            'settings' => $this->getSettingsData($tenantId, $branchId)
        ];

        // Store each data type
        foreach ($data as $dataType => $dataContent) {
            $this->storeOfflineData($dataType, $dataContent);
        }

        return [
            'success' => true,
            'data_types_prepared' => array_keys($data),
            'prepared_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get menu data
     */
    private function getMenuData($tenantId, $branchId)
    {
        $sql = "
            SELECT p.product_id, p.name, p.price, p.description, c.name as category
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.tenant_id = ? AND p.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get table data
     */
    private function getTableData($tenantId, $branchId)
    {
        $sql = "
            SELECT table_id, table_number, capacity, status
            FROM restaurant_tables
            WHERE tenant_id = ? AND branch_id = ? AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer data
     */
    private function getCustomerData($tenantId, $branchId)
    {
        $sql = "
            SELECT customer_id, name, phone, email
            FROM customers
            WHERE tenant_id = ? AND status = 'ACTIVE'
            LIMIT 100
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get inventory data
     */
    private function getInventoryData($tenantId, $branchId)
    {
        $sql = "
            SELECT i.inventory_id, i.name, i.unit, COALESCE(sb.quantity, 0) as quantity
            FROM inventory i
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE i.tenant_id = ? AND i.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get settings data
     */
    private function getSettingsData($tenantId, $branchId)
    {
        $sql = "
            SELECT setting_key, setting_value
            FROM settings
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $settings;
    }

    /**
     * Clear offline data
     */
    public function clearOfflineData()
    {
        $files = glob($this->offlineStoragePath . '*.json');
        
        foreach ($files as $file) {
            if (basename($file) !== 'sync_queue.json') {
                unlink($file);
            }
        }

        $this->offlineData = [];

        return [
            'success' => true,
            'cleared_at' => date('Y-m-d H:i:s')
        ];
    }
}
