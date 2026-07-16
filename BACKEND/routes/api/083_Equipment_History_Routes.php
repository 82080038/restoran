<?php

// Equipment History Routes
$router->addRoute('POST', '/api/v1/maintenance/equipment-history', function($request) use ($equipmentHistoryController) {
    return $equipmentHistoryController->addHistory($request);
});
$router->addRoute('GET', '/api/v1/maintenance/assets/{id}/history', function($request) use ($equipmentHistoryController) {
    return $equipmentHistoryController->getEquipmentHistory($request);
});

