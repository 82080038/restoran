<?php

namespace App\Modules\Compliance\Services;

use App\Core\Database;
use App\Core\Audit;

class HACCPService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = new Audit();
    }

    /**
     * Create critical control point
     */
    public function createCCP($tenantId, $branchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $ccpData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'ccp_name' => $data->ccp_name,
                'process_step' => $data->process_step,
                'hazard_type' => $data->hazard_type,
                'hazard_description' => $data->hazard_description ?? null,
                'critical_limit' => $data->critical_limit,
                'monitoring_procedure' => $data->monitoring_procedure,
                'corrective_action' => $data->corrective_action,
                'monitoring_frequency' => $data->monitoring_frequency,
                'responsible_person' => $data->responsible_person,
                'status' => 'ACTIVE',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO haccp_ccps (tenant_id, branch_id, ccp_name, process_step, hazard_type, hazard_description, critical_limit, monitoring_procedure, corrective_action, monitoring_frequency, responsible_person, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $ccpData['tenant_id'],
                $ccpData['branch_id'],
                $ccpData['ccp_name'],
                $ccpData['process_step'],
                $ccpData['hazard_type'],
                $ccpData['hazard_description'],
                $ccpData['critical_limit'],
                $ccpData['monitoring_procedure'],
                $ccpData['corrective_action'],
                $ccpData['monitoring_frequency'],
                $ccpData['responsible_person'],
                $ccpData['status'],
                $ccpData['created_by']
            ]);

            $ccpId = $this->db->lastInsertId();

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $branchId, $userId, 'haccp_ccp', $ccpId, 'CREATE', json_encode($ccpData));

            return [
                'success' => true,
                'message' => 'Critical control point created',
                'ccp_id' => $ccpId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create CCP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get critical control points
     */
    public function getCCPs($tenantId, $branchId, $status)
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

        $sql = "SELECT hcc.*, 
                    (SELECT COUNT(*) FROM haccp_monitoring hm WHERE hm.ccp_id = hcc.id AND DATE(hm.monitoring_date) = CURDATE()) as today_monitoring_count,
                    u.username as created_by_name
                FROM haccp_ccps hcc
                LEFT JOIN users u ON hcc.created_by = u.id
                {$where}
                ORDER BY hcc.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Record monitoring data
     */
    public function recordMonitoring($tenantId, $branchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $monitoringData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'ccp_id' => $data->ccp_id,
                'monitoring_date' => $data->monitoring_date ?? date('Y-m-d H:i:s'),
                'monitoring_time' => $data->monitoring_time ?? date('H:i:s'),
                'actual_value' => $data->actual_value,
                'within_limits' => $data->within_limits,
                'monitoring_result' => $data->monitoring_result,
                'corrective_action_taken' => $data->corrective_action_taken ?? null,
                'monitoring_by' => $userId
            ];

            $sql = "INSERT INTO haccp_monitoring (tenant_id, branch_id, ccp_id, monitoring_date, monitoring_time, actual_value, within_limits, monitoring_result, corrective_action_taken, monitoring_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $monitoringData['tenant_id'],
                $monitoringData['branch_id'],
                $monitoringData['ccp_id'],
                $monitoringData['monitoring_date'],
                $monitoringData['monitoring_time'],
                $monitoringData['actual_value'],
                $monitoringData['within_limits'],
                $monitoringData['monitoring_result'],
                $monitoringData['corrective_action_taken'],
                $monitoringData['monitoring_by']
            ]);

            $monitoringId = $this->db->lastInsertId();

            // If out of limits, create alert
            if (!$data->within_limits) {
                $this->createHACCPAlert($tenantId, $branchId, $data->ccp_id, $monitoringId, $userId);
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $branchId, $userId, 'haccp_monitoring', $monitoringId, 'CREATE', json_encode($monitoringData));

            return [
                'success' => true,
                'message' => 'Monitoring recorded',
                'monitoring_id' => $monitoringId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to record monitoring: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create HACCP alert
     */
    private function createHACCPAlert($tenantId, $branchId, $ccpId, $monitoringId, $userId)
    {
        $sql = "INSERT INTO haccp_alerts (tenant_id, branch_id, ccp_id, monitoring_id, alert_type, alert_level, status, created_by, created_at)
                VALUES (?, ?, ?, ?, 'LIMIT_VIOLATION', 'HIGH', 'OPEN', ?, NOW())";
        
        $this->db->prepare($sql)->execute([$tenantId, $branchId, $ccpId, $monitoringId, $userId]);
    }

    /**
     * Get monitoring records
     */
    public function getMonitoringRecords($tenantId, $branchId, $ccpId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE hm.tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND hm.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($ccpId) {
            $where .= " AND hm.ccp_id = ?";
            $params[] = $ccpId;
        }
        
        if ($dateFrom) {
            $where .= " AND hm.monitoring_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND hm.monitoring_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT hm.*, 
                    hcc.ccp_name,
                    hcc.process_step,
                    hcc.critical_limit,
                    u.username as monitoring_by_name
                FROM haccp_monitoring hm
                LEFT JOIN haccp_ccps hcc ON hm.ccp_id = hcc.id
                LEFT JOIN users u ON hm.monitoring_by = u.id
                {$where}
                ORDER BY hm.monitoring_date DESC, hm.monitoring_time DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Generate HACCP report
     */
    public function generateHACCPReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($dateFrom) {
            $where .= " AND monitoring_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND monitoring_date <= ?";
            $params[] = $dateTo;
        }

        // Total monitoring records
        $totalMonitoringSql = "SELECT COUNT(*) as count FROM haccp_monitoring {$where}";
        $totalMonitoring = $this->db->query($totalMonitoringSql, $params)->fetch();

        // Within limits count
        $withinLimitsSql = "SELECT COUNT(*) as count FROM haccp_monitoring {$where} AND within_limits = 1";
        $withinLimits = $this->db->query($withinLimitsSql, $params)->fetch();

        // Violations count
        $violationsSql = "SELECT COUNT(*) as count FROM haccp_monitoring {$where} AND within_limits = 0";
        $violations = $this->db->query($violationsSql, $params)->fetch();

        // Compliance percentage
        $compliancePercentage = $totalMonitoring['count'] > 0 ? ($withinLimits['count'] / $totalMonitoring['count']) * 100 : 0;

        // Open alerts
        $openAlertsSql = "SELECT COUNT(*) as count FROM haccp_alerts WHERE tenant_id = ? AND status = 'OPEN'";
        $openAlerts = $this->db->query($openAlertsSql, [$tenantId])->fetch();

        return [
            'total_monitoring_records' => $totalMonitoring['count'] ?? 0,
            'within_limits' => $withinLimits['count'] ?? 0,
            'violations' => $violations['count'] ?? 0,
            'compliance_percentage' => round($compliancePercentage, 2),
            'open_alerts' => $openAlerts['count'] ?? 0
        ];
    }

    /**
     * Get HACCP summary
     */
    public function getSummary($tenantId, $branchId)
    {
        // Active CCPs
        $ccpsSql = "SELECT COUNT(*) as count FROM haccp_ccps WHERE tenant_id = ? AND status = 'ACTIVE'";
        $ccps = $this->db->query($ccpsSql, [$tenantId])->fetch();

        // Today's monitoring
        $todayMonitoringSql = "SELECT COUNT(*) as count FROM haccp_monitoring WHERE tenant_id = ? AND DATE(monitoring_date) = CURDATE()";
        $todayMonitoring = $this->db->query($todayMonitoringSql, [$tenantId])->fetch();

        // Open alerts
        $openAlertsSql = "SELECT COUNT(*) as count FROM haccp_alerts WHERE tenant_id = ? AND status = 'OPEN'";
        $openAlerts = $this->db->query($openAlertsSql, [$tenantId])->fetch();

        // Overdue monitoring (not monitored in last 24 hours)
        $overdueSql = "SELECT COUNT(DISTINCT hm.ccp_id) as count 
                      FROM haccp_ccps hcc
                      LEFT JOIN haccp_monitoring hm ON hcc.id = hm.ccp_id AND DATE(hm.monitoring_date) >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                      WHERE hcc.tenant_id = ? AND hcc.status = 'ACTIVE' AND hm.id IS NULL";
        $overdue = $this->db->query($overdueSql, [$tenantId])->fetch();

        return [
            'active_ccps' => $ccps['count'] ?? 0,
            'today_monitoring' => $todayMonitoring['count'] ?? 0,
            'open_alerts' => $openAlerts['count'] ?? 0,
            'overdue_monitoring' => $overdue['count'] ?? 0
        ];
    }
}
