<?php

namespace App\Modules\Quality\Services;

use App\Core\Database;
use App\Core\Audit;

class QualityControlService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = new Audit();
    }

    /**
     * Create quality check
     */
    public function createQualityCheck($tenantId, $branchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $checkData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'check_type' => $data->check_type,
                'check_category' => $data->check_category,
                'item_id' => $data->item_id ?? null,
                'item_name' => $data->item_name ?? null,
                'inspection_criteria' => json_encode($data->inspection_criteria ?? []),
                'result' => $data->result,
                'notes' => $data->notes ?? null,
                'inspector_id' => $userId,
                'inspection_date' => $data->inspection_date ?? date('Y-m-d H:i:s'),
                'status' => 'COMPLETED'
            ];

            $sql = "INSERT INTO quality_checks (tenant_id, branch_id, check_type, check_category, item_id, item_name, inspection_criteria, result, notes, inspector_id, inspection_date, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $checkData['tenant_id'],
                $checkData['branch_id'],
                $checkData['check_type'],
                $checkData['check_category'],
                $checkData['item_id'],
                $checkData['item_name'],
                $checkData['inspection_criteria'],
                $checkData['result'],
                $checkData['notes'],
                $checkData['inspector_id'],
                $checkData['inspection_date'],
                $checkData['status']
            ]);

            $checkId = $this->db->lastInsertId();

            // Add check details
            if (isset($data->check_details) && is_array($data->check_details)) {
                foreach ($data->check_details as $detail) {
                    $this->addCheckDetail($checkId, $detail);
                }
            }

            // If failed, create non-conformance
            if ($data->result === 'FAILED') {
                $this->createNonConformance($tenantId, $branchId, $checkId, $userId, $data);
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $branchId, $userId, 'quality_check', $checkId, 'CREATE', json_encode($checkData));

            return [
                'success' => true,
                'message' => 'Quality check created',
                'check_id' => $checkId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create quality check: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add check detail
     */
    private function addCheckDetail($checkId, $detail)
    {
        $sql = "INSERT INTO quality_check_details (quality_check_id, criterion_name, expected_value, actual_value, passed, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $checkId,
            $detail->criterion_name,
            $detail->expected_value,
            $detail->actual_value,
            $detail->passed,
            $detail->notes ?? null
        ]);
    }

    /**
     * Create non-conformance
     */
    private function createNonConformance($tenantId, $branchId, $checkId, $userId, $data)
    {
        $ncData = [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'quality_check_id' => $checkId,
            'severity' => $data->severity ?? 'MEDIUM',
            'description' => $data->non_conformance_description ?? 'Quality check failed',
            'root_cause' => $data->root_cause ?? null,
            'corrective_action' => $data->corrective_action ?? null,
            'responsible_person' => $data->responsible_person ?? null,
            'target_date' => $data->target_date ?? null,
            'status' => 'OPEN',
            'reported_by' => $userId
        ];

        $sql = "INSERT INTO non_conformances (tenant_id, branch_id, quality_check_id, severity, description, root_cause, corrective_action, responsible_person, target_date, status, reported_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $this->db->prepare($sql)->execute([
            $ncData['tenant_id'],
            $ncData['branch_id'],
            $ncData['quality_check_id'],
            $ncData['severity'],
            $ncData['description'],
            $ncData['root_cause'],
            $ncData['corrective_action'],
            $ncData['responsible_person'],
            $ncData['target_date'],
            $ncData['status'],
            $ncData['reported_by']
        ]);
    }

    /**
     * Get quality checks
     */
    public function getQualityChecks($tenantId, $branchId, $checkType, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE qc.tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND qc.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($checkType) {
            $where .= " AND qc.check_type = ?";
            $params[] = $checkType;
        }
        
        if ($dateFrom) {
            $where .= " AND qc.inspection_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND qc.inspection_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT qc.*, 
                    (SELECT COUNT(*) FROM quality_check_details qcd WHERE qcd.quality_check_id = qc.id) as detail_count,
                    u.username as inspector_name
                FROM quality_checks qc
                LEFT JOIN users u ON qc.inspector_id = u.id
                {$where}
                ORDER BY qc.inspection_date DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get non-conformances
     */
    public function getNonConformances($tenantId, $branchId, $status)
    {
        $params = [$tenantId];
        $where = "WHERE nc.tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND nc.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($status) {
            $where .= " AND nc.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT nc.*, 
                    qc.check_type,
                    qc.item_name,
                    u.username as reported_by_name
                FROM non_conformances nc
                LEFT JOIN quality_checks qc ON nc.quality_check_id = qc.id
                LEFT JOIN users u ON nc.reported_by = u.id
                {$where}
                ORDER BY nc.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Update non-conformance status
     */
    public function updateNonConformanceStatus($ncId, $status, $userId, $tenantId)
    {
        try {
            $this->db->beginTransaction();

            $updateData = ['status' => $status];
            
            if ($status === 'RESOLVED') {
                $updateData['resolved_by'] = $userId;
                $updateData['resolved_at'] = date('Y-m-d H:i:s');
            }

            $sql = "UPDATE non_conformances SET status = ?, updated_at = NOW()";
            $params = [$status];

            if (isset($updateData['resolved_by'])) {
                $sql .= ", resolved_by = ?, resolved_at = ?";
                $params[] = $updateData['resolved_by'];
                $params[] = $updateData['resolved_at'];
            }

            $sql .= " WHERE id = ? AND tenant_id = ?";
            $params[] = $ncId;
            $params[] = $tenantId;

            $this->db->prepare($sql)->execute($params);

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'non_conformance', $ncId, 'UPDATE_STATUS', json_encode(['status' => $status]));

            return [
                'success' => true,
                'message' => 'Non-conformance status updated'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get quality metrics
     */
    public function getQualityMetrics($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($dateFrom) {
            $where .= " AND inspection_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND inspection_date <= ?";
            $params[] = $dateTo;
        }

        // Total checks
        $totalChecksSql = "SELECT COUNT(*) as count FROM quality_checks {$where}";
        $totalChecks = $this->db->query($totalChecksSql, $params)->fetch();

        // Passed checks
        $passedChecksSql = "SELECT COUNT(*) as count FROM quality_checks {$where} AND result = 'PASSED'";
        $passedChecks = $this->db->query($passedChecksSql, $params)->fetch();

        // Failed checks
        $failedChecksSql = "SELECT COUNT(*) as count FROM quality_checks {$where} AND result = 'FAILED'";
        $failedChecks = $this->db->query($failedChecksSql, $params)->fetch();

        // Pass rate
        $passRate = $totalChecks['count'] > 0 ? ($passedChecks['count'] / $totalChecks['count']) * 100 : 0;

        // Open non-conformances
        $openNCsSql = "SELECT COUNT(*) as count FROM non_conformances WHERE tenant_id = ? AND status = 'OPEN'";
        $openNCs = $this->db->query($openNCsSql, [$tenantId])->fetch();

        return [
            'total_checks' => $totalChecks['count'] ?? 0,
            'passed_checks' => $passedChecks['count'] ?? 0,
            'failed_checks' => $failedChecks['count'] ?? 0,
            'pass_rate' => round($passRate, 2),
            'open_non_conformances' => $openNCs['count'] ?? 0
        ];
    }

    /**
     * Get quality summary
     */
    public function getSummary($tenantId, $branchId)
    {
        // Today's checks
        $todayChecksSql = "SELECT COUNT(*) as count FROM quality_checks WHERE tenant_id = ? AND DATE(inspection_date) = CURDATE()";
        $todayChecks = $this->db->query($todayChecksSql, [$tenantId])->fetch();

        // Open non-conformances
        $openNCsSql = "SELECT COUNT(*) as count FROM non_conformances WHERE tenant_id = ? AND status = 'OPEN'";
        $openNCs = $this->db->query($openNCsSql, [$tenantId])->fetch();

        // Overdue non-conformances
        $overdueNCsSql = "SELECT COUNT(*) as count FROM non_conformances WHERE tenant_id = ? AND status = 'OPEN' AND target_date < CURDATE()";
        $overdueNCs = $this->db->query($overdueNCsSql, [$tenantId])->fetch();

        // This month's pass rate
        $monthPassRateSql = "SELECT 
                                (SELECT COUNT(*) FROM quality_checks WHERE tenant_id = ? AND result = 'PASSED' AND MONTH(inspection_date) = MONTH(CURDATE()) AND YEAR(inspection_date) = YEAR(CURDATE())) as passed,
                                (SELECT COUNT(*) FROM quality_checks WHERE tenant_id = ? AND MONTH(inspection_date) = MONTH(CURDATE()) AND YEAR(inspection_date) = YEAR(CURDATE())) as total";
        $monthStats = $this->db->query($monthPassRateSql, [$tenantId, $tenantId])->fetch();
        $monthPassRate = $monthStats['total'] > 0 ? ($monthStats['passed'] / $monthStats['total']) * 100 : 0;

        return [
            'today_checks' => $todayChecks['count'] ?? 0,
            'open_non_conformances' => $openNCs['count'] ?? 0,
            'overdue_non_conformances' => $overdueNCs['count'] ?? 0,
            'monthly_pass_rate' => round($monthPassRate, 2)
        ];
    }
}
