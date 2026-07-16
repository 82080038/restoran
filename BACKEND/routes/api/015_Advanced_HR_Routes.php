<?php

// Advanced HR Routes
$router->addRoute('POST', '/api/v1/hr/multi-location-schedules', withAuth(
    function($request) use ($advancedHRController) {
        return $advancedHRController->createMultiLocationSchedule($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/hr/multi-location-schedules', withAuth(
    function($request) use ($advancedHRController) {
        return $advancedHRController->getMultiLocationSchedules($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/hr/labor-cost-analysis', withAuth(
    function($request) use ($advancedHRController) {
        return $advancedHRController->calculateLaborCost($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/hr/training-programs', withAuth(
    function($request) use ($advancedHRController) {
        return $advancedHRController->createTrainingProgram($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/hr/training-programs', withAuth(
    function($request) use ($advancedHRController) {
        return $advancedHRController->getTrainingPrograms($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/hr/training-completion', withAuth(
    function($request) use ($advancedHRController) {
        return $advancedHRController->recordTrainingCompletion($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/hr/staff-performance-labor', withAuth(
    function($request) use ($advancedHRController) {
        return $advancedHRController->getStaffPerformanceWithLaborCost($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/hr/summary', withAuth(
    function($request) use ($advancedHRController) {
        return $advancedHRController->getSummary($request);
    },
    $authMiddleware
));

