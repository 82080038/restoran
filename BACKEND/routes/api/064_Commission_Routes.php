<?php

// Commission Routes
$router->addRoute('POST', '/api/v1/hr/commissions', function($request) use ($commissionController) {
    return $commissionController->createCommission($request);
});
$router->addRoute('POST', '/api/v1/hr/commissions/{id}/approve', function($request) use ($commissionController) {
    return $commissionController->approveCommission($request);
});
$router->addRoute('POST', '/api/v1/hr/commissions/{id}/pay', function($request) use ($commissionController) {
    return $commissionController->payCommission($request);
});
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/commissions', function($request) use ($commissionController) {
    return $commissionController->getEmployeeCommissions($request);
});
$router->addRoute('GET', '/api/v1/hr/commissions/pending', function($request) use ($commissionController) {
    return $commissionController->getPendingCommissions($request);
});

