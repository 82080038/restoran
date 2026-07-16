<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * ComplianceEngine - Compliance Automation Engine for RESTAURANT_ERP
 * 
 * This engine handles labor law compliance, tax calculation automation,
 * food safety compliance tracking, and regulatory requirements
 * 
 * @package EBP\App\Core\Engines
 * @version 1.0.0
 */

class ComplianceEngine implements EngineInterface
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

        $action = $params['action'] ?? 'check_labor_compliance';

        switch ($action) {
            case 'check_labor_compliance':
                return $this->executeCheckLaborCompliance($params);
            case 'calculate_taxes':
                return $this->executeCalculateTaxes($params);
            case 'check_food_safety':
                return $this->executeCheckFoodSafety($params);
            case 'track_regulatory_updates':
                return $this->executeTrackRegulatoryUpdates($params);
            case 'generate_compliance_documentation':
                return $this->executeGenerateComplianceDocumentation($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeCheckLaborCompliance(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$branchId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->checkLaborCompliance($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCalculateTaxes(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$branchId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->calculateTaxes($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeTrackRegulatoryUpdates(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $regulationType = $params['regulation_type'] ?? null;

        if (!$tenantId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id'
            ];
        }

        try {
            $result = $this->trackRegulatoryUpdates($tenantId, $regulationType);
            return [
                'success' => true,
                'updates' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCheckFoodSafety(array $params): array
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
            $result = $this->checkFoodSafetyCompliance($tenantId, $branchId);
            return [
                'success' => true,
                'result' => $result
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
            'name' => 'Compliance Engine',
            'version' => '1.0.0',
            'description' => 'Handles labor law compliance, tax calculation, and food safety',
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
     * Check labor law compliance for a specific period
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Compliance check results
     */
    public function checkLaborCompliance($tenantId, $branchId, $startDate, $endDate)
    {
        $violations = [];
        $warnings = [];

        // Check minimum wage compliance
        $minWageCheck = $this->checkMinimumWage($tenantId, $branchId, $startDate, $endDate);
        if (!$minWageCheck['compliant']) {
            $violations[] = $minWageCheck;
        }

        // Check overtime compliance
        $overtimeCheck = $this->checkOvertimeCompliance($tenantId, $branchId, $startDate, $endDate);
        if (!$overtimeCheck['compliant']) {
            $violations[] = $overtimeCheck;
        }

        // Check break time compliance
        $breakCheck = $this->checkBreakTimeCompliance($tenantId, $branchId, $startDate, $endDate);
        if (!$breakCheck['compliant']) {
            $warnings[] = $breakCheck;
        }

        // Check working hours compliance
        $hoursCheck = $this->checkWorkingHoursCompliance($tenantId, $branchId, $startDate, $endDate);
        if (!$hoursCheck['compliant']) {
            $violations[] = $hoursCheck;
        }

        // Determine overall status
        $status = empty($violations) ? (empty($warnings) ? 'COMPLIANT' : 'WARNING') : 'NON_COMPLIANT';

        // Log compliance check
        $this->logComplianceCheck($tenantId, $branchId, 'LABOR_LAW', $status, $violations, $warnings, $startDate, $endDate);

        return [
            'status' => $status,
            'violations' => $violations,
            'warnings' => $warnings,
            'check_date' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Calculate tax for a specific period
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Tax calculation results
     */
    public function calculateTaxes($tenantId, $branchId, $startDate, $endDate)
    {
        // Get sales data for the period
        $salesData = $this->getSalesData($tenantId, $branchId, $startDate, $endDate);
        
        // Calculate VAT (PPN in Indonesia - 11%)
        $vatRate = 0.11;
        $vatAmount = $salesData['total_sales'] * $vatRate;
        
        // Calculate service tax (if applicable - 10%)
        $serviceTaxRate = 0.10;
        $serviceTaxAmount = $salesData['total_sales'] * $serviceTaxRate;
        
        // Calculate income tax (simplified - 22% of profit)
        $profitMargin = 0.20; // Assume 20% profit margin
        $profit = $salesData['total_sales'] * $profitMargin;
        $incomeTaxRate = 0.22;
        $incomeTaxAmount = $profit * $incomeTaxRate;
        
        $totalTax = $vatAmount + $serviceTaxAmount + $incomeTaxAmount;

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'sales_data' => $salesData,
            'taxes' => [
                'vat' => [
                    'rate' => $vatRate * 100,
                    'amount' => $vatAmount,
                    'description' => 'PPN (Value Added Tax)'
                ],
                'service_tax' => [
                    'rate' => $serviceTaxRate * 100,
                    'amount' => $serviceTaxAmount,
                    'description' => 'Service Tax'
                ],
                'income_tax' => [
                    'rate' => $incomeTaxRate * 100,
                    'amount' => $incomeTaxAmount,
                    'description' => 'Income Tax (PPh)'
                ]
            ],
            'total_tax' => $totalTax,
            'calculated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check food safety compliance
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Food safety compliance results
     */
    public function checkFoodSafetyCompliance($tenantId, $branchId)
    {
        $violations = [];
        $warnings = [];

        // Check temperature monitoring
        $tempCheck = $this->checkTemperatureMonitoring($tenantId, $branchId);
        if (!$tempCheck['compliant']) {
            $violations[] = $tempCheck;
        }

        // Check expiration tracking
        $expirationCheck = $this->checkExpirationTracking($tenantId, $branchId);
        if (!$expirationCheck['compliant']) {
            $violations[] = $expirationCheck;
        }

        // Check hygiene certification
        $hygieneCheck = $this->checkHygieneCertification($tenantId, $branchId);
        if (!$hygieneCheck['compliant']) {
            $warnings[] = $hygieneCheck;
        }

        // Check HACCP compliance
        $haccpCheck = $this->checkHACCPCompliance($tenantId, $branchId);
        if (!$haccpCheck['compliant']) {
            $warnings[] = $haccpCheck;
        }

        $status = empty($violations) ? (empty($warnings) ? 'COMPLIANT' : 'WARNING') : 'NON_COMPLIANT';

        $this->logComplianceCheck($tenantId, $branchId, 'FOOD_SAFETY', $status, $violations, $warnings);

        return [
            'status' => $status,
            'violations' => $violations,
            'warnings' => $warnings,
            'check_date' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check minimum wage compliance
     */
    private function checkMinimumWage($tenantId, $branchId, $startDate, $endDate)
    {
        // Get regional minimum wage (UMR) - simplified
        $regionalMinimumWage = 4500000; // Example: Jakarta UMR 2024
        
        // Get employee hours and wages
        $sql = "
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                SUM(ah.hours_worked) as total_hours,
                SUM(ah.overtime_hours) as total_overtime,
                AVG(ah.hourly_rate) as avg_hourly_rate
            FROM employees e
            LEFT JOIN attendance_history ah ON e.employee_id = ah.employee_id
            WHERE e.tenant_id = ? 
              AND e.branch_id = ?
              AND ah.work_date BETWEEN ? AND ?
            GROUP BY e.employee_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $violations = [];
        foreach ($employees as $employee) {
            if ($employee['total_hours'] > 0) {
                $monthlyWage = ($employee['total_hours'] + $employee['total_overtime']) * $employee['avg_hourly_rate'];
                
                if ($monthlyWage < $regionalMinimumWage) {
                    $violations[] = [
                        'employee_id' => $employee['employee_id'],
                        'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                        'actual_wage' => $monthlyWage,
                        'required_wage' => $regionalMinimumWage,
                        'shortfall' => $regionalMinimumWage - $monthlyWage
                    ];
                }
            }
        }

        return [
            'compliant' => empty($violations),
            'type' => 'MINIMUM_WAGE',
            'regional_minimum' => $regionalMinimumWage,
            'violations' => $violations
        ];
    }

    /**
     * Check overtime compliance
     */
    private function checkOvertimeCompliance($tenantId, $branchId, $startDate, $endDate)
    {
        // Get overtime data
        $sql = "
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                SUM(ah.overtime_hours) as total_overtime,
                COUNT(DISTINCT ah.work_date) as overtime_days
            FROM employees e
            LEFT JOIN attendance_history ah ON e.employee_id = ah.employee_id
            WHERE e.tenant_id = ? 
              AND e.branch_id = ?
              AND ah.work_date BETWEEN ? AND ?
              AND ah.overtime_hours > 0
            GROUP BY e.employee_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $violations = [];
        foreach ($employees as $employee) {
            // Check if overtime exceeds 4 hours per day (Indonesian labor law)
            $sql = "
                SELECT work_date, overtime_hours
                FROM attendance_history
                WHERE employee_id = ? 
                  AND work_date BETWEEN ? AND ?
                  AND overtime_hours > 4
            ";
            $stmt2 = $this->db->prepare($sql);
            $stmt2->execute([$employee['employee_id'], $startDate, $endDate]);
            $excessiveOvertime = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($excessiveOvertime)) {
                $violations[] = [
                    'employee_id' => $employee['employee_id'],
                    'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                    'excessive_overtime_days' => $excessiveOvertime,
                    'total_overtime_hours' => $employee['total_overtime']
                ];
            }
        }

        return [
            'compliant' => empty($violations),
            'type' => 'OVERTIME',
            'violations' => $violations
        ];
    }

    /**
     * Check break time compliance
     */
    private function checkBreakTimeCompliance($tenantId, $branchId, $startDate, $endDate)
    {
        // Check if employees working more than 4 hours without break
        $sql = "
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                COUNT(*) as violations
            FROM employees e
            LEFT JOIN attendance_history ah ON e.employee_id = ah.employee_id
            WHERE e.tenant_id = ? 
              AND e.branch_id = ?
              AND ah.work_date BETWEEN ? AND ?
              AND ah.hours_worked > 4
              AND (ah.break_minutes IS NULL OR ah.break_minutes < 30)
            GROUP BY e.employee_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'compliant' => empty($violations),
            'type' => 'BREAK_TIME',
            'violations' => $violations
        ];
    }

    /**
     * Check working hours compliance
     */
    private function checkWorkingHoursCompliance($tenantId, $branchId, $startDate, $endDate)
    {
        // Check if employees work more than 40 hours per week
        $sql = "
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                SUM(ah.hours_worked) as total_hours,
                COUNT(DISTINCT WEEK(ah.work_date, 1)) as weeks_worked
            FROM employees e
            LEFT JOIN attendance_history ah ON e.employee_id = ah.employee_id
            WHERE e.tenant_id = ? 
              AND e.branch_id = ?
              AND ah.work_date BETWEEN ? AND ?
            GROUP BY e.employee_id
            HAVING total_hours / weeks_worked > 48
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'compliant' => empty($violations),
            'type' => 'WORKING_HOURS',
            'violations' => $violations
        ];
    }

    /**
     * Get sales data for tax calculation
     */
    private function getSalesData($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_sales,
                SUM(tax_amount) as total_tax_collected,
                AVG(total_amount) as average_order_value
            FROM orders
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND created_at BETWEEN ? AND ?
              AND status = 'COMPLETED'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check temperature monitoring
     */
    private function checkTemperatureMonitoring($tenantId, $branchId)
    {
        $sql = "
            SELECT COUNT(*) as missing_records
            FROM temperature_logs
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $expectedRecords = 7 * 3; // 7 days, 3 checks per day
        $compliant = $result['missing_records'] >= ($expectedRecords * 0.8); // 80% compliance threshold

        return [
            'compliant' => $compliant,
            'type' => 'TEMPERATURE_MONITORING',
            'expected_records' => $expectedRecords,
            'actual_records' => $result['missing_records']
        ];
    }

    /**
     * Check expiration tracking
     */
    private function checkExpirationTracking($tenantId, $branchId)
    {
        $sql = "
            SELECT COUNT(*) as expired_items
            FROM inventory_items ii
            JOIN stock_balances sb ON ii.inventory_id = sb.inventory_id
            WHERE ii.tenant_id = ? 
              AND sb.branch_id = ?
              AND ii.expiration_date < CURDATE()
              AND sb.quantity > 0
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'compliant' => $result['expired_items'] == 0,
            'type' => 'EXPIRATION_TRACKING',
            'expired_items' => $result['expired_items']
        ];
    }

    /**
     * Check hygiene certification
     */
    private function checkHygieneCertification($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                certification_number,
                expiry_date,
                issuing_authority
            FROM certifications
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND certification_type = 'HYGIENE'
              AND expiry_date > CURDATE()
            ORDER BY expiry_date DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $certification = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'compliant' => !empty($certification),
            'type' => 'HYGIENE_CERTIFICATION',
            'certification' => $certification
        ];
    }

    /**
     * Check HACCP compliance
     */
    private function checkHACCPCompliance($tenantId, $branchId)
    {
        $sql = "
            SELECT COUNT(*) as completed_checkpoints
            FROM haccp_checkpoints
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND last_check_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "
            SELECT COUNT(*) as total_checkpoints
            FROM haccp_checkpoints
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC);

        $compliant = $total['total_checkpoints'] > 0 && 
                    ($result['completed_checkpoints'] / $total['total_checkpoints']) >= 0.9;

        return [
            'compliant' => $compliant,
            'type' => 'HACCP',
            'completed' => $result['completed_checkpoints'],
            'total' => $total['total_checkpoints']
        ];
    }

    /**
     * Log compliance check
     */
    private function logComplianceCheck($tenantId, $branchId, $checkType, $status, $violations, $warnings, $startDate = null, $endDate = null)
    {
        $sql = "
            INSERT INTO compliance_checks
            (tenant_id, branch_id, check_type, status, violations_json, warnings_json, start_date, end_date, checked_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $checkType,
            $status,
            json_encode($violations),
            json_encode($warnings),
            $startDate,
            $endDate
        ]);
    }

    /**
     * Get compliance dashboard data
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Dashboard data
     */
    public function getDashboardData($tenantId, $branchId)
    {
        // Get recent compliance checks
        $recentChecks = $this->getRecentComplianceChecks($tenantId, $branchId);

        // Get active alerts
        $alerts = $this->getActiveComplianceAlerts($tenantId, $branchId);

        // Get compliance summary
        $summary = $this->getComplianceSummary($tenantId, $branchId);

        return [
            'summary' => $summary,
            'recent_checks' => $recentChecks,
            'alerts' => $alerts
        ];
    }

    /**
     * Get recent compliance checks
     */
    private function getRecentComplianceChecks($tenantId, $branchId)
    {
        $sql = "
            SELECT check_id, check_type, status, checked_at
            FROM compliance_checks
            WHERE tenant_id = ? AND branch_id = ?
            ORDER BY checked_at DESC
            LIMIT 20
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active compliance alerts
     */
    private function getActiveComplianceAlerts($tenantId, $branchId)
    {
        $sql = "
            SELECT alert_id, alert_type, message, created_at
            FROM compliance_alerts
            WHERE tenant_id = ? AND branch_id = ? AND status = 'ACTIVE'
            ORDER BY created_at DESC
            LIMIT 10
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get compliance summary
     */
    private function getComplianceSummary($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_checks,
                SUM(CASE WHEN status = 'COMPLIANT' THEN 1 ELSE 0 END) as compliant,
                SUM(CASE WHEN status = 'WARNING' THEN 1 ELSE 0 END) as warnings,
                SUM(CASE WHEN status = 'NON_COMPLIANT' THEN 1 ELSE 0 END) as non_compliant
            FROM compliance_checks
            WHERE tenant_id = ? AND branch_id = ?
              AND checked_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Track regulatory updates
     * 
     * @param int $tenantId Tenant ID
     * @param string $regulationType Regulation type filter
     * @return array Regulatory updates
     */
    public function trackRegulatoryUpdates($tenantId, $regulationType = null)
    {
        $regulatoryUpdates = [
            [
                'update_id' => 1,
                'regulation_type' => 'LABOR_LAW',
                'title' => 'Minimum Wage Update 2026',
                'description' => 'New minimum wage rates effective January 2026',
                'effective_date' => '2026-01-01',
                'status' => 'PENDING_REVIEW',
                'impact_level' => 'HIGH'
            ],
            [
                'update_id' => 2,
                'regulation_type' => 'FOOD_SAFETY',
                'title' => 'Food Safety Certification Requirements',
                'description' => 'Updated certification requirements for food handlers',
                'effective_date' => '2026-03-01',
                'status' => 'COMPLIANT',
                'impact_level' => 'MEDIUM'
            ],
            [
                'update_id' => 3,
                'regulation_type' => 'TAX',
                'title' => 'Tax Rate Changes Q2 2026',
                'description' => 'Updated tax rates for restaurant sector',
                'effective_date' => '2026-04-01',
                'status' => 'PENDING_IMPLEMENTATION',
                'impact_level' => 'HIGH'
            ]
        ];

        if ($regulationType) {
            $regulatoryUpdates = array_filter($regulatoryUpdates, function($update) use ($regulationType) {
                return $update['regulation_type'] === $regulationType;
            });
        }

        return [
            'tenant_id' => $tenantId,
            'regulation_type_filter' => $regulationType,
            'total_updates' => count($regulatoryUpdates),
            'updates' => array_values($regulatoryUpdates)
        ];
    }

    /**
     * Generate compliance documentation
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $documentType Document type
     * @return array Generated documentation
     */
    public function generateComplianceDocumentation($tenantId, $branchId, $documentType)
    {
        $documentTemplates = [
            'LABOR_COMPLIANCE_REPORT' => [
                'title' => 'Labor Compliance Report',
                'sections' => [
                    'employee_records' => 'Employee Records Compliance',
                    'working_hours' => 'Working Hours Compliance',
                    'overtime_payment' => 'Overtime Payment Compliance',
                    'minimum_wage' => 'Minimum Wage Compliance'
                ]
            ],
            'FOOD_SAFETY_CERTIFICATE' => [
                'title' => 'Food Safety Compliance Certificate',
                'sections' => [
                    'food_handling' => 'Food Handling Procedures',
                    'storage_conditions' => 'Storage Conditions',
                    'temperature_logs' => 'Temperature Logs',
                    'sanitation_records' => 'Sanitation Records'
                ]
            ],
            'TAX_COMPLIANCE_REPORT' => [
                'title' => 'Tax Compliance Report',
                'sections' => [
                    'sales_tax' => 'Sales Tax Compliance',
                    'payroll_tax' => 'Payroll Tax Compliance',
                    'income_tax' => 'Income Tax Withholding',
                    'tax_filing' => 'Tax Filing Records'
                ]
            ]
        ];

        $template = $documentTemplates[$documentType] ?? null;
        if (!$template) {
            throw new Exception("Unknown document type: {$documentType}");
        }

        $documentData = [
            'document_id' => 'DOC_' . time(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'document_type' => $documentType,
            'title' => $template['title'],
            'generated_at' => date('Y-m-d H:i:s'),
            'sections' => $template['sections'],
            'status' => 'GENERATED'
        ];

        return $documentData;
    }

    /**
     * Automated compliance alert system
     * Sends alerts when compliance issues are detected
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Alert results
     */
    public function runAutomatedComplianceAlerts($tenantId, $branchId)
    {
        $alerts = [];
        
        // Check for near-expiring certifications
        $certAlerts = $this->checkExpiringCertifications($tenantId, $branchId);
        $alerts = array_merge($alerts, $certAlerts);
        
        // Check for compliance violations
        $violationAlerts = $this->checkComplianceViolations($tenantId, $branchId);
        $alerts = array_merge($alerts, $violationAlerts);
        
        // Check for upcoming regulatory deadlines
        $deadlineAlerts = $this->checkRegulatoryDeadlines($tenantId);
        $alerts = array_merge($alerts, $deadlineAlerts);
        
        // Create alerts in database
        foreach ($alerts as $alert) {
            $this->createComplianceAlert($tenantId, $branchId, $alert);
        }
        
        return [
            'success' => true,
            'alerts_created' => count($alerts),
            'alerts' => $alerts,
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check for expiring certifications
     */
    private function checkExpiringCertifications($tenantId, $branchId)
    {
        $alerts = [];
        
        $sql = "
            SELECT 
                certification_id,
                certification_number,
                certification_type,
                expiry_date,
                DATEDIFF(expiry_date, CURDATE()) as days_until_expiry
            FROM certifications
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($certifications as $cert) {
            $urgency = 'LOW';
            if ($cert['days_until_expiry'] <= 7) {
                $urgency = 'CRITICAL';
            } elseif ($cert['days_until_expiry'] <= 14) {
                $urgency = 'HIGH';
            } elseif ($cert['days_until_expiry'] <= 30) {
                $urgency = 'MEDIUM';
            }
            
            $alerts[] = [
                'alert_type' => 'CERTIFICATION_EXPIRY',
                'urgency' => $urgency,
                'message' => "{$cert['certification_type']} certification expires in {$cert['days_until_expiry']} days",
                'certification_id' => $cert['certification_id'],
                'expiry_date' => $cert['expiry_date'],
                'days_until_expiry' => $cert['days_until_expiry']
            ];
        }
        
        return $alerts;
    }

    /**
     * Check for compliance violations
     */
    private function checkComplianceViolations($tenantId, $branchId)
    {
        $alerts = [];
        
        // Run compliance checks
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        
        $laborCheck = $this->checkLaborCompliance($tenantId, $branchId, $startDate, $endDate);
        if ($laborCheck['status'] === 'NON_COMPLIANT') {
            $alerts[] = [
                'alert_type' => 'LABOR_VIOLATION',
                'urgency' => 'HIGH',
                'message' => 'Labor law compliance violations detected',
                'violations' => $laborCheck['violations']
            ];
        }
        
        $foodSafetyCheck = $this->checkFoodSafetyCompliance($tenantId, $branchId);
        if ($foodSafetyCheck['status'] === 'NON_COMPLIANT') {
            $alerts[] = [
                'alert_type' => 'FOOD_SAFETY_VIOLATION',
                'urgency' => 'CRITICAL',
                'message' => 'Food safety compliance violations detected',
                'violations' => $foodSafetyCheck['violations']
            ];
        }
        
        return $alerts;
    }

    /**
     * Check for regulatory deadlines
     */
    private function checkRegulatoryDeadlines($tenantId)
    {
        $alerts = [];
        
        // Get regulatory updates with pending implementation
        $updates = $this->trackRegulatoryUpdates($tenantId);
        
        foreach ($updates['updates'] as $update) {
            if (in_array($update['status'], ['PENDING_REVIEW', 'PENDING_IMPLEMENTATION'])) {
                $daysUntilEffective = (strtotime($update['effective_date']) - time()) / 86400;
                
                if ($daysUntilEffective <= 30) {
                    $urgency = $daysUntilEffective <= 7 ? 'CRITICAL' : 'HIGH';
                    
                    $alerts[] = [
                        'alert_type' => 'REGULATORY_DEADLINE',
                        'urgency' => $urgency,
                        'message' => "{$update['title']} effective in " . ceil($daysUntilEffective) . " days",
                        'update_id' => $update['update_id'],
                        'effective_date' => $update['effective_date'],
                        'days_until_effective' => ceil($daysUntilEffective)
                    ];
                }
            }
        }
        
        return $alerts;
    }

    /**
     * Create compliance alert in database
     */
    private function createComplianceAlert($tenantId, $branchId, $alert)
    {
        $sql = "
            INSERT INTO compliance_alerts
            (tenant_id, branch_id, alert_type, urgency, message, alert_data, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 'ACTIVE')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $alert['alert_type'],
            $alert['urgency'],
            $alert['message'],
            json_encode($alert)
        ]);
    }

    /**
     * Schedule automated compliance checks
     * Sets up recurring compliance checks
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param array $schedule Schedule configuration
     * @return array Schedule results
     */
    public function scheduleAutomatedChecks($tenantId, $branchId, $schedule)
    {
        $defaultSchedule = [
            'labor_compliance' => ['frequency' => 'WEEKLY', 'day_of_week' => 1], // Monday
            'food_safety' => ['frequency' => 'DAILY'],
            'tax_calculation' => ['frequency' => 'MONTHLY', 'day_of_month' => 1],
            'regulatory_updates' => ['frequency' => 'WEEKLY', 'day_of_week' => 5] // Friday
        ];
        
        $schedule = array_merge($defaultSchedule, $schedule);
        
        $results = [];
        foreach ($schedule as $checkType => $config) {
            $sql = "
                INSERT INTO compliance_schedules
                (tenant_id, branch_id, check_type, frequency, schedule_config, created_at, status)
                VALUES (?, ?, ?, ?, ?, NOW(), 'ACTIVE')
                ON DUPLICATE KEY UPDATE
                    frequency = VALUES(frequency),
                    schedule_config = VALUES(schedule_config),
                    updated_at = NOW()
            ";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $tenantId,
                $branchId,
                $checkType,
                $config['frequency'],
                json_encode($config)
            ]);
            
            $results[$checkType] = [
                'success' => $result,
                'frequency' => $config['frequency'],
                'config' => $config
            ];
        }
        
        return [
            'success' => true,
            'scheduled_checks' => count($results),
            'schedules' => $results,
            'scheduled_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Execute scheduled compliance checks
     * Runs checks that are due based on schedule
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Execution results
     */
    public function executeScheduledChecks($tenantId, $branchId)
    {
        // Get active schedules
        $sql = "
            SELECT check_type, frequency, schedule_config
            FROM compliance_schedules
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND status = 'ACTIVE'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $results = [];
        $executed = 0;
        $skipped = 0;
        
        foreach ($schedules as $schedule) {
            if ($this->isCheckDue($schedule)) {
                $result = $this->executeScheduledCheck($tenantId, $branchId, $schedule);
                $results[$schedule['check_type']] = $result;
                $executed++;
            } else {
                $skipped++;
            }
        }
        
        return [
            'success' => true,
            'total_schedules' => count($schedules),
            'executed' => $executed,
            'skipped' => $skipped,
            'results' => $results,
            'executed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check if a scheduled check is due
     */
    private function isCheckDue($schedule)
    {
        $config = json_decode($schedule['schedule_config'], true);
        $currentDayOfWeek = date('N'); // 1 = Monday, 7 = Sunday
        $currentDayOfMonth = date('j');
        
        switch ($schedule['frequency']) {
            case 'DAILY':
                return true;
            case 'WEEKLY':
                return $currentDayOfWeek == ($config['day_of_week'] ?? 1);
            case 'MONTHLY':
                return $currentDayOfMonth == ($config['day_of_month'] ?? 1);
            default:
                return false;
        }
    }

    /**
     * Execute a single scheduled check
     */
    private function executeScheduledCheck($tenantId, $branchId, $schedule)
    {
        $checkType = $schedule['check_type'];
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        
        try {
            switch ($checkType) {
                case 'labor_compliance':
                    $result = $this->checkLaborCompliance($tenantId, $branchId, $startDate, $endDate);
                    break;
                case 'food_safety':
                    $result = $this->checkFoodSafetyCompliance($tenantId, $branchId);
                    break;
                case 'tax_calculation':
                    $result = $this->calculateTaxes($tenantId, $branchId, $startDate, $endDate);
                    break;
                case 'regulatory_updates':
                    $result = $this->trackRegulatoryUpdates($tenantId);
                    break;
                default:
                    $result = ['error' => 'Unknown check type'];
            }
            
            // Log execution
            $this->logScheduledCheckExecution($tenantId, $branchId, $checkType, $result);
            
            return [
                'success' => true,
                'check_type' => $checkType,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'check_type' => $checkType,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Log scheduled check execution
     */
    private function logScheduledCheckExecution($tenantId, $branchId, $checkType, $result)
    {
        $sql = "
            INSERT INTO compliance_check_executions
            (tenant_id, branch_id, check_type, result_json, executed_at, status)
            VALUES (?, ?, ?, ?, NOW(), 'COMPLETED')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $checkType, json_encode($result)]);
    }

    /**
     * Assess regulatory change impact
     * Analyzes how regulatory changes affect the business
     * 
     * @param int $tenantId Tenant ID
     * @param array $regulatoryChange Regulatory change details
     * @return array Impact assessment
     */
    public function assessRegulatoryImpact($tenantId, $regulatoryChange)
    {
        $impact = [
            'tenant_id' => $tenantId,
            'regulatory_change' => $regulatoryChange,
            'impact_areas' => [],
            'required_actions' => [],
            'estimated_cost' => 0,
            'timeline' => [],
            'assessed_at' => date('Y-m-d H:i:s')
        ];
        
        // Analyze impact based on regulation type
        switch ($regulatoryChange['regulation_type']) {
            case 'LABOR_LAW':
                $impact = $this->assessLaborLawImpact($tenantId, $regulatoryChange, $impact);
                break;
            case 'FOOD_SAFETY':
                $impact = $this->assessFoodSafetyImpact($tenantId, $regulatoryChange, $impact);
                break;
            case 'TAX':
                $impact = $this->assessTaxImpact($tenantId, $regulatoryChange, $impact);
                break;
            default:
                $impact['impact_areas'][] = [
                    'area' => 'GENERAL',
                    'impact_level' => 'UNKNOWN',
                    'description' => 'Impact assessment not available for this regulation type'
                ];
        }
        
        // Save impact assessment
        $this->saveImpactAssessment($tenantId, $impact);
        
        return $impact;
    }

    /**
     * Assess labor law impact
     */
    private function assessLaborLawImpact($tenantId, $regulatoryChange, $impact)
    {
        // Get current employee data
        $sql = "
            SELECT COUNT(*) as employee_count,
                   AVG(monthly_salary) as avg_salary
            FROM employees
            WHERE tenant_id = ? AND status = 'ACTIVE'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $employeeData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate potential wage increase impact
        if (isset($regulatoryChange['new_minimum_wage'])) {
            $wageIncrease = $regulatoryChange['new_minimum_wage'] - 4500000; // Current UMR
            $affectedEmployees = 0;
            
            if ($employeeData['avg_salary'] < $regulatoryChange['new_minimum_wage']) {
                $affectedEmployees = $employeeData['employee_count'];
            }
            
            $monthlyCostIncrease = $affectedEmployees * $wageIncrease;
            $annualCostIncrease = $monthlyCostIncrease * 12;
            
            $impact['impact_areas'][] = [
                'area' => 'LABOR_COSTS',
                'impact_level' => $annualCostIncrease > 100000000 ? 'HIGH' : 'MEDIUM',
                'description' => "Minimum wage increase of {$wageIncrease} per employee",
                'affected_employees' => $affectedEmployees,
                'monthly_increase' => $monthlyCostIncrease,
                'annual_increase' => $annualCostIncrease
            ];
            
            $impact['estimated_cost'] += $annualCostIncrease;
            $impact['required_actions'][] = [
                'action' => 'UPDATE_SALARY_RATES',
                'priority' => 'HIGH',
                'deadline' => $regulatoryChange['effective_date'],
                'description' => 'Update employee salaries to meet new minimum wage requirements'
            ];
        }
        
        return $impact;
    }

    /**
     * Assess food safety impact
     */
    private function assessFoodSafetyImpact($tenantId, $regulatoryChange, $impact)
    {
        $impact['impact_areas'][] = [
            'area' => 'FOOD_SAFETY_PROCEDURES',
            'impact_level' => 'MEDIUM',
            'description' => 'Updated food safety procedures required'
        ];
        
        $impact['required_actions'][] = [
            'action' => 'TRAIN_STAFF',
            'priority' => 'HIGH',
            'deadline' => $regulatoryChange['effective_date'],
            'description' => 'Train staff on new food safety requirements'
        ];
        
        $impact['estimated_cost'] += 5000000; // Estimated training cost
        
        return $impact;
    }

    /**
     * Assess tax impact
     */
    private function assessTaxImpact($tenantId, $regulatoryChange, $impact)
    {
        if (isset($regulatoryChange['new_tax_rate'])) {
            // Get recent sales data
            $sql = "
                SELECT SUM(total_amount) as total_sales
                FROM orders
                WHERE tenant_id = ? 
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                  AND status = 'COMPLETED'
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
            $salesData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $quarterlySales = $salesData['total_sales'] * 4; // Extrapolate to yearly
            $taxImpact = $quarterlySales * ($regulatoryChange['new_tax_rate'] - 0.11); // Difference from current 11%
            
            $impact['impact_areas'][] = [
                'area' => 'TAX_LIABILITY',
                'impact_level' => abs($taxImpact) > 50000000 ? 'HIGH' : 'MEDIUM',
                'description' => 'Tax rate change affecting tax liability',
                'quarterly_sales' => $quarterlySales,
                'estimated_tax_impact' => $taxImpact
            ];
            
            $impact['estimated_cost'] += abs($taxImpact);
        }
        
        return $impact;
    }

    /**
     * Save impact assessment
     */
    private function saveImpactAssessment($tenantId, $impact)
    {
        $sql = "
            INSERT INTO regulatory_impact_assessments
            (tenant_id, impact_json, assessed_at, status)
            VALUES (?, ?, NOW(), 'PENDING_REVIEW')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, json_encode($impact)]);
    }

    /**
     * Get compliance workflow recommendations
     * Provides automated recommendations for compliance improvements
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Recommendations
     */
    public function getComplianceRecommendations($tenantId, $branchId)
    {
        $recommendations = [];
        
        // Analyze compliance history
        $complianceHistory = $this->getComplianceHistory($tenantId, $branchId);
        
        // Identify recurring issues
        $recurringIssues = $this->identifyRecurringIssues($complianceHistory);
        
        // Generate recommendations based on issues
        foreach ($recurringIssues as $issue) {
            $recommendations[] = $this->generateRecommendation($issue);
        }
        
        // Get proactive recommendations
        $proactiveRecommendations = $this->getProactiveRecommendations($tenantId, $branchId);
        $recommendations = array_merge($recommendations, $proactiveRecommendations);
        
        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'total_recommendations' => count($recommendations),
            'recommendations' => $recommendations,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get compliance history
     */
    private function getComplianceHistory($tenantId, $branchId)
    {
        $sql = "
            SELECT check_type, status, checked_at, violations_json
            FROM compliance_checks
            WHERE tenant_id = ? AND branch_id = ?
            ORDER BY checked_at DESC
            LIMIT 100
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Identify recurring compliance issues
     */
    private function identifyRecurringIssues($complianceHistory)
    {
        $issues = [];
        $issueCount = [];
        
        foreach ($complianceHistory as $check) {
            if ($check['status'] !== 'COMPLIANT') {
                $violations = json_decode($check['violations_json'], true);
                foreach ($violations as $violation) {
                    $issueKey = $violation['type'];
                    if (!isset($issueCount[$issueKey])) {
                        $issueCount[$issueKey] = 0;
                    }
                    $issueCount[$issueKey]++;
                }
            }
        }
        
        // Filter recurring issues (appears 3+ times)
        foreach ($issueCount as $issue => $count) {
            if ($count >= 3) {
                $issues[] = [
                    'type' => $issue,
                    'occurrence_count' => $count,
                    'severity' => $count >= 5 ? 'HIGH' : 'MEDIUM'
                ];
            }
        }
        
        return $issues;
    }

    /**
     * Generate recommendation for an issue
     */
    private function generateRecommendation($issue)
    {
        $recommendations = [
            'MINIMUM_WAGE' => [
                'title' => 'Review and adjust salary structure',
                'description' => 'Ensure all employees meet minimum wage requirements',
                'priority' => 'HIGH',
                'category' => 'LABOR_COMPLIANCE'
            ],
            'OVERTIME' => [
                'title' => 'Implement overtime monitoring system',
                'description' => 'Track and manage overtime hours to prevent violations',
                'priority' => 'MEDIUM',
                'category' => 'LABOR_COMPLIANCE'
            ],
            'TEMPERATURE_MONITORING' => [
                'title' => 'Establish temperature logging procedures',
                'description' => 'Implement regular temperature monitoring for food safety',
                'priority' => 'HIGH',
                'category' => 'FOOD_SAFETY'
            ],
            'EXPIRATION_TRACKING' => [
                'title' => 'Improve inventory expiration tracking',
                'description' => 'Set up automated expiration alerts for inventory items',
                'priority' => 'CRITICAL',
                'category' => 'FOOD_SAFETY'
            ]
        ];
        
        return $recommendations[$issue['type']] ?? [
            'title' => 'Address recurring compliance issue',
            'description' => "Review and resolve {$issue['type']} violations",
            'priority' => $issue['severity'],
            'category' => 'GENERAL'
        ];
    }

    /**
     * Get proactive compliance recommendations
     */
    private function getProactiveRecommendations($tenantId, $branchId)
    {
        return [
            [
                'title' => 'Schedule regular compliance training',
                'description' => 'Conduct monthly compliance training for all staff',
                'priority' => 'MEDIUM',
                'category' => 'TRAINING'
            ],
            [
                'title' => 'Implement compliance monitoring dashboard',
                'description' => 'Set up real-time compliance monitoring and alerts',
                'priority' => 'HIGH',
                'category' => 'MONITORING'
            ],
            [
                'title' => 'Review and update compliance documentation',
                'description' => 'Ensure all compliance documents are current and accessible',
                'priority' => 'MEDIUM',
                'category' => 'DOCUMENTATION'
            ]
        ];
    }
}
