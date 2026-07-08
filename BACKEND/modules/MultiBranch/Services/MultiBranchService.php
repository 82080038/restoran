<?php

namespace App\Modules\MultiBranch\Services;

use App\Core\Database;
use App\Core\Audit;

class MultiBranchService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = new Audit();
    }

    /**
     * Create inter-branch stock transfer
     */
    public function createStockTransfer($tenantId, $fromBranchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $transferData = [
                'tenant_id' => $tenantId,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $data->to_branch_id,
                'transfer_date' => $data->transfer_date ?? date('Y-m-d'),
                'status' => 'PENDING',
                'transfer_reason' => $data->transfer_reason ?? null,
                'requested_by' => $userId
            ];

            $sql = "INSERT INTO stock_transfers (tenant_id, from_branch_id, to_branch_id, transfer_date, status, transfer_reason, requested_by, requested_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $transferData['tenant_id'],
                $transferData['from_branch_id'],
                $transferData['to_branch_id'],
                $transferData['transfer_date'],
                $transferData['status'],
                $transferData['transfer_reason'],
                $transferData['requested_by']
            ]);

            $transferId = $this->db->lastInsertId();

            // Add transfer items
            if (isset($data->items) && is_array($data->items)) {
                foreach ($data->items as $item) {
                    $this->addTransferItem($transferId, $item, $userId);
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $fromBranchId, $userId, 'stock_transfer', $transferId, 'CREATE', json_encode($transferData));

            return [
                'success' => true,
                'message' => 'Stock transfer created',
                'transfer_id' => $transferId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create stock transfer: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add transfer item
     */
    private function addTransferItem($transferId, $item, $userId)
    {
        // Get current stock and cost
        $stockSql = "SELECT current_stock, unit_cost FROM inventory_items WHERE id = ?";
        $stockInfo = $this->db->query($stockSql, [$item->item_id])->fetch();

        $sql = "INSERT INTO stock_transfer_details (transfer_id, item_id, quantity, unit, unit_cost, total_cost, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $transferId,
            $item->item_id,
            $item->quantity,
            $item->unit,
            $stockInfo['unit_cost'] ?? 0,
            ($stockInfo['unit_cost'] ?? 0) * $item->quantity,
            $item->notes ?? null
        ]);
    }

    /**
     * Get stock transfers
     */
    public function getStockTransfers($tenantId, $branchId, $status, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE st.tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND (st.from_branch_id = ? OR st.to_branch_id = ?)";
            $params[] = $branchId;
            $params[] = $branchId;
        }
        
        if ($status) {
            $where .= " AND st.status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND st.transfer_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND st.transfer_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT st.*, 
                    b1.branch_name as from_branch_name,
                    b2.branch_name as to_branch_name,
                    u.username as requested_by_name
                FROM stock_transfers st
                LEFT JOIN branches b1 ON st.from_branch_id = b1.id
                LEFT JOIN branches b2 ON st.to_branch_id = b2.id
                LEFT JOIN users u ON st.requested_by = u.id
                {$where}
                ORDER BY st.transfer_date DESC, st.requested_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Update transfer status
     */
    public function updateTransferStatus($transferId, $status, $userId, $tenantId)
    {
        try {
            $this->db->beginTransaction();

            $updateData = ['status' => $status];
            
            if ($status === 'APPROVED') {
                $updateData['approved_by'] = $userId;
                $updateData['approved_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'IN_TRANSIT') {
                $updateData['shipped_by'] = $userId;
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'COMPLETED') {
                $updateData['received_by'] = $userId;
                $updateData['received_at'] = date('Y-m-d H:i:s');
                
                // Update inventory stocks
                $this->processTransferStockUpdate($transferId, $tenantId);
            }

            $sql = "UPDATE stock_transfers SET status = ?, updated_at = NOW()";
            $params = [$status];

            if (isset($updateData['approved_by'])) {
                $sql .= ", approved_by = ?, approved_at = ?";
                $params[] = $updateData['approved_by'];
                $params[] = $updateData['approved_at'];
            }

            if (isset($updateData['shipped_by'])) {
                $sql .= ", shipped_by = ?, shipped_at = ?";
                $params[] = $updateData['shipped_by'];
                $params[] = $updateData['shipped_at'];
            }

            if (isset($updateData['received_by'])) {
                $sql .= ", received_by = ?, received_at = ?";
                $params[] = $updateData['received_by'];
                $params[] = $updateData['received_at'];
            }

            $sql .= " WHERE transfer_id = ? AND tenant_id = ?";
            $params[] = $transferId;
            $params[] = $tenantId;

            $this->db->prepare($sql)->execute($params);

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'stock_transfer', $transferId, 'UPDATE_STATUS', json_encode(['status' => $status]));

            return [
                'success' => true,
                'message' => 'Transfer status updated'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process stock update for completed transfer
     */
    private function processTransferStockUpdate($transferId, $tenantId)
    {
        // Get transfer details
        $transferSql = "SELECT from_branch_id, to_branch_id FROM stock_transfers WHERE transfer_id = ? AND tenant_id = ?";
        $transfer = $this->db->query($transferSql, [$transferId, $tenantId])->fetch();

        if (!$transfer) {
            throw new Exception('Transfer not found');
        }

        // Get transfer items
        $itemsSql = "SELECT item_id, quantity FROM stock_transfer_details WHERE transfer_id = ?";
        $items = $this->db->query($itemsSql, [$transferId])->fetchAll();

        foreach ($items as $item) {
            // Decrease stock from source branch
            $decreaseSql = "UPDATE inventory_items SET current_stock = current_stock - ? WHERE id = ? AND branch_id = ?";
            $this->db->prepare($decreaseSql)->execute([$item['quantity'], $item['item_id'], $transfer['from_branch_id']]);

            // Increase stock to destination branch
            $increaseSql = "UPDATE inventory_items SET current_stock = current_stock + ? WHERE id = ? AND branch_id = ?";
            $this->db->prepare($increaseSql)->execute([$item['quantity'], $item['item_id'], $transfer['to_branch_id']]);

            // Record stock movements
            $movementSql = "INSERT INTO stock_movements (tenant_id, branch_id, inventory_item_id, movement_type, quantity, reference_type, reference_id, transaction_date, created_at)
                            VALUES (?, ?, ?, 'OUT', ?, 'STOCK_TRANSFER', ?, CURDATE(), NOW())";
            $this->db->prepare($movementSql)->execute([$tenantId, $transfer['from_branch_id'], $item['item_id'], $item['quantity'], $transferId]);

            $movementSql = "INSERT INTO stock_movements (tenant_id, branch_id, inventory_item_id, movement_type, quantity, reference_type, reference_id, transaction_date, created_at)
                            VALUES (?, ?, ?, 'IN', ?, 'STOCK_TRANSFER', ?, CURDATE(), NOW())";
            $this->db->prepare($movementSql)->execute([$tenantId, $transfer['to_branch_id'], $item['item_id'], $item['quantity'], $transferId]);
        }
    }

    /**
     * Create centralized purchase order
     */
    public function createCentralizedPurchase($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $purchaseData = [
                'tenant_id' => $tenantId,
                'po_number' => $this->generatePONumber($tenantId),
                'supplier_id' => $data->supplier_id,
                'order_date' => $data->order_date ?? date('Y-m-d'),
                'expected_delivery_date' => $data->expected_delivery_date,
                'status' => 'DRAFT',
                'is_centralized' => 1,
                'notes' => $data->notes ?? null,
                'created_by' => $userId
            ];

            $sql = "INSERT INTO purchase_orders (tenant_id, po_number, supplier_id, order_date, expected_delivery_date, status, is_centralized, notes, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $purchaseData['tenant_id'],
                $purchaseData['po_number'],
                $purchaseData['supplier_id'],
                $purchaseData['order_date'],
                $purchaseData['expected_delivery_date'],
                $purchaseData['status'],
                $purchaseData['notes'],
                $purchaseData['created_by']
            ]);

            $purchaseOrderId = $this->db->lastInsertId();

            // Add purchase items and allocate to branches
            if (isset($data->items) && is_array($data->items)) {
                foreach ($data->items as $item) {
                    $this->addPurchaseItem($purchaseOrderId, $item, $userId);
                    
                    // Allocate to branches if specified
                    if (isset($item->branch_allocations) && is_array($item->branch_allocations)) {
                        foreach ($item->branch_allocations as $allocation) {
                            $this->addBranchAllocation($purchaseOrderId, $item->item_id, $allocation, $userId);
                        }
                    }
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'centralized_purchase', $purchaseOrderId, 'CREATE', json_encode($purchaseData));

            return [
                'success' => true,
                'message' => 'Centralized purchase created',
                'purchase_order_id' => $purchaseOrderId,
                'po_number' => $purchaseData['po_number']
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create centralized purchase: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate PO number
     */
    private function generatePONumber($tenantId)
    {
        $prefix = 'PO-' . date('Ym');
        $sql = "SELECT COUNT(*) as count FROM purchase_orders WHERE tenant_id = ? AND po_number LIKE ?";
        $result = $this->db->query($sql, [$tenantId, $prefix . '%'])->fetch();
        
        $sequence = str_pad(($result['count'] + 1), 4, '0', STR_PAD_LEFT);
        return $prefix . '-' . $sequence;
    }

    /**
     * Add purchase item
     */
    private function addPurchaseItem($purchaseOrderId, $item, $userId)
    {
        $sql = "INSERT INTO purchase_order_items (purchase_order_id, item_id, quantity, unit, unit_price, total_price, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $purchaseOrderId,
            $item->item_id,
            $item->quantity,
            $item->unit,
            $item->unit_price,
            $item->quantity * $item->unit_price,
            $item->notes ?? null
        ]);
    }

    /**
     * Add branch allocation
     */
    private function addBranchAllocation($purchaseOrderId, $itemId, $allocation, $userId)
    {
        $sql = "INSERT INTO purchase_branch_allocations (purchase_order_id, item_id, branch_id, allocated_quantity, created_at)
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $purchaseOrderId,
            $itemId,
            $allocation->branch_id,
            $allocation->allocated_quantity
        ]);
    }

    /**
     * Get branch performance comparison
     */
    public function getBranchPerformance($tenantId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE o.tenant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND o.order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND o.order_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT b.id as branch_id, b.branch_name, b.branch_location,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as average_order_value,
                    COUNT(DISTINCT o.customer_id) as unique_customers,
                    (SELECT COUNT(*) FROM orders o2 WHERE o2.branch_id = b.id AND o2.order_date >= ? AND o2.order_date <= ?) as order_count
                FROM branches b
                LEFT JOIN orders o ON b.id = o.branch_id
                {$where}
                GROUP BY b.id, b.branch_name, b.branch_location
                ORDER BY total_revenue DESC";

        return $this->db->query($sql, array_merge([$dateFrom ?? date('Y-m-d', strtotime('-30 days')), $dateTo ?? date('Y-m-d')], $params))->fetchAll();
    }

    /**
     * Standardize pricing across branches
     */
    public function standardizePricing($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $productId = $data->product_id;
            $standardPrice = $data->standard_price;
            $effectiveDate = $data->effective_date ?? date('Y-m-d');
            $branchIds = $data->branch_ids ?? [];

            foreach ($branchIds as $branchId) {
                // Get current price
                $currentPriceSql = "SELECT price FROM products WHERE id = ? AND branch_id = ? AND tenant_id = ?";
                $currentPrice = $this->db->query($currentPriceSql, [$productId, $branchId, $tenantId])->fetch();

                if ($currentPrice) {
                    // Archive old price
                    $archiveSql = "INSERT INTO price_history (tenant_id, branch_id, product_id, old_price, new_price, effective_date, changed_by, changed_at)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                    $this->db->prepare($archiveSql)->execute([
                        $tenantId,
                        $branchId,
                        $productId,
                        $currentPrice['price'],
                        $standardPrice,
                        $effectiveDate,
                        $userId
                    ]);

                    // Update price
                    $updateSql = "UPDATE products SET price = ?, updated_by = ?, updated_at = NOW() WHERE id = ? AND branch_id = ? AND tenant_id = ?";
                    $this->db->prepare($updateSql)->execute([$standardPrice, $userId, $productId, $branchId, $tenantId]);
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'price_standardization', $productId, 'STANDARDIZE', json_encode($data));

            return [
                'success' => true,
                'message' => 'Pricing standardized across branches',
                'branches_affected' => count($branchIds)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to standardize pricing: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get multi-branch summary
     */
    public function getSummary($tenantId)
    {
        // Total branches
        $branchesSql = "SELECT COUNT(*) as count FROM branches WHERE tenant_id = ? AND deleted_at IS NULL";
        $totalBranches = $this->db->query($branchesSql, [$tenantId])->fetch();

        // Pending transfers
        $pendingTransfersSql = "SELECT COUNT(*) as count FROM stock_transfers WHERE tenant_id = ? AND status = 'PENDING'";
        $pendingTransfers = $this->db->query($pendingTransfersSql, [$tenantId])->fetch();

        // In-transit transfers
        $inTransitSql = "SELECT COUNT(*) as count FROM stock_transfers WHERE tenant_id = ? AND status = 'IN_TRANSIT'";
        $inTransitTransfers = $this->db->query($inTransitSql, [$tenantId])->fetch();

        // Centralized purchases this month
        $centralizedPurchasesSql = "SELECT COUNT(*) as count FROM purchase_orders WHERE tenant_id = ? AND is_centralized = 1 AND order_date >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $centralizedPurchases = $this->db->query($centralizedPurchasesSql, [$tenantId])->fetch();

        return [
            'total_branches' => $totalBranches['count'] ?? 0,
            'pending_transfers' => $pendingTransfers['count'] ?? 0,
            'in_transit_transfers' => $inTransitTransfers['count'] ?? 0,
            'centralized_purchases_this_month' => $centralizedPurchases['count'] ?? 0
        ];
    }
}
