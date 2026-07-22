<?php

// Facility Management Routes - Floors, Zones, Kitchen Stations

// ========================================================
// FLOOR ROUTES
// ========================================================
// GET /api/v1/floors - Get all floors
$router->addRoute('GET', '/api/v1/floors', withAuth(function($request) use ($floorController) {
    return $floorController->getFloors($request);
}, $authMiddleware));

// GET /api/v1/floors/{floor_id} - Get single floor
$router->addRoute('GET', '/api/v1/floors/{id}', withAuth(function($request) use ($floorController) {
    return $floorController->getFloor($request);
}, $authMiddleware));

// POST /api/v1/floors - Create new floor
$router->addRoute('POST', '/api/v1/floors', withAuth(function($request) use ($floorController) {
    return $floorController->createFloor($request);
}, $authMiddleware));

// PUT /api/v1/floors/{floor_id} - Update floor
$router->addRoute('PUT', '/api/v1/floors/{id}', withAuth(function($request) use ($floorController) {
    return $floorController->updateFloor($request);
}, $authMiddleware));

// DELETE /api/v1/floors/{floor_id} - Delete floor
$router->addRoute('DELETE', '/api/v1/floors/{id}', withAuth(function($request) use ($floorController) {
    return $floorController->deleteFloor($request);
}, $authMiddleware));

// GET /api/v1/floors/{floor_id}/zones - Get zones for a floor
$router->addRoute('GET', '/api/v1/floors/{id}/zones', withAuth(function($request) use ($floorController) {
    return $floorController->getFloorZones($request);
}, $authMiddleware));

// ========================================================
// ZONE ROUTES
// ========================================================
// GET /api/v1/zones - Get all zones
$router->addRoute('GET', '/api/v1/zones', withAuth(function($request) use ($zoneController) {
    return $zoneController->getZones($request);
}, $authMiddleware));

// GET /api/v1/zones/{zone_id} - Get single zone
$router->addRoute('GET', '/api/v1/zones/{id}', withAuth(function($request) use ($zoneController) {
    return $zoneController->getZone($request);
}, $authMiddleware));

// POST /api/v1/zones - Create new zone
$router->addRoute('POST', '/api/v1/zones', withAuth(function($request) use ($zoneController) {
    return $zoneController->createZone($request);
}, $authMiddleware));

// PUT /api/v1/zones/{zone_id} - Update zone
$router->addRoute('PUT', '/api/v1/zones/{id}', withAuth(function($request) use ($zoneController) {
    return $zoneController->updateZone($request);
}, $authMiddleware));

// DELETE /api/v1/zones/{zone_id} - Delete zone
$router->addRoute('DELETE', '/api/v1/zones/{id}', withAuth(function($request) use ($zoneController) {
    return $zoneController->deleteZone($request);
}, $authMiddleware));

// GET /api/v1/zones/{zone_id}/tables - Get tables for a zone
$router->addRoute('GET', '/api/v1/zones/{id}/tables', withAuth(function($request) use ($zoneController) {
    return $zoneController->getZoneTables($request);
}, $authMiddleware));

// ========================================================
// KITCHEN STATION ROUTES
// ========================================================
// GET /api/v1/kitchen-stations - Get all kitchen stations
$router->addRoute('GET', '/api/v1/kitchen-stations', withAuth(function($request) use ($kitchenStationController) {
    return $kitchenStationController->getKitchenStations($request);
}, $authMiddleware));

// GET /api/v1/kitchen-stations/{station_id} - Get single kitchen station
$router->addRoute('GET', '/api/v1/kitchen-stations/{id}', withAuth(function($request) use ($kitchenStationController) {
    return $kitchenStationController->getKitchenStation($request);
}, $authMiddleware));

// POST /api/v1/kitchen-stations - Create new kitchen station
$router->addRoute('POST', '/api/v1/kitchen-stations', withAuth(function($request) use ($kitchenStationController) {
    return $kitchenStationController->createKitchenStation($request);
}, $authMiddleware));

// PUT /api/v1/kitchen-stations/{station_id} - Update kitchen station
$router->addRoute('PUT', '/api/v1/kitchen-stations/{id}', withAuth(function($request) use ($kitchenStationController) {
    return $kitchenStationController->updateKitchenStation($request);
}, $authMiddleware));

// DELETE /api/v1/kitchen-stations/{station_id} - Delete kitchen station
$router->addRoute('DELETE', '/api/v1/kitchen-stations/{id}', withAuth(function($request) use ($kitchenStationController) {
    return $kitchenStationController->deleteKitchenStation($request);
}, $authMiddleware));

// GET /api/v1/kitchen-stations/central - Get central kitchens
$router->addRoute('GET', '/api/v1/kitchen-stations/central', withAuth(function($request) use ($kitchenStationController) {
    return $kitchenStationController->getCentralKitchens($request);
}, $authMiddleware));
