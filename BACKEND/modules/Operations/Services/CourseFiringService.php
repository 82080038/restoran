<?php

namespace App\Modules\Operations\Services;

use App\Core\Database;
use PDO;

class CourseFiringService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getCourseSequences($tenantId, $branchId)
    {
        $sql = "SELECT * FROM course_sequences WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        $sql .= " AND is_active = 1 ORDER BY course_number ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function createCourseSequence($data)
    {
        $sql = "INSERT INTO course_sequences (tenant_id, branch_id, course_number, course_name, course_type, auto_fire_delay_minutes, manual_fire_only, display_order, is_active) 
                VALUES (:tenant_id, :branch_id, :course_number, :course_name, :course_type, :auto_fire_delay_minutes, :manual_fire_only, :display_order, :is_active)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':course_number' => $data['course_number'],
            ':course_name' => $data['course_name'],
            ':course_type' => $data['course_type'] ?? 'CUSTOM',
            ':auto_fire_delay_minutes' => $data['auto_fire_delay_minutes'] ?? 0,
            ':manual_fire_only' => $data['manual_fire_only'] ?? 0,
            ':display_order' => $data['display_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateCourseSequence($courseId, $tenantId, $data)
    {
        $sql = "UPDATE course_sequences SET course_name = :course_name, course_type = :course_type, 
                auto_fire_delay_minutes = :auto_fire_delay_minutes, manual_fire_only = :manual_fire_only, 
                display_order = :display_order, is_active = :is_active 
                WHERE course_id = :course_id AND tenant_id = :tenant_id";
        
        $params = [
            ':course_name' => $data['course_name'],
            ':course_type' => $data['course_type'],
            ':auto_fire_delay_minutes' => $data['auto_fire_delay_minutes'],
            ':manual_fire_only' => $data['manual_fire_only'],
            ':display_order' => $data['display_order'],
            ':is_active' => $data['is_active'],
            ':course_id' => $courseId,
            ':tenant_id' => $tenantId
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteCourseSequence($courseId, $tenantId)
    {
        $sql = "DELETE FROM course_sequences WHERE course_id = :course_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':course_id' => $courseId, ':tenant_id' => $tenantId]);
    }

    public function createOrderCourses($orderId, $tenantId, $branchId)
    {
        $courses = $this->getCourseSequences($tenantId, $branchId);
        
        foreach ($courses as $course) {
            $sql = "INSERT INTO order_courses (order_id, course_id, course_number, fire_status) 
                    VALUES (:order_id, :course_id, :course_number, 'PENDING')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':order_id' => $orderId,
                ':course_id' => $course['course_id'],
                ':course_number' => $course['course_number']
            ]);
        }
        
        return count($courses);
    }

    public function fireCourse($orderCourseId, $tenantId, $firedBy = null)
    {
        $sql = "UPDATE order_courses SET fire_status = 'FIRED', fired_at = NOW(), fired_by = :fired_by 
                WHERE order_course_id = :order_course_id";
        
        $params = [':order_course_id' => $orderCourseId, ':fired_by' => $firedBy];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function completeCourse($orderCourseId, $tenantId)
    {
        $sql = "UPDATE order_courses SET fire_status = 'COMPLETED', completed_at = NOW() 
                WHERE order_course_id = :order_course_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':order_course_id' => $orderCourseId]);
    }

    public function getOrderCourses($orderId)
    {
        $sql = "SELECT oc.*, cs.course_name, cs.course_type, cs.auto_fire_delay_minutes 
                FROM order_courses oc 
                LEFT JOIN course_sequences cs ON oc.course_id = cs.course_id 
                WHERE oc.order_id = :order_id 
                ORDER BY oc.course_number ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public function checkAutoFireCourses()
    {
        // Check for courses that should auto-fire based on delay
        $sql = "UPDATE order_courses oc 
                JOIN order_courses prev_oc ON oc.order_id = prev_oc.order_id 
                    AND oc.course_number = prev_oc.course_number + 1
                JOIN course_sequences cs ON oc.course_id = cs.course_id
                SET oc.fire_status = 'FIRED', oc.fired_at = NOW()
                WHERE prev_oc.fire_status = 'COMPLETED' 
                AND prev_oc.completed_at IS NOT NULL
                AND cs.auto_fire_delay_minutes > 0
                AND TIMESTAMPDIFF(MINUTE, prev_oc.completed_at, NOW()) >= cs.auto_fire_delay_minutes
                AND oc.fire_status = 'PENDING'";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
}
