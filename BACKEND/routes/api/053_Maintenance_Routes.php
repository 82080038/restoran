<?php

// Maintenance Routes
$router->addRoute('POST', '/api/v1/maintenance/assets', function($request) use ($maintenanceController) {
    return $maintenanceController->createAsset($request);
});
$router->addRoute('GET', '/api/v1/maintenance/assets', function($request) use ($maintenanceController) {
    return $maintenanceController->getAssets($request);
});
$router->addRoute('POST', '/api/v1/maintenance/schedules', function($request) use ($maintenanceController) {
    return $maintenanceController->createSchedule($request);
});
$router->addRoute('GET', '/api/v1/maintenance/schedules', function($request) use ($maintenanceController) {
    return $maintenanceController->getSchedules($request);
});
$router->addRoute('POST', '/api/v1/maintenance/schedules/{id}/complete', function($request) use ($maintenanceController) {
    return $maintenanceController->completeMaintenance($request);
});

