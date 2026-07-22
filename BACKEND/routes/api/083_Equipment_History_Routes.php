<?php

// Equipment History Routes
$router->addRoute('POST', '/api/v1/maintenance/equipment-history', withAuth(function($request) use ($equipmentHistoryController) {
    return $equipmentHistoryController->addHistory($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/maintenance/assets/{id}/history', withAuth(function($request) use ($equipmentHistoryController) {
    return $equipmentHistoryController->getEquipmentHistory($request);
}, $authMiddleware));

