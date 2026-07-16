<?php

// HR Routes
$router->addRoute('POST', '/api/v1/hr/employees', function($request) use ($employeeController) {
    return $employeeController->create($request);
});
$router->addRoute('GET', '/api/v1/hr/employees', function($request) use ($employeeController) {
    return $employeeController->getAll($request);
});
$router->addRoute('POST', '/api/v1/hr/employees/{id}/attendance', function($request) use ($employeeController) {
    return $employeeController->recordAttendance($request);
});
$router->addRoute('POST', '/api/v1/hr/payroll/calculate', function($request) use ($employeeController) {
    return $employeeController->calculatePayroll($request);
});

