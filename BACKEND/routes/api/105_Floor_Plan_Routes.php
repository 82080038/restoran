<?php

// Floor Plan Routes
$router->addRoute('GET', '/api/v1/floor-plan/layout', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->getLayout($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/floor-plan/layout/save', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->saveLayout($request);
}, $authMiddleware));

// Floors
$router->addRoute('GET', '/api/v1/floor-plan/floors', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->getFloors($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/floor-plan/floors', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->createFloor($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/floor-plan/floors/{id}', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->updateFloor($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/floor-plan/floors/{id}', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->deleteFloor($request);
}, $authMiddleware));

// Zones
$router->addRoute('GET', '/api/v1/floor-plan/zones', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->getZones($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/floor-plan/zones', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->createZone($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/floor-plan/zones/{id}', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->updateZone($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/floor-plan/zones/{id}', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->deleteZone($request);
}, $authMiddleware));

// Tables (layout)
$router->addRoute('GET', '/api/v1/floor-plan/tables', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->getTables($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/floor-plan/tables', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->createTable($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/floor-plan/tables/{id}', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->updateTable($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/floor-plan/tables/{id}/position', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->updateTablePosition($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/floor-plan/tables/{id}', withAuth(function($request) use ($floorPlanController) {
    return $floorPlanController->deleteTable($request);
}, $authMiddleware));
