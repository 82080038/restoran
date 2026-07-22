<?php

// Tip Routes
$router->addRoute('POST', '/api/v1/hr/tips', withAuth(function($request) use ($tipController) {
    return $tipController->distributeTip($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/hr/tips', withAuth(function($request) use ($tipController) {
    return $tipController->getTipDistributions($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/tips', withAuth(function($request) use ($tipController) {
    return $tipController->getEmployeeTips($request);
}, $authMiddleware));

