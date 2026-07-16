<?php

namespace App\Modules\Operations\Services;

use App\Core\Database;
use PDO;

class TableTurnoverService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getTurnoverMetrics($tenantId, $branchId, $date = null, $tableId = null)
    {
        $sql = "SELECT ttm.*, t.table_number, z.zone_name 
                FROM table_turnover_metrics ttm 
                LEFT JOIN tables t ON ttm.table_id = t.table_id 
                LEFT JOIN zones z ON ttm.zone_id = z.zone_id 
                WHERE ttm.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND ttm.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($date) {
            $sql .= " AND ttm.date = :date";
            $params[':date'] = $date;
        }
        
        if ($tableId) {
            $sql .= " AND ttm.table_id = :table_id";
            $params[':table_id'] = $tableId;
        }
        
        $sql .= " ORDER BY ttm.date DESC, ttm.hour DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function recordTurnover($tenantId, $branchId, $tableId, $zoneId, $seatTime, $revenue)
    {
        $date = date('Y-m-d');
        $hour = date('H');
        
        // Check if metric exists for this date/hour/table
        $sql = "SELECT metric_id, turnover_count, avg_seat_time, revenue_per_turnover 
                FROM table_turnover_metrics 
                WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND table_id = :table_id 
                AND date = :date AND hour = :hour";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':branch_id' => $branchId,
            ':table_id' => $tableId,
            ':date' => $date,
            ':hour' => $hour
        ]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing metric
            $newCount = $existing['turnover_count'] + 1;
            $newAvgSeatTime = ($existing['avg_seat_time'] * $existing['turnover_count'] + $seatTime) / $newCount;
            $newRevenue = $existing['revenue_per_turnover'] + $revenue;
            $newAvgRevenue = $newRevenue / $newCount;
            
            $sql = "UPDATE table_turnover_metrics 
                    SET turnover_count = :turnover_count, avg_seat_time = :avg_seat_time, 
                        revenue_per_turnover = :revenue_per_turnover 
                    WHERE metric_id = :metric_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':turnover_count' => $newCount,
                ':avg_seat_time' => $newAvgSeatTime,
                ':revenue_per_turnover' => $newAvgRevenue,
                ':metric_id' => $existing['metric_id']
            ]);
        } else {
            // Create new metric
            $sql = "INSERT INTO table_turnover_metrics (tenant_id, branch_id, table_id, zone_id, date, hour, turnover_count, avg_seat_time, revenue_per_turnover) 
                    VALUES (:tenant_id, :branch_id, :table_id, :zone_id, :date, :hour, 1, :avg_seat_time, :revenue_per_turnover)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':tenant_id' => $tenantId,
                ':branch_id' => $branchId,
                ':table_id' => $tableId,
                ':zone_id' => $zoneId,
                ':date' => $date,
                ':hour' => $hour,
                ':avg_seat_time' => $seatTime,
                ':revenue_per_turnover' => $revenue
            ]);
        }
    }

    public function getOptimalSeatingRecommendation($tenantId, $branchId, $partySize)
    {
        // Find tables with best turnover rates for given party size
        $sql = "SELECT t.*, ttm.avg_seat_time, ttm.turnover_count, ttm.revenue_per_turnover 
                FROM tables t 
                LEFT JOIN table_turnover_metrics ttm ON t.table_id = ttm.table_id AND ttm.date = CURDATE()
                WHERE t.tenant_id = :tenant_id AND t.branch_id = :branch_id 
                AND t.capacity >= :party_size AND t.status = 'AVAILABLE'
                ORDER BY ttm.avg_seat_time ASC, ttm.turnover_count DESC
                LIMIT 5";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':branch_id' => $branchId,
            ':party_size' => $partySize
        ]);
        return $stmt->fetchAll();
    }
}
