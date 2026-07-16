<?php

// Feature Toggle Routes
$router->addRoute('GET', '/api/v1/features/modules', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->getAllModules($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/features/user', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->getUserFeatures($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/features/user/{user_id}', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->getUserFeaturesById($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/features/role/{role_id}', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->getRoleFeatures($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/features/user/enable', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->enableFeatureForUser($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/features/user/disable', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->disableFeatureForUser($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/features/role/enable', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->enableFeatureForRole($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/features/role/disable', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->disableFeatureForRole($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/features/check/{module_code}', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->checkFeature($request);
    },
    $authMiddleware
));

// Operational Management Module
if (!class_exists('AttendanceController')) {
    require_once __DIR__ . '/../../modules/HR/Controllers/AttendanceController.php';
}
if (!class_exists('HolidayController')) {
    require_once __DIR__ . '/../../modules/HR/Controllers/HolidayController.php';
}
if (!class_exists('BusinessHoursController')) {
    require_once __DIR__ . '/../../modules/HR/Controllers/BusinessHoursController.php';
}
if (!class_exists('EmergencyClosureController')) {
    require_once __DIR__ . '/../../modules/HR/Controllers/EmergencyClosureController.php';
}

