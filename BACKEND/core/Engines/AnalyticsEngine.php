<?php

use PDO;

require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * AnalyticsEngine - Analytics and Reporting Engine for RESTAURANT_ERP
 * 
 * This engine handles POS analytics, inventory reports, and business intelligence
 * 
 * @package EBP\Core\Engines
 * @version 1.0.0
 */

class AnalyticsEngine implements EngineInterface
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

        $action = $params['action'] ?? 'get_pos_analytics';

        switch ($action) {
            case 'get_pos_analytics':
                return $this->executeGetPOSAnalytics($params);
            case 'get_inventory_reports':
                return $this->executeGetInventoryReports($params);
            case 'get_sales_analytics':
                return $this->executeGetSalesAnalytics($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeGetPOSAnalytics(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$branchId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->getPOSAnalytics($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'analytics' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGetInventoryReports(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $reportType = $params['report_type'] ?? 'summary';

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->getInventoryReports($tenantId, $branchId, $reportType);
            return [
                'success' => true,
                'report' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGetSalesAnalytics(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$branchId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->getSalesAnalytics($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'analytics' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get POS analytics dashboard data
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array POS analytics data
     */
    public function getPOSAnalytics($tenantId, $branchId, $startDate, $endDate)
    {
        // Get total orders
        $sql = "
            SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue
            FROM orders
            WHERE tenant_id = ? AND branch_id = ?
            AND order_date BETWEEN ? AND ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get orders by hour
        $sql = "
            SELECT HOUR(order_time) as hour, COUNT(*) as order_count, SUM(total_amount) as revenue
            FROM orders
            WHERE tenant_id = ? AND branch_id = ?
            AND order_date BETWEEN ? AND ?
            GROUP BY HOUR(order_time)
            ORDER BY hour
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $hourlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get top selling items
        $sql = "
            SELECT 
                mi.name as item_name,
                COUNT(oi.order_item_id) as order_count,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            INNER JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id
            INNER JOIN orders o ON oi.order_id = o.order_id
            WHERE o.tenant_id = ? AND o.branch_id = ?
            AND o.order_date BETWEEN ? AND ?
            GROUP BY mi.menu_item_id, mi.name
            ORDER BY total_revenue DESC
            LIMIT 10
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $topItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get payment method breakdown
        $sql = "
            SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total
            FROM orders
            WHERE tenant_id = ? AND branch_id = ?
            AND order_date BETWEEN ? AND ?
            GROUP BY payment_method
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'summary' => $summary,
            'hourly_data' => $hourlyData,
            'top_items' => $topItems,
            'payment_methods' => $paymentMethods
        ];
    }

    /**
     * Get inventory reports
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $reportType Report type
     * @return array Inventory report data
     */
    public function getInventoryReports($tenantId, $branchId, $reportType = 'summary')
    {
        switch ($reportType) {
            case 'summary':
                return $this->getInventorySummary($tenantId, $branchId);
            case 'movement':
                return $this->getInventoryMovement($tenantId, $branchId);
            case 'valuation':
                return $this->getInventoryValuation($tenantId, $branchId);
            default:
                throw new Exception("Unknown report type: {$reportType}");
        }
    }

    /**
     * Get inventory summary
     */
    private function getInventorySummary($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                i.inventory_id,
                i.name,
                i.category,
                i.unit,
                COALESCE(sb.quantity, 0) as current_quantity,
                i.reorder_level,
                i.unit_price,
                (COALESCE(sb.quantity, 0) * i.unit_price) as total_value
            FROM inventory i
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE i.tenant_id = ? AND i.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalValue = array_sum(array_column($items, 'total_value'));
        $lowStockCount = count(array_filter($items, function($item) {
            return $item['current_quantity'] <= $item['reorder_level'];
        }));

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'total_items' => count($items),
            'total_value' => $totalValue,
            'low_stock_count' => $lowStockCount,
            'items' => $items
        ];
    }

    /**
     * Get inventory movement
     */
    private function getInventoryMovement($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                st.transaction_id,
                st.inventory_id,
                i.name as item_name,
                st.transaction_type,
                st.quantity,
                st.created_at
            FROM stock_transactions st
            INNER JOIN inventory i ON st.inventory_id = i.inventory_id
            WHERE st.tenant_id = ? AND st.branch_id = ?
            AND st.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY st.created_at DESC
            LIMIT 100
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period_days' => 30,
            'total_movements' => count($movements),
            'movements' => $movements
        ];
    }

    /**
     * Get inventory valuation
     */
    private function getInventoryValuation($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                i.category,
                COUNT(*) as item_count,
                SUM(COALESCE(sb.quantity, 0) * i.unit_price) as category_value
            FROM inventory i
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE i.tenant_id = ? AND i.status = 'ACTIVE'
            GROUP BY i.category
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId]);
        $categoryValuation = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalValue = array_sum(array_column($categoryValuation, 'category_value'));

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'total_value' => $totalValue,
            'category_breakdown' => $categoryValuation
        ];
    }

    /**
     * Get sales analytics
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Sales analytics data
     */
    public function getSalesAnalytics($tenantId, $branchId, $startDate, $endDate)
    {
        // Get daily sales
        $sql = "
            SELECT 
                DATE(order_date) as date,
                COUNT(*) as order_count,
                SUM(total_amount) as revenue,
                AVG(total_amount) as average_order_value
            FROM orders
            WHERE tenant_id = ? AND branch_id = ?
            AND order_date BETWEEN ? AND ?
            GROUP BY DATE(order_date)
            ORDER BY date
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $dailySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get sales by category
        $sql = "
            SELECT 
                mi.category,
                COUNT(oi.order_item_id) as item_count,
                SUM(oi.subtotal) as revenue
            FROM order_items oi
            INNER JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id
            INNER JOIN orders o ON oi.order_id = o.order_id
            WHERE o.tenant_id = ? AND o.branch_id = ?
            AND o.order_date BETWEEN ? AND ?
            GROUP BY mi.category
            ORDER BY revenue DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $categorySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'daily_sales' => $dailySales,
            'category_sales' => $categorySales
        ];
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'AnalyticsEngine',
            'version' => '1.0.0',
            'description' => 'Analytics and Reporting Engine for RESTAURANT_ERP',
            'capabilities' => [
                'pos_analytics',
                'inventory_reports',
                'sales_analytics'
            ]
        ];
    }

    public function getHealth(): array
    {
        return [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
