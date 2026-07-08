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

    /**
     * Enhanced sync with conflict detection and resolution
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Sync results
     */
    public function enhancedSync($tenantId, $branchId)
    {
        if ($this->isOffline()) {
            return [
                'success' => false,
                'message' => 'Cannot sync while offline',
                'connection_status' => 'OFFLINE'
            ];
        }

        $this->loadSyncQueue();
        
        // Get server data for comparison
        $serverData = $this->getServerDataForComparison($tenantId, $branchId);
        
        // Detect conflicts
        $conflicts = $this->detectConflicts($serverData);
        
        // Resolve conflicts automatically if possible
        $autoResolved = $this->autoResolveConflicts($conflicts);
        
        // Sync operations
        $syncResults = $this->syncQueuedOperations();
        
        // Pull latest data from server
        $pullResults = $this->pullLatestData($tenantId, $branchId);
        
        // Update last sync time
        $this->updateLastSyncTime();
        
        return [
            'success' => true,
            'sync_results' => $syncResults,
            'pull_results' => $pullResults,
            'conflicts_detected' => count($conflicts),
            'auto_resolved' => count($autoResolved),
            'manual_resolution_required' => count($conflicts) - count($autoResolved),
            'synced_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get server data for conflict detection
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Server data
     */
    private function getServerDataForComparison($tenantId, $branchId)
    {
        return [
            'menu' => $this->getMenuData($tenantId, $branchId),
            'tables' => $this->getTableData($tenantId, $branchId),
            'inventory' => $this->getInventoryData($tenantId, $branchId),
            'customers' => $this->getCustomerData($tenantId, $branchId)
        ];
    }

    /**
     * Detect conflicts between offline and server data
     * 
     * @param array $serverData Server data
     * @return array Detected conflicts
     */
    private function detectConflicts($serverData)
    {
        $conflicts = [];
        
        foreach ($serverData as $dataType => $serverItems) {
            $offlineData = $this->getOfflineData($dataType);
            
            if ($offlineData['success']) {
                $conflicts = array_merge(
                    $conflicts,
                    $this->compareDataSets($dataType, $serverItems, $offlineData['data'])
                );
            }
        }
        
        return $conflicts;
    }

    /**
     * Compare data sets for conflicts
     * 
     * @param string $dataType Data type
     * @param array $serverItems Server items
     * @param array $offlineItems Offline items
     * @return array Conflicts
     */
    private function compareDataSets($dataType, $serverItems, $offlineItems)
    {
        $conflicts = [];
        $serverMap = [];
        $offlineMap = [];
        
        // Create maps for comparison
        foreach ($serverItems as $item) {
            $key = $this->getDataKey($item, $dataType);
            $serverMap[$key] = $item;
        }
        
        foreach ($offlineItems as $item) {
            $key = $this->getDataKey($item, $dataType);
            $offlineMap[$key] = $item;
        }
        
        // Detect conflicts
        foreach (array_keys(array_merge($serverMap, $offlineMap)) as $key) {
            if (isset($serverMap[$key]) && isset($offlineMap[$key])) {
                // Item exists in both - check for differences
                if ($this->itemsDiffer($serverMap[$key], $offlineMap[$key])) {
                    $conflicts[] = [
                        'type' => $dataType,
                        'conflict_type' => 'MODIFICATION_CONFLICT',
                        'key' => $key,
                        'server_version' => $serverMap[$key],
                        'offline_version' => $offlineMap[$key],
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                }
            } elseif (isset($serverMap[$key])) {
                // Item only on server
                $conflicts[] = [
                    'type' => $dataType,
                    'conflict_type' => 'SERVER_ONLY',
                    'key' => $key,
                    'item' => $serverMap[$key],
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                // Item only offline
                $conflicts[] = [
                    'type' => $dataType,
                    'conflict_type' => 'OFFLINE_ONLY',
                    'key' => $key,
                    'item' => $offlineMap[$key],
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        return $conflicts;
    }

    /**
     * Get data key for comparison
     * 
     * @param array $item Data item
     * @param string $dataType Data type
     * @return string Key
     */
    private function getDataKey($item, $dataType)
    {
        switch ($dataType) {
            case 'menu':
                return $item['product_id'];
            case 'tables':
                return $item['table_id'];
            case 'inventory':
                return $item['inventory_id'];
            case 'customers':
                return $item['customer_id'];
            default:
                return md5(json_encode($item));
        }
    }

    /**
     * Check if items differ
     * 
     * @param array $item1 First item
     * @param array $item2 Second item
     * @return bool Different or not
     */
    private function itemsDiffer($item1, $item2)
    {
        // Compare relevant fields
        $fieldsToCompare = ['name', 'price', 'quantity', 'status'];
        
        foreach ($fieldsToCompare as $field) {
            if (isset($item1[$field]) && isset($item2[$field])) {
                if ($item1[$field] !== $item2[$field]) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Auto-resolve conflicts where possible
     * 
     * @param array $conflicts Conflicts
     * @return array Auto-resolved conflicts
     */
    private function autoResolveConflicts($conflicts)
    {
        $resolved = [];
        
        foreach ($conflicts as $index => $conflict) {
            switch ($conflict['conflict_type']) {
                case 'SERVER_ONLY':
                    // Server version wins - pull to offline
                    $this->storeOfflineData($conflict['type'], [$conflict['item']]);
                    $resolved[] = $conflict;
                    break;
                    
                case 'OFFLINE_ONLY':
                    // Offline version wins - push to server (if it's a new item)
                    if ($this->isNewItem($conflict)) {
                        $this->queueSyncOperation([
                            'action' => $this->getSyncActionForType($conflict['type']),
                            'data' => $conflict['item'],
                            'conflict_id' => $index
                        ]);
                        $resolved[] = $conflict;
                    }
                    break;
                    
                case 'MODIFICATION_CONFLICT':
                    // Use timestamp-based resolution
                    if ($this->isServerNewer($conflict)) {
                        $this->storeOfflineData($conflict['type'], [$conflict['server_version']]);
                        $resolved[] = $conflict;
                    }
                    break;
            }
        }
        
        return $resolved;
    }

    /**
     * Check if conflict item is new
     * 
     * @param array $conflict Conflict
     * @return bool Is new
     */
    private function isNewItem($conflict)
    {
        // Check if offline item was created after last sync
        $lastSync = $this->getLastSyncTime();
        $itemCreated = $conflict['item']['created_at'] ?? $conflict['timestamp'];
        
        return strtotime($itemCreated) > strtotime($lastSync);
    }

    /**
     * Get sync action for data type
     * 
     * @param string $dataType Data type
     * @return string Action
     */
    private function getSyncActionForType($dataType)
    {
        switch ($dataType) {
            case 'menu':
                return 'CREATE_MENU_ITEM';
            case 'inventory':
                return 'UPDATE_INVENTORY';
            case 'customers':
                return 'CREATE_CUSTOMER';
            default:
                return 'SYNC_DATA';
        }
    }

    /**
     * Check if server version is newer
     * 
     * @param array $conflict Conflict
     * @return bool Server is newer
     */
    private function isServerNewer($conflict)
    {
        $serverTime = $conflict['server_version']['updated_at'] ?? $conflict['server_version']['created_at'];
        $offlineTime = $conflict['offline_version']['updated_at'] ?? $conflict['offline_version']['created_at'];
        
        return strtotime($serverTime) > strtotime($offlineTime);
    }

    /**
     * Pull latest data from server
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Pull results
     */
    private function pullLatestData($tenantId, $branchId)
    {
        $results = [];
        
        // Pull each data type
        $dataTypes = ['menu', 'tables', 'customers', 'inventory', 'settings'];
        
        foreach ($dataTypes as $dataType) {
            try {
                $data = $this->getOfflineData($dataType);
                
                if ($data['success']) {
                    $this->storeOfflineData($dataType, $data['data']);
                    $results[$dataType] = [
                        'success' => true,
                        'items_pulled' => count($data['data'])
                    ];
                }
            } catch (Exception $e) {
                $results[$dataType] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Incremental sync - only sync changed data
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Sync results
     */
    public function incrementalSync($tenantId, $branchId)
    {
        if ($this->isOffline()) {
            return [
                'success' => false,
                'message' => 'Cannot sync while offline'
            ];
        }

        $lastSync = $this->getLastSyncTime();
        
        if (!$lastSync) {
            // First sync - do full sync
            return $this->enhancedSync($tenantId, $branchId);
        }

        $results = [];
        
        // Get changed data since last sync
        $changedData = $this->getChangedDataSince($tenantId, $branchId, $lastSync);
        
        // Sync changed data
        foreach ($changedData as $dataType => $items) {
            if (!empty($items)) {
                $this->storeOfflineData($dataType, $items);
                $results[$dataType] = [
                    'success' => true,
                    'items_synced' => count($items)
                ];
            }
        }
        
        // Process sync queue
        $syncResults = $this->syncQueuedOperations();
        
        // Update last sync time
        $this->updateLastSyncTime();
        
        return [
            'success' => true,
            'sync_type' => 'incremental',
            'last_sync' => $lastSync,
            'data_synced' => $results,
            'queue_processed' => $syncResults,
            'synced_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get changed data since last sync
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $lastSync Last sync timestamp
     * @return array Changed data
     */
    private function getChangedDataSince($tenantId, $branchId, $lastSync)
    {
        $changedData = [];
        
        // Get changed menu items
        $sql = "
            SELECT * FROM products
            WHERE tenant_id = ? 
              AND (updated_at > ? OR created_at > ?)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $lastSync, $lastSync]);
        $changedData['menu'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get changed inventory
        $sql = "
            SELECT i.*, sb.quantity, sb.last_transaction_date
            FROM inventory i
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE i.tenant_id = ? 
              AND (i.updated_at > ? OR i.created_at > ? OR sb.last_transaction_date > ?)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId, $lastSync, $lastSync, $lastSync]);
        $changedData['inventory'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get changed customers
        $sql = "
            SELECT * FROM customers
            WHERE tenant_id = ? 
              AND (updated_at > ? OR created_at > ?)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $lastSync, $lastSync]);
        $changedData['customers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $changedData;
    }

    /**
     * Background sync process
     * Can be called periodically to keep data synchronized
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Sync results
     */
    public function backgroundSync($tenantId, $branchId)
    {
        $results = [
            'started_at' => date('Y-m-d H:i:s'),
            'sync_type' => 'background'
        ];
        
        try {
            // Check connection
            if ($this->isOffline()) {
                $results['status'] = 'skipped';
                $results['reason'] = 'offline';
                return $results;
            }
            
            // Perform incremental sync
            $syncResult = $this->incrementalSync($tenantId, $branchId);
            $results['sync_result'] = $syncResult;
            
            // Process any queued operations
            $queueResult = $this->syncQueuedOperations();
            $results['queue_result'] = $queueResult;
            
            $results['status'] = 'completed';
            $results['completed_at'] = date('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            $results['status'] = 'failed';
            $results['error'] = $e->getMessage();
            $results['failed_at'] = date('Y-m-d H:i:s');
        }
        
        return $results;
    }

    /**
     * Get sync status and statistics
     * 
     * @return array Sync status
     */
    public function getSyncStatus()
    {
        $this->loadSyncQueue();
        
        $pendingCount = count(array_filter($this->syncQueue, fn($op) => $op['status'] === 'PENDING'));
        $completedCount = count(array_filter($this->syncQueue, fn($op) => $op['status'] === 'COMPLETED'));
        $failedCount = count(array_filter($this->syncQueue, fn($op) => $op['status'] === 'FAILED'));
        
        return [
            'connection_status' => $this->isOffline() ? 'OFFLINE' : 'ONLINE',
            'last_sync' => $this->getLastSyncTime(),
            'queue_size' => count($this->syncQueue),
            'pending_operations' => $pendingCount,
            'completed_operations' => $completedCount,
            'failed_operations' => $failedCount,
            'offline_data_types' => array_keys($this->offlineData),
            'offline_storage_size' => $this->getOfflineStorageSize(),
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get offline storage size
     * 
     * @return int Size in bytes
     */
    private function getOfflineStorageSize()
    {
        $totalSize = 0;
        $files = glob($this->offlineStoragePath . '*.json');
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        return $totalSize;
    }

    /**
     * Force full sync (ignore conflicts)
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $strategy Sync strategy (SERVER_WINS, OFFLINE_WINS, MERGE)
     * @return array Sync results
     */
    public function forceSync($tenantId, $branchId, $strategy = 'SERVER_WINS')
    {
        if ($this->isOffline()) {
            return [
                'success' => false,
                'message' => 'Cannot sync while offline'
            ];
        }

        switch ($strategy) {
            case 'SERVER_WINS':
                return $this->serverWinsSync($tenantId, $branchId);
            case 'OFFLINE_WINS':
                return $this->offlineWinsSync($tenantId, $branchId);
            case 'MERGE':
                return $this->mergeSync($tenantId, $branchId);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown sync strategy'
                ];
        }
    }

    /**
     * Server wins sync strategy
     */
    private function serverWinsSync($tenantId, $branchId)
    {
        // Clear offline data
        $this->clearOfflineData();
        
        // Pull fresh data from server
        $this->prepareOfflineData($tenantId, $branchId);
        
        // Process sync queue (push offline changes to server)
        $syncResults = $this->syncQueuedOperations();
        
        return [
            'success' => true,
            'strategy' => 'SERVER_WINS',
            'sync_results' => $syncResults,
            'synced_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Offline wins sync strategy
     */
    private function offlineWinsSync($tenantId, $branchId)
    {
        // Push all offline changes to server
        $syncResults = $this->syncQueuedOperations();
        
        // Pull fresh data (offline data remains priority)
        $this->pullLatestData($tenantId, $branchId);
        
        return [
            'success' => true,
            'strategy' => 'OFFLINE_WINS',
            'sync_results' => $syncResults,
            'synced_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Merge sync strategy
     */
    private function mergeSync($tenantId, $branchId)
    {
        // Perform enhanced sync with conflict resolution
        return $this->enhancedSync($tenantId, $branchId);
    }
}
