<?php

// Tip Routes
$router->addRoute('POST', '/api/v1/hr/tips', function($request) use ($tipController) {
    return $tipController->distributeTip($request);
});
$router->addRoute('GET', '/api/v1/hr/tips', function($request) use ($tipController) {
    return $tipController->getTipDistributions($request);
});
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/tips', function($request) use ($tipController) {
    return $tipController->getEmployeeTips($request);
});

