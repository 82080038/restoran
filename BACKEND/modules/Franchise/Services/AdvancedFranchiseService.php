<?php

namespace App\Modules\Franchise\Services;

use App\Core\Database;
use App\Core\Audit;

class AdvancedFranchiseService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = new Audit();
    }

    /**
     * Create brand compliance checklist
     */
    public function createComplianceChecklist($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $checklistData = [
                'tenant_id' => $tenantId,
                'checklist_name' => $data->checklist_name,
                'checklist_type' => $data->checklist_type,
                'frequency' => $data->frequency,
                'status' => 'ACTIVE',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO brand_compliance_checklists (tenant_id, checklist_name, checklist_type, frequency, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $checklistData['tenant_id'],
                $checklistData['checklist_name'],
                $checklistData['checklist_type'],
                $checklistData['frequency'],
                $checklistData['status'],
                $checklistData['created_by']
            ]);

            $checklistId = $this->db->lastInsertId();

            // Add checklist items
            if (isset($data->items) && is_array($data->items)) {
                foreach ($data->items as $item) {
                    $this->addChecklistItem($checklistId, $item);
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'brand_compliance_checklist', $checklistId, 'CREATE', json_encode($checklistData));

            return [
                'success' => true,
                'message' => 'Compliance checklist created',
                'checklist_id' => $checklistId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create checklist: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add checklist item
     */
    private function addChecklistItem($checklistId, $item)
    {
        $sql = "INSERT INTO brand_compliance_items (checklist_id, item_name, item_description, required, created_at)
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $checklistId,
            $item->item_name,
            $item->item_description ?? null,
            $item->required ?? true
        ]);
    }

    /**
     * Record compliance audit
     */
    public function recordComplianceAudit($tenantId, $branchId, $franchiseeId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $auditData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'franchisee_id' => $franchiseeId,
                'checklist_id' => $data->checklist_id,
                'audit_date' => $data->audit_date ?? date('Y-m-d'),
                'auditor_id' => $userId,
                'overall_score' => $data->overall_score,
                'compliance_status' => $data->compliance_status,
                'findings' => json_encode($data->findings ?? []),
                'recommendations' => json_encode($data->recommendations ?? []),
                'follow_up_required' => $data->follow_up_required ?? false,
                'follow_up_date' => $data->follow_up_date ?? null
            ];

            $sql = "INSERT INTO brand_compliance_audits (tenant_id, branch_id, franchisee_id, checklist_id, audit_date, auditor_id, overall_score, compliance_status, findings, recommendations, follow_up_required, follow_up_date, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $auditData['tenant_id'],
                $auditData['branch_id'],
                $auditData['franchisee_id'],
                $auditData['checklist_id'],
                $auditData['audit_date'],
                $auditData['auditor_id'],
                $auditData['overall_score'],
                $auditData['compliance_status'],
                $auditData['findings'],
                $auditData['recommendations'],
                $auditData['follow_up_required'],
                $auditData['follow_up_date']
            ]);

            $auditId = $this->db->lastInsertId();

            // Record audit results for each item
            if (isset($data->item_results) && is_array($data->item_results)) {
                foreach ($data->item_results as $result) {
                    $this->addAuditItemResult($auditId, $result);
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $branchId, $userId, 'brand_compliance_audit', $auditId, 'CREATE', json_encode($auditData));

            return [
                'success' => true,
                'message' => 'Compliance audit recorded',
                'audit_id' => $auditId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to record audit: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add audit item result
     */
    private function addAuditItemResult($auditId, $result)
    {
        $sql = "INSERT INTO brand_compliance_audit_results (audit_id, checklist_item_id, passed, notes, evidence_url, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $auditId,
            $result->checklist_item_id,
            $result->passed,
            $result->notes ?? null,
            $result->evidence_url ?? null
        ]);
    }

    /**
     * Get compliance audits
     */
    public function getComplianceAudits($tenantId, $franchiseeId, $status, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE bca.tenant_id = ?";
        
        if ($franchiseeId) {
            $where .= " AND bca.franchisee_id = ?";
            $params[] = $franchiseeId;
        }
        
        if ($status) {
            $where .= " AND bca.compliance_status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND bca.audit_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND bca.audit_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT bca.*, 
                    bcc.checklist_name,
                    bcc.checklist_type,
                    f.franchisee_name,
                    u.username as auditor_name
                FROM brand_compliance_audits bca
                LEFT JOIN brand_compliance_checklists bcc ON bca.checklist_id = bcc.id
                LEFT JOIN franchisees f ON bca.franchisee_id = f.id
                LEFT JOIN users u ON bca.auditor_id = u.id
                {$where}
                ORDER BY bca.audit_date DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Generate franchise performance report
     */
    public function generateFranchiseReport($tenantId, $franchiseeId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE fr.tenant_id = ?";
        
        if ($franchiseeId) {
            $where .= " AND fr.franchisee_id = ?";
            $params[] = $franchiseeId;
        }
        
        if ($dateFrom) {
            $where .= " AND fr.royalty_period_start >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND fr.royalty_period_end <= ?";
            $params[] = $dateTo;
        }

        // Get franchise revenue
        $revenueSql = "SELECT SUM(fr.gross_revenue) as total_revenue, SUM(fr.royalty_amount) as total_royalty, SUM(fr.marketing_fee_amount) as total_marketing_fee, SUM(fr.total_due) as total_due
                      FROM franchise_royalties fr
                      LEFT JOIN franchisees f ON fr.franchisee_id = f.id
                      {$where}";
        $revenue = $this->db->query($revenueSql, $params)->fetch();

        // Get compliance score
        $complianceSql = "SELECT AVG(bca.overall_score) as avg_compliance_score
                           FROM brand_compliance_audits bca
                           WHERE bca.tenant_id = ?";
        if ($franchiseeId) {
            $complianceSql .= " AND bca.franchisee_id = ?";
            $params[] = $franchiseeId;
        }
        $compliance = $this->db->query($complianceSql, array_slice($params, 0, count($params) - ($franchiseeId ? 1 : 0)))->fetch();

        // Get performance metrics
        $performanceSql = "SELECT AVG(fp.sales_growth) as avg_sales_growth, AVG(fp.customer_satisfaction) as avg_customer_satisfaction, AVG(fp.operational_efficiency) as avg_operational_efficiency
                            FROM franchise_performance fp
                            LEFT JOIN franchisees f ON fp.franchisee_id = f.id
                            {$where}";
        $performance = $this->db->query($performanceSql, $params)->fetch();

        return [
            'total_revenue' => $revenue['total_revenue'] ?? 0,
            'total_royalty' => $revenue['total_royalty'] ?? 0,
            'total_marketing_fee' => $revenue['total_marketing_fee'] ?? 0,
            'total_due' => $revenue['total_due'] ?? 0,
            'avg_compliance_score' => round($compliance['avg_compliance_score'] ?? 0, 2),
            'avg_sales_growth' => round($performance['avg_sales_growth'] ?? 0, 2),
            'avg_customer_satisfaction' => round($performance['avg_customer_satisfaction'] ?? 0, 2),
            'avg_operational_efficiency' => round($performance['avg_operational_efficiency'] ?? 0, 2)
        ];
    }

    /**
     * Get franchise summary
     */
    public function getSummary($tenantId)
    {
        // Active franchisees
        $activeFranchiseesSql = "SELECT COUNT(*) as count FROM franchisees WHERE tenant_id = ? AND franchisee_status = 'active'";
        $activeFranchisees = $this->db->query($activeFranchiseesSql, [$tenantId])->fetch();

        // Pending compliance audits
        $pendingAuditsSql = "SELECT COUNT(*) as count FROM brand_compliance_audits WHERE tenant_id = ? AND follow_up_required = 1 AND (follow_up_date IS NULL OR follow_up_date < CURDATE())";
        $pendingAudits = $this->db->query($pendingAuditsSql, [$tenantId])->fetch();

        // Overdue royalties
        $overdueRoyaltiesSql = "SELECT COUNT(*) as count FROM franchise_royalties WHERE tenant_id = ? AND payment_status = 'pending' AND royalty_period_end < DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $overdueRoyalties = $this->db->query($overdueRoyaltiesSql, [$tenantId])->fetch();

        // This month's royalty collection
        $monthlyRoyaltySql = "SELECT SUM(total_due) as total FROM franchise_royalties WHERE tenant_id = ? AND payment_status = 'paid' AND royalty_period_end >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $monthlyRoyalty = $this->db->query($monthlyRoyaltySql, [$tenantId])->fetch();

        return [
            'active_franchisees' => $activeFranchisees['count'] ?? 0,
            'pending_compliance_audits' => $pendingAudits['count'] ?? 0,
            'overdue_royalties' => $overdueRoyalties['count'] ?? 0,
            'monthly_royalty_collected' => $monthlyRoyalty['total'] ?? 0
        ];
    }
}
