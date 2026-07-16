<?php

namespace App\Modules\Operations\Services;

use App\Core\Database;
use PDO;

class LoadBalancingService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function recordStationLoad($tenantId, $branchId, $stationId)
    {
        // Get current load metrics
        $sql = "SELECT COUNT(*) as active_tickets, COUNT(CASE WHEN item_status = 'PENDING' THEN 1 END) as pending_items 
                FROM kds_tickets t 
                LEFT JOIN kds_ticket_items ti ON t.ticket_id = ti.ticket_id 
                WHERE t.tenant_id = :tenant_id AND t.branch_id = :branch_id 
                AND t.station_id = :station_id AND t.ticket_status IN ('NEW', 'IN_PROGRESS')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId, ':station_id' => $stationId]);
        $loadData = $stmt->fetch();
        
        // Calculate load level
        $activeTickets = $loadData['active_tickets'] ?? 0;
        $pendingItems = $loadData['pending_items'] ?? 0;
        
        if ($activeTickets >= 15 || $pendingItems >= 30) {
            $loadLevel = 'OVERLOADED';
            $capacityUtilization = 100.00;
        } elseif ($activeTickets >= 10 || $pendingItems >= 20) {
            $loadLevel = 'HIGH';
            $capacityUtilization = 75.00;
        } elseif ($activeTickets >= 5 || $pendingItems >= 10) {
            $loadLevel = 'MEDIUM';
            $capacityUtilization = 50.00;
        } else {
            $loadLevel = 'LOW';
            $capacityUtilization = 25.00;
        }
        
        // Record metric
        $sql = "INSERT INTO station_load_metrics (tenant_id, branch_id, station_id, active_tickets, pending_items, capacity_utilization, load_level) 
                VALUES (:tenant_id, :branch_id, :station_id, :active_tickets, :pending_items, :capacity_utilization, :load_level)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':branch_id' => $branchId,
            ':station_id' => $stationId,
            ':active_tickets' => $activeTickets,
            ':pending_items' => $pendingItems,
            ':capacity_utilization' => $capacityUtilization,
            ':load_level' => $loadLevel
        ]);
        
        return $loadLevel;
    }

    public function getStationLoadMetrics($tenantId, $branchId, $stationId = null, $minutes = 60)
    {
        $sql = "SELECT slm.*, st.station_name 
                FROM station_load_metrics slm 
                LEFT JOIN kitchen_stations st ON slm.station_id = st.station_id 
                WHERE slm.tenant_id = :tenant_id 
                AND slm.timestamp >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";
        $params = [':tenant_id' => $tenantId, ':minutes' => $minutes];
        
        if ($branchId) {
            $sql .= " AND slm.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($stationId) {
            $sql .= " AND slm.station_id = :station_id";
            $params[':station_id'] = $stationId;
        }
        
        $sql .= " ORDER BY slm.timestamp DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getLeastLoadedStation($tenantId, $branchId, $stationType = null)
    {
        $sql = "SELECT slm.station_id, st.station_name, st.kitchen_category, 
                AVG(slm.active_tickets) as avg_tickets, 
                AVG(slm.capacity_utilization) as avg_utilization,
                MAX(slm.load_level) as max_load_level
                FROM station_load_metrics slm 
                LEFT JOIN kitchen_stations st ON slm.station_id = st.station_id 
                WHERE slm.tenant_id = :tenant_id 
                AND slm.timestamp >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND slm.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($stationType) {
            $sql .= " AND st.kitchen_category = :station_type";
            $params[':station_type'] = $stationType;
        }
        
        $sql .= " GROUP BY slm.station_id, st.station_name, st.kitchen_category 
                ORDER BY avg_utilization ASC, avg_tickets ASC 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function recommendReroute($tenantId, $branchId, $currentStationId)
    {
        $currentLoad = $this->recordStationLoad($tenantId, $branchId, $currentStationId);
        
        if ($currentLoad !== 'OVERLOADED' && $currentLoad !== 'HIGH') {
            return null; // No reroute needed
        }
        
        // Get station type
        $sql = "SELECT kitchen_category FROM kitchen_stations WHERE station_id = :station_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':station_id' => $currentStationId]);
        $station = $stmt->fetch();
        
        if (!$station) return null;
        
        // Find least loaded station of same type
        $alternativeStation = $this->getLeastLoadedStation($tenantId, $branchId, $station['kitchen_category']);
        
        if (!$alternativeStation || $alternativeStation['station_id'] == $currentStationId) {
            return null;
        }
        
        return [
            'current_station_id' => $currentStationId,
            'current_load_level' => $currentLoad,
            'recommended_station_id' => $alternativeStation['station_id'],
            'recommended_station_name' => $alternativeStation['station_name'],
            'recommended_utilization' => $alternativeStation['avg_utilization']
        ];
    }

    public function getBottleneckStations($tenantId, $branchId)
    {
        $sql = "SELECT slm.station_id, st.station_name, st.kitchen_category,
                COUNT(*) as overload_count,
                AVG(slm.active_tickets) as avg_tickets,
                AVG(slm.capacity_utilization) as avg_utilization
                FROM station_load_metrics slm 
                LEFT JOIN kitchen_stations st ON slm.station_id = st.station_id 
                WHERE slm.tenant_id = :tenant_id 
                AND slm.load_level IN ('HIGH', 'OVERLOADED')
                AND slm.timestamp >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND slm.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY slm.station_id, st.station_name, st.kitchen_category 
                ORDER BY overload_count DESC, avg_utilization DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
