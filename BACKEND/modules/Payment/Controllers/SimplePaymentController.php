<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

class SimplePaymentController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get all payments for a tenant
     */
    public function getPayments($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);
            $status = $request['query']['status'] ?? null;
            $method = $request['query']['method'] ?? null;
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            $sql = "SELECT p.*, o.order_number, o.order_type
                    FROM payments p
                    LEFT JOIN orders o ON p.order_id = o.order_id
                    WHERE p.tenant_id = ?";
            $params = [$tenantId];

            if ($status) {
                $sql .= " AND p.payment_status = ?";
                $params[] = $status;
            }
            if ($method) {
                $sql .= " AND p.payment_method = ?";
                $params[] = $method;
            }

            $countSql = "SELECT COUNT(*) as total FROM payments p WHERE p.tenant_id = ?";
            $countParams = [$tenantId];
            if ($status) {
                $countSql .= " AND p.payment_status = ?";
                $countParams[] = $status;
            }
            if ($method) {
                $countSql .= " AND p.payment_method = ?";
                $countParams[] = $method;
            }

            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $total = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'data' => $payments,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => (int)ceil($total / $limit)
                ]
            ], 'Payments retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve payments: ' . $e->getMessage());
        }
    }

    /**
     * Get single payment by ID
     */
    public function getPayment($request)
    {
        try {
            $pdo = $this->db->connect();
            $paymentId = $request['id'] ?? 0;
            $tenantId = $request['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("
                SELECT p.*, o.order_number, o.order_type, o.total_amount
                FROM payments p
                LEFT JOIN orders o ON p.order_id = o.order_id
                WHERE p.payment_id = ? AND p.tenant_id = ?
            ");
            $stmt->execute([$paymentId, $tenantId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payment) {
                return Response::notFound('Payment not found');
            }

            return Response::success($payment, 'Payment retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve payment: ' . $e->getMessage());
        }
    }

    /**
     * Create a new payment record
     */
    public function createPayment($request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];
            $tenantId = $request['tenant_id'] ?? ($body['tenant_id'] ?? 1);
            $branchId = $request['branch_id'] ?? ($body['branch_id'] ?? null);
            $userId = $request['user_id'] ?? ($body['processed_by'] ?? null);

            $orderId = $body['order_id'] ?? null;
            $paymentMethod = $body['payment_method'] ?? '';
            $amount = (float)($body['amount'] ?? 0);

            if (!$orderId || !$paymentMethod || $amount <= 0) {
                return Response::error('order_id, payment_method, and amount are required', 400);
            }

            $paymentNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $stmt = $pdo->prepare("
                INSERT INTO payments (tenant_id, branch_id, order_id, payment_number, payment_method, payment_status, amount, currency, processed_by, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', ?, 'IDR', ?, NOW())
            ");
            $stmt->execute([$tenantId, $branchId, $orderId, $paymentNumber, $paymentMethod, $amount, $userId]);
            $paymentId = $pdo->lastInsertId();

            return Response::success([
                'payment_id' => $paymentId,
                'payment_number' => $paymentNumber,
                'payment_status' => 'pending'
            ], 'Payment created successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to create payment: ' . $e->getMessage());
        }
    }

    /**
     * Process a payment (mark as completed)
     */
    public function processPayment($request)
    {
        try {
            $pdo = $this->db->connect();
            $paymentId = $request['id'] ?? 0;
            $tenantId = $request['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = ? AND tenant_id = ?");
            $stmt->execute([$paymentId, $tenantId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payment) {
                return Response::notFound('Payment not found');
            }

            if ($payment['payment_status'] !== 'pending') {
                return Response::error('Payment already processed', 400);
            }

            $gatewayTxnId = $body['gateway_transaction_id'] ?? ('SIM-' . time());
            $gatewayResponse = $body['gateway_response'] ?? json_encode(['status' => 'success']);

            $stmt = $pdo->prepare("
                UPDATE payments
                SET payment_status = 'completed', processed_at = NOW(), completed_at = NOW(),
                    gateway_transaction_id = ?, gateway_response = ?
                WHERE payment_id = ?
            ");
            $stmt->execute([$gatewayTxnId, $gatewayResponse, $paymentId]);

            // Update order payment status
            $this->updateOrderPaymentStatus($pdo, $payment['order_id']);

            return Response::success([
                'payment_id' => $paymentId,
                'payment_status' => 'completed',
                'gateway_transaction_id' => $gatewayTxnId
            ], 'Payment processed successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Get payment methods for a tenant
     */
    public function getPaymentMethods($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);

            $stmt = $pdo->prepare("
                SELECT * FROM payment_methods
                WHERE tenant_id = ? AND is_active = 1
                ORDER BY display_order, method_name
            ");
            $stmt->execute([$tenantId]);
            $methods = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($methods, 'Payment methods retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve payment methods: ' . $e->getMessage());
        }
    }

    /**
     * Generate Z-Report (end of day sales summary)
     */
    public function getZReport($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $branchId = $request['branch_id'] ?? null;
            $date = $request['query']['date'] ?? date('Y-m-d');

            $whereClause = "WHERE p.tenant_id = ? AND DATE(p.completed_at) = ? AND p.payment_status = 'completed'";
            $params = [$tenantId, $date];

            if ($branchId) {
                $whereClause .= " AND p.branch_id = ?";
                $params[] = $branchId;
            }

            // Total sales
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_transactions, SUM(p.amount) as total_sales
                FROM payments p
                $whereClause
            ");
            $stmt->execute($params);
            $totals = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Sales by payment method
            $stmt = $pdo->prepare("
                SELECT p.payment_method, COUNT(*) as count, SUM(p.amount) as total
                FROM payments p
                $whereClause
                GROUP BY p.payment_method
                ORDER BY total DESC
            ");
            $stmt->execute($params);
            $byMethod = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Sales by order type
            $stmt = $pdo->prepare("
                SELECT o.order_type, COUNT(*) as count, SUM(p.amount) as total
                FROM payments p
                LEFT JOIN orders o ON p.order_id = o.order_id
                $whereClause
                GROUP BY o.order_type
                ORDER BY total DESC
            ");
            $stmt->execute($params);
            $byOrderType = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Refunds
            $refundWhere = "WHERE p.tenant_id = ? AND DATE(pr.processed_at) = ? AND pr.refund_status = 'completed'";
            $refundParams = [$tenantId, $date];
            if ($branchId) {
                $refundWhere .= " AND p.branch_id = ?";
                $refundParams[] = $branchId;
            }
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_refunds, SUM(pr.refund_amount) as total_refunded
                FROM payment_refunds pr
                LEFT JOIN payments p ON pr.payment_id = p.payment_id
                $refundWhere
            ");
            $stmt->execute($refundParams);
            $refunds = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Tips
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_tips, SUM(tip_amount) as total_tip_amount
                FROM tips
                WHERE tenant_id = ? AND DATE(created_at) = ?
            ");
            $stmt->execute([$tenantId, $date]);
            $tips = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Cash drawer info
            $drawerWhere = "WHERE tenant_id = ? AND DATE(opened_at) = ?";
            $drawerParams = [$tenantId, $date];
            if ($branchId) {
                $drawerWhere .= " AND branch_id = ?";
                $drawerParams[] = $branchId;
            }
            $stmt = $pdo->prepare("
                SELECT * FROM cash_drawers
                $drawerWhere
                ORDER BY opened_at DESC
            ");
            $stmt->execute($drawerParams);
            $cashDrawers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Net sales
            $netSales = ($totals['total_sales'] ?? 0) - ($refunds['total_refunded'] ?? 0);

            return Response::success([
                'report_date' => $date,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'summary' => [
                    'total_transactions' => (int)($totals['total_transactions'] ?? 0),
                    'gross_sales' => (float)($totals['total_sales'] ?? 0),
                    'total_refunds' => (int)($refunds['total_refunds'] ?? 0),
                    'total_refunded' => (float)($refunds['total_refunded'] ?? 0),
                    'net_sales' => $netSales,
                    'total_tips' => (int)($tips['total_tips'] ?? 0),
                    'tip_amount' => (float)($tips['total_tip_amount'] ?? 0)
                ],
                'sales_by_method' => $byMethod,
                'sales_by_order_type' => $byOrderType,
                'cash_drawers' => $cashDrawers
            ], 'Z-Report generated successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to generate Z-Report: ' . $e->getMessage());
        }
    }

    /**
     * Get payment statistics
     */
    public function getStatistics($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $dateFrom = $request['query']['date_from'] ?? date('Y-m-01');
            $dateTo = $request['query']['date_to'] ?? date('Y-m-d');

            $stmt = $pdo->prepare("
                SELECT
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_completed,
                    SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as total_pending,
                    SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as total_failed,
                    AVG(CASE WHEN payment_status = 'completed' THEN amount ELSE NULL END) as avg_payment
                FROM payments
                WHERE tenant_id = ? AND DATE(created_at) BETWEEN ? AND ?
            ");
            $stmt->execute([$tenantId, $dateFrom, $dateTo]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

            return Response::success($stats, 'Payment statistics retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve statistics: ' . $e->getMessage());
        }
    }

    /**
     * Update order payment status based on completed payments
     */
    private function updateOrderPaymentStatus($pdo, $orderId)
    {
        $stmt = $pdo->prepare("
            SELECT SUM(amount) as total_paid
            FROM payments
            WHERE order_id = ? AND payment_status = 'completed'
        ");
        $stmt->execute([$orderId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $totalPaid = (float)($result['total_paid'] ?? 0);

        $stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        $orderTotal = (float)($order['total_amount'] ?? 0);

        $paymentStatus = 'unpaid';
        if ($totalPaid >= $orderTotal && $orderTotal > 0) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, paid_amount = ? WHERE order_id = ?");
        $stmt->execute([$paymentStatus, $totalPaid, $orderId]);
    }
}
