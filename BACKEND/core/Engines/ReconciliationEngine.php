<?php

use PDO;

require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * ReconciliationEngine - Unified Reconciliation Engine for RESTAURANT_ERP
 * 
 * This engine handles order-level matching, multi-source aggregation,
 * discrepancy detection, and reconciliation reporting to solve the
 * fundamental problem of payment verification and data trust.
 * 
 * @package EBP\Core\Engines
 * @version 1.0.0
 */

class ReconciliationEngine implements EngineInterface
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

        $action = $params['action'] ?? 'reconcile_order';

        switch ($action) {
            case 'reconcile_order':
                return $this->executeReconcileOrder($params);
            case 'reconcile_batch':
                return $this->executeReconcileBatch($params);
            case 'manual_override':
                return $this->executeManualOverride($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeReconcileOrder(array $params): array
    {
        $orderId = $params['order_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$orderId || !$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: order_id, tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->reconcileOrder($orderId, $tenantId, $branchId);
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

    private function executeReconcileBatch(array $params): array
    {
        $orderIds = $params['order_ids'] ?? [];
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (empty($orderIds) || !$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: order_ids, tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->reconcileBatch($orderIds, $tenantId, $branchId);
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

    private function executeManualOverride(array $params): array
    {
        $orderId = $params['order_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $reason = $params['reason'] ?? '';
        $userId = $params['user_id'] ?? null;

        if (!$orderId || !$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: order_id, tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->manualOverride($orderId, $tenantId, $branchId, $reason, $userId);
            return [
                'success' => $result,
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
            'name' => 'Reconciliation Engine',
            'version' => '1.0.0',
            'description' => 'Handles order-level reconciliation and discrepancy detection',
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

    /**
     * Perform order-level reconciliation for a specific order
     * 
     * @param int $orderId Order ID to reconcile
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Reconciliation result with status and discrepancies
     */
    public function reconcileOrder($orderId, $tenantId, $branchId)
    {
        // Get order details
        $order = $this->getOrderDetails($orderId, $tenantId, $branchId);
        if (!$order) {
            return [
                'success' => false,
                'message' => 'Order not found',
                'discrepancies' => []
            ];
        }

        // Get all payment sources for this order
        $paymentSources = $this->getPaymentSources($orderId, $tenantId, $branchId);

        // Calculate expected total from order
        $expectedTotal = $order['total_amount'];

        // Calculate actual total from payment sources
        $actualTotal = $this->calculateActualTotal($paymentSources);

        // Detect discrepancies
        $discrepancies = $this->detectDiscrepancies($expectedTotal, $actualTotal, $paymentSources, $order);

        // Determine reconciliation status
        $status = $this->determineReconciliationStatus($discrepancies);

        // Log reconciliation result
        $this->logReconciliation($orderId, $tenantId, $branchId, $status, $discrepancies, $expectedTotal, $actualTotal);

        // Create alert if discrepancy detected
        if (!empty($discrepancies)) {
            $this->createDiscrepancyAlert($orderId, $tenantId, $branchId, $discrepancies);
        }

        return [
            'success' => true,
            'order_id' => $orderId,
            'status' => $status,
            'expected_total' => $expectedTotal,
            'actual_total' => $actualTotal,
            'discrepancies' => $discrepancies,
            'payment_sources' => $paymentSources
        ];
    }

    /**
     * Perform batch reconciliation for multiple orders
     * 
     * @param array $orderIds Array of order IDs
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Batch reconciliation results
     */
    public function reconcileBatch($orderIds, $tenantId, $branchId)
    {
        $results = [];
        $summary = [
            'total_orders' => count($orderIds),
            'reconciled' => 0,
            'discrepancies' => 0,
            'failed' => 0
        ];

        foreach ($orderIds as $orderId) {
            $result = $this->reconcileOrder($orderId, $tenantId, $branchId);
            $results[$orderId] = $result;

            if ($result['success']) {
                $summary['reconciled']++;
                if (!empty($result['discrepancies'])) {
                    $summary['discrepancies']++;
                }
            } else {
                $summary['failed']++;
            }
        }

        return [
            'results' => $results,
            'summary' => $summary
        ];
    }

    /**
     * Get order details for reconciliation
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array|false Order details or false if not found
     */
    private function getOrderDetails($orderId, $tenantId, $branchId)
    {
        $sql = "
            SELECT o.order_id, o.total_amount, o.status, o.created_at,
                   o.payment_status, o.tenant_id, o.branch_id
            FROM orders o
            WHERE o.order_id = ? 
              AND o.tenant_id = ? 
              AND o.branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $tenantId, $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all payment sources for an order
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Payment sources
     */
    private function getPaymentSources($orderId, $tenantId, $branchId)
    {
        $sources = [];

        // Get POS payments
        $posPayments = $this->getPOSPayments($orderId, $tenantId, $branchId);
        if ($posPayments) {
            $sources['pos'] = $posPayments;
        }

        // Get payment processor records
        $processorPayments = $this->getProcessorPayments($orderId, $tenantId, $branchId);
        if ($processorPayments) {
            $sources['processor'] = $processorPayments;
        }

        // Get delivery platform payments
        $deliveryPayments = $this->getDeliveryPayments($orderId, $tenantId, $branchId);
        if ($deliveryPayments) {
            $sources['delivery'] = $deliveryPayments;
        }

        return $sources;
    }

    /**
     * Get POS payment records
     */
    private function getPOSPayments($orderId, $tenantId, $branchId)
    {
        $sql = "
            SELECT payment_id, amount, payment_method, payment_date, status
            FROM payments
            WHERE order_id = ? 
              AND tenant_id = ? 
              AND branch_id = ?
              AND source = 'POS'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payment processor records
     */
    private function getProcessorPayments($orderId, $tenantId, $branchId)
    {
        $sql = "
            SELECT transaction_id, amount, processor, transaction_date, status
            FROM payment_processor_transactions
            WHERE order_id = ? 
              AND tenant_id = ? 
              AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get delivery platform payment records
     */
    private function getDeliveryPayments($orderId, $tenantId, $branchId)
    {
        $sql = "
            SELECT delivery_order_id, amount, platform, delivery_date, status
            FROM delivery_orders
            WHERE order_id = ? 
              AND tenant_id = ? 
              AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate actual total from all payment sources
     * 
     * @param array $paymentSources Payment sources array
     * @return float Total actual amount
     */
    private function calculateActualTotal($paymentSources)
    {
        $total = 0;

        foreach ($paymentSources as $source => $payments) {
            foreach ($payments as $payment) {
                if (isset($payment['amount']) && $payment['status'] === 'COMPLETED') {
                    $total += (float)$payment['amount'];
                }
            }
        }

        return $total;
    }

    /**
     * Detect discrepancies between expected and actual totals
     * 
     * @param float $expectedTotal Expected total
     * @param float $actualTotal Actual total
     * @param array $paymentSources Payment sources
     * @param array $order Order details
     * @return array Detected discrepancies
     */
    private function detectDiscrepancies($expectedTotal, $actualTotal, $paymentSources, $order)
    {
        $discrepancies = [];
        $tolerance = 0.01; // 1 cent tolerance for rounding

        // Check for total mismatch
        if (abs($expectedTotal - $actualTotal) > $tolerance) {
            $discrepancies[] = [
                'type' => 'TOTAL_MISMATCH',
                'severity' => 'HIGH',
                'expected' => $expectedTotal,
                'actual' => $actualTotal,
                'difference' => $expectedTotal - $actualTotal,
                'description' => 'Total amount mismatch between order and payments'
            ];
        }

        // Check for missing payment sources
        if (empty($paymentSources)) {
            $discrepancies[] = [
                'type' => 'NO_PAYMENT_SOURCE',
                'severity' => 'HIGH',
                'description' => 'No payment records found for this order'
            ];
        }

        // Check for pending payments
        foreach ($paymentSources as $source => $payments) {
            foreach ($payments as $payment) {
                if ($payment['status'] === 'PENDING' || $payment['status'] === 'FAILED') {
                    $discrepancies[] = [
                        'type' => 'PAYMENT_PENDING',
                        'severity' => 'MEDIUM',
                        'source' => $source,
                        'payment_id' => $payment['payment_id'] ?? $payment['transaction_id'] ?? $payment['delivery_order_id'],
                        'status' => $payment['status'],
                        'description' => "Payment from {$source} is not completed"
                    ];
                }
            }
        }

        // Check for duplicate payments
        $this->checkDuplicatePayments($paymentSources, $discrepancies);

        return $discrepancies;
    }

    /**
     * Check for duplicate payments across sources
     * 
     * @param array $paymentSources Payment sources
     * @param array $discrepancies Discrepancies array (passed by reference)
     */
    private function checkDuplicatePayments($paymentSources, &$discrepancies)
    {
        $allPayments = [];

        foreach ($paymentSources as $source => $payments) {
            foreach ($payments as $payment) {
                $key = $payment['amount'] . '_' . $payment['payment_date'] ?? $payment['transaction_date'] ?? $payment['delivery_date'];
                if (isset($allPayments[$key])) {
                    $discrepancies[] = [
                        'type' => 'DUPLICATE_PAYMENT',
                        'severity' => 'HIGH',
                        'sources' => [$allPayments[$key]['source'], $source],
                        'amount' => $payment['amount'],
                        'description' => 'Potential duplicate payment detected'
                    ];
                }
                $allPayments[$key] = [
                    'source' => $source,
                    'amount' => $payment['amount']
                ];
            }
        }
    }

    /**
     * Determine reconciliation status based on discrepancies
     * 
     * @param array $discrepancies Detected discrepancies
     * @return string Reconciliation status
     */
    private function determineReconciliationStatus($discrepancies)
    {
        if (empty($discrepancies)) {
            return 'RECONCILED';
        }

        $hasHighSeverity = false;
        foreach ($discrepancies as $discrepancy) {
            if ($discrepancy['severity'] === 'HIGH') {
                $hasHighSeverity = true;
                break;
            }
        }

        return $hasHighSeverity ? 'DISCREPANCY_HIGH' : 'DISCREPANCY_LOW';
    }

    /**
     * Log reconciliation result
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $status Reconciliation status
     * @param array $discrepancies Discrepancies
     * @param float $expectedTotal Expected total
     * @param float $actualTotal Actual total
     */
    private function logReconciliation($orderId, $tenantId, $branchId, $status, $discrepancies, $expectedTotal, $actualTotal)
    {
        $sql = "
            INSERT INTO reconciliation_logs
            (order_id, tenant_id, branch_id, status, expected_total, actual_total, 
             discrepancies_count, discrepancies_json, reconciled_at, reconciled_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'SYSTEM')
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderId,
            $tenantId,
            $branchId,
            $status,
            $expectedTotal,
            $actualTotal,
            count($discrepancies),
            json_encode($discrepancies)
        ]);
    }

    /**
     * Create discrepancy alert
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param array $discrepancies Discrepancies
     */
    private function createDiscrepancyAlert($orderId, $tenantId, $branchId, $discrepancies)
    {
        $hasHighSeverity = false;
        foreach ($discrepancies as $discrepancy) {
            if ($discrepancy['severity'] === 'HIGH') {
                $hasHighSeverity = true;
                break;
            }
        }

        $alertType = $hasHighSeverity ? 'CRITICAL' : 'WARNING';
        $message = "Reconciliation discrepancy detected for order #{$orderId}";

        $sql = "
            INSERT INTO reconciliation_alerts
            (order_id, tenant_id, branch_id, alert_type, message, 
             discrepancies_json, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 'ACTIVE')
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderId,
            $tenantId,
            $branchId,
            $alertType,
            $message,
            json_encode($discrepancies)
        ]);
    }

    /**
     * Get reconciliation dashboard data
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Dashboard data
     */
    public function getDashboardData($tenantId, $branchId, $startDate, $endDate)
    {
        // Get reconciliation summary
        $summary = $this->getReconciliationSummary($tenantId, $branchId, $startDate, $endDate);

        // Get active alerts
        $alerts = $this->getActiveAlerts($tenantId, $branchId);

        // Get recent discrepancies
        $recentDiscrepancies = $this->getRecentDiscrepancies($tenantId, $branchId, $startDate, $endDate);

        return [
            'summary' => $summary,
            'alerts' => $alerts,
            'recent_discrepancies' => $recentDiscrepancies
        ];
    }

    /**
     * Get reconciliation summary
     */
    private function getReconciliationSummary($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'RECONCILED' THEN 1 ELSE 0 END) as reconciled,
                SUM(CASE WHEN status = 'DISCREPANCY_HIGH' THEN 1 ELSE 0 END) as high_discrepancies,
                SUM(CASE WHEN status = 'DISCREPANCY_LOW' THEN 1 ELSE 0 END) as low_discrepancies,
                SUM(expected_total) as total_expected,
                SUM(actual_total) as total_actual
            FROM reconciliation_logs
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND reconciled_at BETWEEN ? AND ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get active alerts
     */
    private function getActiveAlerts($tenantId, $branchId)
    {
        $sql = "
            SELECT alert_id, order_id, alert_type, message, created_at
            FROM reconciliation_alerts
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND status = 'ACTIVE'
            ORDER BY created_at DESC
            LIMIT 20
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent discrepancies
     */
    private function getRecentDiscrepancies($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT order_id, status, expected_total, actual_total, 
                   discrepancies_count, reconciled_at
            FROM reconciliation_logs
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND status != 'RECONCILED'
              AND reconciled_at BETWEEN ? AND ?
            ORDER BY reconciled_at DESC
            LIMIT 50
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Manual override for reconciliation
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $overrideReason Reason for override
     * @param int $userId User performing override
     * @return bool Success status
     */
    public function manualOverride($orderId, $tenantId, $branchId, $overrideReason, $userId)
    {
        $sql = "
            UPDATE reconciliation_logs
            SET status = 'MANUALLY_OVERRIDDEN',
                override_reason = ?,
                overridden_by = ?,
                overridden_at = NOW()
            WHERE order_id = ? 
              AND tenant_id = ? 
              AND branch_id = ?
              AND status != 'RECONCILED'
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$overrideReason, $userId, $orderId, $tenantId, $branchId]);
    }
}
