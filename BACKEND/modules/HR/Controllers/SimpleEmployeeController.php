<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleEmployeeController extends \App\Core\BaseController
{
    // Simple endpoint to get employees without middleware
    public function getEmployees($request = null)
    {
        $db = db();

        $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) AS employee_name, e.position, e.phone, e.email, e.salary, e.hire_date AS start_date, e.status
                FROM employees e
                WHERE e.deleted_at IS NULL
                ORDER BY e.first_name ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($employees);
    }
}
