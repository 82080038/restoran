<?php

// Predictive Maintenance Routes
$router->addRoute('POST', '/api/v1/maintenance/predict', withAuth(function($request) use ($predictiveMaintenanceController) {
    return $predictiveMaintenanceController->predictNeeds($request);
}, $authMiddleware));

