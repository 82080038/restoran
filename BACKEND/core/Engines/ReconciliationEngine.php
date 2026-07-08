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

    /**
     * Real-time reconciliation trigger
     * Automatically reconciles orders when payment status changes
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Reconciliation result
     */
    public function triggerRealTimeReconciliation($orderId, $tenantId, $branchId)
    {
        try {
            // Check if order is eligible for real-time reconciliation
            if (!$this->isEligibleForRealTimeReconciliation($orderId, $tenantId, $branchId)) {
                return [
                    'success' => false,
                    'message' => 'Order not eligible for real-time reconciliation',
                    'order_id' => $orderId
                ];
            }

            // Perform immediate reconciliation
            $result = $this->reconcileOrder($orderId, $tenantId, $branchId);

            // Log real-time reconciliation
            $this->logRealTimeReconciliation($orderId, $tenantId, $branchId, $result);

            return $result;
        } catch (Exception $e) {
            // Queue for later processing if real-time fails
            $this->queueForLaterReconciliation($orderId, $tenantId, $branchId, $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Real-time reconciliation failed, queued for later processing',
                'error' => $e->getMessage(),
                'order_id' => $orderId
            ];
        }
    }

    /**
     * Check if order is eligible for real-time reconciliation
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return bool Eligible or not
     */
    private function isEligibleForRealTimeReconciliation($orderId, $tenantId, $branchId)
    {
        $sql = "
            SELECT payment_status, status, created_at
            FROM orders
            WHERE order_id = ? 
              AND tenant_id = ? 
              AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $tenantId, $branchId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return false;
        }

        // Eligible if payment is completed and order is not too old
        $isPaymentCompleted = in_array($order['payment_status'], ['PAID', 'PARTIALLY_PAID']);
        $isRecentOrder = strtotime($order['created_at']) > strtotime('-1 hour');
        
        return $isPaymentCompleted && $isRecentOrder;
    }

    /**
     * Log real-time reconciliation
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param array $result Reconciliation result
     */
    private function logRealTimeReconciliation($orderId, $tenantId, $branchId, $result)
    {
        $sql = "
            INSERT INTO real_time_reconciliation_log
            (order_id, tenant_id, branch_id, reconciliation_status, 
             processing_time_ms, triggered_at, triggered_by)
            VALUES (?, ?, ?, ?, ?, NOW(), 'SYSTEM')
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderId,
            $tenantId,
            $branchId,
            $result['status'],
            $result['processing_time_ms'] ?? 0
        ]);
    }

    /**
     * Queue order for later reconciliation
     * 
     * @param int $orderId Order ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $reason Reason for queueing
     */
    private function queueForLaterReconciliation($orderId, $tenantId, $branchId, $reason)
    {
        $sql = "
            INSERT INTO reconciliation_queue
            (order_id, tenant_id, branch_id, queue_reason, queued_at, status)
            VALUES (?, ?, ?, ?, NOW(), 'PENDING')
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $tenantId, $branchId, $reason]);
    }

    /**
     * Process queued reconciliations
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $limit Maximum number to process
     * @return array Processing results
     */
    public function processQueuedReconciliations($tenantId, $branchId, $limit = 100)
    {
        // Get queued reconciliations
        $sql = "
            SELECT queue_id, order_id, tenant_id, branch_id
            FROM reconciliation_queue
            WHERE tenant_id = ? 
              AND branch_id = ? 
              AND status = 'PENDING'
            ORDER BY queued_at ASC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $limit]);
        $queuedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        $processed = 0;
        $failed = 0;

        foreach ($queuedItems as $item) {
            try {
                // Reconcile the order
                $result = $this->reconcileOrder($item['order_id'], $item['tenant_id'], $item['branch_id']);

                // Update queue status
                $updateSql = "
                    UPDATE reconciliation_queue
                    SET status = 'PROCESSED', processed_at = NOW(), result_json = ?
                    WHERE queue_id = ?
                ";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([json_encode($result), $item['queue_id']]);

                $processed++;
                $results[] = [
                    'queue_id' => $item['queue_id'],
                    'order_id' => $item['order_id'],
                    'success' => true,
                    'result' => $result
                ];
            } catch (Exception $e) {
                // Mark as failed
                $updateSql = "
                    UPDATE reconciliation_queue
                    SET status = 'FAILED', processed_at = NOW(), error_message = ?
                    WHERE queue_id = ?
                ";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([$e->getMessage(), $item['queue_id']]);

                $failed++;
                $results[] = [
                    'queue_id' => $item['queue_id'],
                    'order_id' => $item['order_id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => true,
            'total_queued' => count($queuedItems),
            'processed' => $processed,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Get real-time reconciliation statistics
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $hours Number of hours to analyze
     * @return array Statistics
     */
    public function getRealTimeReconciliationStats($tenantId, $branchId, $hours = 24)
    {
        $startTime = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        $sql = "
            SELECT 
                COUNT(*) as total_reconciliations,
                SUM(CASE WHEN reconciliation_status = 'RECONCILED' THEN 1 ELSE 0 END) as reconciled,
                SUM(CASE WHEN reconciliation_status = 'DISCREPANCY_HIGH' THEN 1 ELSE 0 END) as high_discrepancies,
                SUM(CASE WHEN reconciliation_status = 'DISCREPANCY_LOW' THEN 1 ELSE 0 END) as low_discrepancies,
                AVG(processing_time_ms) as avg_processing_time,
                MIN(processing_time_ms) as min_processing_time,
                MAX(processing_time_ms) as max_processing_time
            FROM real_time_reconciliation_log
            WHERE tenant_id = ? 
              AND branch_id = ? 
              AND triggered_at >= ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startTime]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get queue statistics
        $queueSql = "
            SELECT 
                COUNT(*) as queue_size,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'PROCESSED' THEN 1 ELSE 0 END) as processed,
                SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed
            FROM reconciliation_queue
            WHERE tenant_id = ? 
              AND branch_id = ?
        ";

        $queueStmt = $this->db->prepare($queueSql);
        $queueStmt->execute([$tenantId, $branchId]);
        $queueStats = $queueStmt->fetch(PDO::FETCH_ASSOC);

        return [
            'period_hours' => $hours,
            'reconciliation_stats' => $stats,
            'queue_stats' => $queueStats,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Set up real-time reconciliation triggers
     * Creates database triggers for automatic reconciliation
     * 
     * @return array Setup result
     */
    public function setupRealTimeTriggers()
    {
        $triggers = [
            'after_payment_update' => "
                CREATE TRIGGER IF NOT EXISTS tr_reconcile_after_payment
                AFTER UPDATE ON payments
                FOR EACH ROW
                BEGIN
                    IF NEW.status = 'COMPLETED' AND OLD.status != 'COMPLETED' THEN
                        INSERT INTO reconciliation_queue (order_id, tenant_id, branch_id, queue_reason, queued_at, status)
                        VALUES (NEW.order_id, NEW.tenant_id, NEW.branch_id, 'Payment completed', NOW(), 'PENDING');
                    END IF;
                END;
            ",
            'after_order_creation' => "
                CREATE TRIGGER IF NOT EXISTS tr_queue_order_reconciliation
                AFTER INSERT ON orders
                FOR EACH ROW
                BEGIN
                    IF NEW.payment_status = 'PAID' THEN
                        INSERT INTO reconciliation_queue (order_id, tenant_id, branch_id, queue_reason, queued_at, status)
                        VALUES (NEW.order_id, NEW.tenant_id, NEW.branch_id, 'Order created with payment', NOW(), 'PENDING');
                    END IF;
                END;
            "
        ];

        $results = [];
        foreach ($triggers as $name => $sql) {
            try {
                $this->db->exec($sql);
                $results[$name] = [
                    'success' => true,
                    'message' => 'Trigger created successfully'
                ];
            } catch (Exception $e) {
                $results[$name] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => true,
            'triggers' => $results,
            'setup_at' => date('Y-m-d H:i:s')
        ];
    }
}
