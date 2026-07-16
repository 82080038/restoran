<?php

// Sustainability Routes
$router->addRoute('POST', '/api/v1/sustainability/waste', function($request) use ($sustainabilityController) {
    return $sustainabilityController->recordWaste($request);
});
$router->addRoute('GET', '/api/v1/sustainability/waste', function($request) use ($sustainabilityController) {
    return $sustainabilityController->getWasteTracking($request);
});
$router->addRoute('POST', '/api/v1/sustainability/metrics', function($request) use ($sustainabilityController) {
    return $sustainabilityController->recordMetrics($request);
});
$router->addRoute('GET', '/api/v1/sustainability/metrics', function($request) use ($sustainabilityController) {
    return $sustainabilityController->getSustainabilityMetrics($request);
});

