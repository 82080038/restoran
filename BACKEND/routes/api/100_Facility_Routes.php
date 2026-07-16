<?php

// Facility Management Routes - Floors, Zones, Kitchen Stations

// ========================================================
// FLOOR ROUTES
// ========================================================
// GET /api/v1/floors - Get all floors
$router->addRoute('GET', '/api/v1/floors', function($request) use ($floorController) {
    return $floorController->getFloors($request);
});

// GET /api/v1/floors/{floor_id} - Get single floor
$router->addRoute('GET', '/api/v1/floors/{id}', function($request) use ($floorController) {
    return $floorController->getFloor($request);
});

// POST /api/v1/floors - Create new floor
$router->addRoute('POST', '/api/v1/floors', function($request) use ($floorController) {
    return $floorController->createFloor($request);
});

// PUT /api/v1/floors/{floor_id} - Update floor
$router->addRoute('PUT', '/api/v1/floors/{id}', function($request) use ($floorController) {
    return $floorController->updateFloor($request);
});

// DELETE /api/v1/floors/{floor_id} - Delete floor
$router->addRoute('DELETE', '/api/v1/floors/{id}', function($request) use ($floorController) {
    return $floorController->deleteFloor($request);
});

// GET /api/v1/floors/{floor_id}/zones - Get zones for a floor
$router->addRoute('GET', '/api/v1/floors/{id}/zones', function($request) use ($floorController) {
    return $floorController->getFloorZones($request);
});

// ========================================================
// ZONE ROUTES
// ========================================================
// GET /api/v1/zones - Get all zones
$router->addRoute('GET', '/api/v1/zones', function($request) use ($zoneController) {
    return $zoneController->getZones($request);
});

// GET /api/v1/zones/{zone_id} - Get single zone
$router->addRoute('GET', '/api/v1/zones/{id}', function($request) use ($zoneController) {
    return $zoneController->getZone($request);
});

// POST /api/v1/zones - Create new zone
$router->addRoute('POST', '/api/v1/zones', function($request) use ($zoneController) {
    return $zoneController->createZone($request);
});

// PUT /api/v1/zones/{zone_id} - Update zone
$router->addRoute('PUT', '/api/v1/zones/{id}', function($request) use ($zoneController) {
    return $zoneController->updateZone($request);
});

// DELETE /api/v1/zones/{zone_id} - Delete zone
$router->addRoute('DELETE', '/api/v1/zones/{id}', function($request) use ($zoneController) {
    return $zoneController->deleteZone($request);
});

// GET /api/v1/zones/{zone_id}/tables - Get tables for a zone
$router->addRoute('GET', '/api/v1/zones/{id}/tables', function($request) use ($zoneController) {
    return $zoneController->getZoneTables($request);
});

// ========================================================
// KITCHEN STATION ROUTES
// ========================================================
// GET /api/v1/kitchen-stations - Get all kitchen stations
$router->addRoute('GET', '/api/v1/kitchen-stations', function($request) use ($kitchenStationController) {
    return $kitchenStationController->getKitchenStations($request);
});

// GET /api/v1/kitchen-stations/{station_id} - Get single kitchen station
$router->addRoute('GET', '/api/v1/kitchen-stations/{id}', function($request) use ($kitchenStationController) {
    return $kitchenStationController->getKitchenStation($request);
});

// POST /api/v1/kitchen-stations - Create new kitchen station
$router->addRoute('POST', '/api/v1/kitchen-stations', function($request) use ($kitchenStationController) {
    return $kitchenStationController->createKitchenStation($request);
});

// PUT /api/v1/kitchen-stations/{station_id} - Update kitchen station
$router->addRoute('PUT', '/api/v1/kitchen-stations/{id}', function($request) use ($kitchenStationController) {
    return $kitchenStationController->updateKitchenStation($request);
});

// DELETE /api/v1/kitchen-stations/{station_id} - Delete kitchen station
$router->addRoute('DELETE', '/api/v1/kitchen-stations/{id}', function($request) use ($kitchenStationController) {
    return $kitchenStationController->deleteKitchenStation($request);
});

// GET /api/v1/kitchen-stations/central - Get central kitchens
$router->addRoute('GET', '/api/v1/kitchen-stations/central', function($request) use ($kitchenStationController) {
    return $kitchenStationController->getCentralKitchens($request);
});
