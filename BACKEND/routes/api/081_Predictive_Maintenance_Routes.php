<?php

// Predictive Maintenance Routes
$router->addRoute('POST', '/api/v1/maintenance/predict', function($request) use ($predictiveMaintenanceController) {
    return $predictiveMaintenanceController->predictNeeds($request);
});

