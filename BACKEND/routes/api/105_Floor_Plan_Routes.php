<?php

// Floor Plan Routes
$router->addRoute('GET', '/api/v1/floor-plan/layout', function($request) use ($floorPlanController) {
    return $floorPlanController->getLayout($request);
});
$router->addRoute('POST', '/api/v1/floor-plan/layout/save', function($request) use ($floorPlanController) {
    return $floorPlanController->saveLayout($request);
});

// Floors
$router->addRoute('GET', '/api/v1/floor-plan/floors', function($request) use ($floorPlanController) {
    return $floorPlanController->getFloors($request);
});
$router->addRoute('POST', '/api/v1/floor-plan/floors', function($request) use ($floorPlanController) {
    return $floorPlanController->createFloor($request);
});
$router->addRoute('PUT', '/api/v1/floor-plan/floors/{id}', function($request) use ($floorPlanController) {
    return $floorPlanController->updateFloor($request);
});
$router->addRoute('DELETE', '/api/v1/floor-plan/floors/{id}', function($request) use ($floorPlanController) {
    return $floorPlanController->deleteFloor($request);
});

// Zones
$router->addRoute('GET', '/api/v1/floor-plan/zones', function($request) use ($floorPlanController) {
    return $floorPlanController->getZones($request);
});
$router->addRoute('POST', '/api/v1/floor-plan/zones', function($request) use ($floorPlanController) {
    return $floorPlanController->createZone($request);
});
$router->addRoute('PUT', '/api/v1/floor-plan/zones/{id}', function($request) use ($floorPlanController) {
    return $floorPlanController->updateZone($request);
});
$router->addRoute('DELETE', '/api/v1/floor-plan/zones/{id}', function($request) use ($floorPlanController) {
    return $floorPlanController->deleteZone($request);
});

// Tables (layout)
$router->addRoute('GET', '/api/v1/floor-plan/tables', function($request) use ($floorPlanController) {
    return $floorPlanController->getTables($request);
});
$router->addRoute('POST', '/api/v1/floor-plan/tables', function($request) use ($floorPlanController) {
    return $floorPlanController->createTable($request);
});
$router->addRoute('PUT', '/api/v1/floor-plan/tables/{id}', function($request) use ($floorPlanController) {
    return $floorPlanController->updateTable($request);
});
$router->addRoute('PUT', '/api/v1/floor-plan/tables/{id}/position', function($request) use ($floorPlanController) {
    return $floorPlanController->updateTablePosition($request);
});
$router->addRoute('DELETE', '/api/v1/floor-plan/tables/{id}', function($request) use ($floorPlanController) {
    return $floorPlanController->deleteTable($request);
});
