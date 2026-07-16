<?php

// Quality Control Routes
$router->addRoute('POST', '/api/v1/quality/checks', withAuth(
    function($request) use ($qualityControlController) {
        return $qualityControlController->createQualityCheck($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/quality/checks', withAuth(
    function($request) use ($qualityControlController) {
        return $qualityControlController->getQualityChecks($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/quality/non-conformances', withAuth(
    function($request) use ($qualityControlController) {
        return $qualityControlController->getNonConformances($request);
    },
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/quality/non-conformances/{id}/status', withAuth(
    function($request) use ($qualityControlController) {
        return $qualityControlController->updateNonConformanceStatus($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/quality/metrics', withAuth(
    function($request) use ($qualityControlController) {
        return $qualityControlController->getQualityMetrics($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/quality/summary', withAuth(
    function($request) use ($qualityControlController) {
        return $qualityControlController->getSummary($request);
    },
    $authMiddleware
));

