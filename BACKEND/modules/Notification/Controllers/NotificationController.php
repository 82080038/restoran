<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

class NotificationController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * SSE endpoint for real-time notifications
     * GET /api/v1/notifications/stream
     */
    public function stream($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
        } catch (\Throwable $e) {
            Response::error("Authentication required", 401);
        }

        $tenantId = $payload['tenant_id'] ?? 1;
        $branchId = $payload['branch_id'] ?? null;
        $userId = $payload['user_id'] ?? null;
        $role = $payload['role'] ?? '';

        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        // Disable time limit
        set_time_limit(0);
        ob_end_flush();

        $lastCheck = date('Y-m-d H:i:s');

        // Send initial connection event
        echo "event: connected\n";
        echo "data: " . json_encode([
            'type' => 'connected',
            'user_id' => $userId,
            'timestamp' => time()
        ]) . "\n\n";
        flush();

        // Keep connection alive and check for new notifications
        $startTime = time();
        $maxDuration = 300; // 5 minutes max per connection

        while (time() - $startTime < $maxDuration) {
            if (connection_aborted()) {
                break;
            }

            $notifications = $this->getRecentNotifications($tenantId, $branchId, $lastCheck, $role);

            if (!empty($notifications)) {
                foreach ($notifications as $notification) {
                    echo "event: notification\n";
                    echo "data: " . json_encode($notification) . "\n\n";
                    flush();
                }
                $lastCheck = date('Y-m-d H:i:s');
            }

            // Send heartbeat every 15 seconds
            echo ": heartbeat\n\n";
            flush();

            sleep(5);
        }

        // Send close event
        echo "event: close\n";
        echo "data: " . json_encode(['type' => 'timeout', 'message' => 'Connection timeout, please reconnect']) . "\n\n";
        flush();
        exit;
    }

    /**
     * Get recent notifications since last check
     */
    private function getRecentNotifications($tenantId, $branchId, $lastCheck, $role)
    {
        $notifications = [];
        $pdo = $this->db->connect();

        try {
            // Check for new orders
            $sql = "SELECT order_id, order_number, order_type, total_amount, status, created_at
                    FROM orders
                    WHERE tenant_id = ? AND created_at > ?";
            $params = [$tenantId, $lastCheck];

            if ($branchId) {
                $sql .= " AND (branch_id = ? OR branch_id IS NULL)";
                $params[] = $branchId;
            }

            $sql .= " ORDER BY created_at DESC LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            while ($order = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $notifications[] = [
                    'type' => 'new_order',
                    'order_id' => $order['order_id'],
                    'order_number' => $order['order_number'],
                    'order_type' => $order['order_type'],
                    'total_amount' => $order['total_amount'],
                    'status' => $order['status'],
                    'timestamp' => strtotime($order['created_at'])
                ];
            }

            // Check for updated order statuses
            $sql = "SELECT order_id, order_number, status, updated_at
                    FROM orders
                    WHERE tenant_id = ? AND updated_at > ? AND status != 'pending'
                    ORDER BY updated_at DESC LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tenantId, $lastCheck]);

            while ($order = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $notifications[] = [
                    'type' => 'order_update',
                    'order_id' => $order['order_id'],
                    'order_number' => $order['order_number'],
                    'status' => $order['status'],
                    'timestamp' => strtotime($order['updated_at'])
                ];
            }

            // Check for low stock alerts (for kitchen/inventory staff)
            if (in_array($role, ['Administrator', 'Manager', 'Kitchen Staff', 'Inventory Staff'])) {
                $sql = "SELECT item_name, current_stock, min_stock_level, unit
                        FROM inventory_items
                        WHERE tenant_id = ? AND current_stock <= min_stock_level AND updated_at > ?";
                $params = [$tenantId, $lastCheck];
                if ($branchId) {
                    $sql .= " AND (branch_id = ? OR branch_id IS NULL)";
                    $params[] = $branchId;
                }
                $sql .= " LIMIT 5";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                while ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $notifications[] = [
                        'type' => 'low_stock',
                        'item_name' => $item['item_name'],
                        'current_stock' => $item['current_stock'],
                        'min_stock_level' => $item['min_stock_level'],
                        'unit' => $item['unit'],
                        'timestamp' => time()
                    ];
                }
            }

            // Check for new reservations
            $sql = "SELECT reservation_id, customer_name, party_size, reservation_date, reservation_time
                    FROM reservations
                    WHERE tenant_id = ? AND created_at > ?";
            $params = [$tenantId, $lastCheck];
            if ($branchId) {
                $sql .= " AND (branch_id = ? OR branch_id IS NULL)";
                $params[] = $branchId;
            }
            $sql .= " ORDER BY created_at DESC LIMIT 5";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            while ($reservation = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $notifications[] = [
                    'type' => 'new_reservation',
                    'reservation_id' => $reservation['reservation_id'],
                    'customer_name' => $reservation['customer_name'],
                    'party_size' => $reservation['party_size'],
                    'date' => $reservation['reservation_date'],
                    'time' => $reservation['reservation_time'],
                    'timestamp' => time()
                ];
            }
        } catch (\Exception $e) {
            error_log("Notification check error: " . $e->getMessage());
        }

        return $notifications;
    }

    /**
     * Get notification history (paginated)
     * GET /api/v1/notifications
     */
    public function getNotifications($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            // Get recent orders as notifications
            $stmt = $pdo->prepare("
                SELECT 'order' as notification_type, order_id, order_number, status, total_amount, created_at
                FROM orders
                WHERE tenant_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$tenantId, $limit, $offset]);
            $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'data' => $notifications,
                'page' => $page,
                'limit' => $limit
            ], 'Notifications retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve notifications: ' . $e->getMessage());
        }
    }

    /**
     * Mark notification as read
     * POST /api/v1/notifications/{id}/read
     */
    public function markAsRead($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $notificationId = $request['id'] ?? 0;

            $pdo = $this->db->connect();
            $stmt = $pdo->prepare("
                INSERT INTO notification_reads (notification_id, user_id, read_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE read_at = NOW()
            ");
            $stmt->execute([$notificationId, $payload['user_id']]);

            return Response::success([], 'Notification marked as read');
        } catch (\Exception $e) {
            return Response::error('Failed to mark notification: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notification count
     * GET /api/v1/notifications/unread-count
     */
    public function getUnreadCount($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;

            // Count recent orders not marked as read
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as unread
                FROM orders o
                LEFT JOIN notification_reads nr ON o.order_id = nr.notification_id AND nr.user_id = ?
                WHERE o.tenant_id = ? AND nr.notification_id IS NULL
                AND o.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stmt->execute([$payload['user_id'], $tenantId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return Response::success([
                'unread_count' => (int)($result['unread'] ?? 0)
            ], 'Unread count retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to get unread count: ' . $e->getMessage());
        }
    }
}
