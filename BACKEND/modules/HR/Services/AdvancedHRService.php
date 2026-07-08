<?php

namespace App\Modules\HR\Services;

use App\Core\Database;
use App\Core\Audit;

class AdvancedHRService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = new Audit();
    }

    /**
     * Create multi-location schedule
     */
    public function createMultiLocationSchedule($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $scheduleData = [
                'tenant_id' => $tenantId,
                'schedule_name' => $data->schedule_name,
                'schedule_type' => $data->schedule_type ?? 'MULTI_LOCATION',
                'start_date' => $data->start_date,
                'end_date' => $data->end_date,
                'status' => 'DRAFT',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO multi_location_schedules (tenant_id, schedule_name, schedule_type, start_date, end_date, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $scheduleData['tenant_id'],
                $scheduleData['schedule_name'],
                $scheduleData['schedule_type'],
                $scheduleData['start_date'],
                $scheduleData['end_date'],
                $scheduleData['status'],
                $scheduleData['created_by']
            ]);

            $scheduleId = $this->db->lastInsertId();

            // Add schedule assignments for multiple branches
            if (isset($data->assignments) && is_array($data->assignments)) {
                foreach ($data->assignments as $assignment) {
                    $this->addScheduleAssignment($scheduleId, $assignment, $userId);
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'multi_location_schedule', $scheduleId, 'CREATE', json_encode($scheduleData));

            return [
                'success' => true,
                'message' => 'Multi-location schedule created',
                'schedule_id' => $scheduleId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add schedule assignment
     */
    private function addScheduleAssignment($scheduleId, $assignment, $userId)
    {
        $sql = "INSERT INTO schedule_assignments (schedule_id, branch_id, employee_id, shift_id, assigned_date, start_time, end_time, break_duration, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $scheduleId,
            $assignment->branch_id,
            $assignment->employee_id,
            $assignment->shift_id,
            $assignment->assigned_date,
            $assignment->start_time,
            $assignment->end_time,
            $assignment->break_duration ?? 0
        ]);
    }

    /**
     * Get multi-location schedules
     */
    public function getMultiLocationSchedules($tenantId, $status, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND start_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND end_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT mls.*, 
                    (SELECT COUNT(*) FROM schedule_assignments sa WHERE sa.schedule_id = mls.id) as assignment_count,
                    u.username as created_by_name
                FROM multi_location_schedules mls
                LEFT JOIN users u ON mls.created_by = u.id
                {$where}
                ORDER BY mls.start_date DESC, mls.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Calculate labor cost analysis
     */
    public function calculateLaborCost($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE sa.tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND sa.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($dateFrom) {
            $where .= " AND sa.assigned_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND sa.assigned_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT sa.branch_id, b.branch_name,
                    COUNT(DISTINCT sa.employee_id) as total_employees,
                    SUM(sa.total_hours) as total_hours,
                    SUM(sa.overtime_hours) as total_overtime_hours,
                    SUM(sa.regular_cost) as regular_cost,
                    SUM(sa.overtime_cost) as overtime_cost,
                    SUM(sa.total_cost) as total_cost,
                    AVG(sa.hourly_rate) as average_hourly_rate
                FROM schedule_assignments sa
                LEFT JOIN branches b ON sa.branch_id = b.id
                {$where}
                GROUP BY sa.branch_id, b.branch_name
                ORDER BY total_cost DESC";

        $laborCosts = $this->db->query($sql, $params)->fetchAll();

        // Calculate labor cost percentage vs revenue
        foreach ($laborCosts as &$cost) {
            $revenueSql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE tenant_id = ? AND branch_id = ? AND order_date >= ? AND order_date <= ?";
            $revenue = $this->db->query($revenueSql, [$tenantId, $cost['branch_id'], $dateFrom, $dateTo])->fetch();
            
            $totalRevenue = $revenue['total_revenue'] ?? 0;
            $cost['total_revenue'] = $totalRevenue;
            $cost['labor_cost_percentage'] = $totalRevenue > 0 ? ($cost['total_cost'] / $totalRevenue) * 100 : 0;
        }

        return [
            'success' => true,
            'data' => $laborCosts
        ];
    }

    /**
     * Create training program
     */
    public function createTrainingProgram($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $trainingData = [
                'tenant_id' => $tenantId,
                'training_name' => $data->training_name,
                'training_description' => $data->training_description ?? null,
                'training_type' => $data->training_type,
                'category' => $data->category,
                'duration_hours' => $data->duration_hours,
                'start_date' => $data->start_date,
                'end_date' => $data->end_date,
                'instructor' => $data->instructor ?? null,
                'location' => $data->location ?? null,
                'max_participants' => $data->max_participants ?? null,
                'status' => 'PLANNED',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO training_programs (tenant_id, training_name, training_description, training_type, category, duration_hours, start_date, end_date, instructor, location, max_participants, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $trainingData['tenant_id'],
                $trainingData['training_name'],
                $trainingData['training_description'],
                $trainingData['training_type'],
                $trainingData['category'],
                $trainingData['duration_hours'],
                $trainingData['start_date'],
                $trainingData['end_date'],
                $trainingData['instructor'],
                $trainingData['location'],
                $trainingData['max_participants'],
                $trainingData['status'],
                $trainingData['created_by']
            ]);

            $trainingId = $this->db->lastInsertId();

            // Add training participants
            if (isset($data->participants) && is_array($data->participants)) {
                foreach ($data->participants as $participant) {
                    $this->addTrainingParticipant($trainingId, $participant, $userId);
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'training_program', $trainingId, 'CREATE', json_encode($trainingData));

            return [
                'success' => true,
                'message' => 'Training program created',
                'training_id' => $trainingId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create training: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add training participant
     */
    private function addTrainingParticipant($trainingId, $participant, $userId)
    {
        $sql = "INSERT INTO training_participants (training_id, employee_id, enrollment_status, enrollment_date, enrolled_by, created_at)
                VALUES (?, ?, 'ENROLLED', CURDATE(), ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$trainingId, $participant->employee_id, $userId]);
    }

    /**
     * Get training programs
     */
    public function getTrainingPrograms($tenantId, $status, $category, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($category) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        
        if ($dateFrom) {
            $where .= " AND start_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND end_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT tp.*, 
                    (SELECT COUNT(*) FROM training_participants tpart WHERE tpart.training_id = tp.id) as participant_count,
                    u.username as created_by_name
                FROM training_programs tp
                LEFT JOIN users u ON tp.created_by = u.id
                {$where}
                ORDER BY tp.start_date DESC, tp.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Record training completion
     */
    public function recordTrainingCompletion($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $completionData = [
                'training_id' => $data->training_id,
                'employee_id' => $data->employee_id,
                'completion_status' => $data->completion_status,
                'score' => $data->score ?? null,
                'feedback' => $data->feedback ?? null,
                'completed_by' => $userId
            ];

            $sql = "UPDATE training_participants 
                    SET completion_status = ?, score = ?, feedback = ?, completion_date = CURDATE(), completed_by = ?
                    WHERE training_id = ? AND employee_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $completionData['completion_status'],
                $completionData['score'],
                $completionData['feedback'],
                $completionData['completed_by'],
                $completionData['training_id'],
                $completionData['employee_id']
            ]);

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'training_completion', $data->training_id, 'UPDATE', json_encode($completionData));

            return [
                'success' => true,
                'message' => 'Training completion recorded'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to record completion: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get staff performance with labor cost
     */
    public function getStaffPerformanceWithLaborCost($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE sa.tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND sa.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($dateFrom) {
            $where .= " AND sa.assigned_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND sa.assigned_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT sa.employee_id, e.employee_name, e.position, e.hourly_rate,
                    COUNT(DISTINCT sa.assigned_date) as days_worked,
                    SUM(sa.total_hours) as total_hours,
                    SUM(sa.overtime_hours) as total_overtime_hours,
                    SUM(sa.total_cost) as total_cost,
                    (SELECT COUNT(*) FROM orders o WHERE o.created_by = sa.employee_id AND o.order_date >= ? AND o.order_date <= ?) as orders_processed,
                    (SELECT SUM(o.total_amount) FROM orders o WHERE o.created_by = sa.employee_id AND o.order_date >= ? AND o.order_date <= ?) as sales_generated
                FROM schedule_assignments sa
                LEFT JOIN employees e ON sa.employee_id = e.id
                {$where}
                GROUP BY sa.employee_id, e.employee_name, e.position, e.hourly_rate
                ORDER BY total_cost DESC";

        $performance = $this->db->query($sql, array_merge([$dateFrom, $dateTo, $dateFrom, $dateTo], $params))->fetchAll();

        // Calculate efficiency metrics
        foreach ($performance as &$perf) {
            $perf['cost_per_order'] = $perf['orders_processed'] > 0 ? $perf['total_cost'] / $perf['orders_processed'] : 0;
            $perf['sales_per_labor_cost'] = $perf['total_cost'] > 0 ? $perf['sales_generated'] / $perf['total_cost'] : 0;
            $perf['orders_per_hour'] = $perf['total_hours'] > 0 ? $perf['orders_processed'] / $perf['total_hours'] : 0;
        }

        return [
            'success' => true,
            'data' => $performance
        ];
    }

    /**
     * Get HR summary
     */
    public function getSummary($tenantId, $branchId)
    {
        // Active training programs
        $activeTrainingSql = "SELECT COUNT(*) as count FROM training_programs WHERE tenant_id = ? AND status = 'IN_PROGRESS'";
        $activeTraining = $this->db->query($activeTrainingSql, [$tenantId])->fetch();

        // Pending training enrollments
        $pendingEnrollmentsSql = "SELECT COUNT(*) as count FROM training_participants tp 
                                  JOIN training_programs tpr ON tp.training_id = tpr.id 
                                  WHERE tpr.tenant_id = ? AND tp.enrollment_status = 'ENROLLED'";
        $pendingEnrollments = $this->db->query($pendingEnrollmentsSql, [$tenantId])->fetch();

        // This month's labor cost
        $laborCostSql = "SELECT SUM(total_cost) as total_cost FROM schedule_assignments 
                         WHERE tenant_id = ? AND assigned_date >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $monthlyLaborCost = $this->db->query($laborCostSql, [$tenantId])->fetch();

        // Multi-location schedules this week
        $schedulesSql = "SELECT COUNT(*) as count FROM multi_location_schedules 
                         WHERE tenant_id = ? AND start_date >= CURDATE() AND start_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $upcomingSchedules = $this->db->query($schedulesSql, [$tenantId])->fetch();

        return [
            'active_training_programs' => $activeTraining['count'] ?? 0,
            'pending_training_enrollments' => $pendingEnrollments['count'] ?? 0,
            'monthly_labor_cost' => $monthlyLaborCost['total_cost'] ?? 0,
            'upcoming_multi_location_schedules' => $upcomingSchedules['count'] ?? 0
        ];
    }
}
