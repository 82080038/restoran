<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

class StockEngine implements EngineInterface
{

    private $db;
    private $initialized = false;



    public function __construct($db = null)
    {
        if ($db) {
            $this->initialize(['db' => $db]);
        }
    }

    public function initialize($dependencies): void
    {
        $this->db = $dependencies['db'] ?? null;
        $this->initialized = !empty($this->db);
    }

    public function validate(): bool
    {
        return $this->initialized && !empty($this->db);
    }

    public function execute(array $params): array
    {
        if (!$this->validate()) {
            return [
                'success' => false,
                'message' => 'Engine not properly initialized'
            ];
        }

        $action = $params['action'] ?? 'deduct_from_recipe';

        switch ($action) {
            case 'deduct_from_recipe':
                return $this->executeDeductFromRecipe($params);
            case 'check_reorder_points':
                return $this->executeCheckReorderPoints($params);
            case 'set_reorder_point':
                return $this->executeSetReorderPoint($params);
            case 'generate_purchase_order':
                return $this->executeGeneratePurchaseOrder($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeCheckReorderPoints(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->checkReorderPoints($tenantId, $branchId);
            return [
                'success' => true,
                'items_to_reorder' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeSetReorderPoint(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $inventoryId = $params['inventory_id'] ?? null;
        $reorderPoint = $params['reorder_point'] ?? null;
        $maxStock = $params['max_stock'] ?? null;

        if (!$tenantId || !$branchId || !$inventoryId || $reorderPoint === null) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, inventory_id, reorder_point'
            ];
        }

        try {
            $result = $this->setReorderPoint($tenantId, $branchId, $inventoryId, $reorderPoint, $maxStock);
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeForecastDemand(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $inventoryId = $params['inventory_id'] ?? null;
        $days = $params['days'] ?? 30;

        if (!$tenantId || !$branchId || !$inventoryId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, inventory_id'
            ];
        }

        try {
            $result = $this->forecastDemand($tenantId, $branchId, $inventoryId, $days);
            return [
                'success' => true,
                'forecast' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCalculateInventoryValue(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->calculateInventoryValue($tenantId, $branchId);
            return [
                'success' => true,
                'inventory_value' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGeneratePurchaseOrder(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $supplierId = $params['supplier_id'] ?? null;

        if (!$tenantId || !$branchId || !$supplierId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, supplier_id'
            ];
        }

        try {
            $result = $this->generatePurchaseOrder($tenantId, $branchId, $supplierId);
            return [
                'success' => true,
                'purchase_order' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeDeductFromRecipe(array $params): array
    {
        $orderId = $params['order_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$orderId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: order_id, branch_id'
            ];
        }

        try {
            $result = $this->deductFromRecipe($orderId, $branchId);
            return [
                'success' => $result !== false,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Stock Engine',
            'version' => '1.0.0',
            'description' => 'Handles inventory stock deduction and management',
            'author' => 'EBP Team',
            'created_at' => '2026-07-08'
        ];
    }

    public function getHealth(): array
    {
        return [
            'status' => $this->validate() ? 'healthy' : 'unhealthy',
            'initialized' => $this->initialized,
            'database_connected' => !empty($this->db),
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }



    public function deductFromRecipe($orderId, $branchId)
    {
        // Get order details to determine tenant
        $sql = "SELECT tenant_id FROM orders WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return false;
        }
        
        $tenantId = $order['tenant_id'];

        $sql = "
            SELECT od.product_id, od.quantity, ri.ingredient_id, ri.quantity as recipe_qty
            FROM order_items od
            INNER JOIN recipes r ON od.product_id = r.product_id
            INNER JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
            WHERE od.order_id = ? AND r.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $recipeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Validate stock availability before deduction
        foreach ($recipeItems as $item) {
            $requiredQty = $item['quantity'] * $item['recipe_qty'];
            $currentStock = $this->getCurrentStock($tenantId, $branchId, $item['ingredient_id']);
            
            if ($currentStock < $requiredQty) {
                // Insufficient stock - throw exception or return error
                throw new Exception("Insufficient stock for ingredient ID {$item['ingredient_id']}. Required: {$requiredQty}, Available: {$currentStock}");
            }
        }

        // Proceed with stock deduction
        foreach ($recipeItems as $item) {
            $requiredQty = $item['quantity'] * $item['recipe_qty'];

            $this->updateStock(
                $tenantId,
                $branchId,
                $item['ingredient_id'],
                -$requiredQty,
                'SALE_USAGE',
                $orderId
            );
        }
        
        return true;
    }

    /**
     * Get current stock balance for an ingredient
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $inventoryId Inventory/Ingredient ID
     * @return float Current stock quantity
     */
    private function getCurrentStock($tenantId, $branchId, $inventoryId)
    {
        $sql = "
            SELECT COALESCE(quantity, 0) as quantity
            FROM stock_balances
            WHERE tenant_id = ? AND branch_id = ? AND inventory_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $inventoryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (float)$result['quantity'] : 0;
    }



    private function updateStock($tenantId, $branchId, $inventoryId, $quantity, $type, $referenceId)
    {
        // First, ensure stock balance exists
        $sql = "INSERT INTO stock_balances (tenant_id, branch_id, inventory_id, quantity, last_transaction_date) 
                VALUES (?, ?, ?, 0, NOW())
                ON DUPLICATE KEY UPDATE last_transaction_date = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $inventoryId]);

        $sql = "
            UPDATE stock_balances
            SET quantity = quantity + ?,
                last_transaction_date = NOW()
            WHERE branch_id = ? AND inventory_id = ?
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$quantity, $branchId, $inventoryId]);

        // Get product_id from inventory
        $sql = "SELECT product_id FROM inventory WHERE inventory_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inventoryId]);
        $inventory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$inventory) {
            return;
        }
        
        $productId = $inventory['product_id'];
        
        // Determine transaction type
        $transactionType = ($quantity < 0) ? 'OUT' : 'IN';
        $absQuantity = abs($quantity);
        
        // Get unit from inventory
        $sql = "SELECT unit FROM inventory WHERE inventory_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inventoryId]);
        $unit = $stmt->fetchColumn();

        $sql = "
            INSERT INTO stock_transactions
            (tenant_id, branch_id, product_id, transaction_type, quantity, unit, reference_type, reference_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'ORDER', ?, NOW())
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$tenantId, $branchId, $productId, $transactionType, $absQuantity, $unit, $referenceId]);

    }

    /**
     * Check which items need to be reordered based on reorder points
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Items that need reordering
     */
    public function checkReorderPoints($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                i.inventory_id,
                i.product_id,
                i.name,
                i.unit,
                sb.quantity as current_stock,
                ir.reorder_point,
                ir.max_stock,
                i.supplier_id,
                (ir.reorder_point - sb.quantity) as quantity_needed
            FROM inventory i
            INNER JOIN stock_balances sb ON i.inventory_id = sb.inventory_id
            LEFT JOIN inventory_reorder ir ON i.inventory_id = ir.inventory_id AND ir.branch_id = ?
            WHERE i.tenant_id = ? AND sb.branch_id = ? AND i.status = 'ACTIVE'
            AND ir.reorder_point IS NOT NULL
            AND sb.quantity <= ir.reorder_point
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Set reorder point for an inventory item
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $inventoryId Inventory ID
     * @param float $reorderPoint Reorder point quantity
     * @param float|null $maxStock Maximum stock level (optional)
     * @return bool Success status
     */
    public function setReorderPoint($tenantId, $branchId, $inventoryId, $reorderPoint, $maxStock = null)
    {
        $sql = "
            INSERT INTO inventory_reorder (tenant_id, branch_id, inventory_id, reorder_point, max_stock, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                reorder_point = VALUES(reorder_point),
                max_stock = VALUES(max_stock),
                updated_at = NOW()
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$tenantId, $branchId, $inventoryId, $reorderPoint, $maxStock]);
    }

    /**
     * Generate purchase order for items needing reorder
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $supplierId Supplier ID
     * @return array Purchase order details
     */
    public function generatePurchaseOrder($tenantId, $branchId, $supplierId)
    {
        // Get items needing reorder for this supplier
        $sql = "
            SELECT 
                i.inventory_id,
                i.product_id,
                i.name,
                i.unit,
                sb.quantity as current_stock,
                ir.reorder_point,
                ir.max_stock,
                (ir.max_stock - sb.quantity) as order_quantity
            FROM inventory i
            INNER JOIN stock_balances sb ON i.inventory_id = sb.inventory_id
            INNER JOIN inventory_reorder ir ON i.inventory_id = ir.inventory_id AND ir.branch_id = ?
            WHERE i.tenant_id = ? AND sb.branch_id = ? AND i.supplier_id = ? AND i.status = 'ACTIVE'
            AND sb.quantity <= ir.reorder_point
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId, $branchId, $supplierId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) {
            return [
                'message' => 'No items need reordering for this supplier'
            ];
        }

        // Generate PO number
        $poNumber = 'PO-' . date('Ymd') . '-' . rand(1000, 9999);

        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            // Get unit price from inventory or supplier
            $priceSql = "SELECT unit_price FROM inventory WHERE inventory_id = ?";
            $priceStmt = $this->db->prepare($priceSql);
            $priceStmt->execute([$item['inventory_id']]);
            $unitPrice = $priceStmt->fetchColumn() ?: 0;
            
            $lineTotal = $item['order_quantity'] * $unitPrice;
            $subtotal += $lineTotal;
        }

        $taxAmount = $subtotal * 0.11; // 11% tax
        $totalAmount = $subtotal + $taxAmount;

        return [
            'po_number' => $poNumber,
            'supplier_id' => $supplierId,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Forecast demand for inventory item
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $inventoryId Inventory ID
     * @param int $days Number of days to forecast
     * @return array Demand forecast
     */
    public function forecastDemand($tenantId, $branchId, $inventoryId, $days = 30)
    {
        // Get historical usage data
        $sql = "
            SELECT 
                DATE(st.created_at) as usage_date,
                SUM(st.quantity) as daily_usage
            FROM stock_transactions st
            WHERE st.tenant_id = ? 
            AND st.branch_id = ?
            AND st.inventory_id = ?
            AND st.transaction_type = 'OUT'
            AND st.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY DATE(st.created_at)
            ORDER BY usage_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $inventoryId]);
        $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($historicalData)) {
            return [
                'inventory_id' => $inventoryId,
                'forecast_days' => $days,
                'average_daily_usage' => 0,
                'predicted_demand' => 0,
                'confidence' => 'low'
            ];
        }

        // Calculate average daily usage
        $totalUsage = array_sum(array_column($historicalData, 'daily_usage'));
        $averageDailyUsage = $totalUsage / count($historicalData);

        // Simple moving average forecast
        $predictedDemand = $averageDailyUsage * $days;

        // Calculate variance for confidence
        $variances = [];
        foreach ($historicalData as $data) {
            $variances[] = pow($data['daily_usage'] - $averageDailyUsage, 2);
        }
        $variance = count($variances) > 0 ? array_sum($variances) / count($variances) : 0;
        $stdDev = sqrt($variance);
        
        $confidence = 'medium';
        if ($stdDev / $averageDailyUsage < 0.2) {
            $confidence = 'high';
        } elseif ($stdDev / $averageDailyUsage > 0.5) {
            $confidence = 'low';
        }

        return [
            'inventory_id' => $inventoryId,
            'forecast_days' => $days,
            'average_daily_usage' => round($averageDailyUsage, 2),
            'predicted_demand' => round($predictedDemand, 2),
            'confidence' => $confidence,
            'standard_deviation' => round($stdDev, 2),
            'historical_data_points' => count($historicalData)
        ];
    }

    /**
     * Calculate total inventory value
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Inventory value breakdown
     */
    public function calculateInventoryValue($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                i.inventory_id,
                i.name,
                i.unit,
                i.unit_price,
                COALESCE(sb.quantity, 0) as quantity,
                (COALESCE(sb.quantity, 0) * i.unit_price) as total_value
            FROM inventory i
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE i.tenant_id = ? AND i.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId]);
        $inventoryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalValue = 0;
        $totalQuantity = 0;
        $categoryBreakdown = [];

        foreach ($inventoryItems as $item) {
            $totalValue += $item['total_value'];
            $totalQuantity += $item['quantity'];

            $category = $item['category'] ?? 'uncategorized';
            if (!isset($categoryBreakdown[$category])) {
                $categoryBreakdown[$category] = [
                    'quantity' => 0,
                    'value' => 0
                ];
            }
            $categoryBreakdown[$category]['quantity'] += $item['quantity'];
            $categoryBreakdown[$category]['value'] += $item['total_value'];
        }

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'total_items' => count($inventoryItems),
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'category_breakdown' => $categoryBreakdown,
            'items' => $inventoryItems
        ];
    }

}
