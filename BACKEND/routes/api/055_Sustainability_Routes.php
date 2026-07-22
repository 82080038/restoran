<?php

// Sustainability Routes
$router->addRoute('POST', '/api/v1/sustainability/waste', withAuth(function($request) use ($sustainabilityController) {
    return $sustainabilityController->recordWaste($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/sustainability/waste', withAuth(function($request) use ($sustainabilityController) {
    return $sustainabilityController->getWasteTracking($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sustainability/metrics', withAuth(function($request) use ($sustainabilityController) {
    return $sustainabilityController->recordMetrics($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/sustainability/metrics', withAuth(function($request) use ($sustainabilityController) {
    return $sustainabilityController->getSustainabilityMetrics($request);
}, $authMiddleware));

