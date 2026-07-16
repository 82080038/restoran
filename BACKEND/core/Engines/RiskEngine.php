<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * RiskEngine - Risk Assessment and Mitigation Engine
 * 
 * This engine handles risk identification, assessment, mitigation planning,
 * and risk monitoring for the RESTAURANT_ERP system
 * 
 * @package EBP\App\Core\Engines
 * @version 1.0.0
 */

class RiskEngine implements EngineInterface
{
    private $db;
    private $initialized = false;

    public function __construct($db = null)
    {
        if ($db) {
            $this->initialize(['db' => $db]);
        }
    }

    public function initialize($dependencies): void
    {
        $this->db = $dependencies['db'] ?? null;
        $this->initialized = !empty($this->db);
    }

    public function validate(): bool
    {
        return $this->initialized && !empty($this->db);
    }

    public function execute(array $params): array
    {
        if (!$this->validate()) {
            return [
                'success' => false,
                'message' => 'Engine not properly initialized'
            ];
        }

        $action = $params['action'] ?? 'assess_risks';

        switch ($action) {
            case 'assess_risks':
                return $this->executeAssessRisks($params);
            case 'create_mitigation_plan':
                return $this->executeCreateMitigationPlan($params);
            case 'monitor_risks':
                return $this->executeMonitorRisks($params);
            case 'calculate_risk_score':
                return $this->executeCalculateRiskScore($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeAssessRisks(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->assessRisks($tenantId, $branchId);
            return [
                'success' => true,
                'risk_assessment' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateMitigationPlan(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $riskId = $params['risk_id'] ?? null;
        $mitigationData = $params['mitigation_data'] ?? [];

        if (!$tenantId || !$branchId || !$riskId || empty($mitigationData)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, risk_id, mitigation_data'
            ];
        }

        try {
            $result = $this->createMitigationPlan($tenantId, $branchId, $riskId, $mitigationData);
            return [
                'success' => true,
                'mitigation_plan' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeMonitorRisks(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->monitorRisks($tenantId, $branchId);
            return [
                'success' => true,
                'risk_monitoring' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateTraining(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $trainingData = $params['training_data'] ?? [];

        if (!$tenantId || empty($trainingData)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, training_data'
            ];
        }

        try {
            $result = $this->createTraining($tenantId, $branchId, $trainingData);
            return [
                'success' => true,
                'training' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGetTrainingProgress(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $employeeId = $params['employee_id'] ?? null;

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->getTrainingProgress($tenantId, $branchId, $employeeId);
            return [
                'success' => true,
                'progress' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCalculateRiskScore(array $params): array
    {
        $riskData = $params['risk_data'] ?? [];

        if (empty($riskData)) {
            return [
                'success' => false,
                'message' => 'Missing required parameter: risk_data'
            ];
        }

        try {
            $result = $this->calculateRiskScore($riskData);
            return [
                'success' => true,
                'risk_score' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Risk Engine',
            'version' => '1.0.0',
            'description' => 'Handles risk assessment and mitigation planning',
            'author' => 'EBP Team',
            'created_at' => '2026-07-08'
        ];
    }

    public function getHealth(): array
    {
        return [
            'status' => $this->validate() ? 'healthy' : 'unhealthy',
            'initialized' => $this->initialized,
            'database_connected' => !empty($this->db),
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Assess risks for a tenant/branch
     */
    public function assessRisks($tenantId, $branchId)
    {
        $risks = [];

        // Assess operational risks
        $operationalRisks = $this->assessOperationalRisks($tenantId, $branchId);
        $risks = array_merge($risks, $operationalRisks);

        // Assess financial risks
        $financialRisks = $this->assessFinancialRisks($tenantId, $branchId);
        $risks = array_merge($risks, $financialRisks);

        // Assess compliance risks
        $complianceRisks = $this->assessComplianceRisks($tenantId, $branchId);
        $risks = array_merge($risks, $complianceRisks);

        // Assess supply chain risks
        $supplyChainRisks = $this->assessSupplyChainRisks($tenantId, $branchId);
        $risks = array_merge($risks, $supplyChainRisks);

        // Assess technology risks
        $technologyRisks = $this->assessTechnologyRisks($tenantId, $branchId);
        $risks = array_merge($risks, $technologyRisks);

        // Calculate overall risk score
        $overallRiskScore = $this->calculateOverallRiskScore($risks);

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'assessment_date' => date('Y-m-d H:i:s'),
            'risks' => $risks,
            'total_risks' => count($risks),
            'overall_risk_score' => $overallRiskScore,
            'risk_level' => $this->getRiskLevel($overallRiskScore),
            'recommendations' => $this->generateRiskRecommendations($risks)
        ];
    }

    /**
     * Assess operational risks
     */
    private function assessOperationalRisks($tenantId, $branchId)
    {
        $risks = [];

        // Check staff shortage risk
        $staffRisk = $this->checkStaffShortageRisk($tenantId, $branchId);
        if ($staffRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'OPERATIONAL',
                'type' => 'STAFF_SHORTAGE',
                'description' => 'Staff shortage risk',
                'risk_level' => $staffRisk['risk_level'],
                'score' => $staffRisk['score'],
                'factors' => $staffRisk['factors'],
                'mitigation' => 'Implement hiring plan and cross-training'
            ];
        }

        // Check equipment failure risk
        $equipmentRisk = $this->checkEquipmentFailureRisk($tenantId, $branchId);
        if ($equipmentRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'OPERATIONAL',
                'type' => 'EQUIPMENT_FAILURE',
                'description' => 'Equipment failure risk',
                'risk_level' => $equipmentRisk['risk_level'],
                'score' => $equipmentRisk['score'],
                'factors' => $equipmentRisk['factors'],
                'mitigation' => 'Implement preventive maintenance schedule'
            ];
        }

        // Check inventory stockout risk
        $inventoryRisk = $this->checkInventoryStockoutRisk($tenantId, $branchId);
        if ($inventoryRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'OPERATIONAL',
                'type' => 'INVENTORY_STOCKOUT',
                'description' => 'Inventory stockout risk',
                'risk_level' => $inventoryRisk['risk_level'],
                'score' => $inventoryRisk['score'],
                'factors' => $inventoryRisk['factors'],
                'mitigation' => 'Implement safety stock and reorder point automation'
            ];
        }

        return $risks;
    }

    /**
     * Check staff shortage risk
     */
    private function checkStaffShortageRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_staff,
                SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active_staff
            FROM employees
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalStaff = $staff['total_staff'] ?? 0;
        $activeStaff = $staff['active_staff'] ?? 0;

        $factors = [];
        $score = 0;

        if ($activeStaff < $totalStaff * 0.8) {
            $factors[] = 'High inactive staff ratio';
            $score += 30;
        }

        if ($totalStaff < 5) {
            $factors[] = 'Low total staff count';
            $score += 40;
        }

        if ($activeStaff < 3) {
            $factors[] = 'Critical low active staff';
            $score += 30;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Check equipment failure risk
     */
    private function checkEquipmentFailureRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_equipment,
                SUM(CASE WHEN status = 'NEEDS_MAINTENANCE' THEN 1 ELSE 0 END) as needs_maintenance,
                SUM(CASE WHEN last_maintenance_date < DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as overdue_maintenance
            FROM equipment
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        if ($equipment['needs_maintenance'] > 0) {
            $factors[] = 'Equipment needs maintenance';
            $score += 30;
        }

        if ($equipment['overdue_maintenance'] > 0) {
            $factors[] = 'Overdue maintenance';
            $score += 40;
        }

        if ($equipment['total_equipment'] > 0 && $equipment['overdue_maintenance'] / $equipment['total_equipment'] > 0.3) {
            $factors[] = 'High overdue maintenance ratio';
            $score += 30;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Check inventory stockout risk
     */
    private function checkInventoryStockoutRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_items,
                SUM(CASE WHEN quantity <= reorder_level THEN 1 ELSE 0 END) as at_reorder_level,
                SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock
            FROM stock_balances sb
            JOIN inventory i ON sb.inventory_id = i.inventory_id
            WHERE sb.branch_id = ? AND i.tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId]);
        $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        if ($inventory['out_of_stock'] > 0) {
            $factors[] = 'Items out of stock';
            $score += 50;
        }

        if ($inventory['at_reorder_level'] > $inventory['total_items'] * 0.2) {
            $factors[] = 'High ratio of items at reorder level';
            $score += 30;
        }

        if ($inventory['at_reorder_level'] > 5) {
            $factors[] = 'Multiple items at reorder level';
            $score += 20;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Assess financial risks
     */
    private function assessFinancialRisks($tenantId, $branchId)
    {
        $risks = [];

        // Check cash flow risk
        $cashFlowRisk = $this->checkCashFlowRisk($tenantId, $branchId);
        if ($cashFlowRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'FINANCIAL',
                'type' => 'CASH_FLOW',
                'description' => 'Cash flow risk',
                'risk_level' => $cashFlowRisk['risk_level'],
                'score' => $cashFlowRisk['score'],
                'factors' => $cashFlowRisk['factors'],
                'mitigation' => 'Improve cash flow management and secure credit line'
            ];
        }

        // Check receivables risk
        $receivablesRisk = $this->checkReceivablesRisk($tenantId, $branchId);
        if ($receivablesRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'FINANCIAL',
                'type' => 'RECEIVABLES',
                'description' => 'Accounts receivable risk',
                'risk_level' => $receivablesRisk['risk_level'],
                'score' => $receivablesRisk['score'],
                'factors' => $receivablesRisk['factors'],
                'mitigation' => 'Implement stricter credit policies and follow-up procedures'
            ];
        }

        return $risks;
    }

    /**
     * Check cash flow risk
     */
    private function checkCashFlowRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                SUM(CASE WHEN payment_status = 'PENDING' THEN total_amount ELSE 0 END) as pending_payments,
                SUM(total_amount) as total_revenue
            FROM orders
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              AND status = 'COMPLETED'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $financials = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        $pendingRatio = $financials['total_revenue'] > 0 
            ? ($financials['pending_payments'] / $financials['total_revenue']) * 100 
            : 0;

        if ($pendingRatio > 30) {
            $factors[] = 'High pending payment ratio';
            $score += 40;
        }

        if ($pendingRatio > 50) {
            $factors[] = 'Critical pending payment ratio';
            $score += 30;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Check receivables risk
     */
    private function checkReceivablesRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = 'OVERDUE' THEN 1 ELSE 0 END) as overdue_invoices,
                SUM(CASE WHEN due_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as severely_overdue
            FROM invoices
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $invoices = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        if ($invoices['overdue_invoices'] > 0) {
            $factors[] = 'Overdue invoices exist';
            $score += 30;
        }

        if ($invoices['severely_overdue'] > 0) {
            $factors[] = 'Severely overdue invoices';
            $score += 40;
        }

        if ($invoices['total_invoices'] > 0 && $invoices['overdue_invoices'] / $invoices['total_invoices'] > 0.2) {
            $factors[] = 'High overdue invoice ratio';
            $score += 30;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Assess compliance risks
     */
    private function assessComplianceRisks($tenantId, $branchId)
    {
        $risks = [];

        // Check license expiration risk
        $licenseRisk = $this->checkLicenseExpirationRisk($tenantId, $branchId);
        if ($licenseRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'COMPLIANCE',
                'type' => 'LICENSE_EXPIRATION',
                'description' => 'License expiration risk',
                'risk_level' => $licenseRisk['risk_level'],
                'score' => $licenseRisk['score'],
                'factors' => $licenseRisk['factors'],
                'mitigation' => 'Implement license tracking and renewal reminders'
            ];
        }

        // Check food safety compliance risk
        $foodSafetyRisk = $this->checkFoodSafetyComplianceRisk($tenantId, $branchId);
        if ($foodSafetyRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'COMPLIANCE',
                'type' => 'FOOD_SAFETY',
                'description' => 'Food safety compliance risk',
                'risk_level' => $foodSafetyRisk['risk_level'],
                'score' => $foodSafetyRisk['score'],
                'factors' => $foodSafetyRisk['factors'],
                'mitigation' => 'Implement regular food safety audits and training'
            ];
        }

        return $risks;
    }

    /**
     * Check license expiration risk
     */
    private function checkLicenseExpirationRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_licenses,
                SUM(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_soon,
                SUM(CASE WHEN expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired
            FROM licenses
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $licenses = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        if ($licenses['expired'] > 0) {
            $factors[] = 'Expired licenses exist';
            $score += 50;
        }

        if ($licenses['expiring_soon'] > 0) {
            $factors[] = 'Licenses expiring soon';
            $score += 30;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Check food safety compliance risk
     */
    private function checkFoodSafetyComplianceRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_audits,
                SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed_audits,
                SUM(CASE WHEN last_audit_date < DATE_SUB(CURDATE(), INTERVAL 180 DAY) THEN 1 ELSE 0 END) as overdue_audits
            FROM food_safety_audits
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $audits = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        if ($audits['failed_audits'] > 0) {
            $factors[] = 'Failed food safety audits';
            $score += 50;
        }

        if ($audits['overdue_audits'] > 0) {
            $factors[] = 'Overdue food safety audits';
            $score += 30;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Assess supply chain risks
     */
    private function assessSupplyChainRisks($tenantId, $branchId)
    {
        $risks = [];

        // Check supplier dependency risk
        $supplierRisk = $this->checkSupplierDependencyRisk($tenantId, $branchId);
        if ($supplierRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'SUPPLY_CHAIN',
                'type' => 'SUPPLIER_DEPENDENCY',
                'description' => 'Supplier dependency risk',
                'risk_level' => $supplierRisk['risk_level'],
                'score' => $supplierRisk['score'],
                'factors' => $supplierRisk['factors'],
                'mitigation' => 'Diversify supplier base and develop backup suppliers'
            ];
        }

        return $risks;
    }

    /**
     * Check supplier dependency risk
     */
    private function checkSupplierDependencyRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_suppliers,
                COUNT(DISTINCT category) as categories_covered
            FROM suppliers
            WHERE tenant_id = ? AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $suppliers = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        if ($suppliers['total_suppliers'] < 3) {
            $factors[] = 'Low supplier count';
            $score += 40;
        }

        if ($suppliers['categories_covered'] < 5) {
            $factors[] = 'Limited category coverage';
            $score += 30;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Assess technology risks
     */
    private function assessTechnologyRisks($tenantId, $branchId)
    {
        $risks = [];

        // Check data backup risk
        $backupRisk = $this->checkDataBackupRisk($tenantId, $branchId);
        if ($backupRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'TECHNOLOGY',
                'type' => 'DATA_BACKUP',
                'description' => 'Data backup risk',
                'risk_level' => $backupRisk['risk_level'],
                'score' => $backupRisk['score'],
                'factors' => $backupRisk['factors'],
                'mitigation' => 'Implement automated backup and disaster recovery plan'
            ];
        }

        // Check system downtime risk
        $downtimeRisk = $this->checkSystemDowntimeRisk($tenantId, $branchId);
        if ($downtimeRisk['risk_level'] !== 'LOW') {
            $risks[] = [
                'category' => 'TECHNOLOGY',
                'type' => 'SYSTEM_DOWNTIME',
                'description' => 'System downtime risk',
                'risk_level' => $downtimeRisk['risk_level'],
                'score' => $downtimeRisk['score'],
                'factors' => $downtimeRisk['factors'],
                'mitigation' => 'Implement high availability and monitoring'
            ];
        }

        return $risks;
    }

    /**
     * Check data backup risk
     */
    private function checkDataBackupRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                last_backup_date,
                backup_status
            FROM system_backups
            WHERE tenant_id = ? AND branch_id = ?
            ORDER BY last_backup_date DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        if (!$backup) {
            $factors[] = 'No backup records found';
            $score += 50;
        } else {
            if ($backup['backup_status'] !== 'SUCCESS') {
                $factors[] = 'Last backup failed';
                $score += 40;
            }

            if ($backup['last_backup_date'] < date('Y-m-d', strtotime('-7 days'))) {
                $factors[] = 'Backup is more than 7 days old';
                $score += 30;
            }
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Check system downtime risk
     */
    private function checkSystemDowntimeRisk($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_incidents,
                SUM(CASE WHEN duration_minutes > 60 THEN 1 ELSE 0 END) as long_downtime,
                SUM(CASE WHEN incident_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recent_incidents
            FROM system_incidents
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $incidents = $stmt->fetch(PDO::FETCH_ASSOC);

        $factors = [];
        $score = 0;

        if ($incidents['recent_incidents'] > 3) {
            $factors[] = 'High recent incident count';
            $score += 40;
        }

        if ($incidents['long_downtime'] > 0) {
            $factors[] = 'Long downtime incidents occurred';
            $score += 30;
        }

        $riskLevel = $this->getRiskLevel($score);

        return [
            'risk_level' => $riskLevel,
            'score' => $score,
            'factors' => $factors
        ];
    }

    /**
     * Calculate overall risk score
     */
    private function calculateOverallRiskScore($risks)
    {
        if (empty($risks)) {
            return 0;
        }

        $totalScore = 0;
        foreach ($risks as $risk) {
            $totalScore += $risk['score'];
        }

        return $totalScore / count($risks);
    }

    /**
     * Get risk level from score
     */
    private function getRiskLevel($score)
    {
        if ($score >= 70) return 'CRITICAL';
        if ($score >= 50) return 'HIGH';
        if ($score >= 30) return 'MEDIUM';
        return 'LOW';
    }

    /**
     * Generate risk recommendations
     */
    private function generateRiskRecommendations($risks)
    {
        $recommendations = [];

        foreach ($risks as $risk) {
            if ($risk['risk_level'] === 'CRITICAL' || $risk['risk_level'] === 'HIGH') {
                $recommendations[] = [
                    'priority' => $risk['risk_level'],
                    'risk_type' => $risk['type'],
                    'recommendation' => $risk['mitigation'],
                    'target_date' => date('Y-m-d', strtotime('+30 days'))
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Create mitigation plan
     */
    public function createMitigationPlan($tenantId, $branchId, $riskId, $mitigationData)
    {
        $sql = "
            INSERT INTO risk_mitigation_plans
            (tenant_id, branch_id, risk_id, plan_name, description, actions, 
             responsible_person, target_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $tenantId,
            $branchId,
            $riskId,
            $mitigationData['plan_name'],
            $mitigationData['description'] ?? '',
            json_encode($mitigationData['actions'] ?? []),
            $mitigationData['responsible_person'],
            $mitigationData['target_date']
        ]);

        return [
            'success' => $result,
            'plan_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Monitor risks
     */
    public function monitorRisks($tenantId, $branchId)
    {
        // Get active risks
        $activeRisks = $this->getActiveRisks($tenantId, $branchId);
        
        // Get mitigation plans
        $mitigationPlans = $this->getMitigationPlans($tenantId, $branchId);
        
        // Get risk trends
        $riskTrends = $this->getRiskTrends($tenantId, $branchId);

        return [
            'active_risks' => $activeRisks,
            'mitigation_plans' => $mitigationPlans,
            'risk_trends' => $riskTrends,
            'monitored_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get active risks
     */
    private function getActiveRisks($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                risk_id,
                risk_type,
                risk_level,
                score,
                identified_at,
                status
            FROM risk_register
            WHERE tenant_id = ? AND branch_id = ? AND status = 'ACTIVE'
            ORDER BY score DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get mitigation plans
     */
    private function getMitigationPlans($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                plan_id,
                risk_id,
                plan_name,
                status,
                target_date,
                progress_percentage
            FROM risk_mitigation_plans
            WHERE tenant_id = ? AND branch_id = ? AND status != 'COMPLETED'
            ORDER BY target_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get risk trends
     */
    private function getRiskTrends($tenantId, $branchId)
    {
        $trends = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t', strtotime("-$i months"));
            
            $assessment = $this->assessRisks($tenantId, $branchId);
            
            $trends[] = [
                'month' => date('Y-m', strtotime("-$i months")),
                'overall_score' => $assessment['overall_risk_score'],
                'total_risks' => $assessment['total_risks']
            ];
        }

        return $trends;
    }

    /**
     * Calculate risk score for specific risk data
     */
    public function calculateRiskScore($riskData)
    {
        $score = 0;
        
        // Impact score (0-50)
        $impact = $riskData['impact'] ?? 'MEDIUM';
        $impactScores = ['LOW' => 10, 'MEDIUM' => 30, 'HIGH' => 50];
        $score += $impactScores[$impact] ?? 30;
        
        // Likelihood score (0-30)
        $likelihood = $riskData['likelihood'] ?? 'MEDIUM';
        $likelihoodScores = ['LOW' => 5, 'MEDIUM' => 15, 'HIGH' => 30];
        $score += $likelihoodScores[$likelihood] ?? 15;
        
        // Detectability score (0-20)
        $detectability = $riskData['detectability'] ?? 'MEDIUM';
        $detectabilityScores = ['HIGH' => 5, 'MEDIUM' => 10, 'LOW' => 20];
        $score += $detectabilityScores[$detectability] ?? 10;

        return [
            'score' => $score,
            'risk_level' => $this->getRiskLevel($score),
            'components' => [
                'impact' => $impactScores[$impact] ?? 30,
                'likelihood' => $likelihoodScores[$likelihood] ?? 15,
                'detectability' => $detectabilityScores[$detectability] ?? 10
            ]
        ];
    }

    /**
     * Get risk dashboard data
     */
    public function getDashboardData($tenantId, $branchId)
    {
        $assessment = $this->assessRisks($tenantId, $branchId);
        $monitoring = $this->monitorRisks($tenantId, $branchId);

        return [
            'current_assessment' => $assessment,
            'monitoring' => $monitoring,
            'risk_by_category' => $this->getRisksByCategory($assessment['risks']),
            'top_risks' => $this->getTopRisks($assessment['risks'])
        ];
    }

    /**
     * Get risks by category
     */
    private function getRisksByCategory($risks)
    {
        $byCategory = [];
        
        foreach ($risks as $risk) {
            $category = $risk['category'];
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = 0;
            }
            $byCategory[$category]++;
        }

        return $byCategory;
    }

    /**
     * Get top risks
     */
    private function getTopRisks($risks)
    {
        usort($risks, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($risks, 0, 5);
    }
}
