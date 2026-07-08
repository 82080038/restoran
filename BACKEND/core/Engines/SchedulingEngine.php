<?php

use PDO;

require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * SchedulingEngine - Staff Scheduling and Labor Cost Optimization Engine
 * 
 * This engine handles demand-based scheduling, labor cost optimization,
 * staff availability tracking, and compliance with labor regulations
 * 
 * @package EBP\Core\Engines
 * @version 1.0.0
 */

class SchedulingEngine implements EngineInterface
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

        $action = $params['action'] ?? 'generate_schedule';

        switch ($action) {
            case 'generate_schedule':
                return $this->executeGenerateSchedule($params);
            case 'optimize_labor_cost':
                return $this->executeOptimizeLaborCost($params);
            case 'check_availability':
                return $this->executeCheckAvailability($params);
            case 'calculate_payroll':
                return $this->executeCalculatePayroll($params);
            case 'generate_payroll_report':
                return $this->executeGeneratePayrollReport($params);
            case 'track_performance':
                return $this->executeTrackPerformance($params);
            case 'generate_performance_report':
                return $this->executeGeneratePerformanceReport($params);
            case 'send_staff_notification':
                return $this->executeSendStaffNotification($params);
            case 'get_staff_messages':
                return $this->executeGetStaffMessages($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeGenerateSchedule(array $params): array
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
            $result = $this->generateSchedule($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'schedule' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeOptimizeLaborCost(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$branchId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters'
            ];
        }

        try {
            $result = $this->optimizeLaborCost($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'optimization' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCalculatePayroll(array $params): array
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
            $result = $this->calculatePayroll($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'payroll' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeTrackPerformance(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $employeeId = $params['employee_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$branchId || !$employeeId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, employee_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->trackPerformance($tenantId, $branchId, $employeeId, $startDate, $endDate);
            return [
                'success' => true,
                'performance' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeSendStaffNotification(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $senderId = $params['sender_id'] ?? null;
        $recipientId = $params['recipient_id'] ?? null;
        $message = $params['message'] ?? null;
        $messageType = $params['message_type'] ?? 'GENERAL';

        if (!$tenantId || !$branchId || !$senderId || !$recipientId || !$message) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, sender_id, recipient_id, message'
            ];
        }

        try {
            $result = $this->sendStaffNotification($tenantId, $branchId, $senderId, $recipientId, $message, $messageType);
            return [
                'success' => true,
                'notification' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGetStaffMessages(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $employeeId = $params['employee_id'] ?? null;

        if (!$tenantId || !$branchId || !$employeeId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, employee_id'
            ];
        }

        try {
            $result = $this->getStaffMessages($tenantId, $branchId, $employeeId);
            return [
                'success' => true,
                'messages' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGeneratePerformanceReport(array $params): array
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
            $result = $this->generatePerformanceReport($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'report' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGeneratePayrollReport(array $params): array
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
            $result = $this->generatePayrollReport($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'report' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCheckAvailability(array $params): array
    {
        $employeeId = $params['employee_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$employeeId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: employee_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->checkAvailability($employeeId, $startDate, $endDate);
            return [
                'success' => true,
                'availability' => $result
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
            'name' => 'Scheduling Engine',
            'version' => '1.0.0',
            'description' => 'Handles staff scheduling and labor cost optimization',
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
     * Generate schedule based on demand forecast
     */
    public function generateSchedule($tenantId, $branchId, $startDate, $endDate)
    {
        // Get demand forecast for the period
        $demandForecast = $this->getDemandForecast($tenantId, $branchId, $startDate, $endDate);
        
        // Get available staff
        $staff = $this->getAvailableStaff($tenantId, $branchId);
        
        // Get business hours
        $businessHours = $this->getBusinessHours($tenantId, $branchId);
        
        $schedule = [];
        
        foreach ($demandForecast as $day => $demand) {
            $daySchedule = $this->generateDaySchedule(
                $tenantId,
                $branchId,
                $day,
                $demand,
                $staff,
                $businessHours
            );
            $schedule[$day] = $daySchedule;
        }
        
        return $schedule;
    }

    /**
     * Get demand forecast
     */
    private function getDemandForecast($tenantId, $branchId, $startDate, $endDate)
    {
        // Simplified demand forecast based on historical data
        $sql = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as order_count,
                COUNT(DISTINCT order_id) as unique_orders
            FROM orders
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND created_at BETWEEN ? AND ?
              AND status = 'COMPLETED'
            GROUP BY DATE(created_at)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create forecast based on historical patterns
        $forecast = [];
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        while ($current <= $end) {
            $dayOfWeek = $current->format('N'); // 1 (Monday) to 7 (Sunday)
            $dateStr = $current->format('Y-m-d');
            
            // Base demand on day of week patterns
            $baseDemand = $this->getBaseDemandByDay($dayOfWeek);
            
            // Adjust for historical data if available
            foreach ($historicalData as $historical) {
                if (date('N', strtotime($historical['date'])) == $dayOfWeek) {
                    $baseDemand = ($baseDemand + $historical['order_count']) / 2;
                    break;
                }
            }
            
            $forecast[$dateStr] = [
                'expected_orders' => round($baseDemand),
                'required_staff' => $this->calculateRequiredStaff($baseDemand),
                'peak_hours' => $this->getPeakHours($dayOfWeek)
            ];
            
            $current->modify('+1 day');
        }
        
        return $forecast;
    }

    /**
     * Get base demand by day of week
     */
    private function getBaseDemandByDay($dayOfWeek)
    {
        // Typical restaurant demand patterns
        $patterns = [
            1 => 50,  // Monday - low
            2 => 60,  // Tuesday - low-medium
            3 => 70,  // Wednesday - medium
            4 => 80,  // Thursday - medium-high
            5 => 100, // Friday - high
            6 => 120, // Saturday - very high
            7 => 110  // Sunday - high
        ];
        
        return $patterns[$dayOfWeek] ?? 70;
    }

    /**
     * Calculate required staff based on demand
     */
    private function calculateRequiredStaff($expectedOrders)
    {
        // Assume 1 staff can handle 15 orders per shift
        $staffPerShift = ceil($expectedOrders / 15);
        return max(2, $staffPerShift); // Minimum 2 staff
    }

    /**
     * Get peak hours for day of week
     */
    private function getPeakHours($dayOfWeek)
    {
        // Typical peak hours
        $peakHours = [
            1 => ['12:00-14:00', '19:00-21:00'],
            2 => ['12:00-14:00', '19:00-21:00'],
            3 => ['12:00-14:00', '19:00-21:00'],
            4 => ['12:00-14:00', '19:00-22:00'],
            5 => ['12:00-14:00', '19:00-23:00'],
            6 => ['11:00-15:00', '18:00-23:00'],
            7 => ['10:00-15:00', '18:00-22:00']
        ];
        
        return $peakHours[$dayOfWeek] ?? ['12:00-14:00', '19:00-21:00'];
    }

    /**
     * Get available staff
     */
    private function getAvailableStaff($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                e.position,
                e.hourly_rate,
                e.max_hours_per_week,
                GROUP_CONCAT(DISTINCT s.skill_name) as skills
            FROM employees e
            LEFT JOIN employee_skills es ON e.employee_id = es.employee_id
            LEFT JOIN skills s ON es.skill_id = s.skill_id
            WHERE e.tenant_id = ? 
              AND e.branch_id = ?
              AND e.status = 'ACTIVE'
            GROUP BY e.employee_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get business hours
     */
    private function getBusinessHours($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                business_hours_start,
                business_hours_end,
                is_24_hours
            FROM branches
            WHERE tenant_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Generate schedule for a single day
     */
    private function generateDaySchedule($tenantId, $branchId, $date, $demand, $staff, $businessHours)
    {
        $requiredStaff = $demand['required_staff'];
        $peakHours = $demand['peak_hours'];
        
        $shifts = $this->createShifts($businessHours, $peakHours, $requiredStaff);
        
        $assignments = [];
        
        foreach ($shifts as $shift) {
            $assignedStaff = $this->assignStaffToShift($staff, $shift, $date);
            $assignments[] = [
                'shift' => $shift,
                'staff' => $assignedStaff
            ];
        }
        
        return [
            'date' => $date,
            'expected_orders' => $demand['expected_orders'],
            'required_staff' => $requiredStaff,
            'shifts' => $assignments
        ];
    }

    /**
     * Create shifts based on business hours and demand
     */
    private function createShifts($businessHours, $peakHours, $requiredStaff)
    {
        $shifts = [];
        
        $startTime = $businessHours['business_hours_start'];
        $endTime = $businessHours['business_hours_end'];
        
        // Create morning shift
        $shifts[] = [
            'shift_name' => 'Morning',
            'start_time' => $startTime,
            'end_time' => '14:00:00',
            'required_staff' => ceil($requiredStaff * 0.4)
        ];
        
        // Create afternoon/evening shift
        $shifts[] = [
            'shift_name' => 'Evening',
            'start_time' => '14:00:00',
            'end_time' => $endTime,
            'required_staff' => ceil($requiredStaff * 0.6)
        ];
        
        return $shifts;
    }

    /**
     * Assign staff to shift
     */
    private function assignStaffToShift($staff, $shift, $date)
    {
        $requiredStaff = $shift['required_staff'];
        $assigned = [];
        
        // Simple assignment - can be enhanced with optimization
        $availableStaff = array_slice($staff, 0, $requiredStaff);
        
        foreach ($availableStaff as $employee) {
            $assigned[] = [
                'employee_id' => $employee['employee_id'],
                'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'position' => $employee['position'],
                'hourly_rate' => $employee['hourly_rate']
            ];
        }
        
        return $assigned;
    }

    /**
     * Optimize labor cost
     */
    public function optimizeLaborCost($tenantId, $branchId, $startDate, $endDate)
    {
        // Get current schedule
        $currentSchedule = $this->generateSchedule($tenantId, $branchId, $startDate, $endDate);
        
        // Calculate current labor cost
        $currentCost = $this->calculateLaborCost($currentSchedule);
        
        // Generate optimized schedule
        $optimizedSchedule = $this->generateOptimizedSchedule($tenantId, $branchId, $startDate, $endDate);
        
        // Calculate optimized labor cost
        $optimizedCost = $this->calculateLaborCost($optimizedSchedule);
        
        $savings = $currentCost - $optimizedCost;
        $savingsPercentage = $currentCost > 0 ? ($savings / $currentCost) * 100 : 0;
        
        return [
            'current_cost' => $currentCost,
            'optimized_cost' => $optimizedCost,
            'savings' => $savings,
            'savings_percentage' => $savingsPercentage,
            'optimized_schedule' => $optimizedSchedule
        ];
    }

    /**
     * Calculate labor cost for schedule
     */
    private function calculateLaborCost($schedule)
    {
        $totalCost = 0;
        
        foreach ($schedule as $day => $daySchedule) {
            foreach ($daySchedule['shifts'] as $shift) {
                foreach ($shift['staff'] as $employee) {
                    $hours = $this->calculateShiftHours($shift['shift']);
                    $cost = $hours * $employee['hourly_rate'];
                    $totalCost += $cost;
                }
            }
        }
        
        return $totalCost;
    }

    /**
     * Calculate shift hours
     */
    private function calculateShiftHours($shift)
    {
        $start = new DateTime($shift['start_time']);
        $end = new DateTime($shift['end_time']);
        $interval = $start->diff($end);
        return $interval->h + ($interval->i / 60);
    }

    /**
     * Generate optimized schedule
     */
    private function generateOptimizedSchedule($tenantId, $branchId, $startDate, $endDate)
    {
        // For now, return the same schedule
        // In production, this would use optimization algorithms
        return $this->generateSchedule($tenantId, $branchId, $startDate, $endDate);
    }

    /**
     * Check staff availability
     */
    public function checkAvailability($employeeId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                s.shift_id,
                s.shift_date,
                s.start_time,
                s.end_time,
                s.status
            FROM schedules s
            WHERE s.employee_id = ? 
              AND s.shift_date BETWEEN ? AND ?
              AND s.status IN ('SCHEDULED', 'CONFIRMED')
            ORDER BY s.shift_date, s.start_time
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $startDate, $endDate]);
        $scheduledShifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get employee availability preferences
        $sql = "
            SELECT 
                day_of_week,
                is_available,
                preferred_start_time,
                preferred_end_time
            FROM employee_availability
            WHERE employee_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId]);
        $availability = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'scheduled_shifts' => $scheduledShifts,
            'availability_preferences' => $availability,
            'total_scheduled_hours' => $this->calculateScheduledHours($scheduledShifts)
        ];
    }

    /**
     * Calculate scheduled hours
     */
    private function calculateScheduledHours($shifts)
    {
        $totalHours = 0;
        
        foreach ($shifts as $shift) {
            $start = new DateTime($shift['start_time']);
            $end = new DateTime($shift['end_time']);
            $interval = $start->diff($end);
            $totalHours += $interval->h + ($interval->i / 60);
        }
        
        return $totalHours;
    }

    /**
     * Get scheduling dashboard data
     */
    public function getDashboardData($tenantId, $branchId, $startDate, $endDate)
    {
        // Get current schedule
        $schedule = $this->generateSchedule($tenantId, $branchId, $startDate, $endDate);
        
        // Calculate labor cost
        $laborCost = $this->calculateLaborCost($schedule);
        
        // Get staff utilization
        $utilization = $this->getStaffUtilization($tenantId, $branchId, $startDate, $endDate);
        
        // Get compliance status
        $compliance = $this->checkCompliance($tenantId, $branchId, $startDate, $endDate);

        return [
            'schedule' => $schedule,
            'labor_cost' => $laborCost,
            'staff_utilization' => $utilization,
            'compliance' => $compliance
        ];
    }

    /**
     * Get staff utilization
     */
    private function getStaffUtilization($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                COUNT(s.shift_id) as scheduled_shifts,
                SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) as scheduled_hours,
                e.max_hours_per_week
            FROM employees e
            LEFT JOIN schedules s ON e.employee_id = s.employee_id 
                AND s.shift_date BETWEEN ? AND ?
            WHERE e.tenant_id = ? AND e.branch_id = ? AND e.status = 'ACTIVE'
            GROUP BY e.employee_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check compliance
     */
    private function checkCompliance($tenantId, $branchId, $startDate, $endDate)
    {
        // Check for overtime violations
        $sql = "
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) as total_hours
            FROM employees e
            JOIN schedules s ON e.employee_id = s.employee_id
            WHERE e.tenant_id = ? 
              AND e.branch_id = ?
              AND s.shift_date BETWEEN ? AND ?
            GROUP BY e.employee_id
            HAVING total_hours > 48
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'compliant' => empty($violations),
            'violations' => $violations
        ];
    }

    /**
     * Calculate payroll for employees within a date range
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Payroll calculation results
     */
    public function calculatePayroll($tenantId, $branchId, $startDate, $endDate)
    {
        // Get employee schedules and calculate hours
        $sql = "
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                e.hourly_rate,
                e.max_hours_per_week,
                COUNT(s.shift_id) as scheduled_shifts,
                SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) as regular_hours,
                SUM(CASE WHEN TIMESTAMPDIFF(HOUR, s.start_time, s.end_time) > 8 
                    THEN TIMESTAMPDIFF(HOUR, s.start_time, s.end_time) - 8 
                    ELSE 0 END) as overtime_hours
            FROM employees e
            LEFT JOIN schedules s ON e.employee_id = s.employee_id 
                AND s.shift_date BETWEEN ? AND ?
                AND s.status = 'COMPLETED'
            WHERE e.tenant_id = ? AND e.branch_id = ? AND e.status = 'ACTIVE'
            GROUP BY e.employee_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $tenantId, $branchId]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payrollData = [];
        $totalRegularPay = 0;
        $totalOvertimePay = 0;
        $totalPay = 0;

        foreach ($employees as $employee) {
            $regularHours = $employee['regular_hours'] ?? 0;
            $overtimeHours = $employee['overtime_hours'] ?? 0;
            $hourlyRate = $employee['hourly_rate'] ?? 0;
            
            $regularPay = $regularHours * $hourlyRate;
            $overtimePay = $overtimeHours * $hourlyRate * 1.5; // 1.5x overtime rate
            $employeeTotalPay = $regularPay + $overtimePay;

            $payrollData[] = [
                'employee_id' => $employee['employee_id'],
                'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'hourly_rate' => $hourlyRate,
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
                'regular_pay' => $regularPay,
                'overtime_pay' => $overtimePay,
                'total_pay' => $employeeTotalPay
            ];

            $totalRegularPay += $regularPay;
            $totalOvertimePay += $overtimePay;
            $totalPay += $employeeTotalPay;
        }

        return [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'employees' => $payrollData,
            'summary' => [
                'total_regular_pay' => $totalRegularPay,
                'total_overtime_pay' => $totalOvertimePay,
                'total_pay' => $totalPay,
                'total_employees' => count($payrollData)
            ]
        ];
    }

    /**
     * Generate detailed payroll report
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Payroll report data
     */
    public function generatePayrollReport($tenantId, $branchId, $startDate, $endDate)
    {
        $payroll = $this->calculatePayroll($tenantId, $branchId, $startDate, $endDate);
        
        // Add additional report details
        $report = [
            'report_type' => 'PAYROLL_REPORT',
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'generated_at' => date('Y-m-d H:i:s'),
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'payroll_data' => $payroll,
            'breakdown_by_department' => $this->getPayrollByDepartment($tenantId, $branchId, $startDate, $endDate),
            'breakdown_by_role' => $this->getPayrollByRole($tenantId, $branchId, $startDate, $endDate)
        ];

        return $report;
    }

    /**
     * Get payroll breakdown by department
     */
    private function getPayrollByDepartment($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                e.department,
                COUNT(DISTINCT e.employee_id) as employee_count,
                SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) as total_hours,
                SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time) * e.hourly_rate) as total_pay
            FROM employees e
            LEFT JOIN schedules s ON e.employee_id = s.employee_id 
                AND s.shift_date BETWEEN ? AND ?
                AND s.status = 'COMPLETED'
            WHERE e.tenant_id = ? AND e.branch_id = ? AND e.status = 'ACTIVE'
            GROUP BY e.department
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payroll breakdown by role
     */
    private function getPayrollByRole($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                e.role,
                COUNT(DISTINCT e.employee_id) as employee_count,
                SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) as total_hours,
                SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time) * e.hourly_rate) as total_pay
            FROM employees e
            LEFT JOIN schedules s ON e.employee_id = s.employee_id 
                AND s.shift_date BETWEEN ? AND ?
                AND s.status = 'COMPLETED'
            WHERE e.tenant_id = ? AND e.branch_id = ? AND e.status = 'ACTIVE'
            GROUP BY e.role
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Track employee performance
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $employeeId Employee ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Performance metrics
     */
    public function trackPerformance($tenantId, $branchId, $employeeId, $startDate, $endDate)
    {
        // Get scheduled shifts
        $sql = "
            SELECT 
                shift_id,
                shift_date,
                start_time,
                end_time,
                status
            FROM schedules
            WHERE tenant_id = ? AND branch_id = ? AND employee_id = ?
            AND shift_date BETWEEN ? AND ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $employeeId, $startDate, $endDate]);
        $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate metrics
        $totalShifts = count($shifts);
        $completedShifts = 0;
        $totalHours = 0;
        $onTimeShifts = 0;

        foreach ($shifts as $shift) {
            if ($shift['status'] === 'COMPLETED') {
                $completedShifts++;
                $start = new DateTime($shift['start_time']);
                $end = new DateTime($shift['end_time']);
                $totalHours += ($end->getTimestamp() - $start->getTimestamp()) / 3600;
                $onTimeShifts++;
            }
        }

        $attendanceRate = $totalShifts > 0 ? ($completedShifts / $totalShifts) * 100 : 0;
        $punctualityRate = $completedShifts > 0 ? ($onTimeShifts / $completedShifts) * 100 : 0;

        return [
            'employee_id' => $employeeId,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'total_shifts' => $totalShifts,
            'completed_shifts' => $completedShifts,
            'total_hours' => round($totalHours, 2),
            'attendance_rate' => round($attendanceRate, 2),
            'punctuality_rate' => round($punctualityRate, 2)
        ];
    }

    /**
     * Generate performance report for all employees
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Performance report
     */
    public function generatePerformanceReport($tenantId, $branchId, $startDate, $endDate)
    {
        // Get all active employees
        $sql = "
            SELECT employee_id, name, role, department
            FROM employees
            WHERE tenant_id = ? AND branch_id = ? AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $performanceData = [];
        foreach ($employees as $employee) {
            $performance = $this->trackPerformance($tenantId, $branchId, $employee['employee_id'], $startDate, $endDate);
            $performanceData[] = array_merge($employee, $performance);
        }

        // Calculate averages
        $avgAttendance = 0;
        $avgPunctuality = 0;
        if (!empty($performanceData)) {
            $avgAttendance = array_sum(array_column($performanceData, 'attendance_rate')) / count($performanceData);
            $avgPunctuality = array_sum(array_column($performanceData, 'punctuality_rate')) / count($performanceData);
        }

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'total_employees' => count($employees),
            'average_attendance_rate' => round($avgAttendance, 2),
            'average_punctuality_rate' => round($avgPunctuality, 2),
            'employee_performance' => $performanceData
        ];
    }

    /**
     * Send staff notification
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $senderId Sender employee ID
     * @param int $recipientId Recipient employee ID
     * @param string $message Message content
     * @param string $messageType Message type
     * @return array Notification result
     */
    public function sendStaffNotification($tenantId, $branchId, $senderId, $recipientId, $message, $messageType = 'GENERAL')
    {
        $sql = "
            INSERT INTO staff_messages
            (tenant_id, branch_id, sender_id, recipient_id, message, message_type, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'SENT', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $senderId, $recipientId, $message, $messageType]);
        $messageId = $this->db->lastInsertId();

        return [
            'message_id' => $messageId,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message' => $message,
            'message_type' => $messageType,
            'status' => 'SENT',
            'sent_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get staff messages for employee
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $employeeId Employee ID
     * @return array Staff messages
     */
    public function getStaffMessages($tenantId, $branchId, $employeeId)
    {
        $sql = "
            SELECT 
                sm.message_id,
                sm.sender_id,
                e_sender.name as sender_name,
                sm.recipient_id,
                sm.message,
                sm.message_type,
                sm.status,
                sm.created_at,
                sm.read_at
            FROM staff_messages sm
            LEFT JOIN employees e_sender ON sm.sender_id = e_sender.employee_id
            WHERE sm.tenant_id = ? AND sm.branch_id = ? 
            AND sm.recipient_id = ?
            ORDER BY sm.created_at DESC
            LIMIT 50
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $employeeId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mark messages as read
        $unreadIds = array_column(array_filter($messages, function($msg) {
            return $msg['read_at'] === null;
        }), 'message_id');

        if (!empty($unreadIds)) {
            $placeholders = str_repeat('?,', count($unreadIds) - 1) . '?';
            $updateSql = "
                UPDATE staff_messages
                SET read_at = NOW(), status = 'READ'
                WHERE message_id IN ({$placeholders})
            ";
            $stmt = $this->db->prepare($updateSql);
            $stmt->execute($unreadIds);
        }

        return [
            'employee_id' => $employeeId,
            'total_messages' => count($messages),
            'unread_count' => count($unreadIds),
            'messages' => $messages
        ];
    }
}
