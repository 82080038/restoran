<?php

// Quality Routes
$router->addRoute('POST', '/api/v1/quality/checks', withAuth(function($request) use ($qualityController) {
    return $qualityController->createCheck($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/quality/checks', withAuth(function($request) use ($qualityController) {
    return $qualityController->getChecks($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/quality/incidents', withAuth(function($request) use ($qualityController) {
    return $qualityController->createIncident($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/quality/incidents', withAuth(function($request) use ($qualityController) {
    return $qualityController->getIncidents($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/quality/incidents/{id}/resolve', withAuth(function($request) use ($qualityController) {
    return $qualityController->resolveIncident($request);
}, $authMiddleware));

