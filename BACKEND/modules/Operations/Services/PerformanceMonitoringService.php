<?php

namespace App\Modules\Operations\Services;

use App\Core\Database;
use PDO;

class PerformanceMonitoringService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getPerformanceMetrics($tenantId, $branchId, $date = null, $stationId = null)
    {
        $sql = "SELECT pm.*, st.station_name 
                FROM performance_metrics pm 
                LEFT JOIN kitchen_stations st ON pm.station_id = st.station_id 
                WHERE pm.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND pm.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($date) {
            $sql .= " AND pm.date = :date";
            $params[':date'] = $date;
        }
        
        if ($stationId) {
            $sql .= " AND pm.station_id = :station_id";
            $params[':station_id'] = $stationId;
        }
        
        $sql .= " ORDER BY pm.date DESC, pm.hour DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function recordOrderTiming($orderId, $tenantId, $branchId, $stationId, $orderPlacedAt, $sentToKitchenAt, $prepStartedAt, $readyAt, $servedAt, $estimatedTime)
    {
        $totalPrepTime = null;
        $totalServiceTime = null;
        $timeVariance = null;
        $isOnTime = 1;
        
        if ($prepStartedAt && $readyAt) {
            $totalPrepTime = strtotime($readyAt) - strtotime($prepStartedAt);
        }
        
        if ($orderPlacedAt && $servedAt) {
            $totalServiceTime = strtotime($servedAt) - strtotime($orderPlacedAt);
        }
        
        if ($estimatedTime && $totalServiceTime) {
            $timeVariance = $totalServiceTime - ($estimatedTime * 60); // Convert to seconds
            $isOnTime = $timeVariance <= 300; // Allow 5 minutes variance
        }
        
        $sql = "INSERT INTO order_timing_metrics (order_id, tenant_id, branch_id, order_placed_at, sent_to_kitchen_at, prep_started_at, ready_at, served_at, total_prep_time, total_service_time, estimated_time, time_variance, is_on_time) 
                VALUES (:order_id, :tenant_id, :branch_id, :order_placed_at, :sent_to_kitchen_at, :prep_started_at, :ready_at, :served_at, :total_prep_time, :total_service_time, :estimated_time, :time_variance, :is_on_time)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':order_id' => $orderId,
            ':tenant_id' => $tenantId,
            ':branch_id' => $branchId,
            ':order_placed_at' => $orderPlacedAt,
            ':sent_to_kitchen_at' => $sentToKitchenAt,
            ':prep_started_at' => $prepStartedAt,
            ':ready_at' => $readyAt,
            ':served_at' => $servedAt,
            ':total_prep_time' => $totalPrepTime,
            ':total_service_time' => $totalServiceTime,
            ':estimated_time' => $estimatedTime,
            ':time_variance' => $timeVariance,
            ':is_on_time' => $isOnTime
        ]);
    }

    public function calculateHourlyMetrics($tenantId, $branchId, $date, $hour, $stationId = null)
    {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN served_at IS NOT NULL THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN served_at IS NULL THEN 1 ELSE 0 END) as cancelled_orders,
                    AVG(total_prep_time) as avg_prep_time,
                    AVG(total_service_time) as avg_order_time,
                    SUM(CASE WHEN is_on_time = 0 THEN 1 ELSE 0 END) as error_count,
                    AVG(CASE WHEN is_on_time = 0 THEN 1 ELSE 0 END) * 100 as error_rate,
                    AVG(CASE WHEN is_on_time = 1 THEN 1 ELSE 0 END) * 100 as on_time_rate
                FROM order_timing_metrics 
                WHERE tenant_id = :tenant_id 
                AND DATE(order_placed_at) = :date 
                AND HOUR(order_placed_at) = :hour";
        $params = [':tenant_id' => $tenantId, ':date' => $date, ':hour' => $hour];
        
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($stationId) {
            $sql .= " AND station_id = :station_id";
            $params[':station_id'] = $stationId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $metrics = $stmt->fetch();
        
        if (!$metrics || $metrics['total_orders'] == 0) return false;
        
        // Determine bottleneck
        $bottleneckFlag = ($metrics['error_rate'] > 15 || $metrics['on_time_rate'] < 85) ? 1 : 0;
        
        // Check if metric exists
        $sql = "SELECT metric_id FROM performance_metrics 
                WHERE tenant_id = :tenant_id AND branch_id = :branch_id 
                AND date = :date AND hour = :hour";
        if ($stationId) {
            $sql .= " AND station_id = :station_id";
        }
        
        $checkParams = [':tenant_id' => $tenantId, ':branch_id' => $branchId, ':date' => $date, ':hour' => $hour];
        if ($stationId) $checkParams[':station_id'] = $stationId;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($checkParams);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $sql = "UPDATE performance_metrics 
                    SET total_orders = :total_orders, completed_orders = :completed_orders, 
                        cancelled_orders = :cancelled_orders, avg_order_time = :avg_order_time, 
                        avg_prep_time = :avg_prep_time, error_count = :error_count, 
                        error_rate = :error_rate, on_time_rate = :on_time_rate, 
                        bottleneck_flag = :bottleneck_flag 
                    WHERE metric_id = :metric_id";
            $params = [
                ':total_orders' => $metrics['total_orders'],
                ':completed_orders' => $metrics['completed_orders'],
                ':cancelled_orders' => $metrics['cancelled_orders'],
                ':avg_order_time' => $metrics['avg_order_time'],
                ':avg_prep_time' => $metrics['avg_prep_time'],
                ':error_count' => $metrics['error_count'],
                ':error_rate' => $metrics['error_rate'],
                ':on_time_rate' => $metrics['on_time_rate'],
                ':bottleneck_flag' => $bottleneckFlag,
                ':metric_id' => $existing['metric_id']
            ];
        } else {
            $sql = "INSERT INTO performance_metrics (tenant_id, branch_id, station_id, date, hour, total_orders, completed_orders, cancelled_orders, avg_order_time, avg_prep_time, error_count, error_rate, on_time_rate, bottleneck_flag) 
                    VALUES (:tenant_id, :branch_id, :station_id, :date, :hour, :total_orders, :completed_orders, :cancelled_orders, :avg_order_time, :avg_prep_time, :error_count, :error_rate, :on_time_rate, :bottleneck_flag)";
            $params = [
                ':tenant_id' => $tenantId,
                ':branch_id' => $branchId,
                ':station_id' => $stationId,
                ':date' => $date,
                ':hour' => $hour,
                ':total_orders' => $metrics['total_orders'],
                ':completed_orders' => $metrics['completed_orders'],
                ':cancelled_orders' => $metrics['cancelled_orders'],
                ':avg_order_time' => $metrics['avg_order_time'],
                ':avg_prep_time' => $metrics['avg_prep_time'],
                ':error_count' => $metrics['error_count'],
                ':error_rate' => $metrics['error_rate'],
                ':on_time_rate' => $metrics['on_time_rate'],
                ':bottleneck_flag' => $bottleneckFlag
            ];
        }
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function getBottlenecks($tenantId, $branchId, $days = 7)
    {
        $sql = "SELECT pm.*, st.station_name 
                FROM performance_metrics pm 
                LEFT JOIN kitchen_stations st ON pm.station_id = st.station_id 
                WHERE pm.tenant_id = :tenant_id 
                AND pm.bottleneck_flag = 1 
                AND pm.date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";
        $params = [':tenant_id' => $tenantId, ':days' => $days];
        
        if ($branchId) {
            $sql .= " AND pm.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY pm.date DESC, pm.hour DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPerformanceSummary($tenantId, $branchId, $days = 30)
    {
        $sql = "SELECT 
                    DATE(order_placed_at) as date,
                    COUNT(*) as total_orders,
                    AVG(total_service_time) as avg_service_time,
                    AVG(CASE WHEN is_on_time = 1 THEN 1 ELSE 0 END) * 100 as on_time_rate,
                    SUM(CASE WHEN is_on_time = 0 THEN 1 ELSE 0 END) as late_orders
                FROM order_timing_metrics 
                WHERE tenant_id = :tenant_id 
                AND DATE(order_placed_at) >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";
        $params = [':tenant_id' => $tenantId, ':days' => $days];
        
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(order_placed_at) ORDER BY date DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
