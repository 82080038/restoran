<?php

namespace App\Modules\Operations\Services;

use App\Core\Database;
use PDO;

class PeakHourService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getPeakHourSchedules($tenantId, $branchId)
    {
        $sql = "SELECT * FROM peak_hour_schedules WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY FIELD(day_of_week, 'MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY'), start_time ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCurrentPeakHour($tenantId, $branchId)
    {
        $sql = "SELECT * FROM peak_hour_schedules 
                WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND is_active = 1
                AND day_of_week = DAYNAME(NOW())
                AND CURTIME() BETWEEN start_time AND end_time";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId]);
        return $stmt->fetch();
    }

    public function createPeakHourSchedule($data)
    {
        $sql = "INSERT INTO peak_hour_schedules (tenant_id, branch_id, day_of_week, start_time, end_time, peak_level, expected_volume_multiplier, staff_multiplier, is_active, notes) 
                VALUES (:tenant_id, :branch_id, :day_of_week, :start_time, :end_time, :peak_level, :expected_volume_multiplier, :staff_multiplier, :is_active, :notes)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':day_of_week' => $data['day_of_week'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':peak_level' => $data['peak_level'] ?? 'HIGH',
            ':expected_volume_multiplier' => $data['expected_volume_multiplier'] ?? 1.50,
            ':staff_multiplier' => $data['staff_multiplier'] ?? 1.25,
            ':is_active' => $data['is_active'] ?? 1,
            ':notes' => $data['notes'] ?? null
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updatePeakHourSchedule($scheduleId, $tenantId, $data)
    {
        $sql = "UPDATE peak_hour_schedules SET day_of_week = :day_of_week, start_time = :start_time, 
                end_time = :end_time, peak_level = :peak_level, expected_volume_multiplier = :expected_volume_multiplier, 
                staff_multiplier = :staff_multiplier, is_active = :is_active, notes = :notes 
                WHERE schedule_id = :schedule_id AND tenant_id = :tenant_id";
        
        $params = [
            ':day_of_week' => $data['day_of_week'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':peak_level' => $data['peak_level'],
            ':expected_volume_multiplier' => $data['expected_volume_multiplier'],
            ':staff_multiplier' => $data['staff_multiplier'],
            ':is_active' => $data['is_active'],
            ':notes' => $data['notes'],
            ':schedule_id' => $scheduleId,
            ':tenant_id' => $tenantId
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deletePeakHourSchedule($scheduleId, $tenantId)
    {
        $sql = "DELETE FROM peak_hour_schedules WHERE schedule_id = :schedule_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':schedule_id' => $scheduleId, ':tenant_id' => $tenantId]);
    }

    public function isPeakHourNow($tenantId, $branchId)
    {
        $peakHour = $this->getCurrentPeakHour($tenantId, $branchId);
        return $peakHour !== false;
    }

    public function getVolumeMultiplier($tenantId, $branchId)
    {
        $peakHour = $this->getCurrentPeakHour($tenantId, $branchId);
        if ($peakHour) {
            return $peakHour['expected_volume_multiplier'];
        }
        return 1.0;
    }

    public function getStaffMultiplier($tenantId, $branchId)
    {
        $peakHour = $this->getCurrentPeakHour($tenantId, $branchId);
        if ($peakHour) {
            return $peakHour['staff_multiplier'];
        }
        return 1.0;
    }
}
