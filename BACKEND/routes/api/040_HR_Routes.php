<?php

// HR Routes
$router->addRoute('POST', '/api/v1/hr/employees', withAuth(function($request) use ($employeeController) {
    return $employeeController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/hr/employees', withAuth(function($request) use ($employeeController) {
    return $employeeController->getAll($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/hr/employees/{id}/attendance', withAuth(function($request) use ($employeeController) {
    return $employeeController->recordAttendance($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/hr/payroll/calculate', withAuth(function($request) use ($employeeController) {
    return $employeeController->calculatePayroll($request);
}, $authMiddleware));

