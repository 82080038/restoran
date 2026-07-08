<?php

namespace App\Modules\Infrastructure\Services;

use App\Core\Database;
use App\Core\Audit;

class InfrastructureMonitoringService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = new Audit();
    }

    /**
     * Record performance metrics
     */
    public function recordPerformanceMetrics($tenantId, $data)
    {
        try {
            $this->db->beginTransaction();

            $metricsData = [
                'tenant_id' => $tenantId,
                'server_id' => $data->server_id ?? 'default',
                'cpu_usage_percent' => $data->cpu_usage_percent,
                'memory_usage_percent' => $data->memory_usage_percent,
                'disk_usage_percent' => $data->disk_usage_percent,
                'network_in_bytes' => $data->network_in_bytes ?? 0,
                'network_out_bytes' => $data->network_out_bytes ?? 0,
                'active_connections' => $data->active_connections ?? 0,
                'request_rate_per_second' => $data->request_rate_per_second ?? 0,
                'response_time_avg_ms' => $data->response_time_avg_ms,
                'error_rate_percent' => $data->error_rate_percent ?? 0,
                'cache_hit_rate_percent' => $data->cache_hit_rate_percent ?? 0,
                'database_connections' => $data->database_connections ?? 0,
                'database_query_time_avg_ms' => $data->database_query_time_avg_ms
            ];

            $sql = "INSERT INTO performance_metrics (tenant_id, server_id, cpu_usage_percent, memory_usage_percent, disk_usage_percent, network_in_bytes, network_out_bytes, active_connections, request_rate_per_second, response_time_avg_ms, error_rate_percent, cache_hit_rate_percent, database_connections, database_query_time_avg_ms, recorded_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $metricsData['tenant_id'],
                $metricsData['server_id'],
                $metricsData['cpu_usage_percent'],
                $metricsData['memory_usage_percent'],
                $metricsData['disk_usage_percent'],
                $metricsData['network_in_bytes'],
                $metricsData['network_out_bytes'],
                $metricsData['active_connections'],
                $metricsData['request_rate_per_second'],
                $metricsData['response_time_avg_ms'],
                $metricsData['error_rate_percent'],
                $metricsData['cache_hit_rate_percent'],
                $metricsData['database_connections'],
                $metricsData['database_query_time_avg_ms']
            ]);

            $metricsId = $this->db->lastInsertId();

            // Check for alerts
            $this->checkPerformanceAlerts($tenantId, $metricsData);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Performance metrics recorded',
                'metrics_id' => $metricsId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to record metrics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check performance alerts
     */
    private function checkPerformanceAlerts($tenantId, $metrics)
    {
        $alerts = [];

        if ($metrics['cpu_usage_percent'] > 80) {
            $alerts[] = [
                'alert_type' => 'CPU_HIGH',
                'severity' => $metrics['cpu_usage_percent'] > 90 ? 'CRITICAL' : 'HIGH',
                'message' => 'CPU usage is ' . $metrics['cpu_usage_percent'] . '%'
            ];
        }

        if ($metrics['memory_usage_percent'] > 85) {
            $alerts[] = [
                'alert_type' => 'MEMORY_HIGH',
                'severity' => $metrics['memory_usage_percent'] > 95 ? 'CRITICAL' : 'HIGH',
                'message' => 'Memory usage is ' . $metrics['memory_usage_percent'] . '%'
            ];
        }

        if ($metrics['response_time_avg_ms'] > 1000) {
            $alerts[] = [
                'alert_type' => 'RESPONSE_TIME_HIGH',
                'severity' => $metrics['response_time_avg_ms'] > 3000 ? 'CRITICAL' : 'HIGH',
                'message' => 'Response time is ' . $metrics['response_time_avg_ms'] . 'ms'
            ];
        }

        if ($metrics['error_rate_percent'] > 5) {
            $alerts[] = [
                'alert_type' => 'ERROR_RATE_HIGH',
                'severity' => $metrics['error_rate_percent'] > 10 ? 'CRITICAL' : 'HIGH',
                'message' => 'Error rate is ' . $metrics['error_rate_percent'] . '%'
            ];
        }

        // Create alerts if needed
        foreach ($alerts as $alert) {
            $sql = "INSERT INTO infrastructure_alerts (tenant_id, alert_type, severity, message, status, created_at)
                    VALUES (?, ?, ?, ?, 'OPEN', NOW())";
            $this->db->prepare($sql)->execute([
                $tenantId,
                $alert['alert_type'],
                $alert['severity'],
                $alert['message']
            ]);
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics($tenantId, $serverId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($serverId) {
            $where .= " AND server_id = ?";
            $params[] = $serverId;
        }
        
        if ($dateFrom) {
            $where .= " AND recorded_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND recorded_at <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT * FROM performance_metrics {$where} ORDER BY recorded_at DESC LIMIT 1000";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get infrastructure alerts
     */
    public function getAlerts($tenantId, $status)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT * FROM infrastructure_alerts {$where} ORDER BY created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get infrastructure summary
     */
    public function getSummary($tenantId)
    {
        // Current metrics (last 5 minutes)
        $currentMetricsSql = "SELECT AVG(cpu_usage_percent) as cpu, AVG(memory_usage_percent) as memory, AVG(response_time_avg_ms) as response_time
                              FROM performance_metrics 
                              WHERE tenant_id = ? AND recorded_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $currentMetrics = $this->db->query($currentMetricsSql, [$tenantId])->fetch();

        // Open alerts
        $openAlertsSql = "SELECT COUNT(*) as count FROM infrastructure_alerts WHERE tenant_id = ? AND status = 'OPEN'";
        $openAlerts = $this->db->query($openAlertsSql, [$tenantId])->fetch();

        // Critical alerts
        $criticalAlertsSql = "SELECT COUNT(*) as count FROM infrastructure_alerts WHERE tenant_id = ? AND severity = 'CRITICAL' AND status = 'OPEN'";
        $criticalAlerts = $this->db->query($criticalAlertsSql, [$tenantId])->fetch();

        // Average response time today
        $avgResponseSql = "SELECT AVG(response_time_avg_ms) as avg FROM performance_metrics WHERE tenant_id = ? AND DATE(recorded_at) = CURDATE()";
        $avgResponse = $this->db->query($avgResponseSql, [$tenantId])->fetch();

        // Uptime calculation (simplified - based on error rate)
        $uptimeSql = "SELECT AVG(error_rate_percent) as avg_error FROM performance_metrics WHERE tenant_id = ? AND DATE(recorded_at) = CURDATE()";
        $avgError = $this->db->query($uptimeSql, [$tenantId])->fetch();
        $uptime = 100 - ($avgError['avg_error'] ?? 0);

        return [
            'current_cpu_usage' => round($currentMetrics['cpu'] ?? 0, 2),
            'current_memory_usage' => round($currentMetrics['memory'] ?? 0, 2),
            'current_response_time' => round($currentMetrics['response_time'] ?? 0, 2),
            'open_alerts' => $openAlerts['count'] ?? 0,
            'critical_alerts' => $criticalAlerts['count'] ?? 0,
            'today_avg_response_time' => round($avgResponse['avg'] ?? 0, 2),
            'uptime_percentage' => round($uptime, 2)
        ];
    }

    /**
     * Get performance report
     */
    public function getPerformanceReport($tenantId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND recorded_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND recorded_at <= ?";
            $params[] = $dateTo;
        }

        // Average CPU
        $avgCpuSql = "SELECT AVG(cpu_usage_percent) as avg, MAX(cpu_usage_percent) as max FROM performance_metrics {$where}";
        $cpu = $this->db->query($avgCpuSql, $params)->fetch();

        // Average memory
        $avgMemorySql = "SELECT AVG(memory_usage_percent) as avg, MAX(memory_usage_percent) as max FROM performance_metrics {$where}";
        $memory = $this->db->query($avgMemorySql, $params)->fetch();

        // Average response time
        $avgResponseSql = "SELECT AVG(response_time_avg_ms) as avg, MAX(response_time_avg_ms) as max FROM performance_metrics {$where}";
        $response = $this->db->query($avgResponseSql, $params)->fetch();

        // Total requests
        $totalRequestsSql = "SELECT SUM(request_rate_per_second * 60) as total FROM performance_metrics {$where}";
        $totalRequests = $this->db->query($totalRequestsSql, $params)->fetch();

        // Average cache hit rate
        $avgCacheSql = "SELECT AVG(cache_hit_rate_percent) as avg FROM performance_metrics {$where}";
        $cache = $this->db->query($avgCacheSql, $params)->fetch();

        return [
            'avg_cpu_usage' => round($cpu['avg'] ?? 0, 2),
            'max_cpu_usage' => round($cpu['max'] ?? 0, 2),
            'avg_memory_usage' => round($memory['avg'] ?? 0, 2),
            'max_memory_usage' => round($memory['max'] ?? 0, 2),
            'avg_response_time' => round($response['avg'] ?? 0, 2),
            'max_response_time' => round($response['max'] ?? 0, 2),
            'total_requests' => round($totalRequests['total'] ?? 0),
            'avg_cache_hit_rate' => round($cache['avg'] ?? 0, 2)
        ];
    }
}
