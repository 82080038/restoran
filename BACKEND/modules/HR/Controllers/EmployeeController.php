<?php

if (!class_exists('EmployeeService')) {
    require_once __DIR__ . '/../Services/EmployeeService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class EmployeeController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new EmployeeService();
    }

    public function create($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createEmployee($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success(['employee_id' => $result['employee_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function recordAttendance($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $employeeId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->recordAttendance($employeeId, $data['date'], $data['check_in_time'], $data['check_out_time'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function calculatePayroll($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->calculatePayroll($user['tenant_id'], $user['branch_id'], $data['period_start'], $data['period_end']);

        if ($result['success']) {
            Response::success($result['message'], ['payroll_id' => $result['payroll_id'], 'total_net_pay' => $result['total_net_pay']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $result = $this->service->getEmployees($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
