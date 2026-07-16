<?php

// Quality Routes
$router->addRoute('POST', '/api/v1/quality/checks', function($request) use ($qualityController) {
    return $qualityController->createCheck($request);
});
$router->addRoute('GET', '/api/v1/quality/checks', function($request) use ($qualityController) {
    return $qualityController->getChecks($request);
});
$router->addRoute('POST', '/api/v1/quality/incidents', function($request) use ($qualityController) {
    return $qualityController->createIncident($request);
});
$router->addRoute('GET', '/api/v1/quality/incidents', function($request) use ($qualityController) {
    return $qualityController->getIncidents($request);
});
$router->addRoute('POST', '/api/v1/quality/incidents/{id}/resolve', function($request) use ($qualityController) {
    return $qualityController->resolveIncident($request);
});

