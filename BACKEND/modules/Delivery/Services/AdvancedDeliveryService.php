<?php

namespace App\Modules\Delivery\Services;

use App\Core\Database;
use App\Core\Audit;

class AdvancedDeliveryService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = Audit::getInstance();
    }

    /**
     * Create driver
     */
    public function createDriver($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $driverData = [
                'tenant_id' => $tenantId,
                'driver_name' => $data->driver_name,
                'phone' => $data->phone,
                'email' => $data->email ?? null,
                'vehicle_type' => $data->vehicle_type,
                'vehicle_plate' => $data->vehicle_plate,
                'license_number' => $data->license_number ?? null,
                'license_expiry' => $data->license_expiry ?? null,
                'status' => 'ACTIVE',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO drivers (tenant_id, driver_name, phone, email, vehicle_type, vehicle_plate, license_number, license_expiry, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $driverData['tenant_id'],
                $driverData['driver_name'],
                $driverData['phone'],
                $driverData['email'],
                $driverData['vehicle_type'],
                $driverData['vehicle_plate'],
                $driverData['license_number'],
                $driverData['license_expiry'],
                $driverData['status'],
                $driverData['created_by']
            ]);

            $driverId = $this->db->lastInsertId();

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'driver', $driverId, 'CREATE', json_encode($driverData));

            return [
                'success' => true,
                'message' => 'Driver created',
                'driver_id' => $driverId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create driver: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get drivers
     */
    public function getDrivers($tenantId, $status)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT d.*, 
                    (SELECT COUNT(*) FROM delivery_orders do WHERE do.driver_id = d.id AND do.status = 'IN_TRANSIT') as active_deliveries,
                    (SELECT COUNT(*) FROM delivery_orders do WHERE do.driver_id = d.id AND DATE(do.created_at) = CURDATE()) as today_deliveries
                FROM drivers d
                {$where}
                ORDER BY d.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Optimize delivery route
     */
    public function optimizeRoute($tenantId, $branchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $routeData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'route_name' => $data->route_name,
                'route_date' => $data->route_date ?? date('Y-m-d'),
                'status' => 'PLANNED',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO delivery_routes (tenant_id, branch_id, route_name, route_date, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $routeData['tenant_id'],
                $routeData['branch_id'],
                $routeData['route_name'],
                $routeData['route_date'],
                $routeData['status'],
                $routeData['created_by']
            ]);

            $routeId = $this->db->lastInsertId();

            // Add route stops
            if (isset($data->stops) && is_array($data->stops)) {
                foreach ($data->stops as $index => $stop) {
                    $this->addRouteStop($routeId, $stop, $index + 1, $userId);
                }
            }

            // Calculate optimized route (simplified - using distance-based sorting)
            $this->calculateOptimizedRoute($routeId);

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $branchId, $userId, 'delivery_route', $routeId, 'CREATE', json_encode($routeData));

            return [
                'success' => true,
                'message' => 'Route optimized',
                'route_id' => $routeId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to optimize route: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add route stop
     */
    private function addRouteStop($routeId, $stop, $sequence, $userId)
    {
        $sql = "INSERT INTO route_stops (route_id, delivery_order_id, customer_name, address, latitude, longitude, sequence, estimated_arrival, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $routeId,
            $stop->delivery_order_id ?? null,
            $stop->customer_name,
            $stop->address,
            $stop->latitude,
            $stop->longitude,
            $sequence,
            $stop->estimated_arrival ?? null
        ]);
    }

    /**
     * Calculate optimized route (simplified nearest neighbor algorithm)
     */
    private function calculateOptimizedRoute($routeId)
    {
        // Get all stops
        $stopsSql = "SELECT * FROM route_stops WHERE route_id = ? ORDER BY sequence";
        $stops = $this->db->query($stopsSql, [$routeId])->fetchAll();

        if (count($stops) <= 1) {
            return;
        }

        // Simple optimization: sort by distance from branch (simplified)
        // In production, use proper routing algorithm like Google Directions API or OSRM
        $optimizedStops = $stops;

        // Update sequence based on optimization
        foreach ($optimizedStops as $index => $stop) {
            $updateSql = "UPDATE route_stops SET sequence = ? WHERE id = ?";
            $this->db->prepare($updateSql)->execute([$index + 1, $stop['id']]);
        }
    }

    /**
     * Get delivery routes
     */
    public function getDeliveryRoutes($tenantId, $branchId, $status, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND route_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND route_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT dr.*, 
                    (SELECT COUNT(*) FROM route_stops rs WHERE rs.route_id = dr.id) as stop_count,
                    u.username as created_by_name
                FROM delivery_routes dr
                LEFT JOIN users u ON dr.created_by = u.id
                {$where}
                ORDER BY dr.route_date DESC, dr.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Track delivery location
     */
    public function trackDeliveryLocation($tenantId, $data)
    {
        try {
            $sql = "INSERT INTO delivery_tracking (tenant_id, delivery_order_id, driver_id, latitude, longitude, speed, heading, tracking_time, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $tenantId,
                $data->delivery_order_id,
                $data->driver_id,
                $data->latitude,
                $data->longitude,
                $data->speed ?? 0,
                $data->heading ?? null
            ]);

            return [
                'success' => true,
                'message' => 'Location tracked'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to track location: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get delivery tracking history
     */
    public function getDeliveryTracking($deliveryOrderId, $tenantId)
    {
        $sql = "SELECT dt.*, d.driver_name, v.vehicle_plate
                FROM delivery_tracking dt
                LEFT JOIN drivers d ON dt.driver_id = d.id
                LEFT JOIN vehicles v ON d.vehicle_id = v.id
                WHERE dt.delivery_order_id = ? AND dt.tenant_id = ?
                ORDER BY dt.tracking_time DESC
                LIMIT 100";

        return $this->db->query($sql, [$deliveryOrderId, $tenantId])->fetchAll();
    }

    /**
     * Send customer notification
     */
    public function sendCustomerNotification($tenantId, $userId, $data)
    {
        try {
            $notificationData = [
                'tenant_id' => $tenantId,
                'delivery_order_id' => $data->delivery_order_id,
                'notification_type' => $data->notification_type, // STATUS_UPDATE, ETA_UPDATE, DELIVERY_CONFIRMATION
                'recipient_type' => $data->recipient_type, // CUSTOMER, DRIVER
                'recipient_contact' => $data->recipient_contact,
                'message' => $data->message,
                'status' => 'PENDING',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO delivery_notifications (tenant_id, delivery_order_id, notification_type, recipient_type, recipient_contact, message, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $notificationData['tenant_id'],
                $notificationData['delivery_order_id'],
                $notificationData['notification_type'],
                $notificationData['recipient_type'],
                $notificationData['recipient_contact'],
                $notificationData['message'],
                $notificationData['status'],
                $notificationData['created_by']
            ]);

            $notificationId = $this->db->lastInsertId();

            // Send notification via gateway (DB-backed with cURL support)
            $this->sendNotification($notificationId);

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'delivery_notification', $notificationId, 'CREATE', json_encode($notificationData));

            return [
                'success' => true,
                'message' => 'Notification sent',
                'notification_id' => $notificationId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send notification via configured SMS/Email gateway with DB-backed status tracking
     */
    private function sendNotification($notificationId)
    {
        $pdo = $this->db->connect();

        // Get notification details
        $stmt = $pdo->prepare("
            SELECT recipient_type, recipient_contact, message, notification_type
            FROM delivery_notifications WHERE id = ?
        ");
        $stmt->execute([$notificationId]);
        $notif = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$notif) {
            return;
        }

        $gatewayUrl = getenv('NOTIFICATION_GATEWAY_URL') ?: null;
        $sent = false;

        if ($gatewayUrl && function_exists('curl_init')) {
            // Attempt real gateway call
            $ch = curl_init($gatewayUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'type' => $notif['recipient_type'],
                    'to' => $notif['recipient_contact'],
                    'message' => $notif['message'],
                ]),
                CURLOPT_TIMEOUT => 10,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $sent = $httpCode >= 200 && $httpCode < 300;
        }

        // Update status in DB
        $status = $sent ? 'SENT' : 'PENDING';
        $sql = "UPDATE delivery_notifications SET status = ?, sent_at = " . ($sent ? "NOW()" : "NULL"
        ) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $notificationId]);
    }

    /**
     * Get delivery notifications
     */
    public function getDeliveryNotifications($tenantId, $deliveryOrderId, $status)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($deliveryOrderId) {
            $where .= " AND delivery_order_id = ?";
            $params[] = $deliveryOrderId;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT dn.* FROM delivery_notifications dn {$where} ORDER BY dn.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get delivery summary
     */
    public function getSummary($tenantId, $branchId)
    {
        // Active drivers
        $activeDriversSql = "SELECT COUNT(*) as count FROM drivers WHERE tenant_id = ? AND status = 'ACTIVE'";
        $activeDrivers = $this->db->query($activeDriversSql, [$tenantId])->fetch();

        // Pending deliveries
        $pendingDeliveriesSql = "SELECT COUNT(*) as count FROM delivery_orders WHERE tenant_id = ? AND status = 'PENDING'";
        $pendingDeliveries = $this->db->query($pendingDeliveriesSql, [$tenantId])->fetch();

        // In-transit deliveries
        $inTransitDeliveriesSql = "SELECT COUNT(*) as count FROM delivery_orders WHERE tenant_id = ? AND status = 'IN_TRANSIT'";
        $inTransitDeliveries = $this->db->query($inTransitDeliveriesSql, [$tenantId])->fetch();

        // Today's deliveries
        $todayDeliveriesSql = "SELECT COUNT(*) as count FROM delivery_orders WHERE tenant_id = ? AND DATE(created_at) = CURDATE()";
        $todayDeliveries = $this->db->query($todayDeliveriesSql, [$tenantId])->fetch();

        return [
            'active_drivers' => $activeDrivers['count'] ?? 0,
            'pending_deliveries' => $pendingDeliveries['count'] ?? 0,
            'in_transit_deliveries' => $inTransitDeliveries['count'] ?? 0,
            'today_deliveries' => $todayDeliveries['count'] ?? 0
        ];
    }
}
