<?php

// Commission Routes
$router->addRoute('POST', '/api/v1/hr/commissions', withAuth(function($request) use ($commissionController) {
    return $commissionController->createCommission($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/hr/commissions/{id}/approve', withAuth(function($request) use ($commissionController) {
    return $commissionController->approveCommission($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/hr/commissions/{id}/pay', withAuth(function($request) use ($commissionController) {
    return $commissionController->payCommission($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/commissions', withAuth(function($request) use ($commissionController) {
    return $commissionController->getEmployeeCommissions($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/hr/commissions/pending', withAuth(function($request) use ($commissionController) {
    return $commissionController->getPendingCommissions($request);
}, $authMiddleware));

