<?php

// Emergency Closure Routes
$router->addRoute('POST', '/api/v1/emergency-closures', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->create($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/emergency-closures/active', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->getActive($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/emergency-closures', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->getAll($request);
    },
    $authMiddleware
));

$router->addRoute('PUT', '/api/v1/emergency-closures/{closure_id}', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->update($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/emergency-closures/{closure_id}/close', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->close($request);
    },
    $authMiddleware
));

$router->addRoute('PUT', '/api/v1/emergency-closures/{closure_id}/notification', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->updateNotification($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/emergency-closures/check', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->checkStatus($request);
    },
    $authMiddleware
));

// Edge Case Handling Module
if (!class_exists('RoleFallbackController')) {
    require_once __DIR__ . '/../../core/RoleFallbackController.php';
}
if (!class_exists('MenuController')) {
    require_once __DIR__ . '/../../core/MenuController.php';
}

