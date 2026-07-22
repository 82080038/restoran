<?php

// Maintenance Routes
$router->addRoute('POST', '/api/v1/maintenance/assets', withAuth(function($request) use ($maintenanceController) {
    return $maintenanceController->createAsset($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/maintenance/assets', withAuth(function($request) use ($maintenanceController) {
    return $maintenanceController->getAssets($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/maintenance/schedules', withAuth(function($request) use ($maintenanceController) {
    return $maintenanceController->createSchedule($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/maintenance/schedules', withAuth(function($request) use ($maintenanceController) {
    return $maintenanceController->getSchedules($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/maintenance/schedules/{id}/complete', withAuth(function($request) use ($maintenanceController) {
    return $maintenanceController->completeMaintenance($request);
}, $authMiddleware));

