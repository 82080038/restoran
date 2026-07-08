<?php

namespace App\Modules\Offline\Services;

use App\Modules\Offline\Models\OfflineTransaction;
use App\Modules\Offline\Models\OfflineDataSnapshot;
use App\Modules\Offline\Models\OfflineConflict;
use App\Modules\Offline\Models\DeviceRegistration;
use App\Modules\Offline\Models\SyncQueue;
use App\Core\Database;

class OfflineService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Register device
     */
    public function registerDevice($restaurantId, $userId, $data)
    {
        $deviceModel = new DeviceRegistration();
        
        $deviceData = [
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'device_id' => $data->device_id,
            'device_name' => $data->device_name ?? null,
            'device_type' => $data->device_type,
            'device_os' => $data->device_os ?? null,
            'device_os_version' => $data->device_os_version ?? null,
            'app_version' => $data->app_version ?? null,
            'storage_capacity_mb' => $data->storage_capacity_mb ?? null,
            'available_storage_mb' => $data->available_storage_mb ?? null,
            'is_active' => true,
            'last_seen_at' => date('Y-m-d H:i:s')
        ];
        
        $deviceId = $deviceModel->create($deviceData);
        
        if (!$deviceId) {
            return ['success' => false, 'message' => 'Failed to register device'];
        }
        
        return ['success' => true, 'message' => 'Device registered successfully', 'device_id' => $deviceId];
    }

    /**
     * Get device info
     */
    public function getDeviceInfo($deviceId, $restaurantId)
    {
        $deviceModel = new DeviceRegistration();
        return $deviceModel->findById($deviceId, $restaurantId);
    }

    /**
     * Upload offline transaction
     */
    public function uploadTransaction($restaurantId, $userId, $data)
    {
        $transactionModel = new OfflineTransaction();
        
        $transactionData = [
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'device_id' => $data->device_id,
            'transaction_type' => $data->transaction_type,
            'transaction_data' => json_encode($data->transaction_data),
            'sync_status' => 'pending'
        ];
        
        $transactionId = $transactionModel->create($transactionData);
        
        if (!$transactionId) {
            return ['success' => false, 'message' => 'Failed to upload transaction'];
        }
        
        // Add to sync queue
        $this->addToSyncQueue($restaurantId, $data->device_id, 'upload', [
            'transaction_id' => $transactionId,
            'transaction_type' => $data->transaction_type
        ]);
        
        return ['success' => true, 'message' => 'Transaction uploaded successfully', 'transaction_id' => $transactionId];
    }

    /**
     * Get offline transactions
     */
    public function getTransactions($restaurantId, $deviceId, $syncStatus, $page, $limit)
    {
        $transactionModel = new OfflineTransaction();
        return $transactionModel->getPaginated($restaurantId, $deviceId, $syncStatus, $page, $limit);
    }

    /**
     * Sync offline transactions
     */
    public function syncTransactions($restaurantId, $userId, $deviceId)
    {
        $transactionModel = new OfflineTransaction();
        
        // Get pending transactions
        $pendingTransactions = $transactionModel->getPending($restaurantId, $deviceId);
        
        $totalProcessed = 0;
        $totalSynced = 0;
        $totalFailed = 0;
        $conflicts = [];
        
        foreach ($pendingTransactions as $transaction) {
            $result = $this->syncTransaction($transaction, $restaurantId, $userId);
            $totalProcessed++;
            
            if ($result['success']) {
                $totalSynced++;
            } elseif ($result['conflict']) {
                $conflicts[] = $result;
                $totalFailed++;
            } else {
                $totalFailed++;
            }
        }
        
        // Update device last seen
        if ($deviceId) {
            $deviceModel = new DeviceRegistration();
            $deviceModel->updateLastSeen($deviceId, $restaurantId);
        }
        
        return [
            'success' => true,
            'message' => 'Sync completed',
            'summary' => [
                'total_processed' => $totalProcessed,
                'total_synced' => $totalSynced,
                'total_failed' => $totalFailed,
                'conflicts' => count($conflicts)
            ],
            'conflicts' => $conflicts
        ];
    }

    /**
     * Sync individual transaction
     */
    private function syncTransaction($transaction, $restaurantId, $userId)
    {
        $transactionModel = new OfflineTransaction();
        
        // Update sync status to syncing
        $transactionModel->update($transaction['id'], [
            'sync_status' => 'syncing',
            'sync_attempts' => $transaction['sync_attempts'] + 1,
            'last_sync_attempt_at' => date('Y-m-d H:i:s')
        ]);
        
        // Process transaction based on type
        $result = $this->processTransaction($transaction, $restaurantId);
        
        if ($result['success']) {
            $transactionModel->update($transaction['id'], [
                'sync_status' => 'synced',
                'synced_at' => date('Y-m-d H:i:s')
            ]);
            
            return ['success' => true];
        } elseif ($result['conflict']) {
            // Create conflict record
            $conflictModel = new OfflineConflict();
            $conflictModel->create([
                'restaurant_id' => $restaurantId,
                'offline_transaction_id' => $transaction['id'],
                'conflict_type' => $result['conflict_type'],
                'conflict_description' => $result['conflict_description'],
                'local_data' => json_encode($transaction['transaction_data']),
                'remote_data' => json_encode($result['remote_data'])
            ]);
            
            $transactionModel->update($transaction['id'], [
                'sync_status' => 'conflict'
            ]);
            
            return ['success' => false, 'conflict' => true] + $result;
        } else {
            $transactionModel->update($transaction['id'], [
                'sync_status' => 'failed'
            ]);
            
            return ['success' => false, 'conflict' => false];
        }
    }

    /**
     * Process transaction
     */
    private function processTransaction($transaction, $restaurantId)
    {
        // In real implementation, this would process the transaction based on type
        // For now, simulate processing
        
        $transactionData = json_decode($transaction['transaction_data'], true);
        
        switch ($transaction['transaction_type']) {
            case 'order':
                // Process order
                return $this->processOrder($transactionData, $restaurantId);
            
            case 'payment':
                // Process payment
                return $this->processPayment($transactionData, $restaurantId);
            
            case 'inventory':
                // Process inventory
                return $this->processInventory($transactionData, $restaurantId);
            
            default:
                return ['success' => false, 'message' => 'Unknown transaction type'];
        }
    }

    /**
     * Process order
     */
    private function processOrder($orderData, $restaurantId)
    {
        // In real implementation, this would create/update the order
        // For now, simulate success
        return ['success' => true];
    }

    /**
     * Process payment
     */
    private function processPayment($paymentData, $restaurantId)
    {
        // In real implementation, this would create/update the payment
        // For now, simulate success
        return ['success' => true];
    }

    /**
     * Process inventory
     */
    private function processInventory($inventoryData, $restaurantId)
    {
        // In real implementation, this would update inventory
        // For now, simulate success
        return ['success' => true];
    }

    /**
     * Download data snapshot
     */
    public function downloadSnapshot($restaurantId, $userId, $deviceId, $dataType)
    {
        $snapshotModel = new OfflineDataSnapshot();
        
        // Get or create snapshot
        $snapshot = $snapshotModel->getLatest($restaurantId, $deviceId, $dataType);
        
        if (!$snapshot) {
            // Create new snapshot
            $snapshotData = $this->generateSnapshotData($restaurantId, $dataType);
            
            $snapshotId = $snapshotModel->create([
                'restaurant_id' => $restaurantId,
                'device_id' => $deviceId,
                'user_id' => $userId,
                'data_type' => $dataType,
                'snapshot_data' => json_encode($snapshotData),
                'snapshot_version' => 1,
                'last_synced_at' => date('Y-m-d H:i:s'),
                'sync_status' => 'synced'
            ]);
            
            if (!$snapshotId) {
                return ['success' => false, 'message' => 'Failed to create snapshot'];
            }
            
            $snapshot = $snapshotModel->findById($snapshotId);
        }
        
        return [
            'success' => true,
            'message' => 'Snapshot downloaded',
            'data' => json_decode($snapshot['snapshot_data'], true),
            'version' => $snapshot['snapshot_version'],
            'last_synced_at' => $snapshot['last_synced_at']
        ];
    }

    /**
     * Upload data snapshot
     */
    public function uploadSnapshot($restaurantId, $userId, $data)
    {
        $snapshotModel = new OfflineDataSnapshot();
        
        $snapshotData = [
            'restaurant_id' => $restaurantId,
            'device_id' => $data->device_id,
            'user_id' => $userId,
            'data_type' => $data->data_type,
            'snapshot_data' => json_encode($data->snapshot_data),
            'last_synced_at' => date('Y-m-d H:i:s'),
            'sync_status' => 'synced'
        ];
        
        $snapshotId = $snapshotModel->create($snapshotData);
        
        if (!$snapshotId) {
            return ['success' => false, 'message' => 'Failed to upload snapshot'];
        }
        
        return ['success' => true, 'message' => 'Snapshot uploaded successfully', 'snapshot_id' => $snapshotId];
    }

    /**
     * Generate snapshot data
     */
    private function generateSnapshotData($restaurantId, $dataType)
    {
        // In real implementation, this would fetch actual data based on type
        // For now, return empty array
        return [];
    }

    /**
     * Get conflicts
     */
    public function getConflicts($restaurantId, $isResolved, $page, $limit)
    {
        $conflictModel = new OfflineConflict();
        return $conflictModel->getPaginated($restaurantId, $isResolved, $page, $limit);
    }

    /**
     * Resolve conflict
     */
    public function resolveConflict($id, $restaurantId, $userId, $data)
    {
        $conflictModel = new OfflineConflict();
        $transactionModel = new OfflineTransaction();
        
        $conflict = $conflictModel->findById($id, $restaurantId);
        
        if (!$conflict) {
            return ['success' => false, 'message' => 'Conflict not found'];
        }
        
        // Update conflict
        $conflictModel->update($id, [
            'resolution_action' => $data->resolution_action,
            'resolved_data' => json_encode($data->resolved_data ?? []),
            'resolved_by' => $userId,
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolution_notes' => $data->notes ?? null,
            'is_resolved' => true
        ]);
        
        // Update transaction
        $transactionModel->update($conflict['offline_transaction_id'], [
            'conflict_resolved' => true,
            'resolved_by' => $userId,
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolution_notes' => $data->notes ?? null
        ]);
        
        // Re-sync transaction if needed
        if ($data->resolution_action === 'keep_local' || $data->resolution_action === 'merge') {
            $transaction = $transactionModel->findById($conflict['offline_transaction_id']);
            if ($transaction) {
                $this->syncTransaction($transaction, $restaurantId, $userId);
            }
        }
        
        return ['success' => true, 'message' => 'Conflict resolved successfully'];
    }

    /**
     * Get sync queue
     */
    public function getSyncQueue($restaurantId, $deviceId, $status, $page, $limit)
    {
        $queueModel = new SyncQueue();
        return $queueModel->getPaginated($restaurantId, $deviceId, $status, $page, $limit);
    }

    /**
     * Add to sync queue
     */
    private function addToSyncQueue($restaurantId, $deviceId, $queueType, $payload)
    {
        $queueModel = new SyncQueue();
        
        $queueModel->create([
            'restaurant_id' => $restaurantId,
            'device_id' => $deviceId,
            'queue_type' => $queueType,
            'priority' => 'normal',
            'payload' => json_encode($payload),
            'status' => 'pending'
        ]);
    }

    /**
     * Get settings
     */
    public function getSettings($restaurantId)
    {
        $sql = "SELECT * FROM offline_settings WHERE restaurant_id = ?";
        $settings = $this->db->query($sql, [$restaurantId])->fetchAll();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = json_decode($setting['setting_value'], true);
        }
        
        return $result;
    }

    /**
     * Update settings
     */
    public function updateSettings($restaurantId, $data)
    {
        foreach ($data as $key => $value) {
            $sql = "INSERT INTO offline_settings (restaurant_id, setting_key, setting_value) 
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
            
            $this->db->query($sql, [$restaurantId, $key, json_encode($value)]);
        }
        
        return ['success' => true, 'message' => 'Settings updated successfully'];
    }

    /**
     * Get status
     */
    public function getStatus($restaurantId, $deviceId)
    {
        $transactionModel = new OfflineTransaction();
        $deviceModel = new DeviceRegistration();
        
        $pendingCount = $transactionModel->countByStatus($restaurantId, $deviceId, 'pending');
        $conflictCount = $transactionModel->countByStatus($restaurantId, $deviceId, 'conflict');
        
        $device = $deviceId ? $deviceModel->findById($deviceId, $restaurantId) : null;
        
        return [
            'online' => true, // In real implementation, check actual connectivity
            'pending_transactions' => $pendingCount,
            'conflicts' => $conflictCount,
            'device' => $device,
            'last_sync_at' => $device ? $device['last_seen_at'] : null
        ];
    }

    /**
     * Get mobile-optimized data for offline use
     * Enhanced for mobile endpoints with lightweight data structures
     */
    public function getMobileData($restaurantId, $deviceId, $dataType, $lastSyncVersion = null)
    {
        $snapshotModel = new OfflineDataSnapshot();
        
        // Check if newer version exists
        $latestSnapshot = $snapshotModel->getLatest($restaurantId, $deviceId, $dataType);
        
        if (!$latestSnapshot) {
            return $this->downloadSnapshot($restaurantId, null, $deviceId, $dataType);
        }
        
        // If client has latest version, return empty response
        if ($lastSyncVersion && $latestSnapshot['snapshot_version'] <= $lastSyncVersion) {
            return [
                'success' => true,
                'message' => 'Data already up to date',
                'has_updates' => false,
                'current_version' => $latestSnapshot['snapshot_version']
            ];
        }
        
        // Return incremental updates if possible
        return [
            'success' => true,
            'message' => 'Mobile data retrieved',
            'has_updates' => true,
            'data' => json_decode($latestSnapshot['snapshot_data'], true),
            'version' => $latestSnapshot['snapshot_version'],
            'last_synced_at' => $latestSnapshot['last_synced_at'],
            'data_size_bytes' => strlen($latestSnapshot['snapshot_data'])
        ];
    }

    /**
     * Batch sync for mobile - process multiple transactions efficiently
     */
    public function batchSync($restaurantId, $userId, $deviceId, $transactions)
    {
        $results = [];
        $totalSuccess = 0;
        $totalFailed = 0;
        
        foreach ($transactions as $transaction) {
            $result = $this->uploadTransaction($restaurantId, $userId, (object)$transaction);
            $results[] = $result;
            
            if ($result['success']) {
                $totalSuccess++;
            } else {
                $totalFailed++;
            }
        }
        
        // Trigger immediate sync if online
        $syncResult = $this->syncTransactions($restaurantId, $userId, $deviceId);
        
        return [
            'success' => true,
            'message' => 'Batch sync completed',
            'summary' => [
                'total_uploaded' => count($transactions),
                'upload_success' => $totalSuccess,
                'upload_failed' => $totalFailed,
                'sync_summary' => $syncResult['summary'] ?? []
            ],
            'results' => $results
        ];
    }

    /**
     * Get delta updates - only changed data since last sync
     */
    public function getDeltaUpdates($restaurantId, $deviceId, $sinceTimestamp)
    {
        // Get data changes since timestamp
        $changes = $this->getDataChanges($restaurantId, $sinceTimestamp);
        
        return [
            'success' => true,
            'message' => 'Delta updates retrieved',
            'since' => $sinceTimestamp,
            'changes' => $changes,
            'has_changes' => !empty($changes)
        ];
    }

    /**
     * Get data changes since timestamp
     */
    private function getDataChanges($restaurantId, $sinceTimestamp)
    {
        $changes = [];
        
        // Check for menu changes
        $sql = "SELECT 'menu' as entity_type, menu_id as entity_id, updated_at 
                FROM menus 
                WHERE tenant_id = ? AND updated_at > ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$restaurantId, $sinceTimestamp]);
        $menuChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $changes = array_merge($changes, $menuChanges);
        
        // Check for inventory changes
        $sql = "SELECT 'inventory' as entity_type, inventory_id as entity_id, updated_at 
                FROM inventory 
                WHERE tenant_id = ? AND updated_at > ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$restaurantId, $sinceTimestamp]);
        $inventoryChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $changes = array_merge($changes, $inventoryChanges);
        
        // Check for price changes
        $sql = "SELECT 'price' as entity_type, price_id as entity_id, updated_at 
                FROM product_prices 
                WHERE tenant_id = ? AND updated_at > ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$restaurantId, $sinceTimestamp]);
        $priceChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $changes = array_merge($changes, $priceChanges);
        
        return $changes;
    }

    /**
     * Compress data for mobile transfer
     */
    public function getCompressedData($restaurantId, $deviceId, $dataType)
    {
        $result = $this->downloadSnapshot($restaurantId, null, $deviceId, $dataType);
        
        if (!$result['success']) {
            return $result;
        }
        
        // In real implementation, apply compression here
        // For now, just return the data with compression flag
        return [
            'success' => true,
            'message' => 'Compressed data retrieved',
            'data' => $result['data'],
            'compressed' => false, // Would be true with actual compression
            'original_size' => strlen(json_encode($result['data'])),
            'compressed_size' => strlen(json_encode($result['data'])) // Would be smaller with compression
        ];
    }

    /**
     * Health check for mobile sync
     */
    public function syncHealthCheck($restaurantId, $deviceId)
    {
        $transactionModel = new OfflineTransaction();
        $deviceModel = new DeviceRegistration();
        
        $pendingCount = $transactionModel->countByStatus($restaurantId, $deviceId, 'pending');
        $conflictCount = $transactionModel->countByStatus($restaurantId, $deviceId, 'conflict');
        $failedCount = $transactionModel->countByStatus($restaurantId, $deviceId, 'failed');
        
        $device = $deviceModel->findById($deviceId, $restaurantId);
        
        $healthStatus = 'healthy';
        if ($conflictCount > 5) {
            $healthStatus = 'warning';
        }
        if ($failedCount > 10 || $conflictCount > 10) {
            $healthStatus = 'critical';
        }
        
        return [
            'success' => true,
            'health_status' => $healthStatus,
            'metrics' => [
                'pending_transactions' => $pendingCount,
                'conflicts' => $conflictCount,
                'failed_transactions' => $failedCount,
                'last_sync' => $device ? $device['last_seen_at'] : null,
                'storage_available_mb' => $device ? $device['available_storage_mb'] : null
            ],
            'recommendations' => $this->getHealthRecommendations($healthStatus, $pendingCount, $conflictCount, $failedCount)
        ];
    }

    /**
     * Get health recommendations
     */
    private function getHealthRecommendations($status, $pending, $conflicts, $failed)
    {
        $recommendations = [];
        
        if ($pending > 20) {
            $recommendations[] = 'High number of pending transactions - consider syncing more frequently';
        }
        
        if ($conflicts > 0) {
            $recommendations[] = 'Resolve conflicts to ensure data consistency';
        }
        
        if ($failed > 5) {
            $recommendations[] = 'Review failed transactions and retry sync';
        }
        
        if ($status === 'critical') {
            $recommendations[] = 'Critical sync issues detected - immediate attention required';
        }
        
        return $recommendations;
    }
}
