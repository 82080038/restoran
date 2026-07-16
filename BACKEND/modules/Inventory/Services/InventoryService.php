<?php

if (!class_exists('InventoryRepository')) {
    require_once __DIR__ . '/../Repositories/InventoryRepository.php';
}



class InventoryService
{
    private $inventoryRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->inventoryRepository = new InventoryRepository();
        $this->transaction = new Transaction();
        // $this->audit = new Audit();
    }

    public function getAllInventory(int $tenantId, ?int $branchId = null): array
    {
        $inventory = $this->inventoryRepository->findAll($tenantId, $branchId);
        return array_map(function($i) { return $i->toArray(); }, $inventory);
    }

    public function getLowStock(int $tenantId, ?int $branchId = null): array
    {
        $inventory = $this->inventoryRepository->getLowStock($tenantId, $branchId);
        return array_map(function($i) { return $i->toArray(); }, $inventory);
    }

    public function getInventory(int $tenantId, int $inventoryId): ?array
    {
        $inventory = $this->inventoryRepository->findById($tenantId, $inventoryId);
        return $inventory ? $inventory->toArray() : null;
    }

    public function createInventory(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            $inventory = new \App\Modules\Inventory\Models\Inventory($data);
            
            // Check if inventory already exists for this product and branch
            $existing = $this->inventoryRepository->findByProduct(
                $tenantId,
                $inventory->branch_id,
                $inventory->product_id
            );
            
            if ($existing) {
                $this->transaction->rollback();
                return false;
            }
            
            $result = $this->inventoryRepository->create($inventory);
            
            if ($result) {
                // $this->audit->log();
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateInventory(int $tenantId, int $inventoryId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldInventory = $this->inventoryRepository->findById($tenantId, $inventoryId);
            
            $data['tenant_id'] = $tenantId;
            $data['inventory_id'] = $inventoryId;
            $inventory = new \App\Modules\Inventory\Models\Inventory($data);
            
            $result = $this->inventoryRepository->update($inventory);
            
            if ($result) {
                // $this->audit->log();
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function adjustStock(int $tenantId, int $branchId, int $productId, float $quantity, string $type, array $reference = []): bool
    {
        $this->transaction->begin();
        
        try {
            // Get current inventory
            $inventory = $this->inventoryRepository->findByProduct($tenantId, $branchId, $productId);
            
            if (!$inventory) {
                // Create inventory if it doesn't exist
                $inventory = new \App\Modules\Inventory\Models\Inventory([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'unit' => 'unit',
                    'status' => 'ACTIVE'
                ]);
                $this->inventoryRepository->create($inventory);
                $inventory = $this->inventoryRepository->findByProduct($tenantId, $branchId, $productId);
            }
            
            $oldQuantity = $inventory->quantity;
            
            // Calculate new quantity
            if ($type === 'IN') {
                $newQuantity = $oldQuantity + $quantity;
            } elseif ($type === 'OUT') {
                $newQuantity = $oldQuantity - $quantity;
                if ($newQuantity < 0) {
                    $this->transaction->rollback();
                    return false;
                }
            } else {
                $newQuantity = $quantity;
            }
            
            // Update inventory quantity
            $result = $this->inventoryRepository->updateQuantity($tenantId, $branchId, $productId, $newQuantity);
            
            if ($result) {
                // Record transaction
                $transaction = new \App\Modules\Inventory\Models\StockTransaction([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'transaction_type' => $type,
                    'quantity' => $quantity,
                    'unit' => $inventory->unit,
                    'reference_type' => $reference['type'] ?? null,
                    'reference_id' => $reference['id'] ?? null,
                    'notes' => $reference['notes'] ?? null
                ]);
                
                $this->inventoryRepository->recordTransaction($transaction);
                
                // $this->audit->log();
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function deleteInventory(int $tenantId, int $inventoryId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldInventory = $this->inventoryRepository->findById($tenantId, $inventoryId);
            
            $result = $this->inventoryRepository->delete($tenantId, $inventoryId);
            
            if ($result) {
                // $this->audit->log();
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function getTransactions(int $tenantId, ?int $branchId = null, ?int $productId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $transactions = $this->inventoryRepository->getTransactions($tenantId, $branchId, $productId, $dateFrom, $dateTo);
        return array_map(function($t) { return $t->toArray(); }, $transactions);
    }

    /**
     * Real-time stock update with notifications
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $productId Product ID
     * @param float $quantity Quantity change
     * @param string $type Transaction type (IN/OUT/ADJUST)
     * @param array $reference Reference information
     * @return array Update result with real-time status
     */
    public function updateStockRealTime(int $tenantId, int $branchId, int $productId, float $quantity, string $type, array $reference = []): array
    {
        $this->transaction->begin();
        
        try {
            // Get current inventory
            $inventory = $this->inventoryRepository->findByProduct($tenantId, $branchId, $productId);
            
            $oldQuantity = $inventory ? $inventory->quantity : 0;
            
            // Calculate new quantity
            if ($type === 'IN') {
                $newQuantity = $oldQuantity + $quantity;
            } elseif ($type === 'OUT') {
                $newQuantity = $oldQuantity - $quantity;
                if ($newQuantity < 0) {
                    $this->transaction->rollback();
                    return [
                        'success' => false,
                        'message' => 'Insufficient stock',
                        'current_quantity' => $oldQuantity,
                        'requested_quantity' => $quantity
                    ];
                }
            } else {
                $newQuantity = $quantity;
            }
            
            // Update or create inventory
            if (!$inventory) {
                $inventory = new \App\Modules\Inventory\Models\Inventory([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity' => $newQuantity,
                    'unit' => 'unit',
                    'status' => 'ACTIVE'
                ]);
                $this->inventoryRepository->create($inventory);
            } else {
                $this->inventoryRepository->updateQuantity($tenantId, $branchId, $productId, $newQuantity);
            }
            
            // Record transaction
            $transaction = new \App\Modules\Inventory\Models\StockTransaction([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'product_id' => $productId,
                'transaction_type' => $type,
                'quantity' => $quantity,
                'unit' => $inventory->unit ?? 'unit',
                'reference_type' => $reference['type'] ?? null,
                'reference_id' => $reference['id'] ?? null,
                'notes' => $reference['notes'] ?? null
            ]);
            
            $this->inventoryRepository->recordTransaction($transaction);
            
            // Check for low stock alert
            $lowStockAlert = $this->checkLowStockAlert($tenantId, $branchId, $productId, $newQuantity);
            
            // Trigger real-time notification
            $this->triggerInventoryNotification($tenantId, $branchId, $productId, $type, $oldQuantity, $newQuantity);
            
            // Update real-time cache
            $this->updateRealTimeCache($tenantId, $branchId, $productId, $newQuantity);
            
            $this->transaction->commit();
            
            return [
                'success' => true,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'change' => $quantity,
                'low_stock_alert' => $lowStockAlert,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    /**
     * Check for low stock alert
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $productId Product ID
     * @param float $currentQuantity Current stock quantity
     * @return array|null Alert data or null if no alert
     */
    private function checkLowStockAlert(int $tenantId, int $branchId, int $productId, float $currentQuantity): ?array
    {
        // Get product minimum stock level
        global $db;
        
        $sql = "
            SELECT minimum_stock, reorder_level, item_name
            FROM inventory_items
            WHERE tenant_id = ? 
              AND (branch_id = ? OR branch_id IS NULL)
              AND inventory_id = ?
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            return null;
        }
        
        $minimumStock = $product['minimum_stock'] ?? 10;
        $reorderLevel = $product['reorder_level'] ?? 5;
        
        // Check if stock is below minimum
        if ($currentQuantity <= $minimumStock) {
            $alert = [
                'product_id' => $productId,
                'product_name' => $product['item_name'],
                'current_quantity' => $currentQuantity,
                'minimum_stock' => $minimumStock,
                'reorder_level' => $reorderLevel,
                'alert_type' => $currentQuantity <= $reorderLevel ? 'critical' : 'warning',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Log alert
            $this->logLowStockAlert($tenantId, $branchId, $alert);
            
            return $alert;
        }
        
        return null;
    }

    /**
     * Log low stock alert
     */
    private function logLowStockAlert(int $tenantId, int $branchId, array $alert): void
    {
        global $db;
        
        $sql = "
            INSERT INTO inventory_alerts
            (tenant_id, branch_id, product_id, alert_type, current_quantity, threshold, message, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'ACTIVE')
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $alert['product_id'],
            $alert['alert_type'],
            $alert['current_quantity'],
            $alert['minimum_stock'],
            "Low stock alert: {$alert['product_name']} at {$alert['current_quantity']} units"
        ]);
    }

    /**
     * Trigger inventory notification
     */
    private function triggerInventoryNotification(int $tenantId, int $branchId, int $productId, string $type, float $oldQuantity, float $newQuantity): void
    {
        global $db;
        
        $messages = [
            'IN' => "Stock increased from {$oldQuantity} to {$newQuantity}",
            'OUT' => "Stock decreased from {$oldQuantity} to {$newQuantity}",
            'ADJUST' => "Stock adjusted from {$oldQuantity} to {$newQuantity}"
        ];
        
        $message = $messages[$type] ?? "Stock updated to {$newQuantity}";
        
        $sql = "
            INSERT INTO inventory_notifications
            (tenant_id, branch_id, product_id, notification_type, message, created_at, status)
            VALUES (?, ?, ?, 'STOCK_UPDATE', ?, NOW(), 'PENDING')
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $productId, $message]);
    }

    /**
     * Update real-time cache
     */
    private function updateRealTimeCache(int $tenantId, int $branchId, int $productId, float $newQuantity): void
    {
        // This would typically update Redis or similar cache
        // For now, we'll update a cache table
        global $db;
        
        $sql = "
            INSERT INTO inventory_cache
            (tenant_id, branch_id, product_id, quantity, updated_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                quantity = VALUES(quantity),
                updated_at = VALUES(updated_at)
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $productId, $newQuantity]);
    }

    /**
     * Get real-time stock levels
     * 
     * @param int $tenantId Tenant ID
     * @param int|null $branchId Branch ID filter
     * @param array $productIds Product IDs to query
     * @return array Real-time stock data
     */
    public function getRealTimeStockLevels(int $tenantId, ?int $branchId = null, array $productIds = []): array
    {
        global $db;
        
        $sql = "
            SELECT 
                ic.product_id,
                ic.quantity,
                ic.updated_at,
                ii.item_name,
                ii.unit,
                ii.minimum_stock,
                ii.reorder_level
            FROM inventory_cache ic
            LEFT JOIN inventory_items ii ON ic.product_id = ii.inventory_id
            WHERE ic.tenant_id = ?
        ";
        
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND ic.branch_id = ?";
            $params[] = $branchId;
        }
        
        if (!empty($productIds)) {
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            $sql .= " AND ic.product_id IN ({$placeholders})";
            $params = array_merge($params, $productIds);
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Batch real-time stock update
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param array $updates Array of updates [{product_id, quantity, type, reference}]
     * @return array Batch update results
     */
    public function batchUpdateStockRealTime(int $tenantId, int $branchId, array $updates): array
    {
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($updates as $update) {
            try {
                $result = $this->updateStockRealTime(
                    $tenantId,
                    $branchId,
                    $update['product_id'],
                    $update['quantity'],
                    $update['type'],
                    $update['reference'] ?? []
                );
                
                $results[$update['product_id']] = $result;
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (\Exception $e) {
                $results[$update['product_id']] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                $failureCount++;
            }
        }
        
        return [
            'total_updates' => count($updates),
            'successful' => $successCount,
            'failed' => $failureCount,
            'results' => $results,
            'processed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get inventory alerts
     * 
     * @param int $tenantId Tenant ID
     * @param int|null $branchId Branch ID filter
     * @param string $alertType Alert type filter
     * @return array Active alerts
     */
    public function getInventoryAlerts(int $tenantId, ?int $branchId = null, ?string $alertType = null): array
    {
        global $db;
        
        $sql = "
            SELECT 
                alert_id,
                tenant_id,
                branch_id,
                product_id,
                alert_type,
                current_quantity,
                threshold,
                message,
                created_at,
                status
            FROM inventory_alerts
            WHERE tenant_id = ? 
              AND status = 'ACTIVE'
        ";
        
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($alertType) {
            $sql .= " AND alert_type = ?";
            $params[] = $alertType;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Acknowledge inventory alert
     * 
     * @param int $tenantId Tenant ID
     * @param int $alertId Alert ID
     * @param string $action Action taken
     * @return bool Success status
     */
    public function acknowledgeAlert(int $tenantId, int $alertId, string $action): bool
    {
        global $db;
        
        $sql = "
            UPDATE inventory_alerts
            SET status = 'ACKNOWLEDGED',
                action_taken = ?,
                acknowledged_at = NOW()
            WHERE alert_id = ? AND tenant_id = ?
        ";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([$action, $alertId, $tenantId]);
    }

    /**
     * Sync inventory across branches
     * 
     * @param int $tenantId Tenant ID
     * @param int $sourceBranchId Source branch ID
     * @param int $targetBranchId Target branch ID
     * @param int $productId Product ID
     * @param float $quantity Quantity to transfer
     * @return array Sync result
     */
    public function syncInventoryAcrossBranches(int $tenantId, int $sourceBranchId, int $targetBranchId, int $productId, float $quantity): array
    {
        $this->transaction->begin();
        
        try {
            // Deduct from source branch
            $sourceResult = $this->updateStockRealTime(
                $tenantId,
                $sourceBranchId,
                $productId,
                $quantity,
                'OUT',
                ['type' => 'BRANCH_TRANSFER', 'id' => $targetBranchId, 'notes' => "Transfer to branch {$targetBranchId}"]
            );
            
            if (!$sourceResult['success']) {
                $this->transaction->rollback();
                return $sourceResult;
            }
            
            // Add to target branch
            $targetResult = $this->updateStockRealTime(
                $tenantId,
                $targetBranchId,
                $productId,
                $quantity,
                'IN',
                ['type' => 'BRANCH_TRANSFER', 'id' => $sourceBranchId, 'notes' => "Transfer from branch {$sourceBranchId}"]
            );
            
            if (!$targetResult['success']) {
                $this->transaction->rollback();
                return $targetResult;
            }
            
            // Log transfer
            $this->logBranchTransfer($tenantId, $sourceBranchId, $targetBranchId, $productId, $quantity);
            
            $this->transaction->commit();
            
            return [
                'success' => true,
                'source_branch' => $sourceBranchId,
                'target_branch' => $targetBranchId,
                'product_id' => $productId,
                'quantity_transferred' => $quantity,
                'transferred_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    /**
     * Log branch transfer
     */
    private function logBranchTransfer(int $tenantId, int $sourceBranchId, int $targetBranchId, int $productId, float $quantity): void
    {
        global $db;
        
        $sql = "
            INSERT INTO inventory_branch_transfers
            (tenant_id, source_branch_id, target_branch_id, product_id, quantity, transfer_date, status)
            VALUES (?, ?, ?, ?, ?, NOW(), 'COMPLETED')
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $sourceBranchId, $targetBranchId, $productId, $quantity]);
    }

    /**
     * Get inventory statistics in real-time
     * 
     * @param int $tenantId Tenant ID
     * @param int|null $branchId Branch ID filter
     * @return array Inventory statistics
     */
    public function getRealTimeStatistics(int $tenantId, ?int $branchId = null): array
    {
        global $db;
        
        $sql = "
            SELECT 
                COUNT(*) as total_items,
                SUM(CASE WHEN current_stock <= minimum_stock THEN 1 ELSE 0 END) as low_stock_items,
                SUM(CASE WHEN current_stock <= reorder_level THEN 1 ELSE 0 END) as critical_stock_items,
                SUM(current_stock) as total_stock,
                SUM(current_stock * unit_cost) as total_value
            FROM inventory_items
            WHERE tenant_id = ?
        ";
        
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get recent movements
        $movementSql = "
            SELECT 
                COUNT(*) as recent_movements,
                SUM(CASE WHEN transaction_type = 'IN' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN transaction_type = 'OUT' THEN quantity ELSE 0 END) as total_out
            FROM stock_transactions
            WHERE tenant_id = ? 
              AND transaction_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ";
        
        $movementParams = [$tenantId];
        
        if ($branchId) {
            $movementSql .= " AND branch_id = ?";
            $movementParams[] = $branchId;
        }
        
        $stmt = $db->prepare($movementSql);
        $stmt->execute($movementParams);
        $movements = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_items' => $stats['total_items'] ?? 0,
            'low_stock_items' => $stats['low_stock_items'] ?? 0,
            'critical_stock_items' => $stats['critical_stock_items'] ?? 0,
            'total_stock' => $stats['total_stock'] ?? 0,
            'total_value' => $stats['total_value'] ?? 0,
            'recent_movements' => $movements['recent_movements'] ?? 0,
            'total_in_24h' => $movements['total_in'] ?? 0,
            'total_out_24h' => $movements['total_out'] ?? 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
}
